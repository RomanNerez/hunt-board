# Design

## Context

- Розділ «Профіль» складається з п'яти Filament-сторінок (`EditProfileGeneral`, `EditProfileEducation`, `EditProfileLanguages`, `EditProfileSkills`, `EditProfileLinks`), які наслідують `ProfileFormPage`. Це не ресурси, а власні `Page` з формою в `$data` і методом `save()`.
- «Загальне» пише через `UpdateUserProfileAction` → `UpsertUserProfileTask` (`updateOrCreate`). Сторінки-списки (освіта, мови, навички, посилання) пишуть через `Sync*Action` → `Replace*Task`: усі записи користувача видаляються й створюються наново з repeater-а.
- Перекладні колонки зараз `string`/`text`; `profile_skill_groups.skills` уже `json`. БД у всіх середовищах, зокрема в тестах, — Postgres 16.
- Локаль застосунку `uk`, `fallback_locale` `en` (з `localize-interface-uk`). Інтерфейс не змінюється.
- Користувач обрав перемикач мови в шапці сторінки (див. proposal.md).

## Goals / Non-Goals

**Goals:**
- Перекладність описано один раз у моделях і використано однаково на всіх п'яти сторінках.
- Перемикання мови працює миттєво й без втрати незбережених змін.

**Non-Goals:**
- Читання профілю певною мовою поза формами (резюме, експорт). З'явиться окремою зміною, але fallback для неї налаштовуємо вже зараз (рішення 6).
- Перемикач мови в таблицях чи ресурсах інших контейнерів.

## Decisions

### 1. `spatie/laravel-translatable` ^6, JSON-колонки
Моделі `UserProfile`, `ProfileEducation`, `ProfileLanguage`, `ProfileSkillGroup`, `ProfileLink` отримують трейт контейнера `Models/Concerns/HasProfileTranslations` (обгортка над `Spatie\Translatable\HasTranslations`) і властивість `public array $translatable` з переліком полів зі spec `profile-translations`. Обгортка змінює два випадки `setAttribute()`: `null` записується як SQL `NULL` (spatie записав би `{"uk": null}`), а асоціативний масив замінює всі переклади через `replaceTranslations()` (spatie лише дописує передані мови, тож очищена англійська лишилася б у БД). Залежність додається в `app/Containers/AppSection/Profile/composer.json` (його зливає `wikimedia/composer-merge-plugin`), а не в кореневий `composer.json`.
*Чому:* пакет запропонував і погодив користувач. Значення зберігаються в тій самій колонці як `{"uk": "...", "en": "..."}`, тож зв'язки, сортування й Actions не змінюються.
*Альтернатива:* окремі таблиці перекладів (`astrotomic/laravel-translatable`) — більше таблиць і join-ів для ~12 полів, і користувач обрав spatie. Окремі колонки `headline_en` тощо — подвоює схему й не масштабується на третю мову.

### 2. Мови — enum `ProfileLocale`
`app/Containers/AppSection/Profile/Enums/ProfileLocale.php`: `Ukrainian = 'uk'`, `English = 'en'`, `HasLabel` з підписами «Українська» / «English». Перша (`uk`) — основна: на ній обов'язковість і вона активна за замовчуванням. Enum використовують форма, перемикач, Tasks і валідація.
*Альтернатива:* список локалей у конфігу контейнера `appSection-profile.php`. Відхилено: потрібні ще й підписи та «основна мова», а enum дає їх типобезпечно, як `LanguageLevel`/`EducationType`.

### 3. `jsonb` у наявних міграціях
Профіль ще не в продакшені, тож перекладні колонки оголошуються як `jsonb` прямо в наявних міграціях контейнера:
- `2026_11_22_100000_create_user_profiles_table`: `first_name`, `last_name`, `location`, `headline`, `summary`;
- `2026_11_22_100001_create_profile_links_table`: `label`, `url` (мовні версії профілів, наприклад LinkedIn, мають різні адреси);
- `2026_11_22_100002_create_profile_educations_table`: `title`, `institution`, `description`;
- `2026_11_22_100003_create_profile_languages_table`: `language`;
- `2026_11_22_100004_create_profile_skill_groups_table`: `name`;
- `2026_11_22_100005_add_description_to_profile_languages_table`: `description` → `->jsonb()->nullable()->after('level')`.

`->string(...)`/`->text(...)` замінюються на `->jsonb(...)`, модифікатори `nullable()` лишаються, тож `NOT NULL` для обов'язкових полів зберігається.
*Чому:* без продакшен-даних перенесення не потрібне, а схема одразу описана в одному місці, без проміжного стану.
*Альтернатива:* нова міграція з `ALTER COLUMN … TYPE jsonb USING jsonb_build_object('uk', …)` і `down()` назад у текст. Відхилено як зайву: вона переносить дані, яких немає, і додає Postgres-специфічний SQL.
*Наслідок:* обмеження довжини тепер є лише у валідації форм, на рівні БД їх немає.

### 4. Стан форми: вкладені ключі за локаллю
Перекладне поле у формі — пара полів зі statePath `headline.uk` та `headline.en`. Тоді `$data['headline'] = ['uk' => ..., 'en' => ...]`, а це саме той формат, який приймає spatie. `mount()` заповнює форму через `$model->getTranslations('<field>')` (сирі значення без fallback), а Actions і Tasks отримують масиви без перетворень.
Пара полів будується одним методом `ProfileFormPage::translatable(string $name, \Closure $field, bool $required = false): array`. `$field` отримує statePath поля (`<name>.<locale>`) і `ProfileLocale` та повертає налаштоване поле (label, maxLength, rows тощо). Для `uk` метод додає `->required()`, якщо поле обов'язкове (параметр `bool $required`). Так кожна сторінка описує поле один раз, як зараз.

### 5. Перемикач: Livewire-властивість + Alpine `x-show`
- `ProfileFormPage` має `public string $dataLocale = 'uk'`. Під час відкриття сторінки це завжди `uk`; між сторінками значення не зберігається (spec: «Українська за замовчуванням»).
- У `getHeaderActions()` стоїть `ActionGroup::make([...])->buttonGroup()` з двома діями «Українська» / «English». Активна дія має колір `primary`, неактивна — `gray`; клік виставляє `dataLocale`.
- Кожне поле з пари отримує `->visibleJs("\$wire.dataLocale === '<locale>'")`. Це вбудований у Filament 5 механізм клієнтської видимості: обгортка прихованого поля отримує `fi-hidden` і не займає місця в сітці (на відміну від `Group` з `x-show`, яка лишала б порожню клітинку у формах на 2 колонки), а на сервері поле лишається видимим. Поля обох мов завжди є у формі, тож вони валідуються й зберігаються разом (spec: «Збереження обох мов однією дією»), а незбережені значення неактивної мови лишаються в стані Livewire.
- Спільні поля (email, дати, URL освіти, рівень, технології) не дублюються й видимі завжди. Repeater один, тож склад і порядок записів однакові для обох мов.
- `itemLabel` repeater-ів (освіта, навички, посилання) бере назву активної мови, а якщо її немає — українську.

*Альтернативи:*
- Пакет `lara-zeus/spatie-translatable` (перемикач `LocaleSwitcher`, який пропонувався як варіант UI). Відхилено: він розрахований на сторінки ресурсів (`EditRecord`, `ListRecords`…) і тримає в формі лише одну локаль, а наші сторінки — власні `Page` з repeater-ами, що повністю перезаписують записи. Щоб зберегти інші мови, довелося б кешувати їх вручну, і з'явилася б ще одна залежність. Власний перемикач дає той самий UI в шапці сторінки без нової залежності.
- `->visible(fn () => ...)` + `dehydratedWhenHidden()` замість `x-show`. Відхилено: Filament не валідує приховані поля, тож помилки неактивної мови пройшли б непоміченими, а кожне перемикання перерендерювало б форму.
- Вкладки UK / EN у формі. Відхилено: користувач обрав перемикач у шапці.

### 6. Fallback читання — на `uk`
У `ProfileServiceProvider::boot()` викликається `Translatable::fallback(fallbackLocale: ProfileLocale::Ukrainian->value)`, щоб `$profile->headline` у локалі `en` без англійського значення повертав українське, а не `fallback_locale` застосунку (`en`). Форми fallback не використовують (рішення 4), тож порожнє англійське поле показується порожнім.
*Альтернатива:* опублікувати `config/translatable.php` — це новий файл у кореневому `config/` лише заради одного значення; виклик у провайдері контейнера тримає налаштування поруч із кодом, який на нього спирається.

### 7. Нормалізація в Tasks
`UpsertUserProfileTask` і `Replace*Task` нормалізують кожне перекладне поле через спільний трейт `Tasks/Concerns/NormalizesTranslations` (Tasks не викликають одна одну, а перелік полів береться з `$translatable` моделі): лишають тільки ключі з `ProfileLocale`, обрізають пробіли, відкидають порожні мови. Якщо для необов'язкового поля не лишилося жодної мови, зберігається `null`. Рядок (наприклад, із фабрик чи старих тестів) Task трактує як значення основної мови, а spatie у фабриках записує його в поточну локаль (`uk`), тож фабрики можна не змінювати.

### 8. Валідація й показ помилок
- Обов'язковість — лише на `uk`-полях (`->required()` у рішенні 4). `maxLength` — на обох.
- Перевірка повтору мови (`EditProfileLanguages::isRepeatedLanguage`) приймає локаль і порівнює значення тільки в межах цієї локалі.
- `ProfileFormPage` перевизначає `onValidationError(ValidationException $exception)`: бере перший ключ помилки, якщо він закінчується на `.uk`/`.en` — виставляє `dataLocale` на цю мову. Тоді повідомлення видно під полем (spec: «Показ помилок неактивної мови»).

### 9. Тести
- Міграції: `Tests/Unit/Data/Migrations/MigrationTest` перевіряє тип `jsonb` перекладних колонок.
- Tasks/Actions: збереження двох мов, обрізання пробілів, порожня англійська, `null` для повністю порожнього необов'язкового поля.
- Сторінки (`Tests/Functional/Filament/Pages/*`): за замовчуванням `dataLocale === 'uk'`; `fillForm` з обома мовами + `call('save')` зберігає обидві; англійська необов'язкова; помилка `uk` при `dataLocale = 'en'` перемикає на `uk`; повтор мови в межах `en`.
- Наявні тести з `assertDatabaseHas([... 'headline' => 'x'])` переходять на `'headline->uk'`.

## Risks / Trade-offs

- [Livewire-морфінг після серверного запиту відновлює DOM і може скинути видимість] → вираз `visibleJs` прив'язаний до `$wire.dataLocale`, тож після морфінгу Alpine обчислює його заново; перевірити вручну, перемкнувши мову й додавши запис у repeater.
- [Обмеження довжини зникають з БД] → валідація форм — єдиний шлях запису (усі записи йдуть через Actions зі сторінок); тести на довжину для обох мов.
- [Dev-дані профілю зникнуть після перестворення таблиць] → перестворюються лише таблиці профілю (Migration Plan); профіль доведеться ввести заново.
- [Змінені міграції вже виконані в інших dev-оточеннях] → `migrate` їх не підхопить; у кожному оточенні виконати `migrate:refresh` з `--path` контейнера Profile.

## Migration Plan

1. `docker compose exec app composer update spatie/laravel-translatable` (через merge-plugin з `composer.json` контейнера).
2. `docker compose exec app php artisan migrate:refresh --path=app/Containers/AppSection/Profile/Data/Migrations`: перестворюються лише таблиці профілю, а користувачі, компанії й статуси лишаються.
3. Відкат: повернути код і знову виконати ту саму команду `migrate:refresh`.
