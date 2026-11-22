# Tasks

## 1. Дані

- [x] 1.1 Міграція `profile_skill_groups` за рішенням 1 (`user_id` FK з `cascadeOnDelete` та індексом, `name` string(100), `skills` json, `sort`). Перевірка: `docker compose exec app php artisan migrate` проходить; `MigrationTest` перевіряє колонки таблиці.
- [x] 1.2 Модель `ProfileSkillGroup` (`@property`-PHPDoc, `$fillable`, каст `skills` → `array`, `belongsTo user`), `ProfileSkillGroupRepository`, `ProfileSkillGroupFactory`; зв'язок `skillGroups()` у `UserProfile` з `orderBy('sort')`. Перевірка: unit-тест фабрики (назва й непорожній список технологій); `UserProfileTest` — групи читаються за `sort`, а після видалення користувача не лишається жодної його групи.

## 2. Task і Action

- [x] 2.1 `ReplaceProfileSkillGroupsTask` (видалити групи користувача → вставити зі `sort` = індекс; у `skills` — `trim`, без порожніх, без точних дублікатів, порядок збережено) і `SyncProfileSkillGroupsAction` у транзакції. Перевірка: unit-тести — порядок груп і технологій; `[' Docker ', 'Docker', 'Redis']` зберігається як `['Docker', 'Redis']`; повторний виклик замінює список; порожній список очищає; групи іншого користувача залишаються.

## 3. Сторінка «Навички»

- [x] 3.1 `UI/Filament/Pages/EditProfileSkills` на базі `ProfileFormPage` (slug `skills`, `$navigationSort = 15`, Repeater `groups` з `name` і `TagsInput` `skills` за рішенням 3, `save()` → `SyncProfileSkillGroupsAction`). Перевірка: functional-тести з `Repeater::fake()` — додавання «Backend» і «Frontend» з технологіями та сповіщення; перейменування групи; видалення групи; зміна порядку груп і зміна порядку технологій зберігаються після повторного `mount`; видалення технології; помилки для групи без назви, групи без технологій і технології з 101 символу без зміни збереженого списку; збереження порожнього списку користувачем Б не зачіпає групи користувача А.
- [x] 3.2 Навігація. Оновити `ProfileSubNavigationTest` і `ProfileClusterTest`. Перевірка: субнавігація — «Загальне», «Навички», «Посилання», «Освіта», «Мови»; на `/admin/profile/skills` активний пункт «Навички»; `EditProfileSkills::getUrl()` — `/admin/profile/skills`; гостя з `/admin/profile/skills` перенаправлено на `/admin/login`; «Навички» немає в головній навігації; `/admin/profile` досі веде на «Загальне».

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити `docker compose exec -T -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit app/Containers/AppSection/Profile app/Containers/AppSection/Status app/Containers/AppSection/Company`. Усі тести мають пройти.
- [x] 4.2 Запустити `vendor/bin/phpstan analyse app/Containers/AppSection/Profile` і `composer fixer`. Перевірка: у нових класах поза тестами немає інших типів помилок, ніж наявні в контейнері (`staticMethod.dynamicCall` на `orderBy`); fixer не змінює файлів; у коді немає коментарів, тільки tag-only PHPDoc.
- [x] 4.3 Перевірити вручну в браузері (`http://localhost:8000/admin/profile/skills`): додати групи «Backend» і «Frontend» з кількома технологіями, перетягнути групу й тег, зберегти; після перезавантаження порядок і дані збереглися.

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive add-profile-skills`.
