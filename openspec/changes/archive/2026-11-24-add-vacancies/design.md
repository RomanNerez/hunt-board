# Design

## Context

- Довідники, на які посилається вакансія, уже є: `Company` (`app/Containers/AppSection/Company`, керування в модалках на `ManageCompanies`, форма `CompanyForm`, запис через `CreateCompanyAction`) і `Status` (`app/Containers/AppSection/Status`, сторінка в `SettingsCluster`, бейдж через `StatusColor::renderBadge()`).
- Найближчий аналог довідника — `CompanyType`: ресурс у `App\Ship\Filament\Clusters\Settings\SettingsCluster`, `Manage*`-сторінка, `createOptionForm` + `createOptionUsing` з виклику Action у формі компанії. `VacancySource` повторює цей патерн.
- Дані глобальні (без `user_id`), як у компаній і статусів; панель пускає всіх автентифікованих, policies повертають `true`.
- `Model::shouldBeStrict()` увімкнено поза production, тому зв'язки таблиці й огляду треба завантажувати жадібно.
- Валідація в проєкті живе у Filament-формах (правила полів), Actions лише записують дані в транзакції.
- БД — Postgres (і для тестів — окрема БД `hunt-board_testing`).

## Goals / Non-Goals

**Goals:**
- Два нові контейнери за Porto: `VacancySource` (довідник) і `Vacancy` (сутність + розділ).
- Сторінка вакансії як «робочий простір запису» на штатній субнавігації запису Filament, щоб майбутні підсторінки (співбесіди, контакти) додавалися як ще один пункт.
- Повторне використання форми компанії без дублювання правил.

**Non-Goals:**
- Окрема валідація в Actions/Tasks (як і в решті контейнерів, правила — у формі).
- Мультитенантність / прив'язка вакансій до користувача.
- Локалізація через файли перекладу: підписи українською прямо в Filament-класах, як у сусідніх ресурсах.

## Decisions

### 1. Два окремі контейнери: `Vacancy` і `VacancySource`
`app/Containers/AppSection/VacancySource` містить модель `VacancySource`, міграцію `vacancy_sources` (`id`, `name` unique, timestamps), фабрику, репозиторій, Tasks `Create/Update/DeleteVacancySourceTask`, Actions `Create/Update/DeleteVacancySourceAction`, `VacancySourcePolicy`, ресурс `UI/Filament/Resources/VacancySources/` (`VacancySourceResource`, `Pages/ManageVacancySources`, `Schemas/VacancySourceForm`, `Tables/VacancySourcesTable`) з `$cluster = SettingsCluster::class`, slug `vacancy-sources`.

`app/Containers/AppSection/Vacancy` містить усе інше. Залежність однонаправлена: `Vacancy` → `Company`, `Status`, `VacancySource`; жоден із довідників не знає про `Vacancy`.

*Альтернативи:* джерело як рядкова колонка `source` (як у постановці) — відкинуто, бо користувач явно хоче окремий контейнер-довідник; джерело як enum — відкинуто, бо список має поповнюватися з UI.

### 2. Схема `vacancies`
Міграція в `Vacancy/Data/Migrations` з міткою часу після `vacancy_sources`:
`company_id` FK `cascadeOnDelete`, `status_id` FK `restrictOnDelete`, `vacancy_source_id` nullable FK `nullOnDelete`, `title` string, `url` string(2048) nullable, `description` longText nullable, `location` string nullable, `work_format` string nullable, `salary_min`/`salary_max` unsignedInteger nullable, `currency` string(3) nullable, `applied_at` date nullable, `next_action_at` dateTime nullable, `notes` text nullable, timestamps. Індекси на FK створюються `foreignId()->constrained()`.

Колонку джерела названо `vacancy_source_id` (а не `source`), бо це FK на довідник; зв'язок у моделі — `vacancySource()`.

Модель `Vacancy`: `belongsTo` `company`, `status`, `vacancySource`; casts: `work_format` → `WorkFormat`, `currency` → `Currency`, `applied_at` → `date`, `next_action_at` → `datetime`, `salary_*` → `integer`.

### 3. Фіксовані переліки — PHP enums у контейнері `Vacancy`
`Vacancy/Enums/WorkFormat` (`remote`/`hybrid`/`office`, `HasLabel` з «Віддалено», «Гібрид», «Офіс») і `Vacancy/Enums/Currency` (`USD`/`EUR`/`UAH`, `HasLabel`). Filament `Select::make()->options(WorkFormat::class)` дає українські підписи й валідацію `in` автоматично; БД зберігає латинський ключ (узгоджено з `interface-localization`).

*Альтернатива:* довідники-таблиці — зайві для незмінних переліків.

### 4. Опис — rich text, санітизація в моделі
Поле форми — `RichEditor` з обмеженим тулбаром: `bold`, `italic`, `h2`, `h3`, `bulletList`, `orderedList`, `link`, `blockquote`, `undo`, `redo`; вкладень файлів немає. Редактор зберігає HTML (без `->json()`).

Модель має set-мутатор `description`, який пропускає HTML через `Str::sanitizeHtml()` (Symfony HtmlSanitizer, налаштований Filament) і перетворює результат без видимого тексту (зокрема `<p></p>` порожнього редактора) на `null`. Мутатор гарантує інваріант для будь-якого шляху запису (Filament, CLI, фабрики, майбутній імпорт). На «Огляді» опис показує `TextEntry::html()`, який ще раз санітизує вміст під час виводу.

*Альтернатива:* чистий текст у `Textarea` з видаленням тегів — початкове рішення, відкинуте на прохання користувача: опис вакансій із DOU/LinkedIn зручніше читати з форматуванням.

### 5. Валідація у формі `VacancyForm`
`Vacancy/UI/Filament/Resources/Vacancies/Schemas/VacancyForm.php`, спільна для модалки створення й сторінки редагування. Правила: `title` required max 255; `company_id` required; `status_id` required; `url` `->url()->rule('url:http,https')->maxLength(2048)` (як у `CompanyForm`); `salary_min`/`salary_max` `->integer()->minValue(0)`; `salary_max` `->gte('salary_min')` лише коли обидва заповнені; `currency` `->requiredWith('salary_min,salary_max')`; `applied_at` `DatePicker`; `next_action_at` `DateTimePicker`; `description` — `RichEditor` (рішення 4), `notes` — `Textarea`, обидва на всю ширину. Форма згрупована в секції «Основне», «Умови», «Дати», «Опис і нотатки».

### 6. Створення компанії й джерела з форми вакансії
- Компанія: `Select::make('company_id')->searchable()->preload()->createOptionForm(fn (Schema $schema): Schema => CompanyForm::configure($schema))->createOptionUsing(fn (array $data): int => app(CreateCompanyAction::class)->run($attributes)->id)`. Повна форма компанії використовується повторно, тому правила (унікальність назви, URL, логотип) ідентичні розділу «Компанії».
- Джерело: аналогічно з `VacancySourceForm` і `CreateVacancySourceAction`.
- Статус: лише вибір із довідника (з пошуком), без створення з форми — постановка цього не вимагає.

*Альтернатива:* спрощена форма компанії «лише назва» — відкинуто, бо дублювала б правила й давала б неповні компанії.

### 7. Ресурс `VacancyResource`: сторінки й субнавігація запису
`Vacancy/UI/Filament/Resources/Vacancies/`:
- `VacancyResource` — у головній навігації (`Heroicon::OutlinedBriefcase`, label «Вакансії», `$navigationSort` вище за «Компанії»), `$recordTitleAttribute = 'title'`, `$subNavigationPosition = SubNavigationPosition::Top` (вкладки над вмістом сторінки), `getRecordSubNavigation(Page $page)` повертає `$page->generateNavigationItems([ViewVacancy::class])`, щоб майбутні підсторінки (співбесіди, контакти) додавалися поруч з «Оглядом».
- `getPages()`: `index` → `ListVacancies` (`/`), `view` → `ViewVacancy` (`/{record}`). Сторінок `create` і `edit` немає, тому `CreateAction` на `ListVacancies` працює модально, `EditAction` на `ViewVacancy` — у боковій панелі, а `/admin/vacancies/create` і `/admin/vacancies/{id}/edit` дають 404 (`resolveRecordRouteBinding()` відкидає нечислові ключі).
- `ViewVacancy` (`ViewRecord`, `$navigationLabel = 'Огляд'`): `infolist` з `VacancyInfolist`; header actions — `EditAction::make()->slideOver()->using(fn (Vacancy $record, array $data): Vacancy => app(UpdateVacancyAction::class)->run($record->id, $attributes))` і `DeleteAction`. Після збереження Filament оновлює запис сторінки, тож «Огляд» одразу показує нові дані.
- `DeleteAction`: `->using(fn (Vacancy $record): bool => app(DeleteVacancyAction::class)->run($record->id))->successRedirectUrl(VacancyResource::getUrl('index'))`.

*Альтернативи:* `ManageRelatedRecords`-патерн із `WorkExperience` — не підходить, бо тут немає пов'язаних записів; окрема сторінка «Редагування» в субнавігації — початкове рішення, замінене боковою панеллю на прохання користувача: редагування не виводить з контексту «Огляду».

### 8. Модальне створення з переходом на «Огляд»
`ListVacancies::getHeaderActions()`: `CreateAction::make()->modalWidth(Width::FiveExtraLarge)->using(fn (array $data): Vacancy => app(CreateVacancyAction::class)->run($attributes))->successRedirectUrl(fn (Vacancy $record): string => VacancyResource::getUrl('view', ['record' => $record]))`.

### 9. Таблиця `VacanciesTable`
Колонки: `title` (searchable, sortable), `company.name` (searchable), `status.name` бейджем через `StatusColor::renderBadge()` (як у `StatusesTable`), `vacancySource.name`, `work_format`, зарплатна вилка (обчислювана колонка), `applied_at` (date), `next_action_at` (dateTime), `created_at` (прихована за замовчуванням). `defaultSort('created_at', 'desc')`. Фільтри `SelectFilter`: `status` (relationship), `company` (relationship, searchable), `vacancySource` (relationship), `work_format` (options з enum). Жадібне завантаження в `VacancyResource::getEloquentQuery()->with(['company', 'status', 'vacancySource'])`.

`recordActions([ViewAction::make()])` і `recordUrl()` на `view`; `toolbarActions` порожні.

Форматування вилки — `Vacancy/UI/Filament/Support/SalaryRangeFormatter` (статичний метод `format(?int $min, ?int $max, ?Currency $currency): ?string`, результат «4 000–5 500 USD», «від 3 000 USD», «до 5 000 USD»), як `PeriodFormatter` у `Profile`. Використовується і в таблиці, і в огляді.

### 10. `VacancyInfolist`
Секції: «Основне» (назва, компанія, статус-бейдж, джерело, посилання `->url(..., shouldOpenInNewTab: true)`, локація, формат), «Умови» (вилка), «Дати» (`applied_at`, `next_action_at`), «Опис» (`TextEntry` з `->html()`, санітизований вивід), «Нотатки» (`TextEntry` з `->extraAttributes(['class' => 'whitespace-pre-line'])`, щоб зберегти переноси). Порожні значення — `->placeholder('—')`. Зв'язки довантажує `VacancyResource::getEloquentQuery()`.

### 11. Actions і Tasks контейнера `Vacancy`
Tasks: `CreateVacancyTask`, `UpdateVacancyTask`, `DeleteVacancyTask` (через `VacancyRepository`). Actions: `CreateVacancyAction`, `UpdateVacancyAction`, `DeleteVacancyAction` — кожна в `DB::transaction`, повертає модель із завантаженими `company`, `status`, `vacancySource`. `VacancyPolicy` — `viewAny`, `view`, `create`, `update`, `delete` → `true`; `deleteAny` не визначаємо (масових дій немає).

### 12. Відмова у видаленні статусу без залежності `Status` → `Vacancy`
`DeleteStatusTask` перехоплює `Illuminate\Database\QueryException` з SQLSTATE `23503` (foreign key violation, Postgres) і кидає нове `App\Containers\AppSection\Status\Exceptions\StatusInUseException`. У `StatusesTable`:
- `DeleteAction` у `->using()` ловить виняток, показує `Notification::make()->danger()` «Статус використовується вакансіями» і викликає `$action->halt()`/`cancel()`, щоб не показувати сповіщення про успіх.
- `DeleteBulkAction` видаляє кожен запис окремою `DeleteStatusAction` (окрема транзакція — в Postgres помилка FK обриває транзакцію), рахує відмови й наприкінці показує попередження «Частину статусів не видалено, бо їх використовують вакансії».

`Status` при цьому не імпортує нічого з `Vacancy`: він реагує на будь-яке FK-обмеження, а не на конкретну таблицю.

*Альтернатива:* перевірка `Vacancy::where('status_id', …)->exists()` у policy чи Action статусу — створює циклічну залежність контейнерів; `nullOnDelete` для статусу — суперечить вимозі «статус обов'язковий».

## Risks / Trade-offs

- [Вкладений `FileUpload` логотипа в `createOptionForm` може поводитися інакше, ніж у модалці `ManageCompanies` (тимчасовий файл у вкладеній дії)] → функціональний тест створення компанії з логотипом із форми вакансії; якщо не працює, прибрати поле логотипа з вкладеної форми через параметр `CompanyForm::configure()`, не змінюючи правил решти полів.
- [Санітайзер прибирає inline-стилі й нестандартні теги з тексту, вставленого з сайтів вакансій] → прийнятно: структура (абзаци, списки, посилання) зберігається; покрити юніт-тестами мутатора.
- [SQLSTATE `23503` специфічний для Postgres] → проєкт і тести працюють на Postgres; у разі іншої БД доповнити перевірку кодом відповідного драйвера.
- [`gte('salary_min')` спрацьовує, коли `salary_min` порожній] → правило вмикати умовно (`->gte(...)` лише при заповненому `salary_min`, через `->rule()` з closure або `->when`), покрити тестом сценарій «лише максимальна».
- [Каскадне видалення вакансій разом із компанією відбувається без попередження] → прийнято за постановкою (`cascadeOnDelete`); попередження в модалці видалення компанії — поза цією зміною.

## Migration Plan

Дві нові таблиці без змін наявних: `php artisan migrate` у контейнері `app`. Відкат — `migrate:rollback` (спочатку `vacancies`, потім `vacancy_sources`). Сідів немає: джерела й вакансії користувач заводить сам.
