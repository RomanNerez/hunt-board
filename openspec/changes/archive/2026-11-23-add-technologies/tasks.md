# Tasks

## 1. Контейнер Stack: дані

- [x] 1.1 Створити каркас контейнера `app/Containers/AppSection/Stack` (`composer.json` `apiato/stack`, `Providers/StackServiceProvider`, `Tests/{ContainerTestCase,UnitTestCase,FunctionalTestCase}` за зразком `Status`). Перевірка: `composer dump-autoload` без помилок, `php artisan about` стартує
- [x] 1.2 Додати міграції `stacks` (`name` `string(100)`, функціональний унікальний індекс на `lower(name)`) і `stackables` (`stack_id` FK з `cascadeOnDelete`, `morphs('stackable')`, `sort`, складений первинний ключ) за design.md (рішення 2) і `Tests/Unit/Data/Migrations/MigrationTest`: колонки, унікальність без урахування регістру на рівні БД, каскадне видалення pivot разом із технологією. Перевірка: тест зелений
- [x] 1.3 Додати модель `Models/Stack`, `Data/Repositories/StackRepository`, `Data/Factories/StackFactory` (унікальні назви) і `StackFactoryTest`. Перевірка: тест зелений
- [x] 1.4 Додати інтерфейс `Models/Contracts/Stackable` і трейт `Models/Concerns/HasStacks` зі зв'язком `stacks()` (`morphToMany`, `withPivot('sort')`, `orderByPivot('sort')`), design.md, рішення 3. Тест зв'язку додається в задачі 3.2, коли з'явиться перша модель-власник

## 2. Контейнер Stack: бізнес-логіка

- [x] 2.1 Додати `Create`/`UpdateStackTask`: обрізання пробілів, перевірка унікальності без урахування регістру з `ValidationException` на `name` (Update виключає поточний запис). Unit-тести: створення, пробіли, дублікат «postgresql» при наявному «PostgreSQL», перейменування на ту саму назву іншим регістром дозволено для самого запису. Перевірка: тести зелені
- [x] 2.2 Додати `DeleteStackTask` і `SyncStacksTask` (дублікати прибираються зі збереженням першої появи, `sort` = індекс), design.md, рішення 5. Unit-тести `SyncStacksTask` на тестовій моделі-власнику з трейтом `HasStacks` (анонімна модель на таблиці, створеній у тесті, або модель профілю після задачі 3.2): порядок збережено, повтори відкинуто, повторна синхронізація змінює порядок і знімає зайві. Перевірка: тести зелені
- [x] 2.3 Додати `Create`/`Update`/`DeleteStackAction` (у `DB::transaction`) та unit-тести в `Tests/Unit/Actions`. Перевірка: тести зелені
- [x] 2.4 Додати `Policies/StackPolicy` (усі дії дозволено автентифікованому користувачу, як у `DomainPolicy`) і `StackPolicyTest`. Перевірка: тест зелений

## 3. Профіль: схема та моделі

- [x] 3.1 Прибрати колонку `skills` з `2026_11_22_100004_create_profile_skill_groups_table.php` і колонку `technologies` з `2026_11_23_100000_create_profile_projects_table.php`, оновити Profile `MigrationTest`. У dev-БД виконати `docker compose exec app php artisan migrate:fresh`. Перевірка: міграції проходять, тест зелений
- [x] 3.2 Підключити `HasStacks` / `Stackable` до `ProfileSkillGroup` і `ProfileProject`, прибрати `skills` / `stacks` з `$fillable`, `$casts` і PHPDoc, зареєструвати `Relation::morphMap` у `ProfileServiceProvider` (design.md, рішення 4). Тест: технології групи й проєкту повертаються в порядку `sort`, а `stackable_type` у БД — `profile_skill_group` / `profile_project`. Перевірка: тест зелений
- [x] 3.3 Оновити `ProfileSkillGroupFactory` і `ProfileProjectFactory` (прибрати JSON-поля, додати стан `withStacks(int $count)` або `hasAttached`-приклад) і їхні тести. Перевірка: тести зелені

## 4. Профіль: бізнес-логіка

- [x] 4.1 Змінити `ReplaceProfileSkillGroupsTask`: прибрати `skills` і `NormalizesStringList`, перед `deleteWhere` прибирати pivot-рядки груп користувача одним запитом (design.md, рішення 6). Змінити `SyncProfileSkillGroupsAction`: після заміни груп викликати `SyncStacksTask` для кожної групи з її `stacks`. Оновити `SyncProfileSkillGroupsActionTest`: порядок технологій, одна технологія в двох групах, видалення групи не видаляє технологію й не лишає pivot-рядків, некоректні дані не змінюють груп. Перевірка: тести зелені
- [x] 4.2 Прибрати `stacks` з `PreparesProfileProjectData` і `CreateProfileProjectTask`. У `Create`/`UpdateProfileProjectAction` викликати `SyncStacksTask` (Update тільки за наявності ключа `stacks`). Оновити тести Tasks і Actions проєктів: стек зберігає порядок, порожній стек, Update без ключа не змінює стек. Перевірка: тести зелені
- [x] 4.3 `DeleteProfileProjectTask` знімає технології проєкту перед видаленням, `DeleteProfileWorkExperienceTask` прибирає pivot-рядки проєктів запису перед видаленням (design.md, рішення 6). Тести: після видалення проєкту чи запису досвіду роботи в `stackables` немає рядків цих проєктів, а технології лишаються в довіднику. Перевірка: тести зелені
- [x] 4.4 Якщо `NormalizesStringList` більше ніде не використовується, видалити трейт. Перевірка: `grep -rn NormalizesStringList app/` порожній, тести контейнера зелені

## 5. Filament: довідник у Налаштуваннях

- [x] 5.1 Створити `UI/Filament/Resources/Stacks` (`StackResource` у `SettingsCluster`, `Pages/ManageStacks`, `Schemas/StackForm` з `nameInput()`, `Tables/StacksTable`) за design.md (рішення 8): генерувати з явними namespace після `mkdir -p`, прибрати коментарі стабів і додати PHPDoc-теги. Записи йдуть через `->using()` до Actions. Перевірка: `php artisan route:list --path=admin/settings` показує `admin/settings/stacks`, у `app/Filament` нічого не з'явилося
- [x] 5.2 Тест `ManageStacksTest`: пункт «Технології» в субнавігації налаштувань активний і відсутній у головній навігації, сортування за назвою, пошук «Live», створення, редагування, видалення з підтвердженням, валідація порожньої назви, задовгої назви й дубліката з іншим регістром з українськими повідомленнями, видалення використовуваної технології зберігає проєкт і порядок решти стеку. Перевірка: тест зелений
- [x] 5.3 Оновити тест розділу «Налаштування» (наявний тест субнавігації `admin-settings`, якщо він перелічує пункти), щоб він враховував новий пункт. Перевірка: тест зелений

## 6. Filament: вибір технологій у профілі

- [x] 6.1 Перевірити на прототипі `Select::multiple()`, що Filament 5 зберігає й показує обрані значення в порядку стану, а не `options` (design.md, Risks). Якщо ні, застосувати обхід із design.md і зафіксувати рішення там. Перевірка: Livewire-тест, де стан `[3, 1, 2]` після заповнення форми повертається тим самим масивом
- [x] 6.2 Додати `UI/Filament/Components/StackSelect` (design.md, рішення 7) зі створенням технології в модальному вікні через `CreateStackAction`. Перевірка: використовується в задачах 6.3 і 6.4
- [x] 6.3 На сторінці `EditProfileSkills` замінити `TagsInput::make('skills')` на `StackSelect::make('stacks', 'Технології')->required()`, а в `mount()` заповнювати стан id технологій групи у порядку `sort` (з `with('stacks')`). Оновити `EditProfileSkillsTest`: вибір і порядок, зміна порядку перевибором, прибирання технології, створення технології з форми (`callFormComponentAction` для `createOption`), одна технологія в двох групах, група без технологій відхилена, перемикання мови даних не змінює технологій. Перевірка: тест зелений
- [x] 6.4 У `ProjectForm` замінити `TagsInput` на `StackSelect::make('stacks', 'Стек')`, у `recordState()` повертати id у порядку `sort`, у `ProjectsTable` показувати `stacks.name` badge з eager load `stacks` на сторінці `ManageWorkExperienceProjects`. Оновити `ManageWorkExperienceProjectsTest` і `ManageWorkExperienceProjectsLocalizationTest`: порядок стеку у формі й таблиці, створення технології з форми проєкту, проєкт без стеку, зняття технології. Перевірка: тести зелені

## 7. Інтеграційна перевірка

- [x] 7.1 Запустити тести контейнерів `Stack` і `Profile` на тестовій БД (`DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Stack app/Containers/AppSection/Profile`). Перевірка: усі зелені
- [x] 7.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: PHPStan не додає нових помилок понад наявні в проєкті, fixer не лишає змін
- [x] 7.3 Вручну в браузері: створити технологію в «Налаштуваннях», обрати її в групі навичок і в проєкті, створити нову технологію з форми проєкту, перейменувати технологію й побачити нову назву в обох місцях. Перевірка: усе працює, у `browser-logs` немає помилок

## Workflow follow-up

- Після виконання всіх задач і зелених тестів запустити `openspec archive add-technologies`.
