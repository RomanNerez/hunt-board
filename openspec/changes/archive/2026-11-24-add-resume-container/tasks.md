# Tasks

Тести запускати на тестовій БД: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit <шлях>`. Нові тести — PHPUnit-класи; для сторінок Filament — `Livewire::test(...)`.

## 1. Контейнер, дані й модель

- [x] 1.1 Створити контейнер `app/Containers/AppSection/Resume` (`composer.json`, `Providers/ResumeServiceProvider`, `Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}` за зразком `Contact`). Перевірка: `php artisan list` не падає, провайдер підхоплюється
- [x] 1.2 Додати enum `Enums/ResumeKind` (`master`, `application`; `HasLabel` «Основне»/«Під вакансію», `HasColor`) і міграцію `Data/Migrations/2026_11_24_130000_create_resumes_table.php` (рішення 2: FK `user_id` `cascadeOnDelete`, `kind`, `first_name`, `last_name`, `headline` nullable, `summary` nullable, timestamps, індекс `['user_id','updated_at']`). Перевірка: `migrate` і `migrate:rollback --step=1` на `hunt-board_testing` проходять
- [x] 1.3 Додати модель `Resume` (fillable, cast `kind`, `user(): BelongsTo`), `ResumeFactory` (стани `master()`, `application()`), `ResumeRepository`. Перевірка: використовується в тестах задач 2–3
- [x] 1.4 Додати `ResumePolicy` (рішення 7: `view`/`update`/`delete` лише власнику). Перевірка: unit-тест `ResumePolicyTest` зелений

## 2. Actions і Tasks

- [x] 2.1 Додати `Create`/`Update`/`DeleteResumeTask` і `Create`/`Update`/`DeleteResumeAction` (рішення 3: `user_id` задає лише Create, Update змінює тільки `kind`, `first_name`, `last_name`, `headline`, `summary`). Перевірка: unit-тести Actions/Tasks зелені, зокрема сценарій «власник не змінюється»
- [x] 2.2 Додати unit-тести `ResumeKind` і міграції (колонки, каскадне видалення резюме разом із користувачем). Перевірка: тести зелені

## 3. Інтерфейс Filament

- [x] 3.1 Додати `ResumeForm` (рішення 5: тип, імʼя, прізвище, заголовок, короткий опис). Перевірка: використовується в тестах 3.5
- [x] 3.2 Додати `ResumesTable` із картками (рішення 6: `Stack`/`Split`, бейдж типу, `contentGrid`, порядок `updated_at desc`, `recordUrl` на edit, `EditAction`/`DeleteAction` через Action, порожній стан) і CSS `UI/WEB/Assets/css/resume-cards.css`, зареєстрований у `ResumeServiceProvider`. Виконати `docker compose exec app php artisan filament:assets`. Перевірка: картки в dev-середовищі мають вигляд як «Нотатки» вакансії
- [x] 3.3 Додати `ResumeResource` (рішення 4: пункт верхнього рівня «Резюме», `navigationSort = 15`, скоуп `user_id = Auth::id()`, сторінки `index`/`create`/`edit`) і сторінки `ListResumes` (кнопка «Створити резюме» веде на `create`), `CreateResume`, `EditResume` (збереження через Actions, редірект до списку, `DeleteAction` у шапці edit). Перевірка: `/admin/resumes`, `/admin/resumes/create`, `/admin/resumes/{id}/edit` відкриваються
- [x] 3.4 Переконатися, що `AdminPanelProvider::discoverContainerComponents()` підхопив ресурс без змін провайдера. Перевірка: `php artisan route:list --name=resumes` показує три маршрути; пункт «Резюме» зʼявився в меню

## 4. Функціональні тести

- [x] 4.1 Додати `Tests/Functional/Filament/Resources/Resumes/ResumeResourceTest` (гість → login, сторінка за `/admin/resumes`, набір сторінок `index`/`create`/`edit`) і `Pages/ListResumesTest` (картки лише власних резюме, бейдж типу, порядок, порожній стан, видалення з підтвердженням і скасування). Перевірка: тести зелені
- [x] 4.2 Додати `Pages/CreateResumeTest` (успішне створення з власником, валідація обовʼязкових полів і довжини, редірект до списку, тип за замовчуванням `master`) і `Pages/EditResumeTest` (збереження змін, видалення зі сторінки, 404 на чуже резюме). Перевірка: тести зелені

## 5. Інтеграційна перевірка

- [x] 5.1 Запустити всі тести контейнера: `docker compose exec -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit app/Containers/AppSection/Resume`. Перевірка: усі зелені
- [x] 5.2 `vendor/bin/phpstan analyse app/Containers/AppSection/Resume` і `composer fixer`. Перевірка: PHPStan без нових помилок поза `Tests/`, fixer не лишає змін, у коді немає коментарів, PHPDoc лише з тегами
- [x] 5.3 `docker compose exec app php artisan migrate` на dev-БД і вручну: створити master і application-резюме, відредагувати, видалити; перевірити вигляд карток на вузькому екрані. Перевірка: без помилок, `last-error` без нових винятків
- [x] 5.4 `openspec validate add-resume-container --strict`. Перевірка: валідація проходить

## Workflow follow-up

- Після рев'ю й зелених тестів — `openspec archive add-resume-container`.
