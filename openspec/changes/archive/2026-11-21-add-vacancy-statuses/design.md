# Design

## Context

Мотивація описана в proposal.md (розділ Why), вимоги — у `specs/vacancy-statuses/spec.md` і `specs/admin-settings/spec.md`.

Поточний стан:

- У `app/Containers/AppSection` є лише `User` і `Authentication`. Жодного Filament-ресурсу ще немає, і каталогу `app/Ship/Filament/` теж.
- `AdminPanelProvider::discoverContainerComponents()` уже реєструє кластери з `app/Ship/Filament/Clusters` і ресурси з `app/Containers/*/*/UI/Filament/Resources`, тож новий контейнер і кластер підхопляться без змін у провайдері.
- `Model::shouldBeStrict()` увімкнено поза production: незаповнювані атрибути й lazy loading кидають виняток.
- Apiato-генератори (`apiato:make:container`, `apiato:make:model`, `apiato:make:migration`, `apiato:make:action`, `apiato:make:task`, `apiato:make:repository`, `apiato:make:factory`, `apiato:make:policy`) доступні через `docker compose exec app php artisan`.

## Goals / Non-Goals

**Goals:**

- Перший Filament-ресурс у проєкті, зроблений за правилами AGENTS.md, щоб наступні ресурси могли його наслідувати: Actions для записів, Policy, тести через `Livewire::test`.
- Спільний кластер Settings, до якого інші контейнери зможуть додавати свої ресурси та сторінки одним рядком `$cluster`.
- Гарний і читабельний у обох темах бейдж статусу, який без змін перевикористає вибір статусу у вакансіях.

**Non-Goals:**

- Ролі й гранульовані права (політика дозволяє все автентифікованому користувачу).
- Обробка видалення статусу, який використовують вакансії: зв'язку ще немає, тож це вирішуватиметься в зміні, яка додасть вакансії.

## Decisions

### 1. Окремий контейнер `AppSection/Status` з моделлю `Status`

Модель `App\Containers\AppSection\Status\Models\Status`, таблиця `statuses`. Структура контейнера:

```
app/Containers/AppSection/Status/
├── Actions/{Create,Update,Delete}StatusAction.php
├── Tasks/{Create,Update,Delete}StatusTask.php
├── Enums/StatusColor.php
├── Models/Status.php
├── Data/{Migrations,Repositories/StatusRepository.php,Factories/StatusFactory.php}
├── Policies/StatusPolicy.php
├── UI/Filament/Resources/Statuses/
│   ├── StatusResource.php
│   ├── Pages/ManageStatuses.php
│   ├── Schemas/StatusForm.php
│   └── Tables/StatusesTable.php
├── UI/WEB/Views/badge.blade.php + CSS-asset бейджа
├── Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}.php
├── Tests/Unit/Actions/...
├── Tests/Functional/Filament/...
└── composer.json
```

**Чому:** так вирішив користувач. Статуси — самостійний довідник із власним CRUD, і контейнер вакансій пізніше лише посилатиметься на нього.
**Альтернативи:** модель `VacancyStatus` у майбутньому контейнері `AppSection/Vacancy` (статус ближче до власника, але контейнер існував би без своєї головної сутності); тримати статуси в `User` (не має стосунку до користувачів).

### 2. Колір — пресет із фіксованої палітри (backed enum `StatusColor`)

`App\Containers\AppSection\Status\Enums\StatusColor` — string-backed enum із 12 пресетами: `gray`, `red`, `orange`, `amber`, `lime`, `green`, `teal`, `cyan`, `blue`, `indigo`, `violet`, `pink`. Кожен case реалізує `HasLabel` (`getLabel()` — назва англійською, напр. «Blue») і повертає шість кольорів:

- світла тема: фон — відтінок 50, бордер — 200, текст — 700 палітри Tailwind того ж кольору;
- темна тема: фон — відтінок 500 з прозорістю 15% (`#RRGGBB26`), бордер — 500 з прозорістю 30% (`#RRGGBB4D`), текст — 300.

Колонка `color` — `string(20)`, не nullable, з cast у `StatusColor`. У БД зберігається лише ключ пресету (`blue`), самі кольори живуть у коді.

**Чому:** користувач хоче гарні узгоджені бейджі й вибір із готових кольорів, а не підбір HEX. Пресет задає фон, бордер і текст разом, тож бейдж завжди читабельний, а окремі відтінки для темної теми не потребують ще шести колонок.
**Альтернативи:** два HEX-стовпці (фон і бордер), які пресет лише заповнює, — гнучкіше, але дозволяє нечитабельні поєднання й не адаптується до темної теми; один HEX + `Color::hex()` (перша версія дизайну) — немає окремого бордера й фону; третій стовпець для тексту — зайвий, бо текст входить у пресет.

### 3. Бейдж статусу: один Blade-шаблон + CSS-змінні

Перевикористовуваний шаблон `UI/WEB/Views/badge.blade.php` контейнера Status приймає назву та `StatusColor` і рендерить `<span class="hb-status-badge">` з inline CSS-змінними `--hb-status-bg`, `--hb-status-border`, `--hb-status-text` і їхніми `-dark` варіантами. Невеликий CSS-файл (реєстрація через `FilamentAsset::register()` у провайдері контейнера) задає форму бейджа і перемикає змінні під класом `.dark`, який Filament ставить на `<html>` у темній темі.

Шаблон використовують:

- таблиця: `TextColumn::make('name')` рендерить бейдж (`->html()` / `->formatStateUsing()` або `ViewColumn`), `searchable()` і `sortable()`; `ColorColumn` прибрано. Сортування за замовчуванням — за `name`;
- форма: прев'ю бейджа з введеною назвою та варіанти палітри;
- пізніше — вибір статусу у вакансіях (`Select` з HTML-опціями).

**Чому:** бейдж має виглядати однаково скрізь, де з'являється статус, і бути готовим для вакансій. Filament `->badge()->color()` будує палітру з одного кольору й не дає окремо задати бордер і фон.
**Альтернативи:** `->badge()->color(Color::hex(...))` — один колір, без контролю бордера; Tailwind-класи в PHP (`bg-blue-50 …`) — потребують власної теми Filament і компіляції, бо класів немає у вбудованому CSS панелі.

### 3a. Вибір кольору у формі

`color` — поле вибору одного пресету (`ToggleButtons` або `Radio` з HTML-підписами), де кожен варіант показано бейджем у кольорі пресету, плюс прев'ю бейджа з поточною назвою. Якщо вбудовані поля не дають такого вигляду, робиться власний view поля в `UI/Filament`. Правила — `required` і `Rule::enum(StatusColor::class)`.

**Чому:** користувач обирає колір, бачачи готовий результат.

### 4. Валідація: форма + обмеження в БД

Правила стоять у `StatusForm`: `name` — `required`, `maxLength(255)`, `unique(ignoreRecord: true)`; `color` — `required`, значення з `StatusColor`. У міграції на `name` стоїть унікальний індекс як остаточна гарантія. Tasks дані не валідують.

**Чому:** Filament показує помилки біля полів тільки для правил форми, а індекс у БД захищає від гонок і від майбутніх UI, які оминуть форму.
**Альтернативи:** валідація в `CreateStatusTask` через `validator()` (як у `CreateUserTask`) дублювала б правила й показувала б помилки не на тому рівні; тільки індекс у БД дав би користувачу виняток замість повідомлення.

### 5. Записи через Actions, простий ресурс із модальними вікнами

Ресурс простий (`--simple`): одна сторінка `ManageStatuses` (`ManageRecords`) за адресою `/admin/settings/statuses`. Створення й редагування відкриваються в модальних вікнах із формою `StatusForm`, окремих сторінок Create/Edit немає.

- `CreateAction` у заголовку сторінки: `->using(fn (array $data) => app(CreateStatusAction::class)->run($data))`.
- `EditAction` у рядку таблиці: `->using(fn (Status $record, array $data) => app(UpdateStatusAction::class)->run($record->id, $data))`.
- `DeleteAction` у рядку таблиці: `->using(fn (Status $record) => app(DeleteStatusAction::class)->run($record->id))`.
- `DeleteBulkAction`: `->using(...)` викликає `DeleteStatusAction` для кожного виділеного запису.
- Actions обгортають роботу в транзакцію і викликають відповідний Task. Tasks працюють через `StatusRepository`.

**Чому:** так вирішив користувач: у статусу лише два поля, і перехід на окрему сторінку заради них зайвий. AGENTS.md вимагає, щоб записи йшли через Actions; у модальних діях це забезпечує `->using()` замість `handleRecordCreation()` / `handleRecordUpdate()`.
**Альтернативи:** повні сторінки List/Create/Edit (так було в першій версії дизайну) — більше переходів без користі для двох полів.

### 6. Спільний кластер `Settings` у Ship

`app/Ship/Filament/Clusters/Settings/SettingsCluster.php`, namespace `App\Ship\Filament\Clusters\Settings`:

- `final class SettingsCluster extends Cluster`;
- `$navigationIcon = Heroicon::OutlinedCog6Tooth`;
- `$subNavigationPosition = SubNavigationPosition::Start` (ліва субнавігація; це поведінка за замовчуванням, але вона записана явно, бо це вимога);
- `$slug = 'settings'`;
- `$navigationSort` — високе значення, щоб Settings був унизу навігації.

`StatusResource` приєднується через `protected static ?string $cluster = SettingsCluster::class;` і отримує `$navigationIcon` (наприклад, `Heroicon::OutlinedTag`) для пункту субнавігації. Адреса ресурсу — `/admin/settings/statuses`.

**Чому:** Settings групуватиме налаштування з різних контейнерів, тому за AGENTS.md кластер іде в Ship. Клас кластера пишеться вручну, `--cluster` у генератор ресурсу не передається.
**Альтернативи:** кластер у `Status/UI/Filament/Clusters` — інші контейнери залежали б від контейнера статусів; навігаційна група замість кластера — не дає субнавігації й спільного URL-префікса.

### 7. Policy

`StatusPolicy` з методами `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, які повертають `true` для автентифікованого `User`. Laravel знаходить політику автоматично за шаблоном `<Namespace моделі без Models>\Policies\<Model>Policy`. Якщо автовиявлення в Apiato не спрацює, політика реєструється через `Gate::policy()` у провайдері контейнера.

**Чому:** AGENTS.md вимагає політику як справжній контроль доступу. Методи-заглушки дають точку, куди пізніше лягатимуть ролі.
**Альтернативи:** без політики — Filament дозволить усе, але точки розширення не буде.

### 8. Тести

- Unit (`Tests/Unit/Actions`): кожна Action створює, оновлює або видаляє запис у БД.
- Unit (`Tests/Unit/Enums`): кожен пресет `StatusColor` має назву і шість кольорів у форматі HEX.
- Functional (`Tests/Functional/Filament`, `Livewire::test`, після `actingAs(User::factory()->createOne())`): список і пошук, створення в модальному вікні з валідними даними, помилки валідації (порожня назва, порожній колір, колір не з палітри, дублікат назви), бейдж у таблиці з кольорами пресету, редагування в модальному вікні без зміни назви, видалення та масове видалення, перенаправлення гостя на логін, адреса `/admin/settings/statuses` і наявність субнавігації кластера.

## Risks / Trade-offs

- [Порівняння назв на унікальність залежить від регістру в Postgres: «Offer» і «offer» вважаються різними] → Прийнятно для першої версії. Якщо знадобиться, буде окрема зміна з індексом на `lower(name)`.
- [Масове видалення викликає Action для кожного запису окремо] → Статусів буде небагато, тож зайві запити не мають значення; натомість бізнес-правила видалення в одному місці.
- [Палітра змінюється лише кодом; видалення чи перейменування пресету зламає наявні статуси з цим ключем] → Пресети лише додаються; видалення — окремою зміною з міграцією даних.
- [Кольори темної теми підібрані правилом, а не вручну для кожного пресету] → Перевіряються вручну в браузері в обох темах (окрема задача).
- [Генератор Filament без наявного каталогу `UI/Filament` мовчки пише в `app/Filament`] → Створити каталог перед генерацією і перевірити, куди лягли файли (є окрема задача).

## Migration Plan

Одна нова міграція `create_statuses_table` у контейнері, запуск — `docker compose exec app php artisan migrate`. Відкат: `migrate:rollback` видаляє таблицю. Вона нова, тож наявні дані не зачіпаються.

Зміна ще не випущена, тому перехід із HEX на пресет правиться в тій самій міграції (`color` → `string(20)`). У dev-БД цю міграцію треба відкотити й накотити заново; статуси, створені для перевірки, зникнуть.
