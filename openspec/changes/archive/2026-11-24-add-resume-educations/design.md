# Design

## Context

Контейнер `AppSection/Resume` уже має `Resume` з `links()` і `experiences()`, `ReplaceResumeLinksTask`, `ReplaceResumeExperiencesTask`, `CreateResumeAction` / `UpdateResumeAction` (транзакція, ключі `links` і `experiences` виймаються через `Arr::pull`, без ключа список не чіпається), `ResumeLinksRepeater` і `ResumeExperiencesRepeater` у `ResumeForm` та `EditResume::mutateFormDataBeforeFill`. Зміна повторює цей шаблон для освіти. Див. proposal.md.

## Goals / Non-Goals

**Goals:**
- Окрема таблиця й модель `ResumeEducation` у контейнері `Resume`.
- Repeater без `Section`, збереження через Actions тією самою схемою, що й для досвіду.

**Non-Goals:**
- Окремий ресурс Filament, показ у картці, експорт, звʼязок із `ProfileEducation`, технології запису.

## Decisions

1. **`bigint`, а не `uuid`.** `resume_educations.id` це `$table->id()`, `resume_id` це `foreignId('resume_id')->constrained()->cascadeOnDelete()`, бо `resumes.id` вже `bigint`. Альтернатива: `uuid`: не підходить для ключа на `resumes`.
2. **Колонки:** `institution` string(255), `institution_url` string(2048) nullable, `degree` string(255) nullable, `field_of_study` string(255) nullable, `location` string(255) nullable, `start_date` date nullable, `end_date` date nullable, `description` text nullable, `sort_order` unsigned integer, `is_visible` boolean default true, індекс `(resume_id, sort_order)`. Запит наводив `text` для коротких полів: `string(255)` узгоджується з `resume_experiences` і дає ліміт, який перевіряє валідація.
3. **Без enum для ступеня.** `degree` вільний текст: набір значень («Молодший спеціаліст», «Бакалавр», сертифікат курсу) відкритий, а для курсу поле порожнє. Альтернатива: enum на кшталт `EducationType` профілю: не потрібен, бо тип закладу не входить у вимоги.
4. **Дати.** Модель кастує `start_date` / `end_date` у `date`. У формі `DatePicker` з `native(false)` і `displayFormat('m.Y')`; у `ReplaceResumeEducationsTask` дата нормалізується через `startOfMonth()`, порожнє значення дає `null`. Валідація `end_date`: `afterOrEqual('start_date')`; Laravel пропускає це правило, коли `start_date` порожня. Альтернатива: окремі поля місяць/рік: незручніше й не відповідає колонці `date`.
5. **Зберігання повною заміною.** `ReplaceResumeEducationsTask` видаляє наявні записи й створює їх з форми, `sort_order` береться з позиції масиву. Actions виймають ключ `educations` разом із `links` і `experiences` в одній `DB::transaction`; без ключа список не змінюється. Task не викликає інші Tasks, оркестрація в Action. Технологій немає, тож `SyncStacksTask` і `detachStacksFrom` не потрібні. Альтернатива: `->relationship()` на Repeater: порушує правило «записи йдуть через Actions».
6. **Форма.** `ResumeEducationsRepeater::make()` у `UI/Filament/Resources/Resumes/Schemas/`: `Repeater::make('educations')->label('Освіта')`, `->reorderableWithDragAndDrop()`, `->defaultItems(0)`, `->addActionLabel('Додати освіту')`, `->itemLabel()` за закладом і спеціальністю, `->collapsible()`, `->columnSpanFull()`, без `Section`. Поля: `TextInput institution` (required, max 255), `TextInput institution_url` (`startsWith(['http://', 'https://'])`, max 2048), `TextInput degree` (max 255), `TextInput field_of_study` (max 255), `TextInput location` (max 255), `Toggle is_visible` (default true), `DatePicker start_date`, `DatePicker end_date`, `RichEditor description` (HTML; мінімальний тулбар `[['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']]`, `fileAttachments(false)`, як у `ResumeExperiencesRepeater`). `sort_order` у формі не показується. Хелпер `stateFrom()` будує стан форми з колекції.
7. **Заповнення форми.** `EditResume::mutateFormDataBeforeFill` додає `educations` із `$resume->load('educations')->educations` (`hasMany`, сортоване за `sort_order`; `load` через strict-режим).
8. **Незалежність від `Profile`.** Контейнер `Resume` не посилається на `ProfileEducation`: контейнери не залежать один від одного, а набір полів інший. Імпорт із профілю можна додати окремою зміною.
9. **Політика.** Нова policy не потрібна: доступ контролює `ResumePolicy`.
10. **Правила коду.** Без коментарів, PHPDoc лише з тегами, `final`, `ResumeEducationFactory`, `ResumeEducationRepository`, `Resume::educations()`.

## Risks / Trade-offs

- [Повна заміна змінює `id` записів при кожному збереженні] → на записи ніхто не посилається; за потреби перейти на синхронізацію за `id`.
- [Порядок не залежить від дат] → користувач керує ним перетягуванням, як у посиланнях і досвіді.
- [Небезпечні схеми в `institution_url`] → дозволені лише `http` і `https`.
- [Дві дати можуть бути порожніми, тож порядок і підпис періоду неоднозначні] → порядок задає `sort_order`; відображення періоду відкладено до експорту.

## Migration Plan

Одна міграція створює таблицю; відкат `Schema::dropIfExists('resume_educations')`. Наявні резюме отримують порожній список.
