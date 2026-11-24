# Design

## Context

- Мотивація — див. proposal.md (Why). Вимоги — дельти `vacancy-notes` і `vacancies`.
- Сторінка вакансії — `VacancyResource` з `SubNavigationPosition::Top` і єдиним пунктом `ViewVacancy` («Огляд») у `getRecordSubNavigation()`; сторінки: `index` → `ListVacancies`, `view` → `ViewVacancy`. Редагування вакансії — `EditAction` у `slideOver()` на «Огляді».
- Аналог сторінки дочірніх записів — `Profile/UI/Filament/Resources/WorkExperiences/Pages/ManageWorkExperienceProjects` (`ManageRelatedRecords`, `CreateAction` у шапці з `->using()` через Action, `slideOver()`).
- Опис вакансії вже є HTML із `RichEditor`; очищення — приватний `Vacancy::sanitizeDescription()` у мутаторі `description` (`Str::sanitizeHtml` + перевірка видимого тексту, порожнє → `null`).
- Поле `vacancies.notes` (`text nullable`) використовують: міграція створення таблиці, `Vacancy` (`@property`, `$fillable`), `VacancyFactory::complete()`, `VacancyForm` (`Textarea notes`), `VacancyInfolist` (секція «Нотатки» через `multilineEntry()` — єдиний виклик), тести `MigrationTest`, `CreateVacancyActionTest`, `ViewVacancyTest`.
- Рішення користувача: старе поле видалити без перенесення даних; дата події — окреме редаговане поле з типовим «зараз».

## Goals / Non-Goals

**Goals:**
- Нотатки як дочірня сутність вакансії в контейнері `Vacancy` за Porto: UI → Action → Task → Repository/Model.
- Один спосіб очищення rich-text HTML для опису вакансії й тексту нотатки.
- Картки з пагінацією засобами таблиці Filament, без власних Blade-шаблонів.

**Non-Goals:**
- Unit-тести нових Actions/Tasks: поведінку перевіряють функціональні тести сторінки; наявні unit-тести контейнера лише оновлюються під видалення поля.
- Повторне використання `MountedActionValidation` з `Profile`: валідацію тексту робить форма (рішення 5).

## Decisions

### 1. Модель `VacancyNote` у контейнері `Vacancy`
Нові файли в `app/Containers/AppSection/Vacancy`:
- `Models/VacancyNote.php`, `Data/Migrations/…_create_vacancy_notes_table.php`, `Data/Factories/VacancyNoteFactory.php`, `Data/Repositories/VacancyNoteRepository.php`;
- `Actions/{Create,Update,Delete}VacancyNoteAction.php`, `Tasks/{Create,Update,Delete}VacancyNoteTask.php`;
- `Policies/VacancyNotePolicy.php` (усі дії дозволені, як `VacancyPolicy`);
- `UI/Filament/Resources/Vacancies/Pages/ManageVacancyNotes.php`, `.../Schemas/VacancyNoteForm.php`, `.../Tables/VacancyNotesTable.php`.

*Альтернатива:* окремий контейнер `VacancyNote` — відкинуто: нотатка не існує без вакансії, не має власного розділу, а її сторінка мусить реєструватися у `VacancyResource::getPages()` і субнавігації. Окремий контейнер дав би лише двонаправлену залежність.

### 2. Дані
- `vacancy_notes`: `id`, `vacancy_id` FK `constrained()->cascadeOnDelete()`, `title` string(255), `body` longText, `occurred_at` dateTime, timestamps; індекс `['vacancy_id', 'occurred_at']` під вибірку сторінки. Усі поля NOT NULL.
- `cascadeOnDelete` реалізує «Видалення вакансії видаляє її нотатки» без змін у `DeleteVacancyAction`.
- Окрема міграція `…_drop_notes_from_vacancies_table.php` (мітка часу пізніша за всі наявні): `up` — `dropColumn('notes')`, `down` — `text('notes')->nullable()` (дані не повертаються). Міграцію створення `vacancies` не редагуємо: на dev-БД вона вже виконана.
- `VacancyNote`: `$fillable = ['vacancy_id', 'title', 'body', 'occurred_at']` (strict models вимагають fillable `vacancy_id`; перенесення до іншої вакансії блокує `Arr::only` в `UpdateVacancyNoteTask`), `casts` `occurred_at` → `datetime` (`CarbonImmutable` глобально), `vacancy(): BelongsTo`.
- `Vacancy::notes(): HasMany` — `hasMany(VacancyNote::class)`. Атрибут `notes` прибирається з `@property`, `$fillable` і `VacancyFactory::complete()`; в `@property` додається `Collection<int, VacancyNote> $notes`. До `DISPLAY_RELATIONS` зв'язок не додається: «Огляд» нотаток не показує.

*Альтернатива:* дата події = `created_at` — відкинуто за рішенням користувача (подію часто записують пізніше).

### 3. Очищення rich text — спільний трейт
`Vacancy::sanitizeDescription()` переноситься в трейт `Vacancy/Models/Concerns/SanitizesRichText.php` з `public static function sanitizeRichText(string|null $value): string|null` (та сама логіка: `Str::sanitizeHtml`, видимий текст без пробілів і `&nbsp;`, порожнє → `null`). `Vacancy` (мутатор `description`) і `VacancyNote` (мутатор `body`) використовують трейт. Поведінку опису вакансії покривають наявні тести.

*Альтернатива:* копія методу в `VacancyNote` — дві копії правил безпеки, які розійдуться. Хелпер у `Ship` — передчасно для двох моделей одного контейнера (у `Profile` своя логіка для перекладних полів).

### 4. Сторінка `ManageVacancyNotes`
- `extends ManageRelatedRecords` (`@extends ManageRelatedRecords<Vacancy>`), `$resource = VacancyResource::class`, `$relationship = 'notes'`, `$relationshipTitle`/`$navigationLabel` «Нотатки», `$navigationIcon` `Heroicon::OutlinedChatBubbleBottomCenterText`.
- `VacancyResource::getPages()` отримує `'notes' => ManageVacancyNotes::route('/{record}/notes')`, `getRecordSubNavigation()` — `[ViewVacancy::class, ManageVacancyNotes::class]`. Наявний `resolveRecordRouteBinding` (лише цифрові ключі) діє й тут → 404 для неіснуючої вакансії.
- `getTitle()` → `"Нотатки: {назва вакансії}"`; хлібні крихти — ресурс → назва вакансії → «Нотатки», як у `ManageWorkExperienceProjects`. Метод `getOwnerRecord(): Vacancy` для звуження типу.
- `getHeaderActions()`: `CreateAction::make()->label('Додати нотатку')->modalHeading('Додати нотатку')->slideOver()->using(fn (array $data) => app(CreateVacancyNoteAction::class)->run($this->getOwnerRecord()->id, $attributes))`.
- Модалка чи сторінка: **бокова панель (`slideOver`)** для створення й редагування — так само, як редагування вакансії; широка панель зручніша для довгого форматованого тексту. Окремих сторінок create/edit нотатки немає.

### 5. Форма `VacancyNoteForm`
- `TextInput title` — «Заголовок», `required`, `maxLength(255)`, placeholder «Напр. Результат співбесіди з HR».
- `DateTimePicker occurred_at` — «Коли сталося», `required`, `seconds(false)`, `default(fn () => now())`, `native(false)`, формат відображення `d.m.Y H:i`.
- `RichEditor body` — «Текст», `required`, панель інструментів як в описі вакансії (`bold`, `italic`, `link`, `h2`, `h3`, списки, `blockquote`, `undo`/`redo`), `fileAttachments(false)`, `columnSpanFull()`. Окремого правила немає: стан редактора під час валідації — документ tiptap, з якого небезпечні теги (`<script>`) уже викинуто, тож текст лише з такої розмітки стає порожнім і його відхиляє `required()`. Мутатор `body` додатково очищує HTML через `sanitizeRichText()`, а NOT NULL у БД лишається запобіжником для інших UI.

*Альтернатива:* власне правило-замикання з `sanitizeRichText()` — відкинуто під час реалізації: правило отримує не HTML-рядок, а стан tiptap, тож відхиляло б і валідний текст. Валідація в Action з `ValidationException` (як `MountedActionValidation` у `Profile`) — зайва для одного правила, яке вже покриває `required()`.

### 6. Таблиця-картки `VacancyNotesTable`
- `columns([Stack::make([TextColumn title (weight Bold, size Large), TextColumn occurred_at (dateTime('d.m.Y H:i'), color gray, іконка календаря), TextColumn body (html(), prose(), wrap())])])` — один `Stack` на запис дає картку.
- `contentGrid(['default' => 1])` — по одній картці в рядок, бо текст довгий і має читатися повністю. Без `limit()` для `body`.
- Вигляд (уточнено користувачем після першої реалізації): заголовок і дата — в одному рядку через `Split::make([...])->from('sm')`, заголовок зліва, дата справа (`alignEnd()`, `grow(false)`); на вузькому екрані дата переноситься під заголовок. Картки не обгортаються спільним контейнером таблиці, а пагінація й порожній стан — окремі блоки-картки з відступом. Це робить CSS `Vacancy/UI/WEB/Assets/css/vacancy-notes.css`, обмежений класом `hb-vacancy-notes` на таблиці (`extraAttributes`) і зареєстрований у `VacancyServiceProvider` через `FilamentAsset`, як `status-badge.css` у `Status`; опублікована копія лежить у `public/css/app` (`php artisan filament:assets`).
- `defaultSort` через `modifyQueryUsing(fn (Builder $q) => $q->orderByDesc('occurred_at')->orderByDesc('id'))`; сортування за вибором користувача немає.
- Пагінація — стандартна Filament (`paginated()` за замовчуванням, опції кількості записів панелі). Пошуку й фільтрів немає.
- `recordActions([EditAction::make()->slideOver()->using(fn (VacancyNote $record, array $data) => app(UpdateVacancyNoteAction::class)->run($record->id, $attributes)), DeleteAction::make()->using(fn (VacancyNote $record) => app(DeleteVacancyNoteAction::class)->run($record->id))])`; `recordUrl(null)`, без масових дій.
- Порожній стан: `emptyStateHeading('Нотаток ще немає')`, `emptyStateDescription(...)`, `emptyStateActions` з дією додати нотатку (та сама `CreateAction`, що й у шапці — спільний приватний метод сторінки).

*Альтернатива:* `RepeatableEntry`/власний Blade-список з `paginate()` — відкинуто: користувач просив стандартну пагінацію таблиці, а таблиця дає її разом з діями записів і порожнім станом.

### 7. Actions і Tasks
- `CreateVacancyNoteAction::run(int $vacancyId, array $data): VacancyNote` → `CreateVacancyNoteTask::run(int $vacancyId, array $data)` (`repository->create([...$data, 'vacancy_id' => $vacancyId])`; ключ `vacancy_id` з `$data` ігнорується, вакансію задає сторінка).
- `UpdateVacancyNoteAction::run(int $id, array $data): VacancyNote` → `UpdateVacancyNoteTask` (`repository->update(Arr::only($data, ['title', 'body', 'occurred_at']), $id)`): нотатку не можна перенести до іншої вакансії.
- `DeleteVacancyNoteAction::run(int $id): bool` → `DeleteVacancyNoteTask`.
- Транзакції не потрібні: кожна дія — один запис. Структура Tasks/Repository — як у `CreateVacancyTask`.

### 8. Видалення старого поля «Нотатки»
`VacancyForm` втрачає `Textarea notes`, `VacancyInfolist` — секцію «Нотатки» й невикористаний після цього `multilineEntry()`. `ViewVacancy` змін не потребує (`notes` у `mutateRecordDataUsing` не фігурує).

### 9. Тести — функціональні, PHPUnit
- `Vacancy/Tests/Functional/Filament/Resources/Vacancies/Pages/ManageVacancyNotesTest.php`: сценарії дельти `vacancy-notes` — субнавігація й заголовок, гість → логін, 404 для неіснуючої вакансії, лише нотатки цієї вакансії, картка (заголовок, «20.11.2026 15:30», текст), порядок за `occurred_at` і `id`, порожній стан, пагінація (15 нотаток, 10 на сторінці), типова дата події (`Carbon::setTestNow`), подія в минулому, валідація заголовка (порожній, 256 символів), тексту (порожній, лише `<script>`), дати; очищення `<script>` і збереження форматування; редагування (поточні значення, оновлення, невалідні зміни); видалення.
- `ViewVacancyTest`: субнавігація містить «Нотатки»; «Огляд» без секції «Нотатки»; видалення вакансії видаляє її нотатки й лишає нотатки іншої. Прибрати `notes` з даних наявних тестів.
- `MigrationTest`: колонки `vacancy_notes`, відсутність `notes` у `vacancies`. `CreateVacancyActionTest`: прибрати `notes`.

## Risks / Trade-offs

- [Втрата вмісту `vacancies.notes` після `migrate`] → свідоме рішення користувача; у proposal позначено **BREAKING**; `down` повертає колонку без даних. Перед `migrate` на dev-БД за потреби вивантажити колонку вручну.
- [Довгі нотатки роблять сторінку високою] → одна картка в рядку й пагінація; обрізання тексту не робимо, бо нотатка має читатися повністю.
- [Порожній текст після очищення визначає tiptap, а не наш код] → функціональний тест «лише `<script>`» фіксує поведінку; мутатор і NOT NULL у БД не дадуть зберегти порожній текст навіть при зміні Filament.

## Migration Plan

1. `docker compose exec app php artisan migrate` — створює `vacancy_notes` і видаляє `vacancies.notes`.
2. Відкат: `migrate:rollback --step=2` повертає порожню колонку `notes` і видаляє `vacancy_notes` разом із нотатками.
