# Tasks

## 1. Дані

- [x] 1.1 Enum `ResumeLanguageLevel` із сімома значеннями й підписами. Перевірка: юніт-тест значень і підписів.
- [x] 1.2 Міграція `create_resume_languages_table` (`bigint` id, `resume_id` з cascade, колонки з design.md, індекс `(resume_id, sort_order)`, унікальний індекс `(resume_id, language)`). Перевірка: `docker compose exec app php artisan migrate` і юніт-тест міграції, зокрема унікальності.
- [x] 1.3 Модель `ResumeLanguage` (касти: enum, boolean, `sort_order`), відношення `Resume::languages()` із сортуванням за `sort_order`, `ResumeLanguageFactory`, репозиторій. Перевірка: юніт-тест відношення й каскадного видалення.

## 2. Бізнес-логіка

- [x] 2.1 `ReplaceResumeLanguagesTask`: заміна списку, `sort_order` із позиції, `is_visible` за замовчуванням `true`, порожні `level` і `level_label` як `null`, приведення enum до значення. Перевірка: юніт-тест Task.
- [x] 2.2 Підключити Task у `CreateResumeAction` і `UpdateResumeAction` в одній `DB::transaction` разом з іншими списками; без ключа `languages` список не змінюється. Перевірка: юніт-тести Actions, зокрема відкат при помилці.

## 3. Інтерфейс

- [x] 3.1 `ResumeLanguagesRepeater` з усіма полями, drag-and-drop, `distinct:ignore_case` для назви, без `Section`; підключити в `ResumeForm` на всю ширину. Перевірка: Livewire-тест присутності полів.
- [x] 3.2 `CreateResume` і `EditResume`: передавати `languages` в Actions, заповнювати форму наявними записами. Перевірка: функціональні тести створення, редагування, зміни порядку.
- [x] 3.3 Тести валідації: обовʼязкова назва; довжини; допустимі значення `level`; дублікат назви без урахування регістру; та сама мова в різних резюме. Перевірка: функціональні тести.
- [x] 3.4 Переконатися, що картка списку не показує мови. Перевірка: наявні тести `ListResumes` проходять.

## 4. Завершення

- [x] 4.1 Запустити `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` для контейнера `Resume`, `vendor/bin/phpstan analyse`, `composer fixer`. Перевірка: усе зелене.
- [x] 4.2 Запустити `openspec validate add-resume-languages --strict`.
