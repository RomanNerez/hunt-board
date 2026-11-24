# Tasks

Тести запускати на тестовій БД: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit <шлях>`. Нові тести — функціональні PHPUnit-класи через `Livewire::test(...)`.

## 1. Дані та модель нотатки

- [x] 1.1 Додати міграцію `Vacancy/Data/Migrations/…_create_vacancy_notes_table.php` (`vacancy_id` FK `cascadeOnDelete`, `title` string(255), `body` longText, `occurred_at` dateTime, timestamps, індекс `['vacancy_id', 'occurred_at']`) за design.md, рішення 2. Перевірка: `migrate` і `migrate:rollback --step=1` на `hunt-board_testing` проходять
- [x] 1.2 Винести очищення HTML з `Vacancy::sanitizeDescription()` у трейт `Vacancy/Models/Concerns/SanitizesRichText.php` (`public static sanitizeRichText()`), перевести на нього мутатор `description` (рішення 3). Перевірка: наявні тести `Vacancy` про опис (`ListVacanciesTest`, `ViewVacancyTest`, `VacancyTest`) зелені без змін
- [x] 1.3 Додати модель `VacancyNote` (fillable, cast `occurred_at`, мутатор `body` через трейт, `vacancy(): BelongsTo`), зв'язок `Vacancy::notes(): HasMany`, `VacancyNoteFactory`, `VacancyNoteRepository`, `VacancyNotePolicy` (рішення 1, 2). Перевірка: використовується в тестах задачі 2.4; `MigrationTest` доповнено перевіркою колонок `vacancy_notes` — зелений
- [x] 1.4 Додати `Create`/`Update`/`DeleteVacancyNoteAction` і відповідні Tasks (рішення 7: `vacancy_id` задає лише Create, Update змінює тільки `title`, `body`, `occurred_at`). Перевірка: використовується в тестах задачі 2.4

## 2. Сторінка «Нотатки» вакансії

- [x] 2.1 Додати `VacancyNoteForm` (заголовок, «Коли сталося» з типовим «зараз», `RichEditor body` з правилом порожнього тексту після очищення) за рішенням 5. Перевірка: використовується в тестах задачі 2.4
- [x] 2.2 Додати `VacancyNotesTable` (картки `Stack` + `contentGrid(['default' => 1])`, порядок `occurred_at desc, id desc`, стандартна пагінація, `EditAction`/`DeleteAction` у `slideOver` через Actions, порожній стан) за рішенням 6. Перевірка: використовується в тестах задачі 2.4
- [x] 2.3 Додати сторінку `ManageVacancyNotes` (`ManageRelatedRecords`, заголовок «Нотатки: {назва}», хлібні крихти, `CreateAction` «Додати нотатку» в `slideOver` через `CreateVacancyNoteAction`), зареєструвати `'notes' => '/{record}/notes'` у `VacancyResource::getPages()` і в `getRecordSubNavigation()` (рішення 4). Перевірка: `/admin/vacancies/{id}/notes` відкривається в dev-середовищі, у субнавігації два пункти
- [x] 2.4 Додати `Vacancy/Tests/Functional/Filament/Resources/Vacancies/Pages/ManageVacancyNotesTest.php` за дельтою `vacancy-notes` (перелік сценаріїв — design.md, рішення 9) та у `ViewVacancyTest` — пункт «Нотатки» в субнавігації й видалення вакансії разом з її нотатками (нотатка іншої вакансії лишається). Перевірка: тести зелені

## 3. Видалення старого поля `vacancies.notes`

- [x] 3.1 Додати міграцію `…_drop_notes_from_vacancies_table.php` (мітка часу пізніша за 1.1; `down` повертає `text('notes')->nullable()`), прибрати `notes` з `Vacancy` (`@property`, `$fillable`) і `VacancyFactory::complete()` (рішення 2). Перевірка: `migrate` і `migrate:rollback --step=1` на `hunt-board_testing` проходять; `MigrationTest` оновлено (немає `notes` у `vacancies`) і він зелений
- [x] 3.2 Прибрати `Textarea notes` з `VacancyForm`, секцію «Нотатки» й `multilineEntry()` з `VacancyInfolist` (рішення 8); оновити `CreateVacancyActionTest` і `ViewVacancyTest` (без `notes`, «Огляд» не показує секцію «Нотатки»). Перевірка: тести контейнера `Vacancy` зелені; `grep -rn "'notes'" app/Containers/AppSection/Vacancy` знаходить лише зв'язок/сторінку нотаток

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити всі тести контейнера: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit app/Containers/AppSection/Vacancy`. Перевірка: усі зелені
- [x] 4.2 `vendor/bin/phpstan analyse app/Containers/AppSection/Vacancy` і `composer fixer`. Перевірка: PHPStan без нових помилок поза `Tests/`, fixer не лишає змін, у коді немає коментарів, PHPDoc лише з тегами
- [x] 4.3 `docker compose exec app php artisan migrate` на dev-БД і вручну: у вакансії відкрити «Нотатки», додати «Результат співбесіди з HR» зі списком питань і вчорашньою датою, відредагувати, видалити; перевірити, що на «Огляді» й у формі вакансії немає старого поля «Нотатки». Перевірка: без помилок, записи в `vacancy_notes`, `last-error` без нових винятків
- [x] 4.4 `openspec validate add-vacancy-notes --strict`. Перевірка: валідація проходить

## Workflow follow-up

- Після рев'ю й зелених тестів — `openspec archive add-vacancy-notes`.
