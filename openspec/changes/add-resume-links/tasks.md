# Tasks

## 1. Залежності та іконки

- [x] 1.1 Погодити з користувачем набір брендових іконок і додати пакет (рекомендація `owenvoke/blade-fontawesome`). Перевірка: `docker compose exec app php artisan icons:list` показує `fab-github` і `fab-linkedin`.
- [x] 1.2 Створити `Resume/Support/ResumeLinkIcon` з реєстром ключів і назв (Heroicons + бренди). Перевірка: юніт-тест, що кожен ключ існує в Blade Icons.

## 2. Дані

- [x] 2.1 Міграція `create_resume_links_table` (`bigint` id, `resume_id` з cascade, `icon` nullable, `label`, `url`, `sort_order`, `is_visible` default true, індекс). Перевірка: `docker compose exec app php artisan migrate` і юніт-тест міграції.
- [x] 2.2 Модель `ResumeLink`, відношення `Resume::links()` (сортування за `sort_order`), `ResumeLinkFactory`, репозиторій. Перевірка: юніт-тест відношення й каскадного видалення.

## 3. Бізнес-логіка

- [x] 3.1 `ReplaceResumeLinksTask`: заміна списку, `sort_order` із позиції, `is_visible` за замовчуванням `true`. Перевірка: юніт-тест Task.
- [x] 3.2 Підключити Task у `CreateResumeAction` і `UpdateResumeAction` в `DB::transaction`; без ключа `links` список не змінюється. Перевірка: юніт-тести Actions, зокрема відкат при помилці.

## 4. Інтерфейс

- [x] 4.1 `ResumeLinksRepeater` із полями icon/label/url/is_visible, drag-and-drop, без `Section`. Підключити в `ResumeForm` на всю ширину. Перевірка: Livewire-тест присутності й порядку полів.
- [x] 4.2 `CreateResume` і `EditResume`: передавати `links` в Actions, заповнювати форму наявними посиланнями. Перевірка: функціональні тести створення, редагування, зміни порядку.
- [x] 4.3 Тести валідації: обовʼязковість підпису й адреси, недопустимі схеми, довжина. Перевірка: функціональні тести.
- [x] 4.4 Переконатися, що картка списку не показує посилань. Перевірка: наявні тести `ListResumes` проходять.

## 5. Завершення

- [x] 5.1 Запустити `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` для контейнера `Resume`, `vendor/bin/phpstan analyse`, `composer fixer`. Перевірка: усе зелене.
- [x] 5.2 Запустити `openspec validate add-resume-links --strict`.
