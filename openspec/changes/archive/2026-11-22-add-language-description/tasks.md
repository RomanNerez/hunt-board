# Tasks

## 1. Дані

- [x] 1.1 Міграція `add_description_to_profile_languages_table` за рішенням 1 (`description` string(500), nullable). Перевірка: `docker compose exec app php artisan migrate` проходить, наявна мова в dev-базі лишається без змін; `MigrationTest` перевіряє колонку `description` (`varchar`) у `profile_languages`.
- [x] 1.2 Модель `ProfileLanguage`: `@property string|null $description` і `description` у `$fillable`. Перевірка: `ProfileLanguageFactoryTest` — створений запис має `description === null`.

## 2. Збереження

- [x] 2.1 `ReplaceProfileLanguagesTask`: додати `description` до атрибутів і нормалізацію за рішенням 2. Перевірка: unit-тести `SyncProfileLanguagesActionTest` — опис зберігається; `'  Технічна документація  '` зберігається як `'Технічна документація'`; `'   '`, `''` і відсутній ключ дають `null`.

## 3. Сторінка «Мови»

- [x] 3.1 `EditProfileLanguages`: поле `Textarea` `description` за рішенням 3, `mount()` заповнює опис. Перевірка: functional-тести в `EditProfileLanguagesTest` — додавання опису до наявної мови показується після повторного `mount`; мова без опису зберігається зі сповіщенням; заміна опису пробілами очищає його; після зміни порядку опис іде разом зі своєю мовою; опис із 501 символу дає помилку `languages.0.description` => `max` без зміни збереженого списку. Наявні тести мов проходять без змін.

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити `docker compose exec -T -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit app/Containers/AppSection/Profile`. Усі тести мають пройти.
- [x] 4.2 Запустити `vendor/bin/phpstan analyse app/Containers/AppSection/Profile` і `composer fixer`. Перевірка: у змінених класах поза тестами немає інших типів помилок, ніж наявні в контейнері (`staticMethod.dynamicCall` на `orderBy`); fixer не змінює файлів; у коді немає коментарів, тільки tag-only PHPDoc.
- [x] 4.3 Перевірити вручну в браузері (`http://localhost:8000/admin/profile/languages`): додати до «Англійська» опис «Вільне володіння для роботи з технічною документацією, спілкування з клієнтами та проведення нарад чи участі в них», зберегти; після перезавантаження опис на місці, поле стоїть під «Мова» й «Рівень» на всю ширину.

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive add-language-description`.
