# Design

## Context

Мотивація — у proposal.md (розділ Why), вимоги — у `specs/companies`, `specs/company-types`, `specs/company-domains`.

Поточний стан:

- У `app/Containers/AppSection` є `User`, `Authentication` і `Status`. Контейнер `Status` — еталон для нового коду: Actions із `DB::transaction()`, Tasks поверх репозиторію, політика з `true`, простий Filament-ресурс `ManageStatuses` з `->using()` у `CreateAction`/`EditAction`/`DeleteAction`/`DeleteBulkAction`, українські мітки (`$modelLabel`, `$navigationLabel`, `$hasTitleCaseModelLabel = false`), тести в `Tests/Unit` і `Tests/Functional/Filament`.
- Кластер `App\Ship\Filament\Clusters\Settings\SettingsCluster` уже існує; `AdminPanelProvider::discoverContainerComponents()` підхоплює ресурси нових контейнерів без змін провайдера.
- `spatie/laravel-medialibrary` не встановлено. Образ `docker-compose/app/Dockerfile` уже має `exif`, `gd` і `imagick`. Диск `public` налаштовано (`storage/app/public`, URL `APP_URL/storage`), але симлінку `public/storage` немає.
- `Model::shouldBeStrict()` увімкнено поза production: lazy loading (зокрема зв'язку `media`) кидає виняток.
- Тести запускаються з `DB_DATABASE=hunt-board_testing` (інакше стирається dev-БД).

## Goals / Non-Goals

**Goals:**

- Перша сутність із файлами: показати, як medialibrary вписується в Porto (файли пише Task, а не Filament) і як задати власний шлях `companies/{id}/logo.<ext>`.
- Перші зв'язки між моделями (belongs-to і many-to-many), які зберігаються через Actions, а не через `->relationship()` Filament.

**Non-Goals:**

- Filament-плагін `filament/spatie-laravel-media-library-plugin` (див. рішення 5).
- Responsive images, черги для обробки зображень, conversions, крім однієї мініатюри для списку.
- Захист SVG від вбудованих скриптів на рівні nginx (див. Risks).

## Decisions

### 1. Один контейнер `AppSection/Company` з трьома моделями

```
app/Containers/AppSection/Company/
├── Actions/
│   ├── {Create,Update,Delete}CompanyAction.php
│   ├── {Create,Update,Delete}CompanyTypeAction.php
│   └── {Create,Update,Delete}DomainAction.php
├── Tasks/
│   ├── {Create,Update,Delete}CompanyTask.php
│   ├── SyncCompanyDomainsTask.php
│   ├── ReplaceCompanyLogoTask.php
│   ├── DeleteCompanyLogoTask.php
│   ├── {Create,Update,Delete}CompanyTypeTask.php
│   └── {Create,Update,Delete}DomainTask.php
├── Models/{Company,CompanyType,Domain}.php
├── Media/CompanyLogoPathGenerator.php
├── Data/{Migrations,Repositories,Factories}/
├── Policies/{Company,CompanyType,Domain}Policy.php
├── UI/Filament/Resources/
│   ├── Companies/{CompanyResource.php,Pages/ManageCompanies.php,Schemas/CompanyForm.php,Tables/CompaniesTable.php}
│   ├── CompanyTypes/{CompanyTypeResource.php,Pages/ManageCompanyTypes.php,Schemas/…,Tables/…}
│   └── Domains/{DomainResource.php,Pages/ManageDomains.php,Schemas/…,Tables/…}
├── Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}.php, Unit/…, Functional/Filament/…
└── composer.json                 # require: spatie/laravel-medialibrary ^11
```

**Чому:** типи й домени існують лише заради компаній, а форма компанії створює їх на льоту — тримати їх поруч простіше, ніж робити міжконтейнерні залежності.
**Альтернативи:** окремі контейнери `CompanyType` і `Domain`, як `Status` (більше бойлерплейту, а `Company` все одно залежав би від обох); домени як JSON-масив тегів у `companies` (так вирішив не робити користувач — потрібен довідник і фільтр).

### 2. Схема БД

- `company_types`: `id`, `name` string(255) unique, timestamps.
- `domains`: `id`, `name` string(255) unique, timestamps.
- `companies`: `id`, `name` string(255) unique, `description` text nullable, `company_type_id` foreignId nullable `constrained()->nullOnDelete()`, `website_url` string(2048) nullable, `vacancies_url` string(2048) nullable, timestamps.
- `company_domain`: `company_id` і `domain_id` — foreignId `constrained()->cascadeOnDelete()`, складений primary key `(company_id, domain_id)`.
- `media` — стандартна міграція medialibrary.

Моделі: `Company` (`belongsTo companyType`, `belongsToMany domains`, `implements HasMedia`, `use InteractsWithMedia`), `CompanyType` (`hasMany companies`), `Domain` (`belongsToMany companies`). Фабрики для всіх трьох.

**Чому:** `nullOnDelete` і `cascadeOnDelete` реалізують вимоги про видалення типу й домену на рівні БД, без ручного коду в Tasks; складений ключ виключає дублікати домену в компанії. Довжина опису (1000) перевіряється формою, колонка `text` не обмежує майбутні зміни. URL до 2048 — практична межа довжини посилань.
**Альтернативи:** `restrictOnDelete` для типу (заборона видаляти використовуваний тип — суворіше, ніж потрібно в довіднику); `string(1000)` для опису (зміна ліміту вимагала б міграції).

### 3. Medialibrary: встановлення та конфіг

- `spatie/laravel-medialibrary` ^11 додається в `composer.json` контейнера Company, `composer update` об'єднує його в корінь через merge-plugin.
- Міграцію `media` публікуємо в `app/Ship/Migrations` (таблиця поліморфна й спільна для майбутніх сутностей із файлами), конфіг — у `config/media-library.php` з `disk_name` = `public` і `custom_path_generators` = `[Company::class => CompanyLogoPathGenerator::class]`. Коментарі зі згенерованих файлів прибираються за AGENTS.md.
- Колекція `logo` у `Company::registerMediaCollections()`: `->singleFile()`, `->useDisk('public')`, `->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])`.
- Одна conversion `thumb` у `Company::registerMediaConversions()`: `->performOnCollections('logo')->nonQueued()->fit(Fit::Max, 128, 128)->format('webp')`. `Fit::Max` зменшує без обрізання й без збільшення, WebP зберігає прозорість. `nonQueued()` — мініатюра готова одразу після збереження форми. Для `image/svg+xml` conversion не реєструється: вектор масштабується сам, а растеризація через Imagick втратила б прозорість. `Company::logoThumbPath()` повертає шлях мініатюри, а якщо її немає (SVG) — шлях оригіналу.
- `php artisan storage:link` у контейнері; команду додаємо в розділ Environment в AGENTS.md.

**Чому:** користувач явно обрав medialibrary; спільна таблиця `media` належить Ship, бо нею користуватимуться інші контейнери. `acceptsMimeTypes` — друга лінія захисту, якщо файл прийде не через форму.
**Альтернативи:** звичайний `FileUpload` з колонкою `logo_path` у `companies` (простіше, але користувач хоче medialibrary); міграція `media` у контейнері Company (тоді вона виглядала б власністю компаній).

### 4. Шлях `companies/{id}/logo.<ext>`

- `Media/CompanyLogoPathGenerator implements PathGenerator`: `getPath()` → `companies/{$media->model_id}/`, `getPathForConversions()` → `companies/{id}/conversions/`, `getPathForResponsiveImages()` → `companies/{id}/responsive-images/`.
- Ім'я файлу задає `ReplaceCompanyLogoTask`: `->usingFileName('logo.'.$extension)`, де розширення береться з MIME-типу (`UploadedFile::guessExtension()`, `jpeg` → `jpg`), а не з початкової назви. `->usingName()` зберігає початкову назву лише як метадані медіа.

**Чому:** шлях за id компанії (а не id медіа, як у дефолтному генераторі) — вимога користувача; генератор через `custom_path_generators` діє лише для `Company` і не зачіпає майбутні моделі. Розширення з MIME не дає файлу `logo.PNG` чи `logo.jpeg` поруч із `logo.jpg`.
**Альтернативи:** глобальний `path_generator` / `file_namer` у конфігу (вплинув би на всі моделі); розширення з початкової назви (довіряє клієнту).

### 5. Заміна логотипа: спершу видалити старий, потім додати новий

Medialibrary під час видалення медіа видаляє **весь каталог** із `PathGenerator::getPath()`. У нас усі логотипи компанії лежать в одному `companies/{id}/`, тож стандартний порядок `singleFile()` (додати новий → видалити старий) знищив би щойно доданий файл. Тому:

- `ReplaceCompanyLogoTask::run(Company $company, UploadedFile $file)`: `$company->clearMediaCollection('logo')`, потім `addMedia($file)->usingFileName(...)->toMediaCollection('logo')`.
- `DeleteCompanyLogoTask::run(Company $company)`: `clearMediaCollection('logo')`.
- `DeleteCompanyTask`: видалення моделі `HasMedia` видаляє її медіа й каталог автоматично (`InteractsWithMedia::bootInteractsWithMedia`).

Сценарії заміни в `specs/companies` покриваються тестами зі `Storage::fake('public')`.

**Чому:** це єдиний порядок, за якого спільний каталог безпечний.
**Альтернативи:** окремий підкаталог на кожне медіа (`companies/{id}/{media_id}/logo.png`) — суперечить вимозі шляху; власний `FileRemover` — більше коду, який треба підтримувати при оновленнях бібліотеки.

### 6. Записи через Actions; Action розбирає дані форми

`CreateCompanyAction::run(array $data)` і `UpdateCompanyAction::run(int $id, array $data)` у транзакції:

1. відокремлюють `domains` (`list<int>`) і `logo` від атрибутів моделі;
2. викликають `CreateCompanyTask` / `UpdateCompanyTask` з атрибутами (`name`, `description`, `company_type_id`, `website_url`, `vacancies_url`);
3. викликають `SyncCompanyDomainsTask` (`$company->domains()->sync($ids)`);
4. логотип: `UploadedFile` → `ReplaceCompanyLogoTask`; `null` → `DeleteCompanyLogoTask`; ключ відсутній або рядок (шлях наявного файлу, форму не змінювали) → нічого;
5. повертають компанію з `load(['companyType', 'domains', 'media'])`.

Операції з файлами не відкочуються транзакцією, тому логотип обробляється останнім — після всіх записів, які можуть впасти. Actions для типів і доменів — як у `Status`.

**Чому:** AGENTS.md вимагає записів через Actions; оркестрація кількох Tasks — саме роль Action. Контракт «`UploadedFile` | `null` | відсутній» не залежить від Filament (`TemporaryUploadedFile` — підклас `UploadedFile`), тож Action придатна для CLI чи майбутнього API.
**Альтернативи:** окремі Actions для логотипа й доменів, які Filament викликає після збереження (кілька транзакцій на одну дію користувача); Filament `->relationship()` для доменів і `SpatieMediaLibraryFileUpload` для логотипа — пишуть у БД і сховище в обхід Actions, а плагін ще й використовує порядок «додати → видалити» з рішення 5.

### 7. Filament: компанії — простий ресурс у головній навігації

`CompanyResource` (`--simple`, сторінка `ManageCompanies`, адреса `/admin/companies`, `$navigationIcon = Heroicon::OutlinedBuildingOffice2`, мітки «Компанія» / «Компанії»), без кластера. Модальні вікна — вибір користувача.

`CompanyForm` (`->columns(2)`, модальне вікно `->modalWidth(Width::ThreeExtraLarge)` або подібне):

- `TextInput name` — required, maxLength 255, `unique(ignoreRecord: true)`;
- `Select company_type_id` — `->options()` з `CompanyType` за назвою, `searchable()`, `preload()`, `createOptionForm()` з полем `name` і `createOptionUsing()` → `CreateCompanyTypeAction`, повертає id;
- `Select domains` — `multiple()`, те саме з `CreateDomainAction`; без `->relationship()`;
- `TextInput website_url`, `vacancies_url` — `url()`, `maxLength(2048)`, правило `url:http,https` (звичайне `url` пропускає `ftp://`);
- `Textarea description` — maxLength 1000, на всю ширину;
- `FileUpload logo` — `image()`, `acceptedFileTypes([...4 MIME])`, `maxSize(2048)`, `disk('public')`, `storeFiles(false)`, `imageEditor()` не вмикається.

Заповнення під час редагування: `EditAction::make()->fillForm(...)` / `mutateRecordDataUsing()` додає `domains` = id доменів і `logo` = `getFirstMedia('logo')?->getPathRelativeToRoot()`, щоб FileUpload показав наявний файл. Записи — через `->using()`, як у `Status`.

`CompaniesTable`: `ImageColumn logo` зі станом `logoThumbPath()` (шлях відносно диска), `->disk('public')`, `checkFileExistence(false)`, `square()` з `object-fit: contain` (логотип вписується в квадрат без обрізання, світлий фон і рамка), `defaultImageUrl()` — локальна SVG-заглушка (data URI); `TextColumn name` searchable/sortable; `TextColumn companyType.name`; `TextColumn domains.name` `->badge()`; `TextColumn website_url` `->url()->openUrlInNewTab()`, toggleable; `TextColumn vacancies_url` так само, з `limit(40)` і повною адресою в підказці. `SelectFilter` за `companyType` і `domains` (`->relationship()` у фільтрі — це читання, дозволено). `->modifyQueryUsing(fn ($q) => $q->with(['companyType', 'domains', 'media']))`, `defaultSort('name')`. Рядкові `EditAction`, `DeleteAction`, `DeleteBulkAction` — через Actions.

**Чому:** простий ресурс відповідає вибору користувача; `->options()` замість `->relationship()` лишає запис зв'язків Action; eager loading обов'язковий через strict mode.
**Альтернативи:** окремі сторінки Create/Edit/View (відхилено користувачем); `SpatieMediaLibraryImageColumn` (потребує плагін); стан колонки як `getFirstMediaUrl()` — відносний URL (наприклад, зі `Storage::fake`) `ImageColumn` трактує як шлях на диску й показує заглушку.

### 8. Типи компаній і домени — прості ресурси в кластері Settings

`CompanyTypeResource` (`/admin/settings/company-types`, мітки «Тип компанії» / «Типи компаній», `Heroicon::OutlinedRectangleGroup`) і `DomainResource` (`/admin/settings/domains`, «Домен» / «Домени», `Heroicon::OutlinedGlobeAlt`) з `$cluster = SettingsCluster::class`, однією формою `name` (required, maxLength 255, unique) і таблицею з пошуком і сортуванням за назвою. `$navigationSort` новим ресурсам ставиться більшим, ніж у «Статусів»; якщо порядок у субнавігації без явного сорту в `StatusResource` не гарантує «Статуси» першими (вимога `admin-settings`), `StatusResource` отримує `$navigationSort = 10` — єдина правка поза контейнером Company.

**Чому:** довідники — налаштування, як і статуси; кластер Settings для цього й створювався.
**Альтернативи:** RelationManager-и або навігаційна група «Компанії» з трьома пунктами (довідники змішалися б з основними даними).

### 9. Політики й тести

`CompanyPolicy`, `CompanyTypePolicy`, `DomainPolicy` — `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny` повертають `true` (як `StatusPolicy`). Unit-тести Actions/Tasks (зокрема заміна й видалення логотипа зі `Storage::fake('public')` і `UploadedFile::fake()->image()`), functional-тести `Livewire::test(ManageCompanies::class)` для форми, валідації, таблиці, фільтрів і видалення; тести сторінок довідників і субнавігації.

## Risks / Trade-offs

- [Medialibrary видаляє каталог разом із медіа] → порядок «видалити → додати» (рішення 5) і тест «Заміна логотипа тим самим форматом», який перевіряє, що файл існує після заміни.
- [Strict mode і lazy loading `media`] → eager loading у таблиці, `load()` у Actions; тести списку з кількома компаніями зловлять порушення.
- [SVG може містити скрипти, і при прямому відкритті з `/storage` вони виконаються на домені панелі] → у `<img>` скрипти не виконуються; панель однокористувацька. Якщо з'явиться кілька користувачів — додати в nginx `Content-Security-Policy: script-src 'none'` для `/storage/*.svg` або відмовитися від SVG.
- [Файлові операції поза транзакцією: якщо збереження впаде після запису файлу] → логотип обробляється останнім кроком Action, тож після нього вже нема записів, що можуть впасти.
- [Тимчасові файли Livewire лишаються після скасування модального вікна] → штатне очищення `livewire-tmp` (24 години) достатнє.
- [Свіже середовище без `public/storage`] → команда `storage:link` у AGENTS.md і в задачах.

## Migration Plan

1. `composer update` у контейнері `app` (новий пакет), `php artisan migrate` (таблиці `company_types`, `domains`, `companies`, `company_domain`, `media`), `php artisan storage:link`.
2. Відкат: `php artisan migrate:rollback` для нових міграцій, прибрати пакет із `composer.json` контейнера, видалити `storage/app/public/companies`. Наявні дані (`statuses`, `users`) не зачіпаються.
