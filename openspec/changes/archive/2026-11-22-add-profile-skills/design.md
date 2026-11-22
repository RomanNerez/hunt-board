# Design

## Context

Мотивацію описано в proposal.md (розділ Why), вимоги — у `specs/profile-skills` і дельті `specs/user-profile`.

Поточний стан (після архівованої зміни `add-user-profile`):

- Контейнер `app/Containers/AppSection/Profile` уже має кластер `ProfileCluster` (`/admin/profile`, субнавігація ліворуч) і чотири сторінки на базі абстрактної `UI/Filament/Pages/ProfileFormPage`. Цей клас дає форму зі `statePath('data')`, кнопку «Зберегти», `userId()`, `repeaterItems()` і `notifySaved()`.
- Сторінки впорядковано через `$navigationSort`: «Загальне» 10, «Посилання» 20, «Освіта» 30, «Мови» 40. Кластер відкриває сторінку з найменшим значенням.
- Списки (посилання, освіта, мови) зберігаються за однією схемою: таблиця з `user_id` (FK з `cascadeOnDelete`) і колонкою `sort`, `Replace*Task` (видалити все користувача → вставити зі `sort` = індекс) і `Sync*Action` у `DB::transaction()`.
- Функціональні тести використовують `Repeater::fake()`, щоб ключі елементів Repeater були передбачуваними (`groups.0.name`).
- Filament 5 `TagsInput` має `reorderable()` і `nestedRecursiveRules()`. Клієнтська частина обрізає пробіли й не дає додати точний дублікат тега. Сервер такої гарантії не дає, якщо дані надходять не з цього поля.

## Goals / Non-Goals

**Goals:**

- Додати сторінку за наявною схемою сторінок профілю без змін у `ProfileFormPage` і в інших сторінках.
- Зберігати порядок двох рівнів, групи й технологій усередині групи, одним натисканням «Зберегти».

**Non-Goals:**

- Пошук чи фільтрація за окремою технологією (наприклад, «вакансії, де потрібен Laravel»).
- Спільна таблиця технологій для кількох користувачів.

## Decisions

### 1. Одна таблиця груп, технології — JSON-список у групі

`profile_skill_groups`: `id`, `user_id` foreignId `constrained()->cascadeOnDelete()` (index), `name` string(100), `skills` json (список рядків), `sort` unsignedSmallInteger, timestamps.

Модель `Models/ProfileSkillGroup` кастить `skills` як `array`, має `belongsTo user`. `UserProfile` отримує `skillGroups(): HasMany` (`user_id` → `user_id`, `orderBy('sort')`), як уже мають `links()`, `educations()` і `languages()`.

**Чому:** технологія в цій зміні — лише назва без атрибутів, і її завжди читають і записують разом із групою. JSON-масив зберігає порядок тегів без окремої колонки `sort`, а стан `TagsInput` (масив рядків) пишеться в нього як є. Розділ, як і решта профілю, лежить у власній таблиці, прив'язаній до `user_id`.
**Альтернативи:** окрема таблиця `profile_skills` (`group_id`, `name`, `sort`) — нормалізовано й зручно для майбутнього пошуку за технологією, але це дворівнева синхронізація й удвічі більше коду заради запитів, яких зараз немає; рядок через кому в `text` — порядок і окремі значення губляться в парсингу.

### 2. Синхронізація заміною списку

- `Tasks/ReplaceProfileSkillGroupsTask::run(int $userId, list<array<string, mixed>> $items): Collection<int, ProfileSkillGroup>` видаляє групи користувача через репозиторій і вставляє передані зі `sort` = індекс. Для кожної групи бере лише `name` і `skills`. Список `skills` Task нормалізує: `trim()` кожної назви, відкидає порожні, лишає перше входження точного дубліката (`array_values(array_unique(...))`), зберігаючи порядок.
- `Actions/SyncProfileSkillGroupsAction::run(int $userId, list<array<string, mixed>> $items): Collection` обгортає Task у `DB::transaction()`.
- `Data/Repositories/ProfileSkillGroupRepository`, `Data/Factories/ProfileSkillGroupFactory` (назва групи + 2–4 технології).

**Чому:** така сама схема, як у посиланнях, освіті й мовах (рішення 6 у `add-user-profile`). Нормалізація в Task, а не лише у формі, бо вимогу «пробіли відкидаються, повтори зберігаються один раз» сформульовано для збережених даних, а не для конкретного віджета.
**Альтернативи:** синхронізація за id (зберігає id груп, але на групи ніщо не посилається); нормалізація лише в `TagsInput` (покладається на JS-поведінку поля).

### 3. Сторінка `UI/Filament/Pages/EditProfileSkills`

Розширює `ProfileFormPage`: `$slug = 'skills'`, `$navigationSort = 15` (між «Загальне» 10 і «Посилання» 20, наявні сторінки не змінюються), `$title`/`$navigationLabel` = «Навички».

- `mount()` заповнює `groups` групами користувача за `sort` (`name`, `skills`).
- Форма: `Repeater::make('groups')` без `->relationship()`, `hiddenLabel()`, `defaultItems(0)`, `reorderableWithDragAndDrop()`, `collapsible()`, `addActionLabel('Додати групу')`, `itemLabel()` — назва групи. Поля:
  - `TextInput::make('name')` — «Назва групи», required, max 100, `datalist(['Backend', 'Frontend', 'Бази даних', 'DevOps', 'Інструменти'])`;
  - `TagsInput::make('skills')` — «Технології», required, `reorderable()`, `nestedRecursiveRules(['max:100'])`, placeholder «Введіть технологію й натисніть Enter».
- `save()` → `$this->form->getState()` → `SyncProfileSkillGroupsAction` з `userId()` і `repeaterItems($state, 'groups')` → `notifySaved()`.

**Чому:** користувач обрав теги-чіпи. `TagsInput` з `reorderable()` дає і швидкий ввід, і перетягування тегів, а група вміщується в один рядок. Позицію другою в субнавігації (вибір користувача) задає лише `$navigationSort`.
**Альтернативи:** вкладений Repeater технологій (громіздкий для 10+ технологій у групі); `Textarea` через кому (без перевірки окремих значень і без перетягування); перенумерація `$navigationSort` усіх сторінок (зачіпає наявні класи без потреби).

### 4. Авторизація без політик

Як і решта сторінок профілю (рішення 7 у `add-user-profile`): сторінка не приймає id, а читання й записи обмежено `userId()`.

### 5. Тести

- Unit: `SyncProfileSkillGroupsAction` разом із `ReplaceProfileSkillGroupsTask` (порядок груп і технологій, `trim` і дублікати, порожній список очищає, інший користувач не зачіпається), фабрика, `MigrationTest` (колонки `profile_skill_groups`), `UserProfileTest` (зв'язок `skillGroups` і каскадне видалення).
- Functional (`Livewire::test(EditProfileSkills::class)` з `Repeater::fake()`): сценарії з `specs/profile-skills`, оновлені `ProfileSubNavigationTest` (п'ять пунктів, «Навички» другим і активним на своїй сторінці, адреса `/admin/profile/skills`) і `ProfileClusterTest` («Навички» немає в головній навігації, кластер досі відкриває «Загальне»).

## Risks / Trade-offs

- [Технології в JSON не можна проіндексувати чи шукати окремо] → Зараз таких запитів немає. Якщо з'явиться зіставлення навичок із вакансіями, знадобиться міграція в таблицю `profile_skills`; JSON-список легко переносимий.
- [Дублікати відкидаються з урахуванням регістру: «Docker» і «docker» збережуться обидва] → Клієнтське поле так само порівнює точно; різний регістр видно одразу в чіпах. Прийнятно для одного користувача.
- [Немає обмеження кількості груп і технологій] → Обсяг даних одного кандидата малий; обмеження можна додати без зміни схеми.

## Migration Plan

Одна нова таблиця: `php artisan migrate` у контейнері `app`. Відкат — `migrate:rollback` прибирає `profile_skill_groups`, решта профілю не зачіпається.
