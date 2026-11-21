# Tasks

## 1. Каркас контейнера і medialibrary

- [x] 1.1 Створити контейнер `app/Containers/AppSection/Company` за зразком `Status`: `composer.json` з `"spatie/laravel-medialibrary": "^11"` у `require`, `Tests/ContainerTestCase.php`, `UnitTestCase.php`, `FunctionalTestCase.php`, провайдер контейнера; без API/WEB-заготовок. Перевірка: `docker compose exec app composer update spatie/laravel-medialibrary` встановлює пакет, `composer show spatie/laravel-medialibrary` показує 11.x.
- [x] 1.2 Опублікувати міграцію `media` в `app/Ship/Migrations` і конфіг у `config/media-library.php` (`disk_name` = `public`), прибрати коментарі з опублікованих файлів. Перевірка: `php artisan migrate` (у контейнері) створює таблицю `media` (`database-schema`).
- [x] 1.3 Виконати `docker compose exec app php artisan storage:link` і додати цю команду в блок Environment в `AGENTS.md`. Перевірка: `public/storage` — симлінк на `storage/app/public`.

## 2. Довідники: типи компаній і домени

- [x] 2.1 Міграції `company_types` і `domains` (`id`, `name` string unique, timestamps), моделі `CompanyType` і `Domain` з PHPDoc `@property`, репозиторії, фабрики. Перевірка: unit-тест фабрик створює записи; `MigrationTest`-подібна перевірка унікального індексу на `name` в обох таблицях.
- [x] 2.2 Tasks і Actions `{Create,Update,Delete}CompanyTypeTask/Action` і `{Create,Update,Delete}DomainTask/Action` (Actions у `DB::transaction()`). Перевірка: unit-тести в `Tests/Unit/Actions` (створення, оновлення назви, видалення) проходять.
- [x] 2.3 `CompanyTypePolicy` і `DomainPolicy` (усі методи `true`). Перевірка: unit-тест `Gate::getPolicyFor()` повертає відповідну політику для обох моделей.
- [x] 2.4 Створити `UI/Filament/Resources` у контейнері, згенерувати прості ресурси `CompanyType` і `Domain` (`make:filament-resource --simple` з `--model-namespace`, `--resource-namespace`, `--panel=admin`, без `--cluster`), переконатися, що файли не потрапили в `app/Filament`; прибрати коментарі, додати PHPDoc-теги, `$cluster = SettingsCluster::class`, українські мітки, іконки, `$navigationSort`; форма `name` (required, maxLength 255, unique ignoreRecord), таблиця з пошуком і сортуванням за назвою; усі записи через `->using()` з Actions. Перевірка: functional-тести `ManageCompanyTypes` і `ManageDomains` — створення, редагування, видалення, масове видалення, пошук, помилки валідації для порожньої назви й дубліката.
- [x] 2.5 Перевірити субнавігацію «Налаштування»: «Статуси» перші, далі «Типи компаній», «Домени»; за потреби додати `$navigationSort = 10` у `StatusResource`. Перевірка: functional-тести — адреси `/admin/settings/company-types` і `/admin/settings/domains`, активний пункт у субнавігації, відсутність пунктів у головній навігації; наявний тест `admin-settings` (перша сторінка — «Статуси») проходить.

## 3. Модель компанії

- [x] 3.1 Міграції `companies` (`name` unique, `description` text nullable, `company_type_id` nullable FK `nullOnDelete`, `website_url`/`vacancies_url` string(2048) nullable, timestamps) і `company_domain` (FK `cascadeOnDelete`, складений primary key). Перевірка: структура таблиць через `database-schema`; unit-тести — видалення типу лишає компанію з `company_type_id = null`, видалення домену прибирає лише рядок pivot.
- [x] 3.2 Модель `Company` (`HasMedia`, `InteractsWithMedia`, `belongsTo companyType`, `belongsToMany domains`, колекція `logo`: `singleFile`, диск `public`, 4 MIME-типи), `CompanyRepository`, `CompanyFactory` (стани з типом і доменами). Перевірка: unit-тест фабрики зі станами; спроба додати в колекцію `logo` PDF кидає виняток medialibrary.
- [x] 3.3 `Media/CompanyLogoPathGenerator` і реєстрація в `custom_path_generators` конфігу. Перевірка: unit-тест — для медіа компанії з id 7 `getPath()` = `companies/7/`.
- [x] 3.4 `CompanyPolicy` (усі методи `true`). Перевірка: unit-тест `Gate::getPolicyFor(Company::class)`.

## 4. Tasks і Actions компанії

- [x] 4.1 `CreateCompanyTask`, `UpdateCompanyTask`, `DeleteCompanyTask`, `SyncCompanyDomainsTask`. Перевірка: unit-тести Tasks (`--filter CompanyTask`, `SyncCompanyDomainsTask`) проходять.
- [x] 4.2 `ReplaceCompanyLogoTask` (спершу `clearMediaCollection('logo')`, потім `addMedia()->usingFileName('logo.<ext з MIME>')`) і `DeleteCompanyLogoTask`. Перевірка: unit-тести зі `Storage::fake('public')` — `MacPaw Logo (final).png` лягає в `companies/{id}/logo.png`; webp → `logo.webp`; заміна png → svg лишає лише `logo.svg`; заміна png → png лишає існуючий `logo.png` з новим вмістом; видалення прибирає каталог `companies/{id}/`.
- [x] 4.3 `CreateCompanyAction`, `UpdateCompanyAction`, `DeleteCompanyAction` за рішенням 6 design.md (транзакція, розділення `domains` і `logo`, логотип останнім, контракт `UploadedFile` | `null` | відсутній/рядок, `load()` зв'язків). Перевірка: unit-тести — створення лише з назвою; з усіма атрибутами; оновлення без ключа `logo` і з рядком-шляхом не чіпає файл; `logo = null` видаляє логотип; видалення компанії прибирає її каталог.

## 5. Filament-ресурс Companies

- [x] 5.1 Згенерувати простий ресурс `Company` у `UI/Filament/Resources/Companies` (без кластера), прибрати коментарі, додати PHPDoc-теги, мітки «Компанія» / «Компанії», іконку `OutlinedBuildingOffice2`. Перевірка: `php artisan route:list --path=admin/companies` показує лише index-маршрут; `app/Filament` не існує; functional-тест — гість на `/admin/companies` перенаправлений на логін, пункт «Компанії» в головній навігації.
- [x] 5.2 `CompanyForm` за рішенням 7 (name, `company_type_id` і `domains` через `->options()` з `createOptionUsing()` на Actions, два URL-поля з правилом `http/https`, description до 1000, `FileUpload logo` зі `storeFiles(false)`, `image()`, 4 MIME, 2 МБ). Перевірка: functional-тести валідації — порожня назва, дублікат назви, опис 1001 символ, `macpaw.com`, `ftp://macpaw.com`, `not a url`, PDF і зображення 3 МБ як логотип дають помилки на відповідних полях; створення типу й домену з форми додає їх у довідник і обирає для компанії.
- [x] 5.3 `CreateAction` у `ManageCompanies` і `EditAction` у таблиці з `->using()` через Actions; заповнення форми редагування доменами й шляхом наявного логотипа. Перевірка: functional-тести — створення з логотипом (`UploadedFile::fake()->image('logo.png')`) зберігає `companies/{id}/logo.png`; редагування лише опису не змінює логотип; заміна й прибирання логотипа у формі; редагування без зміни назви без помилки унікальності; зняття одного з двох доменів.
- [x] 5.4 `CompaniesTable` (логотип із заглушкою, назва, тип, домени-бейджі, сайт; пошук, сортування за назвою, фільтри за типом і доменом, eager loading `companyType`, `domains`, `media`; `DeleteAction` і `DeleteBulkAction` через `DeleteCompanyAction`). Перевірка: functional-тести `assertCanSeeTableRecords` у порядку назв, `searchTable('Mac')`, `filterTable` за типом і доменом, масове видалення прибирає компанії та їхні каталоги логотипів; без `LazyLoadingViolationException` для кількох компаній з логотипами.
- [x] 5.5 Мініатюра логотипа: conversion `thumb` у `Company::registerMediaConversions()` (WebP, `Fit::Max` 128×128, `nonQueued`, без SVG), `Company::logoThumbPath()`, колонка логотипа в `CompaniesTable` показує мініатюру; `APP_URL` у `.env` / `.env.example` — `http://localhost:8000`, щоб URL файлів вели на nginx. Перевірка: unit-тести — PNG 800×400 дає `conversions/logo-thumb.webp` 128×64, SVG не має мініатюри й повертає шлях оригіналу; functional-тест — список показує `/storage/companies/{id}/conversions/logo-thumb.webp`; `php artisan media-library:regenerate` створює мініатюри для вже завантажених логотипів.

## 6. Інтеграційна перевірка

- [x] 6.1 Запустити тести контейнерів Company і Status: `DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Company app/Containers/AppSection/Status` — усі проходять.
- [x] 6.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer` — без нових помилок.
- [x] 6.3 Вручну в браузері (`http://localhost:8000/admin`): створити компанію з логотипом, типом і двома доменами, перевірити файл у `storage/app/public/companies/{id}/` і відображення логотипа в списку; замінити логотип JPG на PNG; видалити компанію; переглянути сторінки «Типи компаній» і «Домени» в «Налаштуваннях».

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive add-companies`.
