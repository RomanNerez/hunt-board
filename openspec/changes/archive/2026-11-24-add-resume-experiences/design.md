# Design

## Context

Контейнер `AppSection/Resume` уже має `Resume` з `links()`, `ReplaceResumeLinksTask`, `CreateResumeAction` / `UpdateResumeAction` (транзакція, ключ `links` виймається через `Arr::pull`, без ключа список не чіпається), `ResumeLinksRepeater` у `ResumeForm` і `EditResume::mutateFormDataBeforeFill`. Зміна повторює цей шаблон для досвіду роботи. Див. proposal.md.

## Goals / Non-Goals

**Goals:**
- Окрема таблиця й модель `ResumeExperience` у контейнері `Resume`.
- Repeater без `Section`, збереження через Actions тією самою схемою, що й для посилань.

**Non-Goals:**
- Окремий ресурс Filament, показ у картці, експорт, звʼязок із `profile-work-experience`.

## Decisions

1. **`bigint`, а не `uuid`.** `resume_experiences.id` це `$table->id()`, `resume_id` це `foreignId('resume_id')->constrained()->cascadeOnDelete()`, бо `resumes.id` вже `bigint`. Альтернатива: `uuid`: не підходить для ключа на `resumes`.
2. **Колонки:** `company` string(255), `company_url` string(2048) nullable, `position` string(255), `location` string(255) nullable, `employment_type` string(20) nullable, `start_date` date, `end_date` date nullable, `description` text nullable, `sort_order` unsigned integer, `is_visible` boolean default true, індекс `(resume_id, sort_order)`. `employment_type` зберігається рядком, а не нативним enum БД: простіше міняти набір значень, валідність гарантує PHP enum з кастом.
3. **Enum `ResumeEmploymentType`** у `Resume/Enums/` (поруч із `ResumeKind`): backed string, `implements HasLabel`, значення `full_time`, `part_time`, `contract`, `freelance`, `internship`; підписи: «Повна зайнятість», «Часткова зайнятість», «Контракт», «Фріланс», «Стажування». Окремий від `Profile\WorkExperienceType`, бо набір значень інший, а контейнери не мають залежати один від одного.
4. **Дати.** Модель кастує `start_date` / `end_date` у `date` (`CarbonImmutable` за проєктом). У формі `DatePicker` з форматом місяця (`displayFormat('m.Y')`, обрання першого числа); у `ReplaceResumeExperiencesTask` дата нормалізується через `startOfMonth()`. Валідація `end_date`: `afterOrEqual('start_date')`. Альтернатива: окремі поля місяць/рік: незручніше й не відповідає колонці `date`.
5. **Зберігання повною заміною.** `ReplaceResumeExperiencesTask` видаляє наявні записи й створює їх з форми, `sort_order` береться з позиції масиву. Actions виймають ключ `experiences` разом із `links` в одній `DB::transaction`; без ключа список не змінюється. Task не викликає інші Tasks, оркестрація в Action. Альтернатива: `->relationship()` на Repeater: порушує правило «записи йдуть через Actions».
6. **Форма.** `ResumeExperiencesRepeater::make()` у `UI/Filament/Resources/Resumes/Schemas/`: `Repeater::make('experiences')->label('Досвід роботи')`, `->reorderableWithDragAndDrop()`, `->defaultItems(0)`, `->addActionLabel('Додати досвід')`, `->itemLabel()` за компанією й посадою, `->collapsible()`, `->columnSpanFull()`, без `Section`. Поля: `TextInput company` (required, max 255), `TextInput company_url` (url, `startsWith(['http://', 'https://'])`, max 2048), `TextInput position` (required, max 255), `TextInput location` (max 255), `Select employment_type` (опції з enum), `DatePicker start_date` (required), `DatePicker end_date`, `StackSelect stacks` (технології з налаштувань, множинний вибір зі створенням), `RichEditor description` (HTML; мінімальний тулбар, що підтримує PDF: жирний, курсив, посилання, марковані й нумеровані списки, undo/redo; без вкладень файлів), `Toggle is_visible` (default true). `sort_order` у формі не показується. Хелпер `stateFrom()` будує стан форми з колекції, як у `ResumeLinksRepeater`.
7. **Заповнення форми.** `EditResume::mutateFormDataBeforeFill` додає `experiences` із `$resume->load('experiences')->experiences` (`hasMany`, сортоване за `sort_order`; `load` через strict-режим).
8. **Технології.** `ResumeExperience` реалізує `Stackable` і використовує `HasStacks` (поліморфна таблиця `stackables`, morph-аліас `resume_experience` у `ResumeServiceProvider`). `ReplaceResumeExperiencesTask` повертає створені записи, а Action синхронізує технології кожного через `SyncStacksTask` (порядок вибору зберігається в `sort`). Перед видаленням записів (заміна списку, видалення резюме) технології відʼєднуються через `detachStacksFrom`, бо каскад БД не чіпає `stackables`. Форма заповнюється через `load('experiences.stacks')`.
9. **Політика.** Нова policy не потрібна: доступ контролює `ResumePolicy`.
10. **Правила коду.** Без коментарів, PHPDoc лише з тегами, `final`, `ResumeExperienceFactory`, `ResumeExperienceRepository`, `Resume::experiences()`.

## Risks / Trade-offs

- [Повна заміна змінює `id` записів при кожному збереженні] → на записи ніхто не посилається; за потреби перейти на синхронізацію за `id`.
- [Порядок не залежить від дат] → користувач керує ним перетягуванням, як у посиланнях.
- [Небезпечні схеми в `company_url`] → дозволені лише `http` і `https`.
- [Розмір форми з багатьма полями на запис] → `collapsible` з підписом запису.

## Migration Plan

Одна міграція створює таблицю; відкат `Schema::dropIfExists('resume_experiences')`. Наявні резюме отримують порожній список.
