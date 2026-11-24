# Tasks

## 1. Дані

- [x] 1.1 Міграція `create_resume_educations_table` (`bigint` id, `resume_id` з cascade, колонки з design.md, індекс `(resume_id, sort_order)`). Перевірка: `docker compose exec app php artisan migrate` і юніт-тест міграції.
- [x] 1.2 Модель `ResumeEducation` (касти: дати, boolean, `sort_order`), відношення `Resume::educations()` із сортуванням за `sort_order`, `ResumeEducationFactory`, репозиторій. Перевірка: юніт-тест відношення й каскадного видалення.

## 2. Бізнес-логіка

- [x] 2.1 `ReplaceResumeEducationsTask`: заміна списку, `sort_order` із позиції, дати до першого числа місяця, `is_visible` за замовчуванням `true`, порожні необовʼязкові поля й дати як `null`. Перевірка: юніт-тест Task.
- [x] 2.2 Підключити Task у `CreateResumeAction` і `UpdateResumeAction` в одній `DB::transaction` разом із посиланнями й досвідом; без ключа `educations` список не змінюється. Перевірка: юніт-тести Actions, зокрема відкат при помилці.

## 3. Інтерфейс

- [x] 3.1 `ResumeEducationsRepeater` з усіма полями, drag-and-drop, `itemLabel`, `RichEditor` з мінімальним PDF-тулбаром, без `Section`; підключити в `ResumeForm` на всю ширину. Перевірка: Livewire-тест присутності й порядку полів.
- [x] 3.2 `CreateResume` і `EditResume`: передавати `educations` в Actions, заповнювати форму наявними записами. Перевірка: функціональні тести створення, редагування, зміни порядку.
- [x] 3.3 Тести валідації: обовʼязковий `institution`; `end_date` не раніше `start_date`, лише одна дата допустима; схеми `institution_url`; довжини. Перевірка: функціональні тести.
- [x] 3.4 Переконатися, що картка списку не показує освіту. Перевірка: наявні тести `ListResumes` проходять.

## 4. Завершення

- [x] 4.1 Запустити `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` для контейнера `Resume`, `vendor/bin/phpstan analyse`, `composer fixer`. Перевірка: усе зелене.
- [x] 4.2 Запустити `openspec validate add-resume-educations --strict`.
