# Design

## Context

- Мотивація — див. proposal.md (Why). Вимоги — дельти `contacts`, `vacancies`, `vacancy-sources`.
- Довідник джерел — контейнер `VacancySource` (модель `VacancySource`, `VacancySourceForm`, `CreateVacancySourceAction`), сторінка в кластері «Налаштування».
- Вакансія вже має джерела з посиланням: таблиця `vacancy_vacancy_source`, зв'язок `Vacancy::vacancySources()` (`belongsToMany` з `url` у pivot, `orderByPivot('id')`), запис через `ReplaceVacancySourcesTask` у `Create`/`UpdateVacancyAction`. У `VacancyForm` приватний метод `sourcesRepeater()` будує `Repeater sources` (Select джерела з `createOptionForm`, `distinct()`, `TextInput url` з `url:http,https`). URL з pivot читає `Vacancy/UI/Filament/Support/VacancySourceLink`.
- Найближчий аналог розділу з модалками — `Company` (`ManageCompanies` + `CompaniesTable` з `EditAction`/`DeleteAction`/`DeleteBulkAction` через Actions). Мультивибір зі створенням опції без `->relationship()` уже є в `CompanyForm` (поле `domains`), запис — через `SyncCompanyDomainsTask` в Actions.
- Порядок у головній навігації: «Вакансії» (10), «Компанії» (20), кластери «Профіль» (90), «Налаштування» (100).
- Рішення користувача: контакт без компанії; джерела контакту — зі спільного довідника; прив'язка контактів — лише у формі вакансії; розділ «Контакти» — таблиця з модалками (простий ресурс).

## Goals / Non-Goals

**Goals:**
- Новий контейнер `Contact` за структурою Porto, Filament усередині контейнера.
- Один шлях запису для контактів, їхніх джерел і контактів вакансії: Action → Task, у транзакції.
- Повторне використання вже наявного repeater джерел для вакансії й контакту без копіювання.

**Non-Goals:**
- Сторінка «Огляд» контакту, relation manager вакансій у контакті.
- Порядок контактів у вакансії, роль контакту.

## Decisions

### 1. Новий контейнер `app/Containers/AppSection/Contact`
Структура як у `VacancySource`/`Company`:
- `Models/Contact.php` (`name`), `Data/Migrations`, `Data/Factories/ContactFactory.php`, `Data/Repositories/ContactRepository.php`;
- `Actions/{Create,Update,Delete}ContactAction.php`, `Tasks/{Create,Update,Delete}ContactTask.php`, `Tasks/ReplaceContactSourcesTask.php`;
- `Policies/ContactPolicy.php` (усі дії дозволені, як в інших контейнерах), `Providers/ContactServiceProvider.php`, `composer.json`;
- `UI/Filament/Resources/Contacts/{ContactResource.php, Pages/ManageContacts.php, Schemas/ContactForm.php, Tables/ContactsTable.php}`;
- `Tests/{ContainerTestCase, FunctionalTestCase}.php` і `Tests/Functional/...`. `UnitTestCase` і `Tests/Unit` не створюємо: unit-тести поза зміною.

*Альтернатива:* контакти всередині `Vacancy` — відкинуто, бо контакт живе й без вакансії та має власний розділ і довідник.

### 2. Дані
- `contacts`: `id`, `name` string(255), timestamps. Без унікальності імені, без `company_id`.
- `contact_vacancy_source` (у `Contact/Data/Migrations`): `id`, `contact_id` FK `cascadeOnDelete`, `vacancy_source_id` FK `cascadeOnDelete`, `url` string(2048) nullable, timestamps, `unique(['contact_id', 'vacancy_source_id'])`. Та сама форма, що й `vacancy_vacancy_source`; `cascadeOnDelete` на джерелі реалізує дельту `vacancy-sources` для контактів.
- `contact_vacancy` (у `Vacancy/Data/Migrations`, мітка часу пізніша за міграції `contacts`): `id`, `contact_id` FK `cascadeOnDelete`, `vacancy_id` FK `cascadeOnDelete`, timestamps, `unique(['contact_id', 'vacancy_id'])`. Обидва `cascadeOnDelete` видаляють лише рядки зв'язку: видалення контакту чи вакансії лишає другу сторону.
- Назви таблиць — конвенція Laravel для `belongsToMany`, тож зв'язки не потребують явних назв таблиць.

*Альтернатива:* `contact_vacancy` у контейнері `Contact` — відкинуто: зв'язок задають і пишуть з боку вакансії (форма вакансії, `Create`/`UpdateVacancyAction`), тому міграцію тримає `Vacancy`, як і `vacancy_vacancy_source`.

### 3. Моделі й зв'язки
- `Contact::vacancySources(): BelongsToMany` — `belongsToMany(VacancySource::class)->withPivot('url')->withTimestamps()->orderByPivot('id')`, як у `Vacancy`. Та сама назва зв'язку дає змогу повторно використати repeater, `VacancySourceLink` і стан форми.
- `Contact::vacancies(): BelongsToMany` — `belongsToMany(Vacancy::class)->withTimestamps()`, лише для колонки «Вакансії» в таблиці контактів.
- `Vacancy::contacts(): BelongsToMany` — `belongsToMany(Contact::class)->withTimestamps()->orderBy('contacts.name')`. `Vacancy::DISPLAY_RELATIONS` доповнюється `contacts.vacancySources`, щоб огляд не робив lazy loading (strict models).
- `VacancySource` зворотних зв'язків не отримує.

*Компроміс:* `Contact` ↔ `Vacancy` — двонаправлена залежність контейнерів. Альтернатива (колонку «Вакансії» будувати запитом від `Vacancy` без зв'язку в `Contact`) складніша й гірше лягає на `TextColumn` зв'язку; двонаправленість обмежена моделями, Actions і Tasks кожного контейнера пишуть лише свої дані.

### 4. Спільний repeater джерел
`VacancyForm::sourcesRepeater()` і `vacancySourceSelect()` переносяться в контейнер-власник довідника: `VacancySource/UI/Filament/Resources/VacancySources/Schemas/VacancySourcesRepeater.php` з `public static function make(): Repeater` (поле `sources`, ті самі правила: `required`, `distinct`, `disableOptionsWhenSelectedInSiblingRepeaterItems`, `createOptionForm` з моделлю `VacancySource`, `url:http,https`, `maxLength(2048)`, `defaultItems(0)`, без перетягування, `hiddenLabel()`). `VacancyForm` і `ContactForm` викликають `VacancySourcesRepeater::make()`. Поведінка форми вакансії не змінюється — це покривають наявні функціональні тести вакансій.

*Альтернатива:* копія repeater у `ContactForm` — дві копії правил валідації, які розійдуться.

### 5. Запис контакту
- `ContactForm`: `TextInput name` (required, max 255) + секція «Джерела» з `VacancySourcesRepeater::make()`.
- `CreateContactAction::run(array $data): Contact` і `UpdateContactAction::run(int $id, array $data): Contact` — у `DB::transaction`: `Arr::pull($data, 'sources')`, `Create`/`UpdateContactTask` через `ContactRepository`, потім `ReplaceContactSourcesTask::run(Contact, list<array>)` (`sync()` з нормалізацією: `vacancy_source_id` → int, порожній `url` → `null`). `UpdateContactAction` без ключа `sources` джерела не змінює. `DeleteContactAction` → `DeleteContactTask`.
- `ReplaceContactSourcesTask` повторює `ReplaceVacancySourcesTask` для іншої моделі. *Альтернатива:* спільна Task у `Ship` для будь-якої моделі з `vacancySources()` — відкинуто як передчасне узагальнення для двох випадків.

### 6. Розділ «Контакти» — простий ресурс
- `ContactResource`: `$navigationIcon = Heroicon::OutlinedUserGroup`, `$navigationSort = 30` (після «Компанії»), `$navigationLabel`/`$pluralModelLabel` «Контакти», `$modelLabel` «Контакт», `$recordTitleAttribute = 'name'`, сторінка лише `index` → `ManageContacts`.
- `ManageContacts::getHeaderActions()`: `CreateAction::make()->modalHeading('Створити контакт')->using(fn => CreateContactAction)`.
- `ContactsTable`: `modifyQueryUsing(with(['vacancySources', 'vacancies']))`; колонки `name` (searchable, sortable), `vacancySources.name` «Джерела» (badge, placeholder «—»), `vacancies.title` «Вакансії» (badge, `limitList(3)->expandableLimitedList()`, placeholder «—»); `defaultSort('name')`; `EditAction` з `mutateRecordDataUsing` (заповнює `sources` з `vacancySources` + `VacancySourceLink::url`) і `->using(UpdateContactAction)`; `DeleteAction` і `DeleteBulkAction` через `DeleteContactAction`.
- Дані форми редагування збирає статичний хелпер, спільний для `ViewVacancy` і `ContactsTable`: `VacancySourcesRepeater::stateFrom(Collection<int, VacancySource>)` повертає `list<array{vacancy_source_id: int, url: string|null}>`. `VacancySourceLink` переїжджає разом із ним у `VacancySource/UI/Filament/Support/VacancySourceLink.php`, щоб `Contact` не залежав від UI-класів `Vacancy`.

*Альтернатива:* окремі сторінки створення/редагування — відкинуто за рішенням користувача.

### 7. Контакти у формі й на огляді вакансії
- `VacancyForm`: секція «Контакти» після «Джерела» з `Select::make('contacts')->multiple()->hiddenLabel()->options(fn () => Contact::query()->pluck('name', 'id')->sort())->searchable()->createOptionForm(fn (Schema $schema) => ContactForm::configure($schema->model(Contact::class)))->createOptionUsing(fn (array $data) => app(CreateContactAction::class)->run($data)->id)`. Без `->relationship()`, щоб запис ішов через Actions.
- `Create`/`UpdateVacancyAction`: `Arr::pull($data, 'contacts')` і `SyncVacancyContactsTask::run(Vacancy, list<int>)` (`sync()`, нечислові значення відкидаються, як у `CompanyAction::domainIds`) у тій самій транзакції. `UpdateVacancyAction` без ключа `contacts` контакти не змінює; `CreateVacancyAction` трактує відсутній ключ як порожній список.
- `ViewVacancy::mutateRecordDataUsing` додає `contacts` = `$record->contacts->modelKeys()` і переходить на `VacancySourcesRepeater::stateFrom()` для `sources`.
- `VacancyInfolist`: секція «Контакти» після «Джерела» з `RepeatableEntry::make('contacts')->hiddenLabel()->placeholder('—')`, усередині `TextEntry name` і вкладений `RepeatableEntry vacancySources` (`TextEntry name`, `TextEntry pivot.url` з `->url(VacancySourceLink::url(...), shouldOpenInNewTab: true)`).

*Альтернатива:* `Select::relationship('contacts')` — Filament зберігає зв'язок сам, повз Actions, що суперечить AGENTS.md.

### 8. Тести — лише функціональні
- `Contact/Tests/Functional/Filament/Resources/Contacts/ContactResourceTest.php`: гість → логін, сторінка за `/admin/contacts`, немає окремих сторінок create/edit, пункт у головній навігації.
- `Contact/Tests/Functional/Filament/Resources/Contacts/Pages/ManageContactsTest.php`: сценарії дельти `contacts` (таблиця, сортування, пошук, створення з двома джерелами й посиланнями — перевірка записів у БД, джерело без посилання, повтор джерела, посилання без джерела, URL без схеми й `ftp://`, створення джерела з рядка, однакові імена, редагування показує й прибирає джерела, видалення з вакансією, масове видалення).
- `Contact/Tests/Functional/Filament/VacancySources/DeletingVacancySourceTest.php`: видалення джерела на `ManageVacancySources` лишає контакт з рештою джерел.
- `Vacancy` — розширення `ListVacanciesTest` (створення з двома контактами, створення контакту з форми), `ViewVacancyTest` (редагування показує й прибирає контакти, огляд показує контакти з посиланнями й прочерк, видалення вакансії лишає контакт).
- Наявні unit-тести `Vacancy` не змінюються, нові unit-тести не пишуться.

## Risks / Trade-offs

- [Однакові імена контактів нерозрізненні у Select форми вакансії] → прийнятно на старті; підпис опції з джерелами — окрема зміна, якщо знадобиться.
- [Довідник називається «Джерела вакансій», а використовується й для контактів] → перейменування поза зміною; поведінка довідника не змінюється.
- [Вкладений `createOptionForm` (джерело всередині створення контакту всередині форми вакансії) — три рівні модалок] → штатна можливість Filament; покрито тестом створення контакту з форми вакансії, створення джерела з форми контакту перевіряється на сторінці «Контакти».
- [Двонаправлена залежність `Contact` ↔ `Vacancy` на рівні моделей] → обмежена зв'язками; див. рішення 3.
- [Перенесення repeater у `VacancySource` зачіпає форму вакансії] → наявні функціональні тести джерел вакансії мають лишатися зеленими без змін.

## Migration Plan

1. `docker compose exec app php artisan migrate` — створює `contacts`, `contact_vacancy_source`, `contact_vacancy`. Наявні дані не змінюються.
2. Відкат: `php artisan migrate:rollback --step=3` видаляє три таблиці.
