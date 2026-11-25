# Tasks

Тести запускати з `DB_DATABASE=hunt-board_testing`.

## 1. Мова резюме: дані

- [x] 1.1 Створити енум `Resume/Enums/ResumeLocale` (`en`, `uk`; методи `getLabel()`, `fileSuffix()`, `default()`) і покрити юніт-тестом `ResumeLocaleTest`.
- [x] 1.2 Додати колонку `locale` (`string(2)`, за замовчуванням `en`) після `kind` у `2026_11_24_130000_create_resumes_table.php` і оновити `MigrationTest`. Перевірка: тест проходить, у dev виконано `migrate:fresh`.
- [x] 1.3 Додати `locale` у `$fillable`, cast `ResumeLocale` і `@property` моделі `Resume`, а у `ResumeFactory` значення за замовчуванням `en` і стан `ukrainian()`. Перевірка: `ResumeTasksTest`.
- [x] 1.4 Зробити так, щоб `CreateResumeTask` ставив `en`, коли `locale` не передано, а `UpdateResumeTask` зберігав передане значення. Перевірка: тести в `ResumeActionsTest` (мова за замовчуванням, `uk`).

## 2. Мова резюме: форма

- [x] 2.1 Додати `ResumeForm::localeField()` (`ToggleButtons`, обовʼязкове, за замовчуванням English) у `CreateResumeModalAction` і на вкладку «Загальне». Перевірка: `CreateResumeModalTest` (за замовчуванням `en`, вибір `uk`, `de` дає помилку форми) і `EditResumeTest` (зміна мови на `uk`).

## 3. Побудова документа резюме

- [x] 3.1 Додати методи `pdfLabel(ResumeLocale)` в енуми `ResumeLanguageLevel` і `ResumeEmploymentType`, а також мовні файли `Resume/Languages/{en,uk}/pdf.php` (`sections`, `present`, `tech_stack`, `levels`, `employment_types`). Перевірка: розширені `ResumeLanguageLevelTest` і `ResumeEmploymentTypeTest` для обох мов.
- [x] 3.2 Створити value-обʼєкти в `Resume/Values/Pdf/` (`ResumeDocument`, `ResumeDocumentLink`, `ResumeDocumentExperience`, `ResumeDocumentEducation`, `ResumeDocumentLanguage`). Перевірка: phpstan без помилок для нових файлів.
- [x] 3.3 Створити виняток `Resume/Exceptions/ResumePdfUnavailable` з методами `becauseResumeIsMissing()` (404) і `becausePdfServiceFailed()` (503).
- [x] 3.4 Створити `FindUserResumeTask`: пошук за `id` і `user_id` з eager-load відношень, для чужого чи неіснуючого резюме кидає 404. Перевірка: юніт-тест.
- [x] 3.5 Створити `BuildResumeDocumentTask`: приховані записи відкидаються, порядок зберігається, періоди мовою резюме (`Jan 2024 – Present`, «Дотепер»), рядок мови з `level_label` або з перекладом рівня, rich text перетворюється на HTML, імʼя файлу латиницею з `_CV_EN` або `_CV_UA`. Перевірка: юніт-тест `BuildResumeDocumentTaskTest` за сценаріями вимог «Вміст PDF», «Мова PDF», «Рівень мови в PDF» і «Завантаження PDF резюме».

## 4. Шаблон і рендер PDF

- [x] 4.1 Створити шаблон `Resume/UI/WEB/Views/pdf/classic.blade.php` і секції в `pdf/sections/` (`styles`, `header`, `links`, `summary`, `experience`, `education`, `languages`), взявши за зразок профільний шаблон. Порожні секції не виводяться. Перевірка: тест рендеру view (`view(...)->render()`), що перевіряє наявні та відсутні заголовки секцій.
- [x] 4.2 Створити `RenderResumePdfTask` (A4, поля 12 мм, назва файлу) і `GenerateResumePdfAction`. Перевірка: юніт-тест з `Pdf::fake()`, що перевіряє view і `downloadName`.

## 5. Веб-маршрути

- [x] 5.1 Додати трейт `RespondsWithResumePdf`, контролери `PreviewResumePdfController` (inline) і `DownloadResumePdfController` (вкладення) з `Cache-Control: no-store`, а також маршрути `resumes.pdf.preview` і `resumes.pdf.download` (`admin/resumes/{resume}/pdf/...`, `whereNumber`, `auth`). Перевірка: функціональний тест `ResumePdfRoutesTest`: власник отримує 200 inline або attachment з правильним імʼям, чуже і неіснуюче резюме дають 404, гість отримує редирект на `/admin/login`, збій сервісу дає 503.

## 6. Filament: перегляд

- [x] 6.1 Створити `Resume/UI/WEB/Views/preview-frame.blade.php` (iframe з `$previewUrl` і висотою).
- [x] 6.2 Створити `PreviewResumeAction` (slide-over з фреймом і дії «Завантажити PDF» та «Відкрити в новій вкладці») і поставити першою в `ResumesTable::recordActions()`. Перевірка: `ListResumesTest`, де картка має дію `preview`, вона монтується, а у вмісті є url перегляду цього резюме.
- [x] 6.3 Створити сторінку `Pages/PreviewResume` (`ViewRecord`, маршрут `/{record}/preview`, підпис «Перегляд», фрейм, дві header-дії) і додати її в `getPages()` та останньою в `getRecordSubNavigation()`. Перевірка: функціональний тест `PreviewResumeTest`: сторінка відкривається, на ній є url фрейму, немає дії збереження, чуже резюме дає 404. Також оновлений тест порядку вкладок.

## 7. Перевірка

- [x] 7.1 Запустити `vendor/bin/phpunit` для контейнера Resume, `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: усе зелене.
  - Результат: phpunit для Resume (168) і Profile (305) зелені, `composer fixer` без змін. У phpstan немає нових помилок у продакшн-коді. Помилки, що лишились, були в проєкті й раніше, і користувач погодився з таким критерієм.
- [x] 7.2 Вручну перевірити в браузері: модальне вікно зі списку, вкладка «Перегляд», завантаження файлу, і що після збереження на іншій вкладці PDF показує нові дані. Перевірка: зафіксувати результат у звіті apply.
  - Результат: користувач перевірив перегляд у браузері після міграції dev-бази. За його зауваженнями шапку вирівняно по центру, посилання показуються як іконка й підпис, прибрано смугу праворуч (`@page size`), summary став секцією-карткою, заголовки секцій стоять по центру, а картка досвіду має вигляд «компанія / посада» і «місце · тип | період».

## Workflow follow-up

- Після виконання всіх задач і проходження тестів запустити `openspec archive add-resume-pdf-preview`.
