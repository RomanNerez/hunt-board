# Design

## Context

- Мотивація та вимоги — proposal.md і дельта `resumes`.
- Зразок карток — `Vacancy/UI/Filament/Resources/Vacancies/Tables/VacancyNotesTable.php`: `Stack` + `Split`, `contentGrid(['default' => 1])`, клас `hb-vacancy-notes` на таблиці й CSS `Vacancy/UI/WEB/Assets/css/vacancy-notes.css`, зареєстрований у `VacancyServiceProvider` через `FilamentAsset::register()` (опублікована копія — `public/css/app`, `php artisan filament:assets`).
- Зразок власності записів за користувачем — `Profile/.../WorkExperienceResource::getEloquentQuery()` (`where('user_id', Auth::id())`).
- Зразок контейнера із записами верхнього рівня — `Contact` (Resource, Policy, Actions/Tasks, Repository, Factory, `composer.json`). Окремі сторінки створення/редагування в проєкті ще не використовуються (там модалки), тож беремо стандартні `CreateRecord`/`EditRecord` Filament з перевизначеним збереженням (AGENTS.md).
- Profile уже має `profile-resume` (PDF із профілю) — це інша сутність; нова змін у Profile не потребує.
- Рішення користувача: новий контейнер `Resume`, нова секція навігації, картки замість таблиці, create/edit на окремих сторінках.

## Goals / Non-Goals

**Goals:**
- Контейнер `Resume` за Porto: UI → Action → Task → Repository/Model.
- Картки з тим самим виглядом, що й нотатки вакансії.
- Усі записи — через Actions; доступ — через Policy та скоуп запиту.

**Non-Goals:**
- Секції резюме, звʼязок із вакансіями, PDF, копіювання master → application, сторінка View.
- Спільний CSS-пакет для карток у `Ship`: два споживачі з різними префіксами класів — передчасно.

## Decisions

### 1. Контейнер і структура
`app/Containers/AppSection/Resume/`:
- `Models/Resume.php`, `Enums/ResumeKind.php`, `Data/Migrations/2026_11_24_130000_create_resumes_table.php`, `Data/Factories/ResumeFactory.php`, `Data/Repositories/ResumeRepository.php`;
- `Actions/{Create,Update,Delete}ResumeAction.php`, `Tasks/{Create,Update,Delete}ResumeTask.php`;
- `Policies/ResumePolicy.php`, `Providers/ResumeServiceProvider.php`, `composer.json`;
- `UI/Filament/Resources/Resumes/{ResumeResource.php, Pages/{ListResumes,CreateResume,EditResume}.php, Schemas/ResumeForm.php, Tables/ResumesTable.php}`;
- `UI/WEB/Assets/css/resume-cards.css`;
- `Tests/{Unit,Functional}` з `ContainerTestCase`, `UnitTestCase`, `FunctionalTestCase` за зразком `Contact`.

*Альтернатива:* резюме в контейнері `Profile` — відкинуто за вимогою користувача (окремий контейнер і розділ) та щоб не змішувати з профілем.

### 2. Дані й модель
- `resumes`: `id`, `user_id` FK `constrained()->cascadeOnDelete()`, `kind` string(20), `first_name` string(255), `last_name` string(255), `headline` string(255) nullable, `summary` text nullable, timestamps; індекс `['user_id', 'updated_at']` під вибірку списку.
- `kind` — string, а не DB-enum: переносимо між СУБД, значення обмежує PHP-enum `ResumeKind` (`Master = 'master'`, `Application = 'application'`) з `HasLabel` («Основне», «Під вакансію») і `HasColor`; cast у моделі.
- `Resume`: `$fillable = ['user_id', 'kind', 'first_name', 'last_name', 'headline', 'summary']` (strict models вимагають fillable `user_id`; зміну власника блокує `Arr::only` в Update-Task), `user(): BelongsTo`.
- `summary` — звичайний `Textarea` без rich text: у вимогах не просили форматування, тож санітизація HTML не потрібна.
- Тип у БД не унікальний: «одне master на користувача» вимогами не задано (див. Risks).

### 3. Actions і Tasks
- `CreateResumeAction::run(int $userId, array $data): Resume` → `CreateResumeTask` (`repository->create([...Arr::only($data, [...]), 'user_id' => $userId])`).
- `UpdateResumeAction::run(int $id, array $data): Resume` → `UpdateResumeTask` (`Arr::only($data, ['kind', 'first_name', 'last_name', 'headline', 'summary'])`; власника не змінює).
- `DeleteResumeAction::run(int $id): bool` → `DeleteResumeTask`.
- Транзакції не потрібні: одна дія — один запис.

### 4. Ресурс і навігація
- `ResumeResource`: `$model = Resume::class`, `$navigationIcon = Heroicon::OutlinedDocumentText`, `$navigationSort = 15` (між «Вакансії» 10 і «Компанії» 20), мітки «Резюме» / «Резюме» / «Створити резюме», `recordTitleAttribute = 'last_name'`. Верхній рівень без кластера: це самостійний розділ.
- `getEloquentQuery()` → `parent::getEloquentQuery()->where('user_id', Auth::id())`; чуже резюме на `/edit` дає 404 через `resolveRecordRouteBinding` (за зразком `VacancyResource` — лише цифрові ключі).
- `getPages()`: `index` → `ListResumes`, `create` → `CreateResume` (`/create`), `edit` → `EditResume` (`/{record}/edit`). Сторінки `view` немає.

### 5. Окремі сторінки створення/редагування (не модалки)
- `CreateResume::handleRecordCreation(array $data): Model` → `app(CreateResumeAction::class)->run((int) Auth::id(), $attributes)`; `getRedirectUrl()` → `index`.
- `EditResume::handleRecordUpdate(Model $record, array $data): Model` → `app(UpdateResumeAction::class)->run($record->getKey(), $attributes)`; `getRedirectUrl()` → `index`; у `getHeaderActions()` `DeleteAction::make()->using(...DeleteResumeAction...)` з редіректом на список.
- Форма `ResumeForm::configure(Schema)` спільна для обох сторінок: `Select kind` (`ResumeKind`, `required`, `default(Master)`, `native(false)`), `TextInput first_name`/`last_name` (`required`, `maxLength(255)`, дві колонки), `TextInput headline` (`maxLength(255)`), `Textarea summary` (`rows(6)`, `columnSpanFull`).
- Атрибути з `$data` присвоюються змінній під `/** @var array<string, mixed> $attributes */` (AGENTS.md).

*Альтернатива:* простий ресурс із модалками (`ManageResumes`) — відкинуто за вимогою користувача.

### 6. Список-картки `ResumesTable`
- `columns([Stack::make([Split::make([TextColumn full name (state = first_name + last_name; weight Bold, size Large), TextColumn kind (`badge()`, `alignEnd()`, `grow(false)`)])->from('sm'), TextColumn headline (`color('gray')`), TextColumn summary (`wrap()`, без `limit()`)])->space(2)])`, `contentGrid(['default' => 1])`.
- Повне імʼя — `TextColumn::make('first_name')->formatStateUsing(fn (Resume $record) => "{$record->first_name} {$record->last_name}")`.
- Сортування: `modifyQueryUsing(fn (Builder $q) => $q->orderByDesc('updated_at')->orderByDesc('id'))`; користувацького сортування, пошуку, фільтрів немає.
- `recordUrl(fn (Resume $record) => ResumeResource::getUrl('edit', ...))`; `recordActions([EditAction::make(), DeleteAction::make()->using(...DeleteResumeAction...)])`. `EditAction` без модалки веде на сторінку редагування, бо ресурс має сторінку `edit`.
- Клас `hb-resume-cards` через `extraAttributes`; CSS `resume-cards.css` — копія правил `vacancy-notes.css` із префіксом `hb-resume-cards` (прибирає спільну обгортку таблиці, робить кожну картку, пагінацію й порожній стан білими блоками з тінню). Реєстрація — у `ResumeServiceProvider::boot()` через `FilamentAsset::register()`; після змін — `php artisan filament:assets`.
- Порожній стан: `emptyStateHeading('Резюме ще немає')`, опис, дія «Створити резюме» (`CreateAction` з `url()` на `create`).
- Кнопка «Створити резюме» у шапці `ListResumes` — `CreateAction::make()` без `slideOver`/`using`: веде на сторінку створення.

*Альтернатива:* спільний CSS «cards» з `Ship` — відкинуто в Non-Goals; *альтернатива:* власний Blade-список — відкинуто: таблиця дає пагінацію й дії записів.

### 7. Авторизація
`ResumePolicy`: `viewAny`/`create` → `true`; `view`/`update`/`delete` → `$resume->user_id === $user->id`. Скоуп запиту з рішення 4 — перший бар'єр, Policy — другий (Filament перевіряє автоматично).

### 8. Тести (PHPUnit, `Livewire::test`)
- Unit: `ResumeKind` (мітки/колір), міграція (колонки, каскад від `users`), `ResumePolicy`, Create/Update/Delete Actions і Tasks (власник не змінюється при Update, `user_id` задає Create).
- Functional (`Tests/Functional/Filament/Resources/Resumes/`): гість → login; `/admin/resumes` OK; картки показують лише власні резюме в порядку `updated_at desc`; порожній стан; створення (`CreateResume`) з валідацією обовʼязкових полів і редіректом; редагування (`EditResume`) зі збереженням; чуже резюме на `/edit` → 404; видалення з картки й зі сторінки редагування; скасування видалення.

## Risks / Trade-offs

- **Кілька master на користувача:** вимогами не обмежено, тож технічно можна створити кілька «Основних». Обмеження (унікальність або автозняття прапорця) — окрема зміна, коли зʼявиться потреба; зараз тип — лише мітка.
- **Дубль CSS** з `vacancy-notes.css`: свідомий компроміс (Non-Goals); при третьому споживачі виносити в `Ship`.
- **Опублікований CSS:** без `filament:assets` картки виглядатимуть як стандартна таблиця; крок є в задачах.
- **`kind` як string:** некоректне значення з БД дасть виняток cast; запис іде лише через Actions із валідацією форми, тож ризик малий.
