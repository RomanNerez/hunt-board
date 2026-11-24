# Design

## Context

Контейнер `AppSection/Resume` уже має `Resume` з `links()`, `experiences()` і `educations()`, `ReplaceResume*Task`, `CreateResumeAction` / `UpdateResumeAction` (транзакція, ключі списків виймаються через `Arr::pull`, без ключа список не чіпається), `ResumeLinksRepeater` та інші repeater-и в `ResumeForm` і `EditResume::mutateFormDataBeforeFill`. Зміна повторює цей шаблон для мов, за прямим зразком посилань. Див. proposal.md.

## Goals / Non-Goals

**Goals:**
- Окрема таблиця й модель `ResumeLanguage` у контейнері `Resume`.
- Repeater без `Section`, збереження через Actions тією самою схемою, що й для посилань.

**Non-Goals:**
- Окремий ресурс Filament, показ у картці, експорт, звʼязок із `ProfileLanguage`, довідник мов.

## Decisions

1. **`bigint`.** `resume_languages.id` це `$table->id()`, `resume_id` це `foreignId('resume_id')->constrained()->cascadeOnDelete()`, як у `resume_links`.
2. **Колонки:** `language` string(255), `level` string(10) nullable, `level_label` string(255) nullable, `sort_order` unsigned integer, `is_visible` boolean default true, індекс `(resume_id, sort_order)`, унікальний індекс `(resume_id, language)`. `level` зберігається рядком, а не нативним enum БД: простіше міняти набір значень, валідність гарантує PHP enum з кастом. `string(255)` замість `text` з запиту: узгоджується з рештою таблиць контейнера й дає ліміт, який перевіряє валідація.
3. **Enum `ResumeLanguageLevel`** у `Resume/Enums/` (поруч із `ResumeKind` і `ResumeEmploymentType`): backed string, `implements HasLabel`, значення `a1`, `a2`, `b1`, `b2`, `c1`, `c2`, `native`; підписи «A1 Beginner», «A2 Elementary», «B1 Intermediate», «B2 Upper-Intermediate», «C1 Advanced», «C2 Proficient», «Рідна» (ті самі, що в `Profile\LanguageLevel`, для однакового вигляду в обох формах). Окремий від `Profile\LanguageLevel`: контейнери не залежать один від одного. Альтернатива: імпортувати enum із `Profile`: звʼязує контейнери.
4. **Унікальність двома рівнями.** Унікальний індекс гарантує цілісність у БД. У формі `TextInput language` має правило `distinct:ignore_case` у межах repeater, щоб користувач бачив помилку валідації, а не `QueryException`. Валідація суворіша за індекс (регістр не враховується), що припустимо: «Англійська» й «англійська» це одна мова. Альтернатива: лише індекс: давав би неінформативну помилку 500.
5. **Заміна списку й унікальний індекс.** `ReplaceResumeLanguagesTask` спочатку видаляє наявні мови, потім створює нові в одній транзакції Action, тому конфліктів з унікальним індексом при перестановці чи перейменуванні не виникає. Альтернатива: оновлення за `id`: ускладнює Task без користі.
6. **Зберігання повною заміною.** `sort_order` береться з позиції масиву. Actions виймають ключ `languages` разом з іншими списками в одній `DB::transaction`; без ключа список не змінюється. Task не викликає інші Tasks, оркестрація в Action. Альтернатива: `->relationship()` на Repeater: порушує правило «записи йдуть через Actions».
7. **Форма.** `ResumeLanguagesRepeater::make()` у `UI/Filament/Resources/Resumes/Schemas/`: `Repeater::make('languages')->label('Мови')`, `->reorderableWithDragAndDrop()`, `->defaultItems(0)`, `->addActionLabel('Додати мову')`, `->columnSpanFull()`, без `Section`, `->columns(2)`. Поля: `TextInput language` (required, max 255, distinct без регістру), `Select level` (опції з enum, необовʼязковий), `TextInput level_label` (max 255), `Toggle is_visible` (default true). `sort_order` у формі не показується. Хелпер `stateFrom()` будує стан форми з колекції, як у `ResumeLinksRepeater`.
8. **Заповнення форми.** `EditResume::mutateFormDataBeforeFill` додає `languages` із `$resume->load('languages')->languages` (`hasMany`, сортоване за `sort_order`; `load` через strict-режим).
9. **Політика.** Нова policy не потрібна: доступ контролює `ResumePolicy`.
10. **Правила коду.** Без коментарів, PHPDoc лише з тегами, `final`, `ResumeLanguageFactory`, `ResumeLanguageRepository`, `Resume::languages()`.

## Risks / Trade-offs

- [Повна заміна змінює `id` записів при кожному збереженні] → на записи ніхто не посилається; за потреби перейти на синхронізацію за `id`.
- [Select може повертати enum-обʼєкт замість рядка] → Task приводить значення до `->value`, як у `ReplaceResumeExperiencesTask`.
- [Назва мови вільним текстом, тож «Англійська» й «English» вважаються різними] → довідник мов поза обсягом; унікальність лише за введеним текстом.
- [Таблиця `resume_languages` і морфологія Laravel] → «language» має звичайну множину, явний `$table` не потрібен.

## Migration Plan

Одна міграція створює таблицю; відкат `Schema::dropIfExists('resume_languages')`. Наявні резюме отримують порожній список.
