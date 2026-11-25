# Design

## Context

Вкладка «Досвід» (`EditResumeExperiences`) редагує один `Repeater` `experiences` ([ResumeExperiencesRepeater](../../../app/Containers/AppSection/Resume/UI/Filament/Resources/Resumes/Schemas/ResumeExperiencesRepeater.php)). Збереження йде через `UpdateResumeAction`. У транзакції `ReplaceResumeExperiencesTask` видаляє й перестворює записи, а потім Action викликає `SyncStacksTask` для кожного створеного запису. Технології привʼязані поліморфно через `stackables` (трейт `HasStacks` у контейнері `Stack`), тож каскад FK їх не видаляє. Тому перед видаленням записів викликається `detachStacksFrom()`, як у `ReplaceResumeExperiencesTask`, так і в `DeleteResumeTask`. PDF будує `BuildResumeDocumentTask` у value-обʼєкти `Values/Pdf/*`, а рендерить `pdf/sections/experience.blade.php`. Мотивацію див. у proposal.md, розділ «Why».

## Goals / Non-Goals

**Goals:**
- Додати проєкти до запису досвіду, не змінюючи моделі «одна вкладка, одна кнопка Зберегти, повна заміна списку».
- Повторно використати `HasStacks`, `SyncStacksTask` і `StackSelect` без змін контейнера `Stack`.

**Non-Goals:**
- Зберігати ідентичність рядків між збереженнями (diff або upsert). Заміна і далі йде через видалення та створення.
- Спільний компонент із `ProfileProject` контейнера `Profile`.

## Decisions

### 1. Окрема таблиця `resume_experience_projects` з FK на `resume_experiences`
Колонки: `id`, `resume_experience_id` (FK, `cascadeOnDelete`), `name` string, `role` string nullable, `description` text nullable, `sort_order` unsigned int default 0, `is_visible` bool default true, timestamps, індекс `(resume_experience_id, sort_order)`. Модель `Models/ResumeExperienceProject` (`final`, `implements Stackable`, `use HasStacks`) з `belongsTo` на `ResumeExperience`. `ResumeExperience` отримує `projects(): HasMany` з `orderBy('sort_order')`, а `HasStacks`, `Stackable` і `description` з неї прибираються.

Альтернатива: JSON-колонка `projects` у `resume_experiences`. Її відкинуто, бо стек має посилатися на довідник технологій через `stackables`, а JSON цього не дає.

### 2. Міграції змінюються на місці
`2026_11_24_150000_create_resume_experiences_table` втрачає колонку `description`. Нова міграція `2026_11_24_150500_create_resume_experience_projects_table` іде одразу за нею й раніше за освіту. Резюме не в продакшені, тож alter-міграція не потрібна. Ціна цього рішення: локально треба запустити `migrate:fresh`.

Альтернатива: нова міграція `drop column` плюс `create`. Вона зайва, бо даних для збереження немає.

### 3. Вкладений `Repeater` усередині запису досвіду
У схемі `ResumeExperiencesRepeater` поля `stacks` і `description` замінюються на `Repeater::make('projects')` з полями `name` (required, max 255), `role` (max 255), `is_visible` (Toggle, default true), `StackSelect::make('stacks', 'Технології')` і `RichEditor description` з тим самим тулбаром, що був у досвіду, без вкладень. Налаштування: `collapsible()`, `collapsed()` для наявних елементів, `reorderableWithDragAndDrop()`, `defaultItems(0)`, `itemLabel` за назвою, `addActionLabel('Додати проєкт')`. Щоб не роздувати один клас, схему проєктів винесено в окремий `Schemas/ResumeExperienceProjectsRepeater` зі своїм `stateFrom()`. `ResumeExperiencesRepeater::stateFrom()` викликає його для `projects`.

Альтернатива: модальне вікно або окрема сторінка проєктів. Цей варіант користувач відхилив, бо він ламає повну заміну на одне збереження.

### 4. Заміна проєктів окремою задачею, оркестрація в Action
- `ReplaceResumeExperiencesTask` перед видаленням записів відʼєднує технології проєктів: `ResumeExperienceProject::detachStacksFrom(ResumeExperienceProject::query()->whereIn('resume_experience_id', $resume->experiences()->select('id')))`. Рядки проєктів видаляє каскад FK. Технології самого досвіду більше не відʼєднуються, бо їх немає.
- Нова `Tasks/CreateResumeExperienceProjectsTask::run(ResumeExperience $experience, list<array<string,mixed>> $projects): list<ResumeExperienceProject>` створює проєкти з `sort_order` за позицією.
- `UpdateResumeAction` і `CreateResumeAction` (обидва вже обробляють `experiences`) у тій самій транзакції викликають для кожного створеного запису цю задачу, а потім `SyncStacksTask` для кожного проєкту. Так задачі не викликають одна одну (правило Porto), а транзакція Action дає атомарність.
- `DeleteResumeTask` відʼєднує технології проєктів резюме тим самим запитом, що й у пункті вище, замість технологій досвіду.

Альтернатива: усе робить `ReplaceResumeExperiencesTask`. Її відкинуто, бо тоді задача або викликала б `SyncStacksTask`, що порушує Porto, або дублювала б синхронізацію.

### 5. PDF
- Новий `Values/Pdf/ResumeDocumentProject` (`name`, `role|null`, `descriptionHtml|null`, `stack: list<string>`). `ResumeDocumentExperience` втрачає `descriptionHtml` і `stack` та отримує `projects: list<ResumeDocumentProject>`.
- `BuildResumeDocumentTask` пропускає невидимі проєкти, а HTML і назви технологій обробляє тими самими хелперами `html()` і `stackNames()`.
- `FindUserResumeTask` і `EditResumeExperiences::fillForm` завантажують `experiences.projects.stacks`.
- `experience.blade.php`: заголовок картки іде одним рядком: зліва `position | company`, справа `location · type | period`. Далі для кожного проєкту виводиться назва жирним, потім `Role: …` (ключ `pdf.role`), опис і `Tech Stack: …` (наявний `pdf.tech_stack`). Нові ключі: `en` `'role' => 'Role'`, `uk` `'role' => 'Роль'`. Стилі додаються в шаблон `classic` (`.card__project`, `.card__project-name`, `.card__project-role`).

## Risks / Trade-offs

- [Довга форма при багатьох проєктах із rich-editor] → наявні проєкти згорнуті за замовчуванням, а запис досвіду теж згортний.
- [Сирітські рядки в `stackables`, бо поліморфна привʼязка не каскадиться] → явний `detachStacksFrom` для проєктів у заміні й у видаленні резюме плюс тести на обидва сценарії.
- [Зміна вже виконаної міграції ламає локальні бази] → `migrate:fresh` у кроках впровадження. Тестова база (`hunt-board_testing`) перестворюється тестами.
- [Повна заміна змінює `id` проєктів при кожному збереженні] → зовнішніх посилань на проєкти немає, тож це прийнятно, як і для досвіду.

## Migration Plan

1. Змінити міграцію досвіду й додати міграцію проєктів.
2. Локально запустити `docker compose exec app php artisan migrate:fresh`, а потім `create:admin`.
3. Відкат: повернути коміт і знову запустити `migrate:fresh`.
