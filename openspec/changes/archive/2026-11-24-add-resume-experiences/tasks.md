# Tasks

## 1. Дані

- [x] 1.1 Enum `ResumeEmploymentType` з п'ятьма значеннями й українськими підписами. Перевірка: юніт-тест значень і підписів.
- [x] 1.2 Міграція `create_resume_experiences_table` (`bigint` id, `resume_id` з cascade, колонки з design.md, індекс `(resume_id, sort_order)`). Перевірка: `docker compose exec app php artisan migrate` і юніт-тест міграції.
- [x] 1.3 Модель `ResumeExperience` (касти: дати, enum, boolean), відношення `Resume::experiences()` із сортуванням за `sort_order`, `ResumeExperienceFactory`, репозиторій. Перевірка: юніт-тест відношення й каскадного видалення.

## 2. Бізнес-логіка

- [x] 2.1 `ReplaceResumeExperiencesTask`: заміна списку, `sort_order` із позиції, дати до першого числа місяця, `is_visible` за замовчуванням `true`, порожні необовʼязкові поля як `null`. Перевірка: юніт-тест Task.
- [x] 2.2 Підключити Task у `CreateResumeAction` і `UpdateResumeAction` в одній `DB::transaction` разом із посиланнями; без ключа `experiences` список не змінюється. Перевірка: юніт-тести Actions, зокрема відкат при помилці.

## 3. Інтерфейс

- [x] 3.1 `ResumeExperiencesRepeater` з усіма полями, drag-and-drop, `itemLabel`, без `Section`; підключити в `ResumeForm` на всю ширину. Перевірка: Livewire-тест присутності й порядку полів.
- [x] 3.2 `CreateResume` і `EditResume`: передавати `experiences` в Actions, заповнювати форму наявними записами. Перевірка: функціональні тести створення, редагування, зміни порядку.
- [x] 3.3 Тести валідації: обовʼязкові `company`, `position`, `start_date`; `end_date` не раніше `start_date`; схеми `company_url`; довжини; допустимі значення `employment_type`. Перевірка: функціональні тести.
- [x] 3.4 Переконатися, що картка списку не показує досвід. Перевірка: наявні тести `ListResumes` проходять.

- [x] 3.5 Технології: `ResumeExperience` як `Stackable`, morph-аліас, `StackSelect` у repeater, синхронізація в Actions, відʼєднання при заміні й видаленні резюме. Перевірка: юніт-тести Actions і функціональні тести форми.

## 4. Завершення

- [x] 4.1 Запустити `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` для контейнера `Resume`, `vendor/bin/phpstan analyse`, `composer fixer`. Перевірка: усе зелене.
- [x] 4.2 Запустити `openspec validate add-resume-experiences --strict`.
