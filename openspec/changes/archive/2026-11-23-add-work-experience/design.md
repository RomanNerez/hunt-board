# Design

## Context

Мотивацію описано в proposal.md, розділ «Why».

- Профіль кандидата живе в контейнері `app/Containers/AppSection/Profile`. Наявні розділи («Загальне», «Навички», «Посилання», «Освіта», «Мови») зроблено як сторінки-форми кластера `ProfileCluster`, що успадковують `ProfileFormPage`. Списки редагуються там через `Repeater` і зберігаються однією дією `Sync…Action` → `Replace…Task`, яка видаляє всі записи й створює їх знову.
- Перекладні поля зберігаються в `jsonb` через `spatie/laravel-translatable` (трейт `HasProfileTranslations`) і нормалізуються в Tasks трейтом `NormalizesTranslations`. Перемикач мови даних (`$dataLocale`, header actions, `translatable()`, `onValidationError()`) зараз реалізовано лише в `ProfileFormPage`.
- Ресурси з модальними вікнами вже є в контейнерах `Company` і `Status`: `ManageRecords`-сторінка, `CreateAction`/`EditAction`/`DeleteAction` з `->using(...)` через Actions контейнера.
- Користувач вибрав для досвіду роботи окремий ресурс, а не сторінку з `Repeater`, і назву компанії вільним текстом.

## Goals / Non-Goals

**Goals:**
- CRUD досвіду роботи через Actions контейнера `Profile` у простому ресурсі з модальними вікнами.
- Той самий перемикач мови даних, що й на інших сторінках профілю, без копіювання його коду.
- Ізоляція записів між користувачами на рівні запиту й Policy.

**Non-Goals:**
- Перетворювати наявні сторінки профілю на ресурси.
- Показувати досвід роботи на сторінці «Загальне» чи в інших розділах.
- Окремий інфолист чи сторінку перегляду запису.

## Decisions

### 1. Сутність у контейнері `Profile`, а не в новому контейнері
Модель `ProfileWorkExperience`, міграція, enum, фабрика, репозиторій, Tasks, Actions, Policy й ресурс лежать у `app/Containers/AppSection/Profile`.
- **Чому:** досвід роботи є частиною профілю кандидата. Він використовує `ProfileLocale`, `HasProfileTranslations`, `NormalizesTranslations` і `ProfileCluster`, а видаляється каскадно разом з іншими даними профілю.
- **Альтернатива:** окремий контейнер `WorkExperience`. Його відкинуто: він залежав би від внутрішніх трейтів `Profile` і кластера, яким користується лише `Profile` (за AGENTS.md такий кластер має бути в одному контейнері).

### 2. Схема даних
Таблиця `profile_work_experiences`: `id`, `user_id` (`foreignId`, `constrained`, `cascadeOnDelete`), `type` (`string(20)`), `company_name` (`jsonb`), `role` (`jsonb`), `started_on` (`date`), `finished_on` (`date`, nullable), `timestamps`, складений індекс `(user_id, started_on)`. Колонки `sort` немає.
- **Чому:** записи сортуються за датою початку (спека «Сторінка „Досвід роботи“»), тому ручний порядок не потрібен. `jsonb` для перекладних полів збігається з рештою профілю. Каскадне видалення через зовнішній ключ виконує вимогу «Видалення користувача» без змін у контейнері `User`.
- **Альтернатива:** `company_id` на таблицю `companies`. Його відкинуто, бо користувач вибрав вільний текст, а фриланс-проєкти не є роботодавцями з довідника.
- **Альтернатива:** `company_name` без перекладу. Відкинуто: назви фриланс-проєктів («Інтернет-магазин для Kavapro») потрібні англійською для англомовного резюме. Для звичайної компанії англійське значення можна лишити порожнім, і тоді показується українське (fallback `ProfileServiceProvider`).

### 3. Enum `WorkExperienceType`
`app/Containers/AppSection/Profile/Enums/WorkExperienceType.php`: `COMPANY = 'company'` («Компанія»), `FREELANCE = 'freelance'` («Фриланс-проєкт»), реалізує `HasLabel`, як і `EducationType`.
- **Чому:** фіксований набір значень, мітки без окремої таблиці.
- **Альтернатива:** довідник у БД, як `CompanyType`. Відкинуто: два значення, які не редагуються.

### 4. Простий ресурс у кластері «Профіль»
`UI/Filament/Resources/WorkExperiences/`:
- `WorkExperienceResource.php`: `$model = ProfileWorkExperience::class`, `$cluster = ProfileCluster::class`, `$slug = 'work-experience'`, `$navigationSort = 12` (між «Загальне» = 10 і «Навички» = 15), мітки «Досвід роботи» / «запис досвіду», `getEloquentQuery()` обмежує `user_id` поточним користувачем, одна сторінка `index`;
- `Pages/ManageWorkExperiences.php` (`ManageRecords`): `CreateAction` з `->using()` → `CreateProfileWorkExperienceAction`;
- `Schemas/WorkExperienceForm.php`: `Select` «Тип», перекладні `TextInput` «Компанія / проєкт» і «Роль» (`maxLength(255)`, обов'язкові для `uk`), `DatePicker` «Дата початку» (`required`) і «Дата завершення» (`afterOrEqual('started_on')`), формат `d.m.Y`, `native(false)`, як в «Освіті»;
- `Tables/WorkExperiencesTable.php`: колонки «Компанія / проєкт», «Тип» (badge), «Роль», «Період» (`m.Y — m.Y` або `m.Y — дотепер`), `defaultSort('started_on', 'desc')`. `EditAction`, `DeleteAction` і `DeleteBulkAction` викликають Actions контейнера через `->using()`.

Ресурс генерується `make:filament-resource ... --simple` у неймспейс контейнера, без `--cluster`, а `$cluster` додається вручну (AGENTS.md).
- **Чому:** користувач вибрав окремий ресурс. Простий ресурс із модальними вікнами відповідає простій формі з п'яти полів і повторює патерн `Company`/`Status`.
- **Альтернатива:** окремі сторінки Create/Edit. Відкинуто: форма мала, а окремі сторінки вибивали б користувача із субнавігації профілю.

### 5. Перемикач мови даних у спільному трейті
Логіку перемикача з `ProfileFormPage` (`$dataLocale`, кнопки мов у шапці, `translatable()`, `activeDataLocale()`, `translationWithFallback()`, `onValidationError()`) виносимо в трейт `UI/Filament/Concerns/InteractsWithDataLocale.php`. Його використовують `ProfileFormPage` і `ManageWorkExperiences`. Поля модального вікна показуються через `visibleJs("$wire.dataLocale === '…'")`: модальне вікно дії рендериться в тому самому Livewire-компоненті сторінки, тому `$wire.dataLocale` там доступний. Колонки таблиці показують значення активної мови з fallback на українську. Відкрите модальне вікно перекриває шапку сторінки, тому вгорі форми модального вікна стоїть такий самий перемикач «Українська / English». Його кнопки будує той самий трейт (`dataLocaleActions()`), і вони змінюють той самий `$dataLocale` сторінки, тож перемикач у шапці й перемикання на мову з помилкою працюють узгоджено.
- **Чому:** вимога «Перемикач мови даних на сторінках профілю» поширюється на нову сторінку, а дублювати код заборонено конвенціями проєкту.
- **Альтернатива:** вкладки «Українська / English» усередині модального вікна. Відкинуто: це дало б інший UX, ніж на решті профілю, і окремий механізм показу помилок.
- **Альтернатива:** успадкувати `ManageWorkExperiences` від `ProfileFormPage`. Неможливо, бо сторінка має успадковувати `ManageRecords`.

### 6. Actions і Tasks
- `Actions/CreateProfileWorkExperienceAction::run(int $userId, array $data): ProfileWorkExperience`;
- `Actions/UpdateProfileWorkExperienceAction::run(int $id, array $data): ProfileWorkExperience`;
- `Actions/DeleteProfileWorkExperienceAction::run(int $id): bool`;
- `Tasks/CreateProfileWorkExperienceTask`, `UpdateProfileWorkExperienceTask`, `DeleteProfileWorkExperienceTask` через `Data/Repositories/ProfileWorkExperienceRepository`. Create/Update Tasks беруть лише дозволені атрибути (`Arr::only`) і нормалізують переклади трейтом `NormalizesTranslations`.

Actions обгортають роботу в `DB::transaction`. `user_id` передається окремим аргументом із `Auth::id()` сторінки і не береться з даних форми.
- **Чому:** записи йдуть через Actions (AGENTS.md). Власника не можна підмінити через стан форми.
- **Альтернатива:** перевикористати схему `Sync…Action`/`Replace…Task`. Відкинуто: ресурс змінює записи по одному, а повна заміна списку видаляла б і перестворювала записи, змінюючи їхні `id`.

### 7. Авторизація
`Policies/ProfileWorkExperiencePolicy`: `viewAny` і `create` повертають `true`. `view`, `update` і `delete` повертають `$user->id === $experience->user_id`. `deleteAny` повертає `true`, бо масове видалення бачить лише записи з обмеженого `getEloquentQuery()`.
- **Чому:** Policy є справжнім контролем доступу, бо `canAccessPanel()` пускає всіх. Обмеження запиту ховає чужі записи, а Policy відхиляє дії з чужим `id`.

### 8. Зв'язок у `UserProfile`
`UserProfile::workExperiences(): HasMany` по `user_id` з сортуванням `started_on desc`, як інші списки профілю.

## Risks / Trade-offs

- [Filament може не викликати `onValidationError()` сторінки для помилок форми модального вікна, і тоді перемикач не переключиться на мову з помилкою] → перевірити функціональним тестом. Якщо хук не спрацьовує, перехоплювати `ValidationException` в `->using()` неможливо (валідація відбувається раніше), тому слід перевизначити відповідний метод `InteractsWithActions` на сторінці або обробляти помилку в `->beforeFormValidated()`/`->afterFormValidated()`. Вибране рішення зафіксувати в цьому документі. **Результат перевірки:** хук спрацьовує, бо форма модального вікна валідується через `validate()` того самого Livewire-компонента сторінки (`InteractsWithSchemas::validate()` викликає `onValidationError()`). Обхід не знадобився, це покриває `ManageWorkExperiencesLocalizationTest`.
- [`visibleJs` з `$wire.dataLocale` у модальному вікні] → покрити тестом, що поля обох мов є в формі й зберігаються, а видимість перевірити вручну в браузері.
- [Перенесення логіки з `ProfileFormPage` у трейт може зламати наявні сторінки] → наявні тести сторінок профілю мають пройти без змін.
- [Без колонки `sort` два записи з однаковою датою початку мають невизначений порядок] → додатково сортувати за `id desc`.
