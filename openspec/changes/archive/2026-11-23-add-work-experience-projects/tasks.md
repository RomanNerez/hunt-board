# Tasks

## 1. Дані

- [x] 1.1 Створити міграцію `profile_projects` (колонки й індекс за design.md, рішення 2) у `Profile/Data/Migrations` і додати в `MigrationTest` тест на колонки, nullable-поля й каскадне видалення проєктів разом із записом досвіду роботи та з користувачем. Перевірка: `docker compose exec app php artisan migrate` проходить, тест зелений
- [x] 1.2 Додати enum `Enums/ProjectTeamFormat` з мітками «Самостійно» / «У команді» й тест `ProjectTeamFormatTest` на значення й мітки. Перевірка: тест зелений
- [x] 1.3 Додати модель `Models/ProfileProject` (`HasProfileTranslations`, `$translatable` з `name`, `role`, полями опитувальника й `resume_summary`; касти enum, `immutable_date`, `array` для `technologies`, `resume_achievements`, `resume_challenges`; `workExperience()`), репозиторій `ProfileProjectRepository` і фабрику `ProfileProjectFactory` зі станами `solo()`, `current()`, `withResume()` та `ProfileProjectFactoryTest`. Перевірка: тест зелений
- [x] 1.4 Додати `ProfileWorkExperience::projects()` (сортування `started_on desc`, `id desc`) і тест зв'язку: повертає проєкти лише цього запису в потрібному порядку. Перевірка: тест зелений

## 2. Бізнес-логіка проєктів

- [x] 2.1 Винести нормалізацію списку рядків із `ReplaceProfileSkillGroupsTask` у трейт `Tasks/Concerns/NormalizesStringList` і підключити його там без зміни поведінки. Перевірка: наявні тести навичок зелені без змін
- [x] 2.2 Додати Tasks `Create`/`Update`/`DeleteProfileProjectTask`: `Arr::only`, нормалізація перекладів, технологій (обрізання, унікальність, порядок) і пунктів резюме (design.md, рішення 3), перевірка дат щодо періоду роботи з `ValidationException` (рішення 6). Unit-тести: дві мови, fallback порожньої англійської, дублікати технологій, порожні пункти відкинуто, порядок пунктів збережено, `work_experience_id` у даних оновлення ігнорується, дата поза періодом і поточний проєкт у завершеній роботі відхилені. Перевірка: тести зелені
- [x] 2.3 Додати Actions `Create`/`Update`/`DeleteProfileProjectAction` (у `DB::transaction`, `work_experience_id` окремим аргументом Create) та unit-тести в `Tests/Unit/Actions`. Перевірка: тести зелені
- [x] 2.4 Додати `Policies/ProfileProjectPolicy` (design.md, рішення 9) і `ProfileProjectPolicyTest`: власник запису досвіду роботи може `view`/`update`/`delete`, інший користувач не може. Перевірка: тест зелений

## 3. Правила досвіду роботи

- [x] 3.1 Додати `Tasks/AssertWorkExperiencePeriodCoversProjectsTask` (design.md, рішення 7) з unit-тестом на всі сценарії вимоги «Період досвіду роботи охоплює його проєкти»: пізніший початок, раніше завершення, завершення з поточним проєктом, розширення періоду, запис без проєктів. Перевірка: тест зелений
- [x] 3.2 Викликати цю Task в `UpdateProfileWorkExperienceAction` перед оновленням у тій самій транзакції й розширити `UpdateProfileWorkExperienceActionTest`: некоректний період не змінює запис. Перевірка: тест зелений
- [x] 3.3 Додати `modalDescription` про видалення проєктів до `DeleteAction` і `DeleteBulkAction` у `WorkExperiencesTable` та розширити `ManageWorkExperiencesTest`: у формі редагування помилка періоду показана під полем `started_on` / `finished_on` і запис не змінено; видалення запису видаляє його проєкти. Якщо помилка з `->using()` не прив'язується до поля, застосувати обхід із design.md (Risks) і зафіксувати рішення там. Перевірка: тест зелений

## 4. Сторінка «Проєкти»

- [x] 4.1 Винести форматування періоду з `WorkExperiencesTable::period()` у спільний хелпер (design.md, рішення 5) і підключити його в `WorkExperiencesTable`. Перевірка: наявні тести `ManageWorkExperiencesTest` зелені без змін
- [x] 4.2 Створити сторінку `Pages/ManageWorkExperienceProjects` (`ManageRelatedRecords`, `$relationship = 'projects'`, трейт `InteractsWithDataLocale`, перевизначений `canAccess()` з перевіркою власника, заголовок і breadcrumb), зареєструвати її в `WorkExperienceResource::getPages()` і додати дію «Проєкти» в `WorkExperiencesTable`. Прибрати коментарі зі стабів і додати PHPDoc-теги. Тест `ManageWorkExperienceProjectsTest`: дія веде на `/admin/profile/work-experience/{id}/projects`, гість перенаправляється на логін, чужий запис дає 404/403, у субнавігації активний «Досвід роботи». Перевірка: тест зелений, `php artisan route:list --path=admin/profile` показує новий маршрут, у `app/Filament` нічого не з'явилося
- [x] 4.3 Реалізувати `Schemas/ProjectForm` (розділи «Основне», «Повна інформація», «Для резюме», перемикач мови вгорі, `TagsInput` технологій, `Repeater` пунктів з перекладним текстом і перетягуванням, правила довжини й дат щодо власника) і `CreateAction->using()` → `CreateProfileProjectAction` у `slideOver()`. Розширити тест: створення завершеного, поточного й самостійного проєкту; заповнення опитувальника й пунктів резюме; валідація обов'язкових полів, довжин, дат поза періодом і поточного проєкту в завершеній роботі; проєкт прив'язано до запису зі сторінки. Перевірка: тест зелений
- [x] 4.4 Реалізувати `Tables/ProjectsTable` (колонки активної мови з fallback, «Формат роботи» badge, «Період» з «дотепер», «Технології», сортування) з `EditAction`/`DeleteAction`/`DeleteBulkAction` через Actions контейнера. Розширити тест: порядок проєктів, проєкти іншого запису не видно, редагування ролі, зміна порядку викликів, видалення з підтвердженням, порожній стан, редагування чи видалення чужого проєкту відхилено. Перевірка: тест зелений
- [x] 4.5 Тест `ManageWorkExperienceProjectsLocalizationTest`: англійські значення в модальному вікні при активній English, обидві мови «Короткого опису» однією дією й синхронізація перемикача сторінки, порожня українська назва при активній English перемикає на «Українська», пункт лише англійською відхилено з перемиканням мови, пункт без англійської зберігається, технології й період не залежать від мови. Перевірка: тест зелений

## 5. Інтеграційна перевірка

- [x] 5.1 Запустити всі тести контейнера `Profile` на тестовій БД (`DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Profile`). Перевірка: усі зелені
- [x] 5.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: PHPStan не додає нових помилок понад наявні в проєкті, fixer не лишає змін

## 6. Спрощення проєкту

Задачі 1–5 реалізували початковий, ширший набір полів. Після рев'ю користувач скоротив проєкт до назви, дат, стеку й опису та відв'язав дати від періоду роботи (design.md, рішення 2, 3, 5).

- [x] 6.1 Переписати міграцію `profile_projects` за design.md (рішення 2): лишити `name`, `started_on`, `finished_on`, `technologies`, додати `description`; прибрати `role`, `team_format`, поля опитувальника й резюме. У dev-БД виконати `migrate:rollback --step=1` і `migrate`. Оновити `MigrationTest`. Перевірка: міграція проходить, тест зелений
- [x] 6.2 Видалити enum `ProjectTeamFormat` і його тест; спростити модель `ProfileProject` (`$translatable` = `name`, `description`) і фабрику (стани `current()` і `withDescription()` замість `solo()` / `withResume()`), оновити `ProfileProjectFactoryTest`. Дати фабрики не прив'язувати до запису досвіду. Перевірка: тест зелений
- [x] 6.3 Спростити `PreparesProfileProjectData` і Tasks: дозволені атрибути з рішення 6, санітизація й нормалізація опису (рішення 3), перевірка лише порядку дат (рішення 5). Оновити unit-тести Tasks і Actions: дві мови назви, fallback англійської, санітизація HTML, порожній `<p></p>` відкинуто, дублікати стеку, дата завершення раніше за початок відхилена, проєкт поза періодом роботи й поточний проєкт у завершеній роботі збережено. Перевірка: тести зелені
- [x] 6.4 Видалити `AssertWorkExperiencePeriodCoversProjectsTask` і її тест, повернути `UpdateProfileWorkExperienceAction` до одного виклику Task, прибрати `MountedActionValidation` з `EditAction` у `WorkExperiencesTable`; замінити тести періоду в `UpdateProfileWorkExperienceActionTest` і `ManageWorkExperiencesTest` на сценарій «Звуження періоду роботи» (запис збережено, проєкт не змінено). Перевірка: тести зелені
- [x] 6.5 Оновити `ProjectForm` (назва, дати, «Стек», «Опис» як `RichEditor` за рішенням 3) і `ProjectsTable` (колонки «Проєкт», «Період», «Стек»). Оновити `ManageWorkExperienceProjectsTest` і `ManageWorkExperienceProjectsLocalizationTest` під нові вимоги `profile-projects` і `profile-translations`. Перевірка: тести зелені
- [x] 6.6 Запустити всі тести контейнера `Profile` на тестовій БД, `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: тести зелені, PHPStan не додає нових помилок понад наявні в проєкті, fixer не лишає змін
- [x] 6.7 Вручну в браузері: відкрити «Досвід роботи» → «Проєкти», створити проєкт «Аукціон з продажу автомобілів» у VARTEQ Inc. з описом зі списком, перевірити `slideOver`, перемикач мов у формі (назва й опис), «дотепер», попередження під час видалення запису. Перевірка: поведінка збігається зі сценаріями специфікацій

## Workflow follow-up

- Після рев'ю й зелених тестів архівувати зміну: `openspec archive add-work-experience-projects`.
