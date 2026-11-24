# Design

## Context

- Зараз вакансія тримає джерело в колонці `vacancies.vacancy_source_id` (FK `nullOnDelete`) і одне посилання в `vacancies.url` (міграція `Vacancy/Data/Migrations/2026_11_23_200001_create_vacancies_table.php`). Модель `Vacancy` має `belongsTo` `vacancySource()` і константу `DISPLAY_RELATIONS = ['company', 'status', 'vacancySource']`, яку використовують Actions і `VacancyResource::getEloquentQuery()`.
- `VacancyForm` має `Select vacancy_source_id` з `createOptionForm` (`VacancySourceForm` + `CreateVacancySourceAction`) і `TextInput url`. Форма спільна для модалки створення (`ListVacancies`) і бокової панелі редагування (`ViewVacancy`), обидві пишуть через `Create`/`UpdateVacancyAction`.
- Найближчий аналог списку в проєкті — `Profile/UI/Filament/Pages/EditProfileLinks`: `Repeater` без `->relationship()`, дані йдуть у `SyncProfileLinksAction`, яка викликає `ReplaceProfileLinksTask`. Повторюємо цей патерн, щоб запис ішов через Actions, а не через Eloquent-персистенцію Filament.
- На проді вакансій немає, тож наявні дані переносити не потрібно.
- Специфікації `vacancies` і `vacancy-sources` поки що існують лише в незаархівованій зміні `add-vacancies`.

## Goals / Non-Goals

**Goals:**
- Зв'язок «вакансія — джерела» з посиланням для кожної пари, без повторів джерела в межах вакансії.
- Один шлях запису джерел (Action → Task) для створення й редагування.

**Non-Goals:**
- Окрема модель/ресурс для зв'язку (немає власних сторінок чи дій).
- Порядок джерел і «основне» джерело.

## Decisions

### 1. Зв'язувальна таблиця `vacancy_vacancy_source` з колонкою `url`
Нова міграція в `Vacancy/Data/Migrations` (мітка часу після `create_vacancies_table`): `id`, `vacancy_id` FK `cascadeOnDelete`, `vacancy_source_id` FK `cascadeOnDelete`, `url` string(2048) nullable, timestamps, `unique(['vacancy_id', 'vacancy_source_id'])`. Назва таблиці — конвенція Laravel для `belongsToMany` (`Vacancy` + `VacancySource` в алфавітному порядку), тож зв'язок не потребує явного імені таблиці.

`cascadeOnDelete` на `vacancy_source_id` реалізує вимогу «видалення джерела прибирає його з вакансій, вакансії лишаються»: видаляються лише рядки зв'язку, вакансія не зачіпається.

*Альтернативи:* окрема модель `VacancyListing` з `hasMany` — зайва, бо в зв'язку немає власної поведінки, крім одного поля; JSON-колонка зі списком — втрачає FK-цілісність і фільтр через `whereHas`.

### 2. Видалення старих колонок у тій самій міграції, без перенесення даних
`up()`: створити таблицю; потім `dropConstrainedForeignId('vacancy_source_id')` і `dropColumn('url')` у `vacancies`. Дані не переносяться, бо на проді вакансій немає.
`down()`: повернути колонки (`foreignId('vacancy_source_id')->nullable()->constrained()->nullOnDelete()`, `string('url', 2048)->nullable()`) порожніми, видалити таблицю.

*Альтернатива:* редагувати початкову міграцію `create_vacancies_table` — відкинуто, бо вона вже застосована в dev-БД і закомічена в `main`.

### 3. Модель `Vacancy`
- `vacancySource()` замінюється на `vacancySources(): BelongsToMany` — `belongsToMany(VacancySource::class)->withPivot('url')->withTimestamps()->orderByPivot('id')`, щоб порядок джерел у формі, таблиці й огляді відповідав порядку додавання.
- З `$fillable` і `@property` прибрати `vacancy_source_id` і `url`; додати `@property Collection<int, VacancySource> $vacancySources`.
- `DISPLAY_RELATIONS = ['company', 'status', 'vacancySources']`.
- `VacancyFactory`: прибрати `vacancy_source_id` і `url`; стан повністю заповненої вакансії додає два джерела через `hasAttached(VacancySource::factory()->count(2), fn () => ['url' => fake()->url()])`.

`VacancySource` не отримує зворотного зв'язку: залежність лишається однонаправленою `Vacancy` → `VacancySource`.

### 4. Запис джерел: `ReplaceVacancySourcesTask` + розширення Actions
- `Vacancy/Tasks/ReplaceVacancySourcesTask::run(Vacancy $vacancy, list<array{vacancy_source_id: int, url: string|null}> $sources): void` — `$vacancy->vacancySources()->sync([...id => ['url' => ...]])`. `sync()` оновлює `url` наявних пар, додає нові й прибирає відсутні.
- `CreateVacancyAction` і `UpdateVacancyAction` у тій самій `DB::transaction` виокремлюють ключ `sources` із `$data` (`Arr::pull`), створюють/оновлюють вакансію через наявні Tasks і викликають `ReplaceVacancySourcesTask`. Якщо ключа `sources` немає в `$data`, `UpdateVacancyAction` джерела не чіпає (оновлення лише атрибутів, наприклад, з майбутніх дій зміни статусу); `CreateVacancyAction` трактує відсутній ключ як порожній список.
- Нормалізацію рядків (порожній `url` → `null`, приведення id до `int`) робить Task — так само, як `ReplaceProfileLinksTask` у `Profile`.

*Альтернатива:* окрема `SyncVacancySourcesAction`, яку викликає Filament після збереження вакансії — дві транзакції замість однієї, ризик вакансії без джерел при помилці.

### 5. Форма: `Repeater` `sources` у `VacancyForm`
Поля `vacancy_source_id` і `url` прибираються із секції «Основне»; замість них — окрема секція «Джерела» одразу після «Основне» з `Repeater::make('sources')->label('Джерела')->hiddenLabel()` на всю ширину, без `->relationship()`:
- `Select::make('vacancy_source_id')->label('Джерело')->options(fn () => VacancySource::query()->orderBy('name')->pluck('name', 'id'))->searchable()->required()->distinct()->disableOptionsWhenSelectedInSiblingRepeaterItems()` + наявні `createOptionForm`/`createOptionUsing` з `VacancySourceForm` і `CreateVacancySourceAction` (метод `vacancySourceSelect()` лишається, змінюється лише джерело опцій, бо `->relationship()` у рядку repeater без relationship недоступне).
- `TextInput::make('url')->label('Посилання')->url()->rule('url:http,https')->maxLength(2048)` (правила з попереднього поля без змін).
- `->columns(2)->defaultItems(0)->addActionLabel('Додати джерело')`; без перетягування (порядок не є вимогою).

`required()` на джерелі в кожному рядку дає вимогу «посилання без джерела відхиляється»; порожній рядок (без джерела й посилання) теж відхиляється — користувач прибирає його кнопкою видалення рядка. `->distinct()` — серверна валідація повтору джерела, `disableOptionsWhenSelectedInSiblingRepeaterItems()` — підказка в UI.

Заповнення форми редагування: у `ViewVacancy` `EditAction::make()->fillForm(...)` / `->mutateRecordDataUsing()` додає `sources` з `$record->vacancySources` (`vacancy_source_id` + `pivot->url`).

*Альтернатива:* `Select::multiple()` джерел + окремий repeater посилань — розриває пару «джерело — посилання»; `Repeater::relationship()` — пише в БД повз Actions, суперечить AGENTS.md.

### 6. Таблиця `VacanciesTable`
- Прибрати колонки `work_format` («Формат»), `salary` («Зарплата»), `next_action_at` («Наступна дія»). Імпорт `SalaryRangeFormatter` у таблиці стає зайвим; сам форматер лишається для огляду.
- `vacancySource.name` → `TextColumn::make('vacancySources.name')->label('Джерела')->badge()->placeholder('—')` (Filament показує значення зв'язку «багато» списком бейджів).
- Фільтр `SelectFilter::make('vacancySources')->relationship('vacancySources', 'name')->preload()` — Filament будує `whereHas`, що й дає «серед джерел є обране». Фільтр за форматом роботи лишається.

### 7. Огляд `VacancyInfolist`
Записи `vacancySource.name` і `url` прибираються із секції «Основне»; замість них — окрема секція «Джерела» одразу після «Основне» з `RepeatableEntry::make('vacancySources')->label('Джерела')->hiddenLabel()` з `TextEntry name` і `TextEntry pivot.url` (`->url(..., shouldOpenInNewTab: true)`, без placeholder — порожнє посилання не показується), `->placeholder('—')` для вакансії без джерел, на всю ширину секції.

*Альтернатива:* один `TextEntry` з HTML-списком посилань — потребує ручного екранування; `RepeatableEntry` — штатний компонент.

## Risks / Trade-offs

- [Джерела й посилання наявних вакансій губляться під час міграції] → на проді вакансій немає; у dev-БД джерела наявних вакансій треба додати заново.
- [Видалення джерела з довідника мовчки видаляє посилання вакансій на цьому джерелі] → прийнятно за вимогою; попередження в модалці видалення джерела — поза зміною.
- [`down()` повертає порожні колонки] → відкат втрачає джерела вакансій; прийнятно для dev-відкату.
- [`->distinct()` на полі в repeater без relationship може не валідувати порожні значення] → покрити тестом сценарій «повтор джерела відхиляється».
- [Архів цієї зміни відхиляється, поки специфікації `vacancies`/`vacancy-sources` не в `openspec/specs`] → спочатку заархівувати `add-vacancies`.

## Migration Plan

1. Заархівувати `add-vacancies` (`openspec archive add-vacancies`) перед архівом цієї зміни.
2. `docker compose exec app php artisan migrate` — нова міграція створює зв'язувальну таблицю, переносить дані й прибирає старі колонки.
3. Відкат: `php artisan migrate:rollback --step=1` повертає порожні колонки `vacancy_source_id`/`url`.
