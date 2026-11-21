# Tasks

Тести запускати лише проти тестової БД: `docker compose exec -T -e DB_DATABASE=hunt-board_testing app vendor/bin/phpunit <шлях|--filter>`.

## 1. Локаль і мовні файли

- [x] 1.1 Змінити дефолт `locale` у `config/app.php` на `uk` (`fallback_locale` лишити `en`) і виставити `APP_LOCALE=uk` у `.env` та `.env.example`. Перевірка: `docker compose exec app php artisan config:show app.locale` показує `uk`.
- [x] 1.2 У `bootstrap/app.php` перенаправити шлях мовних файлів на `app/Ship/Languages` через `useLangPath()` на створеному застосунку. Перевірка: `docker compose exec app php artisan tinker --execute 'echo lang_path();'` повертає шлях до `app/Ship/Languages`.
- [x] 1.3 Створити `app/Ship/Languages/uk/validation.php` з повним набором правил Laravel 12 українською (`attributes` і `custom` — порожні масиви, без коментарів). Перевірка: `tinker` з `__('validation.required', ['attribute' => 'Назва'])` повертає український текст.
- [x] 1.4 Створити `app/Ship/Languages/uk.json` з перекладами JSON-ключів стандартного шаблону листа Laravel («Hello!», «Whoops!», «Regards,», підрядок про кнопку, «All rights reserved.»). Перевірка: `tinker` з `__('Regards,')` повертає український текст.
- [x] 1.5 Додати функціональний PHPUnit-тест у контейнері Status (`Tests/Functional/Filament/...`): локаль застосунку `uk`, а збереження статусу з порожньою назвою в `ManageStatuses` дає на полі `name` українське повідомлення валідації. Перевірка: тест проходить.

## 2. Підписи панелі та довідника статусів

- [x] 2.1 У `SettingsCluster` додати `$navigationLabel` і `$clusterBreadcrumb` = `'Налаштування'` з PHPDoc `@var`. Оновити в `StatusResourceTest` очікування `'Settings'` на `'Налаштування'`. Перевірка: тест головної навігації проходить.
- [x] 2.2 У `StatusResource` додати `$modelLabel = 'статус'`, `$pluralModelLabel = 'статуси'`, `$navigationLabel = 'Статуси'` з PHPDoc `@var`. Оновити в `StatusResourceTest` очікування `'Statuses'` на `'Статуси'`. Перевірка: тести субнавігації й головної навігації проходять.
- [x] 2.3 У `StatusForm` задати `->label('Назва')` і `->label('Колір')`, а прев'ю без назви показувати як `'Приклад'`. У `StatusesTable` задати колонці `->label('Назва')`. Додати до тесту з 1.5 перевірку, що форма створення показує «Назва» і «Колір», а сторінка показує заголовок «Статуси». Перевірка: тест проходить.
- [x] 2.4 Переписати `StatusColor::getLabel()` на `match` з українськими назвами пресетів (Сірий … Рожевий за специфікацією). Оновити `StatusColorTest`: `BLUE` → `'Синій'`, а всі 12 назв унікальні. Перевірка: `--filter StatusColorTest` проходить, значення enum не змінилися.
- [x] 2.5 Перевірити, що адреса й збережене значення не змінилися: тест на `/admin/settings/statuses` у `StatusResourceTest` проходить, а тест створення статусу з пресетом `blue` зберігає `blue`. Перевірка: `vendor/bin/phpunit app/Containers/AppSection/Status` — усі тести проходять.

## 3. Листи-сповіщення

- [x] 3.1 Перекласти тему й рядки в `Authentication/Notifications/Welcome.php` та `EmailVerified.php`; оновити `WelcomeTest` і `EmailVerifiedTest`, щоб вони перевіряли українську тему. Перевірка: `vendor/bin/phpunit app/Containers/AppSection/Authentication` проходить.
- [x] 3.2 Перекласти тему й рядки в `User/Notifications/PasswordUpdatedNotification.php`; оновити `PasswordUpdatedNotificationTest`, щоб він перевіряв українську тему. Перевірка: `vendor/bin/phpunit app/Containers/AppSection/User` проходить.

## 4. Інтеграційна перевірка

- [x] 4.1 Запустити весь набір тестів проти `hunt-board_testing`. Перевірка: усі тести проходять. Підсумок: усі тести контейнерів проходять (92); 4 падіння `testConfigHasCorrectValues` (App, Mail, DebugBar, IdeHelper) були ще до зміни й не стосуються локалі.
- [x] 4.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: нових помилок немає, `composer fixer` не додає змін. Підсумок: `composer fixer` нічого не змінив; у PHPStan у новому тесті лишилися тільки ті самі помилки `User::factory()` / dynamic static call, що вже є в усіх наявних тестах проєкту.
- [x] 4.3 Вручну в браузері (`http://localhost:8000/admin`): вхід, дашборд, меню користувача, «Налаштування» → «Статуси», створення, редагування, видалення й масове видалення, пошук, помилка валідації та сповіщення про збереження — все українською. Англійські рядки, що лишилися, записати й виправити або зафіксувати в design.md як відомі. Перевірка: описаний сценарій пройдено.

## Workflow follow-up

- Після рев'ю й проходження тестів архівувати зміну: `openspec archive localize-interface-uk`.
