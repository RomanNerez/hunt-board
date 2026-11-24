# Tasks

## 1. Контейнер VacancySource: дані й бізнес-логіка

- [x] 1.1 Створити каркас контейнера `app/Containers/AppSection/VacancySource` (`composer.json`, `Providers/VacancySourceServiceProvider`, `Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}` за зразком `Status`). Перевірка: `composer dump-autoload` без помилок, `php artisan about` стартує
- [x] 1.2 Додати міграцію `vacancy_sources` (`id`, `name` unique, timestamps) і `Tests/Unit/Data/Migrations/MigrationTest` (колонки, унікальність назви). Перевірка: тест зелений
- [x] 1.3 Додати модель `Models/VacancySource`, `Data/Repositories/VacancySourceRepository`, `Data/Factories/VacancySourceFactory` (унікальні назви) і `VacancySourceFactoryTest`. Перевірка: тест зелений
- [x] 1.4 Додати `Create`/`Update`/`DeleteVacancySourceTask` і `Create`/`Update`/`DeleteVacancySourceAction` (у `DB::transaction`) з unit-тестами в `Tests/Unit/Tasks` і `Tests/Unit/Actions`. Перевірка: тести зелені
- [x] 1.5 Додати `Policies/VacancySourcePolicy` (усі дії дозволено автентифікованому користувачу, як у `CompanyTypePolicy`) і `VacancySourcePolicyTest`. Перевірка: тест зелений

## 2. Контейнер VacancySource: Filament

- [x] 2.1 Створити `UI/Filament/Resources/VacancySources` (`VacancySourceResource` у `SettingsCluster` зі slug `vacancy-sources` і підписами «Джерела вакансій» / «джерело вакансії», `Pages/ManageVacancySources`, `Schemas/VacancySourceForm`, `Tables/VacancySourcesTable`) за design.md (рішення 1): `mkdir -p` перед генерацією, явні namespace, без коментарів, PHPDoc-теги; записи через `->using()` до Actions. Перевірка: `php artisan route:list --path=admin/settings` показує `admin/settings/vacancy-sources`, у `app/Filament` нічого не з'явилося
- [x] 2.2 Тести `VacancySourceResourceTest` і `ManageVacancySourcesTest`: пункт у субнавігації налаштувань активний і відсутній у головній навігації, гість перенаправлений на вхід, сортування й пошук «Link», створення, редагування без зміни назви, видалення одного й масове, валідація порожньої назви й дубліката з українськими повідомленнями. Перевірка: тести зелені

## 3. Контейнер Vacancy: дані

- [x] 3.1 Створити каркас контейнера `app/Containers/AppSection/Vacancy` (`composer.json`, `Providers/VacancyServiceProvider`, `Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}`). Перевірка: `composer dump-autoload` без помилок
- [x] 3.2 Додати enums `Enums/WorkFormat` (`remote`/`hybrid`/`office`, `HasLabel` «Віддалено»/«Гібрид»/«Офіс») і `Enums/Currency` (`USD`/`EUR`/`UAH`, `HasLabel`) з unit-тестами підписів і значень. Перевірка: тести зелені
- [x] 3.3 Додати міграцію `vacancies` з міткою часу після `vacancy_sources` за design.md (рішення 2) і `MigrationTest`: колонки й типи, видалення компанії видаляє її вакансії, видалення статусу з вакансією відхиляється БД, видалення джерела лишає вакансію з `vacancy_source_id = null`. Перевірка: тест зелений
- [x] 3.4 Додати модель `Models/Vacancy` (зв'язки `company`, `status`, `vacancySource`; casts enums, дат і зарплат; PHPDoc `@property`), `Data/Repositories/VacancyRepository`, `Data/Factories/VacancyFactory` (зі станом повністю заповненої вакансії) і `VacancyFactoryTest`. Перевірка: тест зелений
- [x] 3.5 Додати set-мутатор `description` у `Vacancy` за design.md (рішення 4) і unit-тести `VacancyTest`: багаторядковий текст без змін, `<p>Ми шукаємо <b>PHP</b>-розробника</p>` → `Ми шукаємо PHP-розробника`, `<br>`/`</p>` → перенос рядка, `команда < 10 людей` без змін, HTML-сутності декодовано, `<script>`/коментарі прибрано, порожній результат → `null`. Перевірка: тести зелені

## 4. Контейнер Vacancy: бізнес-логіка

- [x] 4.1 Додати `Create`/`Update`/`DeleteVacancyTask` з unit-тестами. Перевірка: тести зелені
- [x] 4.2 Додати `Create`/`Update`/`DeleteVacancyAction` (у `DB::transaction`, повертають модель із завантаженими `company`, `status`, `vacancySource`) з unit-тестами: створення з мінімальними й з усіма атрибутами, оновлення статусу, видалення. Перевірка: тести зелені
- [x] 4.3 Додати `Policies/VacancyPolicy` (`viewAny`, `view`, `create`, `update`, `delete` → `true`) і `VacancyPolicyTest`. Перевірка: тест зелений

## 5. Контейнер Vacancy: Filament — список і створення

- [x] 5.1 Додати `UI/Filament/Support/SalaryRangeFormatter` (design.md, рішення 9) і unit-тест: обидві межі, лише мінімальна, лише максимальна, без зарплати → `null`, розділювач тисяч. Перевірка: тест зелений
- [x] 5.2 Створити `UI/Filament/Resources/Vacancies/Schemas/VacancyForm` за design.md (рішення 5, 6): секції, `Select` компанії з `createOptionForm` на основі `CompanyForm::configure()` і `CreateCompanyAction`, `Select` джерела зі створенням через `CreateVacancySourceAction`, `Select` статусу, enums для формату й валюти, правила зарплати, `Textarea` для опису й нотаток. Перевірка: використовується в задачах 5.4 і 6.2
- [x] 5.3 Створити `VacancyResource` (головна навігація, `Heroicon::OutlinedBriefcase`, «Вакансії», `$recordTitleAttribute = 'title'`, `getEloquentQuery()` з eager load) і `Tables/VacanciesTable` (колонки, бейдж статусу, вилка, `defaultSort('created_at', 'desc')`, фільтри статусу/компанії/джерела/формату, `recordActions([ViewAction::make()])`, `recordUrl` на `view`, без bulk-дій) за design.md (рішення 7, 9). Генерувати з явними namespace після `mkdir -p`. Перевірка: `php artisan route:list --path=admin/vacancies` показує `index`, `view`, `edit` і не показує `create`; у `app/Filament` нічого не з'явилося
- [x] 5.4 Створити `Pages/ListVacancies` з модальною `CreateAction` через `CreateVacancyAction` і `successRedirectUrl` на `view` (design.md, рішення 8). Перевірка: використовується в задачі 5.5
- [x] 5.5 Тести `VacancyResourceTest` і `ListVacanciesTest`: пункт «Вакансії» в головній навігації, гість перенаправлений на вхід, `/admin/vacancies/create` → 404, таблиця показує вакансії з компаніями, бейджем статусу й вилкою, сортування від найновіших, пошук за назвою вакансії й за компанією «Gramm», фільтри за статусом і форматом, у рядку лише дія перегляду й немає bulk-дій, створення в модалці з редиректом на «Огляд», валідація (порожні назва/компанія/статус, URL без схеми й `ftp://`, від'ємна зарплата, зарплата без валюти, максимальна менша за мінімальну, лише максимальна без мінімальної проходить), створення компанії з форми (`callFormComponentAction` для `createOption`) і її вибір, дублікат назви компанії відхилено, створення джерела з форми, створення компанії з логотипом із форми вакансії (design.md, Risks). Перевірка: тести зелені

## 6. Контейнер Vacancy: Filament — сторінка вакансії

- [x] 6.1 Створити `Schemas/VacancyInfolist` і `Pages/ViewVacancy` (`$navigationLabel = 'Огляд'`, header actions `EditAction` і `DeleteAction` через `DeleteVacancyAction` з редиректом на `index`) за design.md (рішення 7, 10). Перевірка: використовується в задачі 6.4
- [x] 6.2 Створити `Pages/EditVacancy` (`$navigationLabel = 'Редагування'`, `handleRecordUpdate()` → `UpdateVacancyAction`, `getRedirectUrl()` → `view`, header action `DeleteAction`). Перевірка: використовується в задачі 6.4
- [x] 6.3 Додати в `VacancyResource` `$subNavigationPosition = SubNavigationPosition::Start` і `getRecordSubNavigation()` з `ViewVacancy` і `EditVacancy`. Перевірка: використовується в задачі 6.4
- [x] 6.4 Тести `ViewVacancyTest` і `EditVacancyTest`: субнавігація з пунктами «Огляд» і «Редагування» та активним поточним, заголовок і хлібні крихти з назвою вакансії, огляд показує всі атрибути (статус бейджем, посилання з `target="_blank"`, опис з переносами рядків, `—` для порожніх), кнопка «Редагувати» веде на `edit`, редагування статусу з редиректом на «Огляд», очищена назва не зберігається, видалення з обох сторінок з редиректом на таблицю, скасування видалення лишає вакансію. Перевірка: тести зелені

## 7. Статуси: відмова у видаленні використовуваного статусу

- [x] 7.1 Додати `Status/Exceptions/StatusInUseException` і перехоплення `QueryException` з SQLSTATE `23503` у `DeleteStatusTask` (design.md, рішення 12); unit-тест `DeleteStatusTaskTest`: статус із вакансією → виняток і статус лишається. Перевірка: тест зелений
- [x] 7.2 У `StatusesTable` обробити виняток у `DeleteAction` (сповіщення «Статус використовується вакансіями», без сповіщення про успіх) і в `DeleteBulkAction` (видалити невикористовувані, показати попередження про решту). Доповнити `ManageStatusesTest` сценаріями зі spec `vacancy-statuses`. Перевірка: тести зелені

## 8. Інтеграційна перевірка

- [x] 8.1 Запустити всі тести на тестовій БД: `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` (у контейнері `app`). Перевірка: усі зелені, крім 4 стокових тестів конфігів Apiato (`AppTest`, `MailTest`, `DebugBarTest`, `IdeHelperTest`), що не стосуються вакансій
- [x] 8.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: PHPStan без помилок, fixer не лишає змін, у коді немає коментарів, PHPDoc лише з тегами
- [x] 8.3 Виконати `docker compose exec app php artisan migrate` і вручну пройти сценарій: створити джерело в Налаштуваннях, створити вакансію з новою компанією в модалці, потрапити на «Огляд», відредагувати в боковій панелі, видалити. Перевірка: усі кроки працюють без помилок, `browser-logs` без винятків
- [x] 8.4 `openspec validate add-vacancies --strict`. Перевірка: валідація проходить

## 9. Зміна: редагування в боковій панелі й rich-text опис

- [x] 9.1 Замінити set-мутатор `description` на санітизацію через `Str::sanitizeHtml()` і `null` для опису без видимого тексту (design.md, рішення 4); оновити `VacancyTest`: форматування зберігається, `<script>` прибрано, `<p></p>` → `null`. Перевірка: тести зелені
- [x] 9.2 У `VacancyForm` поле опису — `RichEditor` з тулбаром із design.md; у `VacancyInfolist` опис — `->html()`. Перевірка: використовується в задачі 9.4
- [x] 9.3 Видалити `Pages/EditVacancy` і `EditVacancyTest`; `getPages()` без `edit`; `getRecordSubNavigation()` лише з `ViewVacancy`; `EditAction` у `ViewVacancy` — `slideOver()` через `UpdateVacancyAction`. Перевірка: `php artisan route:list --path=admin/vacancies` показує лише `index` і `view`
- [x] 9.4 Оновити тести: `/admin/vacancies/{id}/edit` → 404, субнавігація лише з активним «Огляд», редагування статусу в боковій панелі з оновленням «Огляду», очищена назва не зберігається, опис рендериться як HTML без `<script>`, сценарій «Редагувати веде на edit» прибрано. Перевірка: тести зелені
- [x] 9.5 Прогнати тести контейнерів, PHPStan для змінених файлів, `composer fixer`, `openspec validate add-vacancies --strict`. Перевірка: усе зелене

## Workflow follow-up

- Після рев'ю й зелених тестів — `openspec archive add-vacancies`.
