## Context

Технології зараз зберігаються JSON-масивами рядків у двох місцях контейнера `Profile`:
- `profile_skill_groups.skills` (`json`), поле `TagsInput` «Технології» на сторінці `EditProfileSkills`, нормалізація в `ReplaceProfileSkillGroupsTask` через `NormalizesStringList`;
- `profile_projects.technologies` (`json`), поле `TagsInput` «Стек» у `ProjectForm`, колонка `TextColumn::make('technologies')->badge()` у `ProjectsTable`, нормалізація в `PreparesProfileProjectData`.

У проєкті вже є схожий шаблон довідника: «Домени» в контейнері `Company` (глобальна таблиця `domains`, `ManageRecords` у `SettingsCluster`, у формі компанії `Select::multiple()->createOptionForm()->createOptionUsing(CreateDomainAction)`, `SyncCompanyDomainsTask` через `sync()`, видалення домену каскадно прибирає рядки pivot). Поліморфних зв'язків і morph map у проєкті поки немає. Spatie Media Library зберігає `model_type` повним іменем класу.

Проєкт не в продакшені, тож наявні міграції можна редагувати без нових міграцій і без перенесення даних (рішення користувача).

## Goals / Non-Goals

**Goals:**
- Окремий глобальний довідник `Stack` у новому контейнері з керуванням у «Налаштуваннях».
- Один поліморфний зв'язок, який можна підключити до будь-якої моделі (зараз `ProfileSkillGroup` і `ProfileProject`), із збереженням порядку.
- Вибір технологій у формах навичок і проєктів зі створенням нової технології на місці.
- Записи проходять через Actions і Tasks, Filament-код живе всередині контейнерів.

**Non-Goals:**
- Переклади, категорії, іконки технологій.
- Перенесення наявних JSON-даних.
- Перетягування технологій усередині поля.
- Прив'язка до вакансій та інших сутностей (лише підготовлений трейт).

## Decisions

### 1. Новий контейнер `app/Containers/AppSection/Stack`
Довідник не належить профілю: він глобальний, керується в «Налаштуваннях» і в майбутньому знадобиться вакансіям. Контейнер і модель називаються `Stack` (namespace `App\Containers\AppSection\Stack`, таблиця `stacks`, зв'язок `stacks()`): один запис довідника — це одна технологія стеку. В інтерфейсі сторінка й поля лишаються українською («Технології», «Стек»). Структура повторює `Status`:

```
Stack/
├── Actions/{Create,Update,Delete}StackAction.php
├── Tasks/{Create,Update,Delete}StackTask.php, SyncStacksTask.php
├── Models/Stack.php
├── Models/Concerns/HasStacks.php
├── Data/Migrations/2026_11_22_090000_create_stacks_table.php
├── Data/Migrations/2026_11_22_090001_create_stackables_table.php
├── Data/Repositories/StackRepository.php
├── Data/Factories/StackFactory.php
├── Policies/StackPolicy.php
├── Providers/StackServiceProvider.php
├── UI/Filament/Components/StackSelect.php
├── UI/Filament/Resources/Stacks/{StackResource.php, Pages/ManageStacks.php, Schemas/StackForm.php, Tables/StacksTable.php}
├── Tests/{UnitTestCase,FunctionalTestCase,ContainerTestCase}.php, Tests/Unit/..., Tests/Functional/Filament/...
└── composer.json   ("apiato/stack")
```

Міграції мають дату раніше за `profile_skill_groups` (`2026_11_22_100004`), щоб порядок міграцій був природним, хоча жорсткої FK-залежності від профілю немає.

*Альтернатива:* модель у контейнері `Profile`. Відкинуто: довідник глобальний і вийде за межі профілю. Тоді Settings-ресурс залежав би від профілю, а майбутні вакансії тягнули б залежність на `Profile`.

### 2. Схема даних
- `stacks`: `id`, `name` `string(100)`, `timestamps`. Унікальний індекс без урахування регістру: `CREATE UNIQUE INDEX stacks_name_lower_unique ON stacks (lower(name))` через `DB::statement` у міграції, бо Blueprint не підтримує функціональних індексів.
- `stackables`: `stack_id` (`foreignId()->constrained()->cascadeOnDelete()`), `morphs('stackable')` (створює `stackable_type`, `stackable_id` та індекс), `sort` `unsignedSmallInteger`. Первинний ключ — `['stack_id', 'stackable_type', 'stackable_id']`, тож технологія не може повторитися в одному записі.
- Видалення технології каскадно прибирає рядки pivot на рівні БД, а `sort` інших технологій не змінюється. Через прогалини в `sort` порядок не ламається.
- `profile_skill_groups`: колонку `skills` прибрано з `2026_11_22_100004_create_profile_skill_groups_table.php`. `profile_projects`: колонку `technologies` прибрано з `2026_11_23_100000_create_profile_projects_table.php`.

*Альтернатива:* окремі pivot-таблиці `profile_skill_group_stack` і `profile_project_stack` з FK на обидва боки. Вони дають БД-каскад і для власника, але користувач явно попросив поліморфізм, і кожна нова сутність вимагала б нової таблиці.

### 3. Зв'язок через трейт `HasStacks`
Трейт `Stack/Models/Concerns/HasStacks.php` дає моделі метод:

```php
public function stacks(): MorphToMany
{
    return $this->morphToMany(Stack::class, 'stackable')
        ->withPivot('sort')
        ->orderByPivot('sort');
}
```

`ProfileSkillGroup` і `ProfileProject` використовують трейт. Модель `Stack` не має зворотних `morphedByMany`, щоб контейнер `Stack` не залежав від `Profile`. Підрахунок використань на сторінці довідника не потрібен.

### 4. Morph map
У `ProfileServiceProvider::boot()` реєструється `Relation::morphMap(['profile_skill_group' => ProfileSkillGroup::class, 'profile_project' => ProfileProject::class])`. Тоді в БД зберігаються стабільні аліаси, а не імена класів, які залежать від namespace контейнера. `enforceMorphMap` не вмикаємо: тоді довелося б мапити всі моделі з поліморфними зв'язками (зокрема `Company` для media), а це поза межами зміни.

### 5. Синхронізація з порядком: `SyncStacksTask`
`Stack/Tasks/SyncStacksTask::run(Model $owner, list<int> $stackIds): void` прибирає дублікати зі збереженням першої появи, а потім викликає `$owner->stacks()->sync([$id => ['sort' => $index], ...])`. Тип `$owner` — модель із трейтом `HasStacks` (PHPStan: `Model&Stackable`). Для цього трейт супроводжує інтерфейс `Stack/Models/Contracts/Stackable` з методом `stacks(): MorphToMany`. Новий інтерфейс потрібен, бо PHPStan не вміє звужувати тип до трейту.

Profile-Actions викликають Task із контейнера `Stack`. Porto дозволяє Action використовувати Tasks інших контейнерів, а Tasks один одного не викликають:
- `SyncProfileSkillGroupsAction`: у транзакції `ReplaceProfileSkillGroupsTask` повертає створені групи, а потім для кожної групи викликається `SyncStacksTask` з її `stacks` (список id з форми).
- `CreateProfileProjectAction` / `UpdateProfileProjectAction`: у транзакції Create/Update-Task, а потім `SyncStacksTask`, якщо ключ `stacks` є в `$data` (Update без ключа не чіпає стек).

*Альтернатива:* синхронізація всередині Profile-Tasks. Відкинуто, бо тоді Task викликав би Task.

### 6. Очищення pivot під час видалення власника
Поліморфний pivot не має FK на власника, тож рядки треба прибирати явно:
- `ReplaceProfileSkillGroupsTask` перед `deleteWhere` одним запитом прибирає pivot-рядки груп користувача: `stackable_type = 'profile_skill_group'`, `stackable_id IN (id груп користувача)`. Цикл `$group->stacks()->detach()` по кожній групі відкинуто, бо він дає N запитів.
- `DeleteProfileProjectTask` перед видаленням робить `$project->stacks()->detach()`.
- `DeleteProfileWorkExperienceTask`: проєкти видаляються каскадом БД, тож перед видаленням запису Task прибирає pivot-рядки проєктів цього запису одним запитом (`stackable_type = 'profile_project'`, `stackable_id IN (select id from profile_projects where work_experience_id = ?)`). Ці Tasks уже викликаються з Actions у транзакції.

*Альтернатива:* Eloquent-подія `deleting` на моделях. Відкинуто: вона не спрацьовує для `deleteWhere` і БД-каскадів, тож уся відповідальність лишилася б неявною.

### 7. Поле вибору `StackSelect`
`Stack/UI/Filament/Components/StackSelect::make(string $name, string $label): Select` повертає:

```php
Select::make($name)->label($label)->multiple()->searchable()->preload()
    ->options(fn (): array => Stack::query()->orderBy('name')->pluck('name', 'id')->all())
    ->createOptionForm([StackForm::nameInput()])
    ->createOptionModalHeading('Створити технологію')
    ->createOptionUsing(fn (array $data): int => app(CreateStackAction::class)->run($attributes)->id)
```

Папка `Components` не є директорією автовиявлення Filament, це звичайний клас. Його використовують `EditProfileSkills` (`StackSelect::make('stacks', 'Технології')->required()`) і `ProjectForm` (`StackSelect::make('stacks', 'Стек')`). Назва поля в групі змінюється з `skills` на `stacks`.

Порядок вибору: стан `Select::multiple()` — масив id у порядку вибору, і `SyncStacksTask` записує цей порядок у `sort`. Під час заповнення форми стан береться з `$record->stacks->pluck('id')`, який уже впорядкований за `sort`. Перед реалізацією треба перевірити, що Filament 5 рендерить обрані значення в порядку стану, а не в порядку `options`. Якщо ні, див. «Risks».

*Альтернатива:* Repeater з одним Select на рядок і перетягуванням. Відкинуто за рішенням користувача: громіздкий UI.

### 8. Сторінка довідника
`StackResource` — простий ресурс (`ManageStacks extends ManageRecords`) з модальними вікнами, як `StatusResource`: `$cluster = SettingsCluster::class`, slug `stacks`, `$navigationLabel = 'Технології'`, `$modelLabel = 'технологія'`, `$pluralModelLabel = 'технології'`, `Heroicon::OutlinedCpuChip`. Таблиця має колонку «Назва» з пошуком і сортуванням і `defaultSort('name')`. Записи йдуть через `->using()` до `Create/Update/DeleteStackAction`. Вибір модальних вікнів, а не окремих сторінок, пояснюється тим, що форма має одне поле, як у «Статусах» і «Доменах».

`StackForm::nameInput()` повертає `TextInput::make('name')->label('Назва')->required()->maxLength(100)` з правилом унікальності без урахування регістру: `->unique(modifyRuleUsing: ...)` не працює з `lower()`, тому використовується `->rule(fn (?Stack $record) => closure-правило з whereRaw('lower(name) = lower(?)'))` з українським повідомленням `validation.unique`. Ту саму перевірку дублює `Create/UpdateStackTask`, кидаючи `ValidationException` на `name`, щоб CLI та інші UI не обійшли правило. Унікальний індекс у БД — остання лінія захисту. Обидва Tasks обрізають пробіли на краях.

### 9. Авторизація
`StackPolicy` дозволяє `viewAny/view/create/update/delete` будь-якому автентифікованому користувачу, як `DomainPolicy`. Довідник спільний. Власність груп і проєктів і далі перевіряють наявні політики профілю.

### 10. Відображення в таблиці проєктів
`ProjectsTable` показує `TextColumn::make('stacks.name')->badge()->limitList(4)`, а запит сторінки підвантажує `with('stacks')`, бо `shouldBeStrict()` забороняє lazy loading. Порядок береться з `orderByPivot('sort')` у визначенні зв'язку.

## Risks / Trade-offs

- [Filament може показувати обрані значення в порядку `options`, а не стану] → перевірено під час реалізації (задача 6.1): `Select::getOptionLabels()` і JS-утиліта `select.js` (`getLabelsForMultipleSelection`, `selectOption`) ітерують стан і дописують нове значення в кінець, тож порядок вибору зберігається без обходу.
- [Осиротілі рядки `stackables` після каскадного видалення користувача (`users` → `profile_skill_groups`, `profile_work_experiences`)] → у UI видалення користувача немає. Осиротілі рядки не впливають на поведінку, бо їх ніхто не читає без власника. Якщо колись додадуть видалення користувача, його Action має прибрати pivot-рядки. Записуємо це як відоме обмеження.
- [Профільні Actions залежать від контейнера `Stack`] → залежність одностороння й очікувана (довідник базовий). `Stack` не імпортує нічого з `Profile`.
- [Втрата наявних даних після `migrate:fresh`] → погоджено з користувачем, проєкт не в продакшені.
- [Перейменування на назву, що відрізняється лише регістром від іншої, падає на унікальному індексі] → валідація в полі й у Task ловить це раніше, ніж БД.
