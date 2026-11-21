# Tasks

## 1. Каркас контейнера Status

- [x] 1.1 Створити контейнер `app/Containers/AppSection/Status` (`apiato:make:container` або вручну за зразком `User`): `composer.json`, `Tests/ContainerTestCase.php`, `UnitTestCase.php`, `FunctionalTestCase.php`; прибрати згенеровані API/WEB-заготовки, які не потрібні. Перевірка: `composer dump-autoload` без помилок, у контейнері немає зайвих Routes/Controllers.
- [x] 1.2 Додати enum `Enums/StatusColor` (string-backed, `HasLabel`) з 12 пресетами `gray`, `red`, `orange`, `amber`, `lime`, `green`, `teal`, `cyan`, `blue`, `indigo`, `violet`, `pink`; кожен повертає фон, бордер і текст для світлої (Tailwind 50 / 200 / 700) і темної (500 з прозорістю 15% / 500 з прозорістю 30% / 300) тем. Перевірка: unit-тест у `Tests/Unit/Enums` — пресетів рівно 12, кожен має назву й шість кольорів у форматі HEX (`#RRGGBB` або `#RRGGBBAA`).
- [x] 1.3 Оновити міграцію `create_statuses_table` (`id`, `name` string unique, `color` string(20), timestamps) у `Data/Migrations`; у dev-БД відкотити й накотити її заново. Перевірка: таблиця `statuses` має `color varchar(20)` і унікальний індекс на `name` (`database-schema`), `MigrationTest` проходить.
- [x] 1.4 Оновити модель `Status` (cast `color` → `StatusColor`, `@property StatusColor $color`) і `StatusFactory` (`color` — `fake()->randomElement(StatusColor::cases())`); `StatusRepository` без змін. Перевірка: unit-тест фабрики створює запис, у якого `color` — екземпляр `StatusColor`.

## 2. Tasks і Actions

- [x] 2.1 Створити `CreateStatusTask`, `UpdateStatusTask`, `DeleteStatusTask` поверх `StatusRepository`; тестові дані кольору — `StatusColor`. Перевірка: unit-тести Tasks проходять (`vendor/bin/phpunit --filter StatusTask`).
- [x] 2.2 Створити `CreateStatusAction`, `UpdateStatusAction`, `DeleteStatusAction`: кожна обгортає виклик Task у транзакцію; тестові дані кольору — `StatusColor`. Перевірка: unit-тести в `Tests/Unit/Actions` (створення, оновлення назви й пресету, видалення) проходять.

## 3. Policy

- [x] 3.1 Додати `Policies/StatusPolicy` з `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, які повертають `true` для `User`; переконатися, що Laravel знаходить політику автоматично, інакше зареєструвати `Gate::policy()` у провайдері контейнера. Перевірка: unit-тест `Gate::getPolicyFor(Status::class)` повертає `StatusPolicy`.

## 4. Кластер Settings

- [x] 4.1 Вручну створити `app/Ship/Filament/Clusters/Settings/SettingsCluster.php` (`final`, extends `Filament\Clusters\Cluster`, `$navigationIcon = Heroicon::OutlinedCog6Tooth`, `$subNavigationPosition = SubNavigationPosition::Start`, `$slug = 'settings'`, `$navigationSort`). Перевірка: `php artisan route:list --path=admin/settings` після задачі 5.1 показує маршрути ресурсу з префіксом `admin/settings`.

## 5. Filament-ресурс Statuses

- [x] 5.1 Створити каталог `app/Containers/AppSection/Status/UI/Filament/Resources`, згенерувати ресурс `make:filament-resource Status` з `--model-namespace`, `--resource-namespace`, `--panel=admin` (без `--cluster`), перевірити, що файли лягли в `UI/Filament/Resources/Statuses/`, а не в `app/Filament`. Прибрати коментарі зі стабів і додати PHPDoc-теги. Перевірка: `app/Filament` не існує, ресурс знаходиться за `/admin/settings/statuses`.
- [x] 5.2 Додати в `StatusResource` `$cluster = SettingsCluster::class` і `$navigationIcon`. Перевірка: functional-тест — сторінка Statuses має адресу `/admin/settings/statuses`, у відповіді є субнавігація кластера з активним пунктом «Statuses», а в головній навігації є «Settings».
- [x] 5.3 Реалізувати `StatusForm`: `TextInput name` (required, maxLength 255, unique ignoreRecord) і вибір пресету `color` (`ToggleButtons` / `Radio` з HTML-підписами або власний view поля; кожен варіант — бейдж у кольорах пресету; required, `Rule::enum(StatusColor::class)`) плюс прев'ю бейджа з поточною назвою. Перевірка: functional-тести валідації (порожня назва, порожній колір, `#3B82F6` і `magenta` не з палітри, дублікат назви) отримують помилки на відповідних полях; у формі доступні всі 12 пресетів.
- [x] 5.4 Реалізувати `StatusesTable`: `TextColumn name`, що рендерить бейдж статусу через шаблон із 5.7, searchable і sortable, сортування за `name`, без `ColorColumn`; рядкові `EditAction`, `DeleteAction` і `DeleteBulkAction` через Actions. Перевірка: functional-тести `assertCanSeeTableRecords`, `searchTable('Off')`, у відповіді сторінки є бейдж зі змінними кольорів пресету, видалення одного запису й масове видалення проходять.
- [x] 5.5 Перевести ресурс на простий (модальний) варіант: замінити сторінки `ListStatuses`, `CreateStatus`, `EditStatus` однією `ManageStatuses` (`ManageRecords`); `CreateAction` у заголовку через `CreateStatusAction`, рядковий `EditAction` через `UpdateStatusAction` (`->using()`). Перевірка: functional-тести створення в модальному вікні, валідації, зміни пресету кольору й збереження без зміни назви (без помилки унікальності) проходять; маршрутів `create` і `{record}/edit` немає.
- [x] 5.6 Перевірити доступ гостя. Перевірка: functional-тест — гість на `/admin/settings/statuses` отримує перенаправлення на сторінку логіну Filament.
- [x] 5.7 Створити шаблон бейджа `UI/WEB/Views/badge.blade.php` (назва + `StatusColor`, inline CSS-змінні `--hb-status-bg`, `--hb-status-border`, `--hb-status-text` і `-dark` варіанти) і CSS-файл, що задає форму бейджа та перемикає змінні під `.dark`; зареєструвати CSS через `FilamentAsset::register()` у провайдері контейнера. Перевірка: functional-тест рендерить шаблон і бачить кольори пресету в розмітці; CSS підключено на сторінці панелі.

## 6. Інтеграційна перевірка

- [x] 6.1 Запустити всі тести контейнера: `vendor/bin/phpunit app/Containers/AppSection/Status` — усі проходять.
- [x] 6.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer` — без нових помилок.
- [x] 6.3 Вручну в браузері (`http://localhost:8000/admin`): пункт «Settings» у навігації, ліва субнавігація зі «Statuses», створення статусу з вибором пресету в модальному вікні, бейджі всіх 12 пресетів гарні й читабельні у світлій і темній темах.

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive add-vacancy-statuses`.
