# Tasks

## 1. Залежність і мови

- [x] 1.1 Додати `"spatie/laravel-translatable": "^6.0"` у `require` файлу `app/Containers/AppSection/Profile/composer.json`, виконати `docker compose exec app composer update spatie/laravel-translatable` і переконатися, що `composer show spatie/laravel-translatable` показує версію 6.x
- [x] 1.2 Створити enum `Profile/Enums/ProfileLocale` (`Ukrainian = 'uk'`, `English = 'en'`, `HasLabel`: «Українська» / «English», метод основної мови) і unit-тест `Tests/Unit/Enums/ProfileLocaleTest`; тест проходить
- [x] 1.3 У `ProfileServiceProvider::boot()` викликати `Translatable::fallback(fallbackLocale: 'uk')` через `ProfileLocale`; unit-тест моделі перевіряє, що в локалі `en` без англійського значення атрибут повертає українське

## 2. Схема БД і моделі

- [x] 2.1 У наявних міграціях `Data/Migrations/2026_11_22_100000`…`100005` змінити перекладні колонки на `->jsonb()` (`nullable()` зберігається, див. design, рішення 3). Перевірити: `migrate:refresh --path=app/Containers/AppSection/Profile/Data/Migrations` проходить на тестовій БД
- [x] 2.2 Оновити `Tests/Unit/Data/Migrations/MigrationTest`: перекладні колонки мають тип `jsonb`. Тест проходить
- [x] 2.3 Додати `HasTranslations` і `public array $translatable` у `UserProfile`, `ProfileEducation`, `ProfileLanguage`, `ProfileSkillGroup`, `ProfileLink` та оновити `@property` у PHPDoc; `UserProfileTest` перевіряє `getTranslations('headline')` для обох мов. Тест проходить

## 3. Tasks і Actions

- [x] 3.1 Нормалізація перекладних полів у `UpsertUserProfileTask`: лише ключі `ProfileLocale`, `trim`, відкидання порожніх мов, `null` для повністю порожнього необов'язкового поля. Оновити `UpdateUserProfileActionTest` (обидві мови, порожня англійська, `null`) і перевести `assertDatabaseHas` на `'<field>->uk'`. Тести проходять
- [x] 3.2 Те саме для `ReplaceProfileEducationTask`, `ReplaceProfileLanguagesTask`, `ReplaceProfileSkillGroupsTask`, `ReplaceProfileLinksTask`; оновити відповідні `Sync*ActionTest`. Тести проходять
- [x] 3.3 Прогнати тести фабрик (`Tests/Unit/Data/Factories`); фабрики з рядковими значеннями мають записувати їх в `uk` без змін коду. Якщо якийсь тест падає — виправити фабрику чи тест

## 4. Перемикач мови в `ProfileFormPage`

- [x] 4.1 Додати в `ProfileFormPage` властивість `public string $dataLocale = 'uk'`, перемикач у `getHeaderActions()` (`ActionGroup::make([...])->buttonGroup()` з діями «Українська» / «English», активна в `primary`) і метод `translatable(string $name, \Closure $field, bool $required = false): array` з парою полів `<name>.uk` / `<name>.en` з `visibleJs` на `$wire.dataLocale` (design, рішення 5)
- [x] 4.2 Перевизначити `onValidationError()` у `ProfileFormPage`: перемикати `dataLocale` на мову першого поля з помилкою (ключ закінчується на `.uk`/`.en`)

## 5. Сторінки профілю

- [x] 5.1 `EditProfileGeneral`: «Ім'я», «Прізвище» (обов'язкові для `uk`), «Локація», «Заголовок», «Про себе» через `translatable()`; `mount()` заповнює їх через `getTranslations()`. Оновити `EditProfileGeneralTest`: `dataLocale` за замовчуванням `uk`, збереження обох мов, порожня англійська, задовгий англійський «Про себе» нічого не зберігає, порожнє українське «Ім'я» при `dataLocale = 'en'` перемикає на `uk` і показує помилку. Тести проходять
- [x] 5.2 Вручну перевірити `/admin/profile/general` у браузері: перемикач у шапці, миттєве перемикання, незбережені зміни не губляться, спільні поля видно на обох мовах. Перевірити, що після додавання запису в repeater видимість мов не скидається (design, Risks) — перевірено користувачем у браузері
- [x] 5.3 `EditProfileEducation`: «Назва» (обов'язкова для `uk`), «Заклад / організація», «Опис» через `translatable()`; `itemLabel` бере назву активної мови з fallback на `uk`. Оновити `EditProfileEducationTest` (обидві мови, один запис на обидві мови, порожня українська назва дає помилку). Тести проходять
- [x] 5.4 `EditProfileLanguages`: «Мова» (обов'язкова для `uk`) і «Опис» через `translatable()`; `isRepeatedLanguage()` порівнює в межах локалі; datalist для `en` — англійські назви мов. Оновити `EditProfileLanguagesTest` (обидві мови, повтор «English»/«english» дає помилку). Тести проходять
- [x] 5.5 `EditProfileSkills`: «Назва групи» (обов'язкова для `uk`) через `translatable()`, технології спільні. Оновити `EditProfileSkillsTest`. Тести проходять
- [x] 5.6 `EditProfileLinks`: «Назва» (обов'язкова для `uk`) через `translatable()`, URL спільний. Оновити `EditProfileLinksTest` (порожня англійська назва зберігається, порожня українська дає помилку). Тести проходять
- [x] 5.7 `EditProfileLinks`: «URL» теж перекладний (обов'язковий для `uk`, правила `url:http,https` і `maxLength(2048)` для обох мов): колонка `profile_links.url` → `jsonb` у міграції `100001`, `url` у `ProfileLink::$translatable`, оновити `MigrationTest`, `SyncProfileLinksActionTest`, `EditProfileLinksTest` (окремий англійський URL, порожній англійський URL зберігається й читається з fallback на `uk`, некоректний англійський URL дає помилку). Тести проходять

## 6. Загальна перевірка

- [x] 6.1 `DB_DATABASE=hunt-board_testing vendor/bin/phpunit` (усі набори) проходить — Примітка: усі тести контейнера Profile (130) та решта наборів проходять; падають лише 4 тести конфігів (`Tests\Unit\Configs\AppTest`, `MailTest`, `App\Ship\Tests\Unit\Configs\DebugBarTest`, `IdeHelperTest`), які так само падають на HEAD без цієї зміни
- [x] 6.2 `vendor/bin/phpstan analyse` без помилок — Примітка: у коді контейнера Profile (без тестів) помилок стало менше (8 замість 10, лише наявні `orderBy()` dynamic call); у тестах нові помилки тих самих типів, що й у наявних тестах; загальний аналіз проєкту має 1000+ помилок, що існували до цієї зміни
- [x] 6.3 `composer fixer` не лишає змін у файлах, а в змінених файлах немає коментарів і є лише PHPDoc з тегами
- [x] 6.4 На dev-БД виконати `php artisan migrate:refresh --path=app/Containers/AppSection/Profile/Data/Migrations` і переконатися, що сторінки профілю відкриваються, перемикач працює, а обидві мови зберігаються — виконано на dev-БД, сторінки перевірено користувачем у браузері

## Workflow follow-up

- Після рев'ю й проходження тестів виконати `openspec archive add-profile-translations`.
