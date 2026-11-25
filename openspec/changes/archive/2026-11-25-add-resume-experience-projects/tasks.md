# Tasks

Тести запускати з `DB_DATABASE=hunt-board_testing`.

## 1. Дані

- [ ] 1.1 Прибрати колонку `description` з `2026_11_24_150000_create_resume_experiences_table.php` і створити `2026_11_24_150500_create_resume_experience_projects_table.php` (`resume_experience_id` FK cascade, `name`, `role` nullable, `description` nullable, `sort_order`, `is_visible`, індекс `(resume_experience_id, sort_order)`). Оновити `Resume/Tests/Unit/Data/MigrationTest`. Перевірка: тест проходить, у dev виконано `migrate:fresh`.
- [x] 1.2 Створити модель `ResumeExperienceProject` (`Stackable`, `HasStacks`, casts, `belongsTo` на досвід), фабрику `ResumeExperienceProjectFactory` і репозиторій за зразком сусідніх. З `ResumeExperience` прибрати `description`, `HasStacks` і `Stackable` та додати `projects(): HasMany` з сортуванням за `sort_order`. Оновити `ResumeExperienceFactory` (без `description`). Перевірка: тест моделі на порядок `projects` і каскадне видалення проєктів разом із досвідом.

## 2. Збереження

- [x] 2.1 Створити `CreateResumeExperienceProjectsTask` (створює проєкти з `sort_order` за позицією, порожні `role`/`description` стають `null`, `is_visible` за замовчуванням `true`). Перевірка: юніт-тест `CreateResumeExperienceProjectsTaskTest`.
- [x] 2.2 Змінити `ReplaceResumeExperiencesTask`: прибрати `description` і відʼєднання технологій досвіду, а перед видаленням відʼєднувати технології проєктів резюме. Перевірка: оновлений `ReplaceResumeExperiencesTaskTest`, у `stackables` немає рядків видалених проєктів.
- [x] 2.3 Змінити `DeleteResumeTask`, щоб він відʼєднував технології проєктів замість технологій досвіду. Перевірка: тест, що після видалення резюме в `stackables` немає рядків його проєктів.
- [x] 2.4 У `UpdateResumeAction` і `CreateResumeAction` для кожного створеного досвіду викликати `CreateResumeExperienceProjectsTask`, а для кожного проєкту `SyncStacksTask`. Синхронізацію технологій досвіду прибрати. Перевірка: `ResumeActionsTest` покриває заміну проєктів, порядок стеку, атомарність (помилка в проєктах відкочує зміни досвіду) і незмінність списку без ключа `experiences`.

## 3. Форма «Досвід»

- [x] 3.1 Створити `Schemas/ResumeExperienceProjectsRepeater` (поля `name` required/255, `role` 255, `is_visible`, `StackSelect`, `RichEditor` з обмеженим тулбаром без вкладень; згортання, наявні згорнуті, перетягування, підпис за назвою, «Додати проєкт») зі `stateFrom()`. У `ResumeExperiencesRepeater` замінити `stacks` і `description` на `projects`, а `stateFrom()` віддавати з проєктами.
- [x] 3.2 У `EditResumeExperiences::fillForm` завантажувати `experiences.projects.stacks`. Перевірка: `EditResumeExperiencesTest` покриває заповнення наявних проєктів у порядку, збереження нових проєктів зі стеком і видимістю, помилку валідації порожньої назви й задовгої ролі (без змін у БД) і відсутність полів «Опис ролі» й «Технології» в досвіді.

## 4. PDF

- [x] 4.1 Створити `Values/Pdf/ResumeDocumentProject`. У `ResumeDocumentExperience` замінити `descriptionHtml` і `stack` на `projects`. У `BuildResumeDocumentTask` будувати видимі проєкти в порядку. У `FindUserResumeTask` завантажувати `experiences.projects.stacks`. Перевірка: `BuildResumeDocumentTaskTest` (невидимий проєкт відкинуто, порядок, HTML опису, назви технологій) і `FindUserResumeTaskTest`.
- [x] 4.2 Додати ключ `role` у `Languages/{en,uk}/pdf.php`. Переписати `pdf/sections/experience.blade.php` на заголовок `position | company` з `location · type | period` справа і блоки проєктів, а в шаблон `classic` додати стилі проєктів. Перевірка: `ClassicResumePdfTemplateTest` за сценаріями «Заголовок картки досвіду в PDF», «Проєкти в картці досвіду PDF» і «Підписи проєкту» обома мовами.

## 5. Перевірка

- [x] 5.1 Прогнати тести контейнера `Resume` (`DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Resume`), `vendor/bin/phpstan analyse` і `composer fixer`. Усе має пройти без помилок.
  - Результат: phpunit для Resume (182) і Stack (46) зелені, `composer fixer` без змін. У phpstan немає нових помилок у продакшн-коді. Лишаються лише помилки, що були в проєкті й раніше (тести та «Dynamic call to static method Builder::…»), як і в `add-resume-pdf-preview`.
- [ ] 5.2 Вручну у вкладці «Досвід» додати досвід із двома проєктами, зберегти й перевірити вкладку «Перегляд»: PDF відповідає прикладу з proposal.

## Workflow follow-up

- Після перевірки архівувати зміну через `openspec archive add-resume-experience-projects`.
