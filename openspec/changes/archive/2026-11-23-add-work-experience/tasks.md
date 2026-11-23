# Tasks

## 1. Дані

- [x] 1.1 Створити міграцію `profile_work_experiences` (колонки й індекс за design.md, рішення 2) у `Profile/Data/Migrations` і додати в `MigrationTest` тест на колонки, nullable `finished_on` і каскадне видалення. Перевірка: `docker compose exec app php artisan migrate` проходить, тест зелений
- [x] 1.2 Додати enum `Enums/WorkExperienceType` з мітками «Компанія» / «Фриланс-проєкт» і тест `WorkExperienceTypeTest` на значення й мітки. Перевірка: тест зелений
- [x] 1.3 Додати модель `Models/ProfileWorkExperience` (`HasProfileTranslations`, `$translatable = ['company_name', 'role']`, касти enum і `immutable_date`, `user()`), репозиторій `ProfileWorkExperienceRepository` і фабрику `ProfileWorkExperienceFactory` зі станами `freelance()` і `current()`, плюс `ProfileWorkExperienceFactoryTest`. Перевірка: тест зелений
- [x] 1.4 Додати `UserProfile::workExperiences()` (сортування `started_on desc`, `id desc`) і розширити `UserProfileTest`: зв'язок повертає записи власника в потрібному порядку, а видалення користувача видаляє його досвід роботи. Перевірка: тест зелений

## 2. Бізнес-логіка

- [x] 2.1 Додати Tasks `Create`/`Update`/`DeleteProfileWorkExperienceTask` з `Arr::only` і `NormalizesTranslations` та unit-тести на них: зберігання двох мов, порожня англійська дає fallback, сторонні атрибути (`user_id` у даних оновлення) ігноруються. Перевірка: тести зелені
- [x] 2.2 Додати Actions `Create`/`Update`/`DeleteProfileWorkExperienceAction` (у `DB::transaction`, `user_id` окремим аргументом Create) та unit-тести в `Tests/Unit/Actions`. Перевірка: тести зелені
- [x] 2.3 Додати `Policies/ProfileWorkExperiencePolicy` (design.md, рішення 7) і `ProfileWorkExperiencePolicyTest`: власник може `view`/`update`/`delete`, інший користувач не може. Перевірка: тест зелений

## 3. Спільний перемикач мови даних

- [x] 3.1 Винести логіку перемикача з `ProfileFormPage` у трейт `UI/Filament/Concerns/InteractsWithDataLocale` і підключити його в `ProfileFormPage` без зміни поведінки. Перевірка: усі наявні тести `Profile/Tests/Functional/Filament/Pages` зелені без змін

## 4. Filament-ресурс

- [x] 4.1 Створити `UI/Filament/Resources` і згенерувати простий ресурс: `make:filament-resource ProfileWorkExperience --simple` з неймспейсами контейнера, без `--cluster`. Перейменувати файли в `WorkExperiences/` (`WorkExperienceResource`, `ManageWorkExperiences`, `WorkExperienceForm`, `WorkExperiencesTable`), прибрати коментарі зі стабів і додати PHPDoc-теги. Перевірка: нічого не з'явилося в `app/Filament`, а `php artisan route:list --path=admin/profile` показує `admin/profile/work-experience`
- [x] 4.2 Налаштувати `WorkExperienceResource`: `$cluster`, `$slug`, `$navigationSort = 12`, українські мітки, `getEloquentQuery()` лише з записами поточного користувача. Оновити `ProfileSubNavigationTest` і `ProfileClusterTest` під порядок «Загальне», «Досвід роботи», «Навички», «Посилання», «Освіта», «Мови» та адресу й редирект гостя з `/admin/profile/work-experience`. Перевірка: тести зелені
- [x] 4.3 Реалізувати `WorkExperienceForm` (поля, валідація й перекладні поля через `translatable()` за design.md, рішення 4–5) і `ManageWorkExperiences` з трейтом `InteractsWithDataLocale` та `CreateAction->using()` → `CreateProfileWorkExperienceAction`. Додати функціональний тест `ManageWorkExperiencesTest`: створення компанії, поточної роботи й фриланс-проєкту; валідація обов'язкових полів, довжини й дати завершення; запис отримує `user_id` автора. Перевірка: тест зелений
- [x] 4.4 Реалізувати `WorkExperiencesTable`: колонки активної мови з fallback, «Період» з «дотепер», сортування, `EditAction`/`DeleteAction`/`DeleteBulkAction` через Actions контейнера. Розширити тест: порядок записів, редагування ролі, видалення, порожній стан, чужі записи не видно, а редагування чи видалення чужого запису відхилено. Перевірка: тест зелений
- [x] 4.5 Перевірити переклади в модальному вікні: роль двома мовами, перемикання таблиці на English, порожня англійська роль зберігається, а порожня українська при активній English перемикає на «Українська» з помилкою. Якщо `onValidationError()` не спрацьовує для модального вікна, застосувати обхід із design.md (Risks) і зафіксувати рішення там. Перевірка: тест `ManageWorkExperiencesLocalizationTest` зелений
- [x] 4.6 Додати перемикач мови у форму модального вікна: трейт `InteractsWithDataLocale` будує кнопки мов (`dataLocaleActions()`) для шапки й для форми, а `WorkExperienceForm` показує їх угорі. Розширити `ManageWorkExperiencesLocalizationTest`: перемикання в модальному вікні змінює `dataLocale` сторінки, вікно лишається відкритим, обидві мови зберігаються однією дією. Перевірка: тести `Profile` зелені

## 5. Інтеграційна перевірка

- [x] 5.1 Запустити всі тести контейнера `Profile` на тестовій БД (`DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Profile`). Перевірка: усі зелені
- [x] 5.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: PHPStan не додає нових типів помилок понад наявні в проєкті (наявні помилки прийнято як є), fixer не лишає змін
- [x] 5.3 Вручну в браузері на `/admin/profile/work-experience` перевірити субнавігацію, модальне вікно, перемикач мов у ньому й показ «дотепер». Перевірка: поведінка збігається зі сценаріями специфікацій

## Workflow follow-up

- Після рев'ю й зелених тестів архівувати зміну: `openspec archive add-work-experience`.
