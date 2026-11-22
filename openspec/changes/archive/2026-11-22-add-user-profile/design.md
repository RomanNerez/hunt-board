# Design

## Context

Мотивацію описано в proposal.md (розділ Why), вимоги — у `specs/user-profile`, `specs/profile-links`, `specs/profile-education`, `specs/profile-languages`.

Поточний стан:

- Еталоном для нового коду є контейнери `Status` і `Company`: Actions обгортають роботу в `DB::transaction()`, Tasks працюють поверх репозиторію, тести лежать у `Tests/Unit` і `Tests/Functional/Filament`, мітки українські. Обидва контейнери мають лише ресурси, а власних Filament-сторінок (не ресурсів) і кластерів, що належать одному контейнеру, у проєкті ще немає.
- `AdminPanelProvider::discoverContainerComponents()` уже виявляє `UI/Filament/Clusters` і `UI/Filament/Pages` кожного контейнера, тож провайдер міняти не треба.
- Вбудовану сторінку профілю Filament (`->profile()`) у панелі не ввімкнено, отже адреса `/admin/profile` вільна.
- Таблиця `users` містить `name` і `email` (входу) і не має полів профілю. Контейнер `User` — це stock-код Apiato з власними тестами.
- `Model::shouldBeStrict()` увімкнено: lazy loading і заповнення полів, яких немає у `$fillable`, кидають виняток.
- Тести запускаються з `DB_DATABASE=hunt-board_testing`, інакше вони стирають dev-БД.

## Goals / Non-Goals

**Goals:**

- Перший кластер, що належить одному контейнеру, і перші власні Filament-сторінки з формою. На них видно, як сторінка без ресурсу зберігає дані через Action.
- Перший приклад синхронізації цілого списку з Repeater через Action, без `->relationship()`.

**Non-Goals:**

- Політики для профілю (див. рішення 6).
- Окремі Actions для створення, оновлення й видалення одного посилання, запису про освіту чи мови.
- Довідник мов (див. рішення 4).

## Decisions

### 1. Новий контейнер `AppSection/Profile`

```
app/Containers/AppSection/Profile/
├── Actions/
│   ├── UpdateUserProfileAction.php
│   ├── SyncProfileLinksAction.php
│   ├── SyncProfileEducationAction.php
│   └── SyncProfileLanguagesAction.php
├── Tasks/
│   ├── UpsertUserProfileTask.php
│   ├── ReplaceProfileLinksTask.php
│   ├── ReplaceProfileEducationTask.php
│   └── ReplaceProfileLanguagesTask.php
├── Enums/{EducationType,LanguageLevel}.php
├── Models/{UserProfile,ProfileLink,ProfileEducation,ProfileLanguage}.php
├── Data/{Migrations,Repositories,Factories}/
├── UI/Filament/
│   ├── Clusters/Profile/ProfileCluster.php
│   └── Pages/{EditProfileGeneral,EditProfileLinks,EditProfileEducation,EditProfileLanguages}.php
├── Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}.php, Unit/…, Functional/Filament/…
├── Providers/ProfileServiceProvider.php
└── composer.json
```

**Чому:** профіль — окрема предметна область, якої немає ні в `User` (stock-код Apiato, який краще не обростає бізнес-полями), ні в інших контейнерах. Кластер використовується лише цим контейнером, тому за AGENTS.md він лежить у `UI/Filament/Clusters`, а не в `app/Ship/Filament/Clusters`.
**Альтернативи:** колонки профілю в `users` і код у контейнері `User` (змішує обліковий запис із даними резюме й вимагає правити stock-міграцію або моделі); кластер у Ship (його ніхто, крім Profile, не використовує).

### 2. Схема БД: окремі таблиці, ключ `user_id`

- `user_profiles`: `id`, `user_id` foreignId unique `constrained()->cascadeOnDelete()`, `first_name` string(100), `last_name` string(100), `email` string(255) nullable, `phone` string(32) nullable, `location` string(255) nullable, `headline` string(255) nullable, `summary` text nullable, timestamps.
- `profile_links`: `id`, `user_id` FK cascade (index), `label` string(100), `url` string(2048), `sort` unsignedSmallInteger, timestamps.
- `profile_educations`: `id`, `user_id` FK cascade (index), `type` string (enum `EducationType`), `title` string(255), `institution` string(255) nullable, `started_on` date nullable, `finished_on` date nullable, `url` string(2048) nullable, `description` text nullable, `sort`, timestamps.
- `profile_languages`: `id`, `user_id` FK cascade, `language` string(100), `level` string (enum `LanguageLevel`), `sort`, timestamps, unique `(user_id, language)`.

Моделі мають `belongsTo user` на `App\Containers\AppSection\User\Models\User`. `UserProfile` отримує ще `hasMany` на три списки, щоб у тестах було зручно. Модель `User` не змінюється.

**Чому:** користувач хоче, щоб кожен розділ зберігався окремо. Ключ `user_id` в усіх таблицях означає, що посилання можна зберегти, не заповнивши спершу «Загальне» з обов'язковими ім'ям і прізвищем. Каскад на `users` закриває вимогу про видалення користувача без коду. Колонка `sort` задає порядок із Repeater. Унікальний індекс `(user_id, language)` захищає від дублікатів, якщо дані прийдуть не через форму. Для коректної роботи індексу назву мови перед записом нормалізує Task (`trim`). Порівняння без регістру перевіряє форма (рішення 5).
**Альтернативи:** дочірні таблиці з `user_profile_id` (вимагає створювати профіль-«порожняк» без імені або знімати з імені й прізвища обов'язковість); JSON-колонки `links`/`education`/`languages` у `user_profiles` (простіше, але користувач просив окреме зберігання, а з JSON гірше працюють майбутні запити й валідація на рівні БД).

### 3. Enum-и

- `EducationType: string` — `University = 'university'` («Університет»), `Course = 'course'` («Курс»), `Certificate = 'certificate'` («Сертифікат»).
- `LanguageLevel: string` — `A1`…`C2` (`'a1'`…`'c2'`) і `Native = 'native'`. Мітки: «A1 Beginner», «A2 Elementary», «B1 Intermediate», «B2 Upper-Intermediate», «C1 Advanced», «C2 Proficient», «Рідна». Порядок case-ів задає порядок у Select.

Обидва реалізують `Filament\Support\Contracts\HasLabel`, як `StatusColor`, і кастяться в моделях.

**Чому:** фіксовані набори значень зі спеки; у БД зберігаються стабільні ключі, а мітки можна перекладати.
**Альтернативи:** довідники в «Налаштуваннях» (зайвий CRUD для незмінної шкали CEFR); вільний текст рівня (не дає однакових значень).

### 4. Мова — вільний текст

Поле «Мова» — це `TextInput` з `datalist` поширених мов («Українська», «Англійська», «Польська», «Німецька», «Іспанська», «Французька»). Окремої таблиці мов немає.

**Чому:** у кандидата 2–5 мов, і довідник з окремою сторінкою був би надмірним. Datalist дає підказки й лишає змогу вписати будь-яку мову.
**Альтернативи:** Select з повним переліком ISO 639 (громіздко, потрібні переклади назв); довідник у кластері Settings.

### 5. Filament: кластер і чотири сторінки з формою

`ProfileCluster` (`$slug = 'profile'`, `$navigationLabel` і `$clusterBreadcrumb` = «Профіль», `$navigationIcon = Heroicon::OutlinedUserCircle`, `$subNavigationPosition = SubNavigationPosition::Start`, `$navigationSort = 90`, тобто перед «Налаштуваннями» зі 100).

Сторінки розширюють `Filament\Pages\Page`, мають `$cluster = ProfileCluster::class`, slug-и `general`, `links`, `education`, `languages`, `$navigationSort` 10/20/30/40 (отже, «Загальне» — перша сторінка кластера) і українські `$title`/`$navigationLabel`. Кожна має `public ?array $data = []`, форму `form(Schema $schema)` зі `statePath('data')` і `content()` з `Form::make([EmbeddedSchema::make('form')])->livewireSubmitHandler('save')` та кнопкою «Зберегти» в footer. Метод `save()` викликає `$this->form->getState()`, далі відповідну Action з `auth()->id()`, а потім `Notification::make()->success()`. Під час реалізації точні API Filament 5 для сторінки з формою звіряємо через `search-docs`.

- **EditProfileGeneral:** `mount()` заповнює форму з `UserProfile::where('user_id', …)->first()`. Якщо профілю немає, у форму потрапляє лише `email` = email входу. Поля: `first_name`, `last_name` (required, max 100), `email` (`email()`, max 255), `phone` (`tel()`, `regex:/^\+?[0-9\s\-()]{7,32}$/`, maxLength 32; кількість символів 7–32 перевіряє сам regex), `location`, `headline` (max 255), `summary` (`Textarea`, max 5000, на всю ширину). Сітка з двох колонок.
- **EditProfileLinks:** `Repeater::make('links')` без `->relationship()`, `reorderableWithDragAndDrop()`, `defaultItems(0)`, `addActionLabel('Додати посилання')`, `columns(2)`. Поля: `label` (`TextInput`, required, max 100, `datalist(['LinkedIn', 'GitHub', 'Portfolio', 'Telegram'])`) і `url` (required, `url()`, `rule('url:http,https')`, max 2048). `itemLabel()` показує назву.
- **EditProfileEducation:** Repeater `education`, `collapsible()`, `itemLabel()` у вигляді «<Тип> · <Назва>». Поля: `type` (`Select` з `EducationType`, required), `title` (required, max 255), `institution` (max 255), `started_on` / `finished_on` (`DatePicker`, `native(false)`, `displayFormat('d.m.Y')`, у `finished_on` — `afterOrEqual('started_on')`), `url` (як у посиланнях, необов'язкове), `description` (`Textarea`, max 2000).
- **EditProfileLanguages:** Repeater `languages`, `columns(2)`. Поля: `language` (required, max 100, datalist з рішення 4, правило-closure для перевірки повтору в межах стану Repeater після `mb_strtolower(trim())`) і `level` (`Select` з `LanguageLevel`, required).

Repeater-сторінки в `mount()` заповнюють стан записами користувача, упорядкованими за `sort` (це читання через Eloquent, і воно дозволене).

**Чому:** кожен розділ має одну форму й одну кнопку, як і обрав користувач. Сторінки, а не ресурси, бо запис у користувача один (або один список), і таблиця з одним рядком тут не потрібна. Помилки валідації Filament показує під полем конкретного елемента Repeater, а `getState()` кидає виняток валідації ще до виклику Action, тож за некоректного елемента нічого не зберігається.
**Альтернативи:** вбудована сторінка `->profile()` Filament (це редагування облікового запису, вона пише в `User` і не має субнавігації); ресурси з таблицею й модалками (користувач обрав Repeater); одна сторінка з табами (користувач хотів окремі сторінки в кластері).

### 6. Записи через Actions; Repeater зберігається повною заміною списку

- `UpdateUserProfileAction::run(int $userId, array $data): UserProfile` у транзакції викликає `UpsertUserProfileTask`, який робить `updateOrCreate(['user_id' => $userId], $data)` через репозиторій.
- `SyncProfileLinksAction::run(int $userId, list<array<string, mixed>> $items): Collection` у транзакції викликає `ReplaceProfileLinksTask`. Task видаляє всі посилання користувача й вставляє передані зі `sort` = індекс у списку (0, 1, 2…), після чого повертає новий упорядкований список. Те саме роблять `SyncProfileEducationAction` / `SyncProfileLanguagesAction`. Task мов додатково виконує `trim()` назви.
- Ключі Repeater (UUID) відкидаються через `array_values()` на сторінці. Action отримує звичайний `list`.

**Чому:** список невеликий, і на його записи ніщо не посилається, тож «видалити все й вставити заново» в одній транзакції дає найпростіший правильний результат для додавання, видалення й зміни порядку одночасно. Транзакція гарантує, що в разі помилки старий список залишиться.
**Альтернативи:** порівняння з id (оновити наявні, видалити відсутні, додати нові) зберігає id і `created_at`, але дає втричі більше коду без видимої користі; Filament `->relationship()` на Repeater пише в БД в обхід Actions.

### 7. Авторизація без політик

Сторінки не приймають id запису: усі читання й записи явно обмежено `auth()->id()`, і Actions отримують `userId` лише від сторінки. Отже, способу звернутися до чужого профілю немає, і політика `update(User, UserProfile)` не мала б чого перевіряти. Ізоляцію між користувачами перевіряють functional-тести зі спеки `user-profile`.

**Чому:** політики Filament захищають ресурси, де запис обирається з URL або таблиці, а тут такого вибору немає.
**Альтернативи:** політика з перевіркою `$profile->user_id === $user->id`, яку викликає сторінка (формальний код, що завжди повертає `true`).

### 8. Тести

- Unit: `UpsertUserProfileTask` (створення й оновлення одного рядка на користувача), Replace-Tasks (заміна, порядок `sort`, порожній список очищає, інший користувач не зачіпається, `trim` мови), Actions, каскадне видалення під час видалення користувача, enum-мітки.
- Functional (`Livewire::test(EditProfile…::class)` після `actingAs`): заповнення форми під час `mount`, підстановка email входу, збереження й сповіщення, кожен сценарій валідації зі спек, порядок після збереження, ізоляція двох користувачів, адреси `/admin/profile/...`, редирект гостя, пункт «Профіль» у навігації, перша сторінка кластера, порядок субнавігації.

## Risks / Trade-offs

- [Повна заміна списку змінює id записів за кожного збереження] → На ці записи ніщо не посилається. Якщо колись знадобляться посилання на конкретний запис (наприклад, «сертифікат у резюме для вакансії X»), Task треба буде переписати на синхронізацію за id.
- [Одночасне збереження однієї сторінки у двох вкладках] → Виграє останнє збереження. Для одного користувача-кандидата це прийнятно.
- [Перевірка повтору мови лише у формі; БД порівнює з урахуванням регістру] → Task нормалізує пробіли на краях, а унікальний індекс ловить точні дублікати. Різний регістр поза формою — допустимий залишковий ризик.
- [Slug `profile` конфліктує з `->profile()` Filament, якщо його колись увімкнуть] → Вбудований профіль за потреби можна ввімкнути з іншим шляхом (`->profile(isSimple: false)` має власний slug), або перейменувати кластер. Ризик фіксуємо тут.
- [Regex телефону не перевіряє реальність номера] → Свідомо, бо потрібен лише захист від явного сміття. Бібліотеку на кшталт libphonenumber не додаємо (нова залежність).

## Migration Plan

Лише нові таблиці: `php artisan migrate` у контейнері `app`. Відкат: `migrate:rollback` прибирає чотири таблиці, а дані інших сутностей не зачіпаються.
