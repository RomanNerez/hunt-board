# Design

## Context

Мотивацію описано в proposal.md, розділ «Why». Вимоги — у `specs/profile-projects`, `specs/profile-work-experience`, `specs/profile-translations`.

- Досвід роботи вже є: модель `ProfileWorkExperience` (`profile_work_experiences`), простий ресурс `WorkExperienceResource` з однією сторінкою `ManageWorkExperiences` (`ManageRecords`) у кластері `ProfileCluster`, модальні Create/Edit/Delete через Actions контейнера `Profile`.
- Перекладні поля зберігаються в `jsonb` через `spatie/laravel-translatable` (трейт моделі `HasProfileTranslations`). Tasks нормалізують їх трейтом `NormalizesTranslations` (обрізання, порожнє значення відкидається, fallback на українську). Перемикач мови даних живе в трейті `UI/Filament/Concerns/InteractsWithDataLocale` (`translatable()`, `dataLocaleActions()`, `onValidationError()`), а поля показуються через `visibleJs("$wire.dataLocale === '…'")`.
- Списки тегів (`TagsInput`) і нормалізація технологій уже є в «Навичках» (`ReplaceProfileSkillGroupsTask`).
- У Filament 5 є `ManageRelatedRecords`: сторінка ресурсу з параметром `{record}`, яка показує таблицю пов'язаних записів і модальні дії в тому самому Livewire-компоненті. Запис вона дістає через `Resource::resolveRecordRouteBinding()`, тобто через `getEloquentQuery()` ресурсу, а доступ перевіряє через `canAccess()` (`viewAny` на моделі зв'язку).
- `RichEditor` у Filament 5 зберігає HTML (або JSON з `->json()`), підтримує `maxLength()` і налаштування `toolbarButtons()`. Його вихід — сирий HTML, який перед показом треба санітизувати; Filament дає `Str::sanitizeHtml()`.
- Користувач вибрав керування проєктами всередині досвіду роботи й мінімальний набір полів: назва, дата початку, дата завершення, стек, опис. Дати проєкту не прив'язані до періоду роботи. Назва й опис перекладаються.

## Goals / Non-Goals

**Goals:**
- Окрема сторінка проєктів для кожного запису досвіду роботи, без зміни модальної логіки самого досвіду роботи.
- Мінімальна форма проєкту, у якій опис пишеться одним форматованим текстом.

**Non-Goals:**
- Перетворювати `WorkExperienceResource` на ресурс з окремими сторінками Create/Edit.
- Окремий ресурс «Проєкти» в субнавігації профілю.
- Узгодження дат проєкту з періодом роботи.
- Структуровані поля для AI чи резюме (роль, формат роботи, опитувальник, досягнення).

## Decisions

### 1. Сутність `ProfileProject` у контейнері `Profile`
Модель, міграція, фабрика, репозиторій, Tasks, Actions, Policy й сторінка лежать у `app/Containers/AppSection/Profile`.
- **Чому:** проєкт є частиною досвіду роботи, використовує ті самі трейти перекладів і перемикач мови, кластер і ресурс `WorkExperienceResource`.
- **Альтернатива:** новий контейнер `Project`. Відкинуто з тих самих причин, що й для досвіду роботи: він залежав би від внутрішніх трейтів і кластера `Profile`.

### 2. Схема даних
Таблиця `profile_projects`:

| Колонка | Тип | Примітка |
|---|---|---|
| `id` | bigint | |
| `work_experience_id` | `foreignId`, `constrained('profile_work_experiences')`, `cascadeOnDelete` | власник |
| `name` | `jsonb` | перекладна, обов'язкова для `uk` |
| `started_on` | `date` | |
| `finished_on` | `date`, nullable | порожня = триває |
| `technologies` | `json` | `list<string>`, у UI — «Стек», як `profile_skill_groups.skills` |
| `description` | `jsonb`, nullable | перекладний HTML |
| `timestamps` | | |

Індекс `(work_experience_id, started_on)`. Колонки `user_id` немає: власник визначається через `work_experience_id`.
- **Чому:** `cascadeOnDelete` виконує вимогу «Видалення досвіду роботи разом із проєктами» на рівні БД, а через каскад `profile_work_experiences → users` проєкти видаляються й разом із користувачем. Колонку стеку названо `technologies`, бо вона зберігає саме список технологій; «Стек» — лише мітка поля.
- **Альтернатива:** дублювати `user_id` у `profile_projects`. Відкинуто: два джерела власника можуть розійтися, а запити й так ідуть від запису досвіду роботи.
- Міграція ще не закомічена, тому її файл переписується, а не доповнюється новою міграцією; у dev-БД — `migrate:rollback --step=1` і `migrate`.

### 3. «Опис» як `RichEditor` із HTML
`RichEditor` через `translatable('description', …)` з панеллю `bold`, `italic`, `link`, `bulletList`, `orderedList`, `undo`, `redo`, без вкладень файлів, `maxLength(20000)`. Зберігається HTML (`->json()` не вмикається).
- Task санітизує кожну мову через `Str::sanitizeHtml()` і вважає порожнім значення, у якому після `strip_tags` лишаються самі пробіли (наприклад, `<p></p>`), тож воно відкидається так само, як порожній рядок у `NormalizesTranslations`.
- **Чому:** HTML найпростіше читати й показувати (з тим самим санітизатором), а санітизація під час збереження захищає дані від підробленого стану Livewire.
- **Альтернатива:** `->json()` (TipTap JSON). Відкинуто: для показу знадобився б рендерер, а редагувати JSON поза формою незручно.
- **Альтернатива:** `MarkdownEditor`. Відкинуто: користувач просив форматований текст.

### 4. Сторінка `ManageWorkExperienceProjects` у `WorkExperienceResource`
`UI/Filament/Resources/WorkExperiences/Pages/ManageWorkExperienceProjects.php` extends `ManageRelatedRecords`, `$relationship = 'projects'`, трейт `InteractsWithDataLocale`. Реєструється в `WorkExperienceResource::getPages()` як `'projects' => ManageWorkExperienceProjects::route('/{record}/projects')`. Форма й таблиця — у `Schemas/ProjectForm.php` і `Tables/ProjectsTable.php` поруч із наявними класами ресурсу.
- `WorkExperiencesTable` отримує record action `Action::make('projects')` «Проєкти» з `->url(fn ($record) => WorkExperienceResource::getUrl('projects', ['record' => $record]))`.
- Заголовок сторінки — «Проєкти: <компанія / проєкт активною мовою>», breadcrumb — «Профіль › Досвід роботи › <запис> › Проєкти».
- `ManageRelatedRecords` за замовчуванням не показує субнавігацію кластера (її будує лише `ListRecords`), тому сторінка перевизначає `getSubNavigation()` так само, як `ListRecords`; активним лишається «Досвід роботи», бо сторінка належить тому самому ресурсу.
- Модальне вікно форми — `->slideOver()`. Угорі стоїть перемикач мови (`dataLocaleActions()`), далі «Назва проєкту», «Дата початку», «Дата завершення» (`afterOrEqual('started_on')`), «Стек» (`TagsInput`), «Опис» на всю ширину.
- Колонки таблиці: «Проєкт» (активна мова з fallback, `translationWithFallback()`), «Період» (`m.Y — m.Y` / `m.Y — дотепер`), «Стек» (badge, `limitList(4)`), `defaultSort` `started_on desc, id desc`.
- Період «m.Y — дотепер» форматує спільний хелпер `UI/Filament/Support/PeriodFormatter`, який використовує й `WorkExperiencesTable`.
- **Чому:** `ManageRelatedRecords` — повноцінна сторінка, тож модальні дії й `$wire.dataLocale` працюють у тому самому Livewire-компоненті, що й на `ManageWorkExperiences`. Модальний CRUD досвіду роботи не змінюється.
- **Альтернатива:** `EditWorkExperience` + `RelationManager`. Відкинуто: довелося б перетворити простий ресурс на ресурс зі сторінками, а `RelationManager` є окремим Livewire-компонентом без `$dataLocale` сторінки.
- **Альтернатива:** вкладений ресурс (`$parentResource`). Відкинуто: окремий ресурс, сторінки й маршрути заради однієї таблиці з модальними вікнами.

### 5. Валідація
- Форма: `name.uk` — `required`, `maxLength(255)`; `started_on` — `required`; `finished_on` — `afterOrEqual('started_on')`; кожна технологія — `max:100`; `description.*` — `maxLength(20000)`.
- Task дублює інваріант порядку дат: якщо `started_on` порожня або `finished_on` раніша за неї, кидає `ValidationException::withMessages([...])` з ключами `started_on` / `finished_on`. Для `UpdateProfileProjectTask` відсутні в даних дати беруться зі збереженого проєкту.
- Дати проєкту не порівнюються з датами запису досвіду роботи, а `UpdateProfileWorkExperienceAction` не перевіряє проєкти.
- **Чому:** форма дає повідомлення під полем, а Task захищає дані, коли Action викликають не з Filament (CLI, майбутній AI-генератор).

### 6. Actions, Tasks, репозиторій
- `Actions/CreateProfileProjectAction::run(int $workExperienceId, array $data): ProfileProject`;
- `Actions/UpdateProfileProjectAction::run(int $id, array $data): ProfileProject`;
- `Actions/DeleteProfileProjectAction::run(int $id): bool`;
- `Tasks/CreateProfileProjectTask`, `UpdateProfileProjectTask`, `DeleteProfileProjectTask` через `Data/Repositories/ProfileProjectRepository`.

Create/Update Tasks беруть `Arr::only(['name', 'started_on', 'finished_on', 'technologies', 'description'])` (без `work_experience_id`), нормалізують `name` через `NormalizesTranslations`, `description` — за рішенням 3, а технології — трейтом `Tasks/Concerns/NormalizesStringList`, винесеним із `ReplaceProfileSkillGroupsTask`. Спільна підготовка даних живе в трейті `Tasks/Concerns/PreparesProfileProjectData`. Actions обгортають роботу в `DB::transaction`. `work_experience_id` береться з `getOwnerRecord()` сторінки, а не з даних форми.

### 7. Авторизація
`Policies/ProfileProjectPolicy`: `viewAny`, `create`, `deleteAny` → `true`; `view`, `update`, `delete` → `$user->id === $project->workExperience->user_id` (зв'язок підвантажується через `loadMissing`). Сторінка додатково перевизначає `canAccess()`: доступ лише якщо `record` належить поточному користувачу (`ProfileWorkExperiencePolicy::update`). Запис досвіду роботи на сторінці й так дістається через обмежений `getEloquentQuery()` ресурсу, тому чужий `id` дає 404. Таблиця читає проєкти лише через зв'язок `projects()` власника й підвантажує `workExperience` для Policy.
- **Чому:** Policy — справжній контроль доступу (`canAccessPanel()` пускає всіх). Шар запиту ховає чужі записи, а Policy відхиляє дії з підставленим `id`.

### 8. Зв'язки
`ProfileWorkExperience::projects(): HasMany` з сортуванням `started_on desc, id desc`; `ProfileProject::workExperience(): BelongsTo`. `Model::shouldBeStrict()` забороняє lazy loading, тому зв'язок для Policy й таблиці підвантажується явно.

### 9. Попередження під час видалення досвіду роботи
`DeleteAction` і `DeleteBulkAction` у `WorkExperiencesTable` отримують `->modalDescription('Разом із записом буде видалено всі його проєкти. Цю дію не можна скасувати.')`. Видалення проєктів виконує `cascadeOnDelete` БД, `DeleteProfileWorkExperienceAction` не змінюється.

### 10. Помилки з Task під полями модального вікна
Filament перекидає `ValidationException`, кинуту з `->using()`, з оригінальними ключами (`finished_on`), а поле модального вікна чекає `mountedActions.{N}.data.finished_on`. Хелпер UI-шару `UI/Filament/Support/MountedActionValidation::run(Action $action, Closure $callback)` перехоплює виняток і перекидає його з ключами, перед якими стоїть statePath змонтованої дії (`$action->getNestingIndex()`). Ним обгорнуто `->using()` створення й редагування проєктів.
- **Чому:** помилку інваріанту з Task видно під полем, і модальне вікно лишається відкритим.

## Risks / Trade-offs

- [`RichEditor` віддає сирий HTML, який можна підробити в стані Livewire] → санітизація в Task під час збереження; майбутній показ опису поза формою теж має йти через `Str::sanitizeHtml()` або `RichContentRenderer`.
- [Обмеження 20 000 символів рахує й розмітку] → для опису проєкту цього досить із запасом; число легко змінити у формі.
- [`ManageRelatedRecords::canAccess()` перевіряє лише `viewAny` моделі зв'язку] → перевизначити `canAccess()` з перевіркою власника й покрити тестом доступу до чужого запису.
- [Без прив'язки дат проєкт може виходити за період роботи] → це свідомий вибір користувача; таблиця показує період проєкту, тож розбіжність видно.
- [Каскадне видалення видаляє проєкти без явного Action] → для поточних вимог побічних ефектів немає (подій, файлів). Якщо з'являться, видалення проєктів слід перенести в `DeleteProfileWorkExperienceAction`.

## Migration Plan

Нова таблиця, наявні дані не змінюються. Відкат — `down()` міграції (`dropIfExists('profile_projects')`) і видалення сторінки з `getPages()`.
