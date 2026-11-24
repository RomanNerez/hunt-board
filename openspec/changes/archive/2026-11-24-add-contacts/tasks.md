# Tasks

Unit-тестів не пишемо: поведінку перевіряють лише функціональні тести через Filament (`Livewire::test(...)`, PHPUnit-класи). Тести запускати на тестовій БД: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit <шлях>`.

## 1. Спільні компоненти джерел у `VacancySource`

- [x] 1.1 Перенести repeater джерел із `VacancyForm` у `VacancySource/UI/Filament/Resources/VacancySources/Schemas/VacancySourcesRepeater.php` (`make()` і `stateFrom()`), а `Vacancy/UI/Filament/Support/VacancySourceLink.php` — у `VacancySource/UI/Filament/Support/VacancySourceLink.php` (design.md, рішення 4, 6). Перевести на них `VacancyForm`, `ViewVacancy`, `VacancyInfolist`. Перевірка: наявні функціональні тести `Vacancy` і `VacancySource` зелені без змін, `grep` не знаходить старого namespace `Vacancy\UI\Filament\Support\VacancySourceLink`

## 2. Розділ «Контакти»

- [x] 2.1 Створити контейнер `app/Containers/AppSection/Contact` за design.md (рішення 1): `composer.json`, `Providers/ContactServiceProvider.php`, `Tests/ContainerTestCase.php`, `Tests/FunctionalTestCase.php`. Перевірка: `docker compose exec app composer dump-autoload` без помилок
- [x] 2.2 Додати міграції `contacts` і `contact_vacancy_source` у `Contact/Data/Migrations` (design.md, рішення 2). Перевірка: `migrate` і `migrate:rollback --step=2` на `hunt-board_testing` проходять
- [x] 2.3 Додати модель `Contact` зі зв'язком `vacancySources()`, `ContactFactory`, `ContactRepository`, `ContactPolicy` (design.md, рішення 1, 3). Перевірка: використовується в тестах задачі 2.6
- [x] 2.4 Додати `Create`/`Update`/`DeleteContactAction`, `Create`/`Update`/`DeleteContactTask`, `ReplaceContactSourcesTask` (design.md, рішення 5). Перевірка: використовується в тестах задачі 2.6
- [x] 2.5 Додати Filament-ресурс `ContactResource` з `ManageContacts`, `ContactForm` (ім'я + `VacancySourcesRepeater::make()`) і `ContactsTable` (ім'я, джерела, сортування й пошук за ім'ям, редагування, видалення, масове видалення) — без колонки «Вакансії», її додає задача 3.1 (design.md, рішення 6). Перевірка: `/admin/contacts` відкривається, використовується в тестах задачі 2.6
- [x] 2.6 Додати функціональні тести `Contact/Tests/Functional/Filament/Resources/Contacts/ContactResourceTest.php` (гість → логін, адреса `/admin/contacts`, немає сторінок create/edit, пункт у головній навігації) і `.../Pages/ManageContactsTest.php` за дельтою `contacts`: створення лише з ім'ям; порожнє ім'я відхилено; однакові імена; два джерела з різними посиланнями (записи в БД); джерело без посилання — `null`; повтор джерела відхилено; посилання без джерела відхилено; URL без схеми й `ftp://` відхилено; створення джерела з рядка repeater; таблиця показує ім'я й джерела; сортування й пошук за ім'ям; редагування показує поточні джерела й прибирає рядок; видалення й масове видалення. Перевірка: тести зелені
- [x] 2.7 Додати `Contact/Tests/Functional/Filament/VacancySources/DeletingVacancySourceTest.php`: видалення джерела на `ManageVacancySources` лишає контакт з рештою джерел і їхніми посиланнями (дельта `vacancy-sources`). Перевірка: тест зелений

## 3. Контакти вакансії

- [x] 3.1 Додати міграцію `contact_vacancy` у `Vacancy/Data/Migrations` (мітка часу пізніша за міграції задачі 2.2), зв'язки `Vacancy::contacts()` і `Contact::vacancies()`, `contacts.vacancySources` у `Vacancy::DISPLAY_RELATIONS`, колонку «Вакансії» в `ContactsTable` з `with(['vacancySources', 'vacancies'])` (design.md, рішення 2, 3, 6). Перевірка: `migrate` і `migrate:rollback --step=1` на `hunt-board_testing` проходять
- [x] 3.2 Додати `Vacancy/Tasks/SyncVacancyContactsTask` і ключ `contacts` у `Create`/`UpdateVacancyAction` в тій самій транзакції; `UpdateVacancyAction` без ключа `contacts` контакти не змінює (design.md, рішення 7). Перевірка: наявні unit-тести `Vacancy` зелені без змін, поведінку перевіряють тести задачі 3.5
- [x] 3.3 У `VacancyForm` додати секцію «Контакти» після «Джерела» з мультивибором контактів і створенням контакту через `ContactForm` + `CreateContactAction`; у `ViewVacancy` заповнювати `contacts` у формі редагування (design.md, рішення 7). Перевірка: використовується в тестах задачі 3.5
- [x] 3.4 У `VacancyInfolist` додати секцію «Контакти» після «Джерела» з іменами контактів і їхніми джерелами з посиланнями в новій вкладці, прочерк для вакансії без контактів (design.md, рішення 7). Перевірка: використовується в тестах задачі 3.5
- [x] 3.5 Розширити функціональні тести за дельтою `vacancies` і рештою дельти `contacts`: у `ListVacanciesTest` — створення вакансії з двома контактами (записи `contact_vacancy`), створення контакту з джерелом із форми вакансії й вибір його; у `ViewVacancyTest` — редагування показує поточні контакти, прибирання контакту лишає контакт у довіднику, огляд показує контакти з джерелами, посилання з `target="_blank"`, джерело без посилання без лінка, прочерк без контактів, видалення вакансії лишає контакт; у `ManageContactsTest` — колонка «Вакансії» показує прив'язану вакансію, один контакт у вакансіях різних компаній, видалення контакту лишає вакансію без нього. Перевірка: тести зелені

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити тести контейнерів `Contact`, `Vacancy`, `VacancySource`: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit app/Containers/AppSection/Contact app/Containers/AppSection/Vacancy app/Containers/AppSection/VacancySource`. Перевірка: усі зелені
- [x] 4.2 Запустити `vendor/bin/phpstan analyse app/Containers/AppSection/Contact app/Containers/AppSection/Vacancy app/Containers/AppSection/VacancySource` і `composer fixer`. Перевірка: PHPStan без помилок у коді цих контейнерів поза `Tests/` (помилки в тестах, які вже були в проєкті, сюди не входять), fixer не лишає змін, у коді немає коментарів, PHPDoc лише з тегами
- [x] 4.3 `docker compose exec app php artisan migrate` на dev-БД і вручну: створити контакт із джерелами LinkedIn і Telegram у розділі «Контакти», прив'язати його до вакансії в боковій панелі, побачити контакт на «Огляді» вакансії й вакансію в таблиці контактів. Перевірка: без помилок, записи в `contacts`, `contact_vacancy_source`, `contact_vacancy` у dev-БД, `last-error` без нових винятків
- [x] 4.4 `openspec validate add-contacts --strict`. Перевірка: валідація проходить

## Workflow follow-up

- Після рев'ю й зелених тестів — `openspec archive add-contacts`.
