# Tasks

Нових unit-тестів не пишемо: поведінку перевіряють функціональні тести всього флоу (задача 3.4). Наявні unit-тести контейнера `Vacancy` лише виправляємо під видалені поля `vacancy_source_id` і `url`, нових перевірок у них не додаємо.

## 1. Дані: зв'язувальна таблиця й модель

- [x] 1.1 Додати міграцію `vacancy_vacancy_source` у `Vacancy/Data/Migrations` за design.md (рішення 1, 2): таблиця з `url`, унікальна пара, видалення старих колонок без перенесення даних, `down()` повертає порожні колонки. У наявному `Vacancy/Tests/Unit/Data/Migrations/MigrationTest` прибрати перевірки колонок `vacancy_source_id`/`url` і сценарій «видалення джерела лишає вакансію з `vacancy_source_id = null`». Перевірка: `migrate` на `hunt-board_testing` проходить, `MigrationTest` зелений
- [x] 1.2 Оновити модель `Vacancy` (design.md, рішення 3): `vacancySources()` `belongsToMany` з `url` у pivot, без `vacancy_source_id`/`url` у `$fillable` і PHPDoc, `DISPLAY_RELATIONS`. Оновити `VacancyFactory` (повний стан має два джерела з посиланнями); `VacancyFactoryTest` лише виправити, якщо звертається до видалених полів. Перевірка: `VacancyFactoryTest` зелений
- [x] 1.3 Знайти й виправити решту звертань до `vacancySource`/`vacancy_source_id`/`url` вакансії в коді й тестах контейнерів `Vacancy` і `VacancySource` (`grep -rn`). Перевірка: grep не знаходить старих звертань поза міграціями

## 2. Бізнес-логіка: запис джерел

- [x] 2.1 Додати `Vacancy/Tasks/ReplaceVacancySourcesTask` (design.md, рішення 4): `sync()` пар «джерело — посилання», порожній `url` → `null`. Перевірка: функціональні тести задачі 3.4
- [x] 2.2 Розширити `CreateVacancyAction` і `UpdateVacancyAction`: ключ `sources` у тій самій транзакції через `ReplaceVacancySourcesTask`; `UpdateVacancyAction` без ключа `sources` не змінює джерела. `CreateVacancyActionTest`, `UpdateVacancyActionTest` лише виправити під видалені поля. Перевірка: ці тести зелені, поведінку перевіряють функціональні тести задачі 3.4

## 3. Filament: форма, таблиця, огляд

- [x] 3.1 У `VacancyForm` замінити `vacancy_source_id` і `url` на `Repeater sources` (design.md, рішення 5) з наявним створенням джерела з форми; у `ViewVacancy` заповнювати `sources` у формі редагування з `vacancySources`. Перевірка: використовується в 3.4
- [x] 3.2 У `VacanciesTable` прибрати колонки «Формат», «Зарплата», «Наступна дія»; колонка «Джерела» бейджами й фільтр `vacancySources` (design.md, рішення 6). Перевірка: використовується в 3.4
- [x] 3.3 У `VacancyInfolist` замінити джерело й посилання на `RepeatableEntry vacancySources` (design.md, рішення 7). Перевірка: використовується в 3.4
- [x] 3.4 Оновити функціональні тести `ListVacanciesTest` і `ViewVacancyTest` (повний флоу через Filament) за дельтами spec `vacancies` і `vacancy-sources`: таблиця показує обидва джерела й не має колонок `work_format`, `salary`, `next_action_at` (`assertTableColumnDoesNotExist`); фільтр за джерелом для вакансії з кількома джерелами; створення з двома джерелами й різними посиланнями (перевірити записи в БД); джерело без посилання зберігається з `null`; повтор джерела відхилено; посилання без джерела відхилено; URL без схеми й `ftp://` у рядку джерела відхилено; створення джерела з рядка repeater; редагування в боковій панелі показує наявні джерела, повторне збереження лишає посилання наявного джерела, прибраний рядок зникає, прибрання всіх рядків лишає вакансію без джерел; «Огляд» показує джерела, посилання з `target="_blank"`, джерело без посилання без лінка. Додати функціональний тест у `Vacancy/Tests/Functional`: видалення джерела на сторінці `ManageVacancySources` лишає вакансію з рештою джерел. Прибрати сценарії старого одиночного поля посилання й колонки вилки. Перевірка: тести зелені

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити тести контейнерів `Vacancy` і `VacancySource`: `DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Vacancy app/Containers/AppSection/VacancySource` (у контейнері `app`). Перевірка: усі зелені
- [x] 4.2 Запустити `vendor/bin/phpstan analyse app/Containers/AppSection/Vacancy app/Containers/AppSection/VacancySource` і `composer fixer`. Перевірка: PHPStan без помилок у коді цих контейнерів поза `Tests/` (помилки, що вже були в проєкті, і типові для тестів помилки поза зміною), fixer не лишає змін, у коді немає коментарів, PHPDoc лише з тегами
- [x] 4.3 `docker compose exec app php artisan migrate` на dev-БД і вручну: додати вакансії два джерела з різними посиланнями у боковій панелі; таблиця без «Формат»/«Зарплата»/«Наступна дія», з обома джерелами. Перевірка: без помилок, `browser-logs` без винятків
- [x] 4.4 `openspec validate add-multiple-vacancy-sources --strict`. Перевірка: валідація проходить

## Workflow follow-up

- Спочатку заархівувати `add-vacancies`, бо дельти цієї зміни модифікують його специфікації.
- Після рев'ю й зелених тестів — `openspec archive add-multiple-vacancy-sources`.
