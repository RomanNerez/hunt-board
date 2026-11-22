# Tasks

## 1. Каркас контейнера

- [x] 1.1 Створити контейнер `app/Containers/AppSection/Profile` за зразком `Status`: `composer.json`, `Providers/ProfileServiceProvider.php`, `Tests/ContainerTestCase.php`, `UnitTestCase.php`, `FunctionalTestCase.php`; без API/WEB-заготовок. Перевірка: `docker compose exec app composer dump-autoload` без помилок, `php artisan about` не падає.

## 2. Дані: enum-и, міграції, моделі

- [x] 2.1 Enum-и `EducationType` і `LanguageLevel` (`HasLabel`, українські й CEFR-мітки, порядок case-ів за design.md, рішення 3). Перевірка: unit-тест — мітки й порядок `LanguageLevel::cases()` від «A1 Beginner» до «Рідна».
- [x] 2.2 Міграції `user_profiles`, `profile_links`, `profile_educations`, `profile_languages` за рішенням 2 (FK `user_id` з `cascadeOnDelete`, unique `user_id` у профілі, unique `(user_id, language)`, колонка `sort`). Перевірка: `docker compose exec app php artisan migrate` проходить, структура таблиць через `database-schema` відповідає design.md.
- [x] 2.3 Моделі `UserProfile`, `ProfileLink`, `ProfileEducation`, `ProfileLanguage` (`@property`-PHPDoc, `$fillable`, касти enum-ів і `immutable_date`, `belongsTo user`, `hasMany` у `UserProfile`), репозиторії, фабрики. Перевірка: unit-тест фабрик; unit-тест — після видалення користувача з профілем, посиланнями, освітою й мовами жодного рядка не залишається.

## 3. Tasks і Actions

- [x] 3.1 `UpsertUserProfileTask` і `UpdateUserProfileAction` (транзакція, `updateOrCreate` за `user_id`). Перевірка: unit-тести — перший виклик створює профіль, другий оновлює той самий рядок; очищення `phone` зберігає `null`; профіль іншого користувача не змінюється.
- [x] 3.2 `ReplaceProfileLinksTask` і `SyncProfileLinksAction` (видалити все користувача → вставити зі `sort` = індекс, повернути упорядкований список). Перевірка: unit-тести — додавання, видалення, зміна порядку, порожній список очищає, посилання іншого користувача залишаються.
- [x] 3.3 `ReplaceProfileEducationTask` і `SyncProfileEducationAction`. Перевірка: unit-тести — збереження трьох типів із датами й без дат, порядок, ізоляція користувачів.
- [x] 3.4 `ReplaceProfileLanguagesTask` (із `trim()` назви) і `SyncProfileLanguagesAction`. Перевірка: unit-тести — збереження й зміна рівня, порядок, `" Англійська "` зберігається як `Англійська`, ізоляція користувачів; виняток посеред вставки (дублікат у БД) відкочує транзакцію й лишає старий список.

## 4. Кластер і сторінка «Загальне»

- [x] 4.1 Створити `UI/Filament/Clusters/Profile/ProfileCluster.php` вручну (slug `profile`, «Профіль», `OutlinedUserCircle`, субнавігація `Start`, `$navigationSort = 90`). Перевірка: після додавання першої сторінки (4.2) `php artisan route:list --path=admin/profile` показує маршрути кластера; `app/Filament` не існує.
- [x] 4.2 Сторінка `UI/Filament/Pages/EditProfileGeneral` (`$cluster`, slug `general`, `$navigationSort = 10`, форма зі `statePath('data')`, кнопка «Зберегти», `save()` → `UpdateUserProfileAction` з `auth()->id()` і сповіщення). Поля: «Ім'я», «Прізвище», «Email», «Телефон», «Локація», «Заголовок» і «Про себе» (`summary`, `Textarea` до 5000 символів на всю ширину), правила — за рішенням 5. Перед написанням звірити API сторінки з формою у Filament 5 через `search-docs`. Перевірка: functional-тести — збереження з ім'ям, прізвищем і заголовком та сповіщення; повторне відкриття показує збережені значення; підстановка email входу, коли профілю немає; збереження іншого контактного email не змінює email входу; помилки для порожнього імені, `roman@`, `abc123`, 5001 символу в «Про себе»; `+380 (67) 123-45-67` проходить; два користувачі бачать свої дані.
- [x] 4.3 Навігація розділу. Перевірка: functional-тести — гостя з `/admin/profile/general` перенаправлено на `/admin/login`; у головній навігації є «Профіль» і немає окремих сторінок; URL кластера веде на «Загальне»; наявні тести `admin-settings` проходять.

## 5. Сторінки зі списками

- [x] 5.1 `EditProfileLinks` (slug `links`, sort 20, Repeater `links` з `label` + datalist і `url` з `url:http,https`, drag-and-drop, `array_values()` перед `SyncProfileLinksAction`). Перевірка: functional-тести — додавання LinkedIn і GitHub, власна назва «Блог», видалення одного, зміна порядку зберігається після повторного `mount`, помилки для `linkedin.com/in/roman`, `ftp://…` і порожньої назви без зміни збереженого списку; збереження порожнього списку користувачем Б не зачіпає посилання користувача А.
- [x] 5.2 `EditProfileEducation` (slug `education`, sort 30, Repeater `education`, `collapsible()`, `itemLabel` «<Тип> · <Назва>», поля й правила за рішенням 5, `afterOrEqual('started_on')`). Перевірка: functional-тести — університет із датами й сертифікат із посиланням зберігаються в порядку введення; курс лише з типом і назвою; видалення; зміна порядку; три варіанти типу; помилки для порожньої назви, дати завершення раніше за початок і `not a url` без зміни списку.
- [x] 5.3 `EditProfileLanguages` (slug `languages`, sort 40, Repeater `languages` з `language` + datalist і `level` Select, closure-правило повтору без урахування регістру й пробілів). Перевірка: functional-тести — «Українська — Рідна» і «Англійська — B2» зберігаються в порядку; зміна рівня на C1; видалення; сім варіантів рівня в порядку; помилки для мови без рівня й для «Англійська» / «англійська» без зміни списку.
- [x] 5.4 Субнавігація. Перевірка: functional-тест — на сторінці «Освіта» субнавігація містить «Загальне», «Посилання», «Освіта», «Мови» в цьому порядку, а «Освіта» активна; адреси всіх чотирьох сторінок — `/admin/profile/{general,links,education,languages}`.

## 6. Інтеграційна перевірка

- [x] 6.1 Запустити тести: `DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Profile app/Containers/AppSection/Status app/Containers/AppSection/Company`. Усі мають пройти.
- [x] 6.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer` і переконатися, що нових помилок немає, а в коді немає коментарів, тільки tag-only PHPDoc.
- [x] 6.3 Перевірити вручну в браузері (`http://localhost:8000/admin`): відкрити «Профіль», заповнити «Загальне», додати два посилання й перетягнути їх, додати університет і сертифікат, додати дві мови; після перезавантаження кожної сторінки дані й порядок збереглися.

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive add-user-profile`.
