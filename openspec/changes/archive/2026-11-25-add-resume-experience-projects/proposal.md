# Proposal

## Why

Зараз запис досвіду в резюме має один опис і один стек на всю компанію. Коли в одній компанії було кілька проєктів з різними ролями й технологіями, кожен проєкт не вдається описати окремо. Резюме ще не в продакшені, тож структуру можна змінити без міграції даних.

## What Changes

- Кожен запис досвіду резюме отримує впорядкований список проєктів. Проєкт має назву, роль, опис (rich text), стек технологій і перемикач видимості.
- **BREAKING:** із запису досвіду прибираються поля «Опис ролі» (`description`) і «Технології» (`stacks`). «Посада» (`position`) лишається як загальна роль у компанії. Наявні описи й стеки досвіду не переносяться, а дані в dev-базі буде скинуто (`migrate:fresh`).
- У вкладці «Досвід» форми резюме кожен запис досвіду отримує вкладений repeater «Проєкти» з перетягуванням і згортанням. Збереження вкладки замінює і досвід, і його проєкти атомарно.
- У PDF картка досвіду показує заголовок «Посада | Компанія» з локацією, типом зайнятості й періодом справа. Під ним ідуть видимі проєкти: назва, рядок «Role:» / «Роль:» (якщо заповнено), опис і рядок «Tech Stack:» / «Технології:» (якщо заповнено).
- Міграцію `create_resume_experiences_table` змінено на місці, і додано нову міграцію `create_resume_experience_projects_table`.

Не входить у зміну:

- Повʼязування з проєктами профілю (`ProfileProject` у контейнері `Profile`) або імпорт із них.
- Дати проєктів. Порядок задається лише перетягуванням.
- Окрема сторінка чи модальне вікно для керування проєктами.
- Зміни інших розділів резюме (посилання, освіта, мови) і шаблону резюме в контейнері `Profile`.

## Capabilities

### New Capabilities
- `resume-experience-projects`: проєкти в межах запису досвіду резюме, тобто поля, валідація, стек, порядок, видимість і редагування у вкладці «Досвід».

### Modified Capabilities
- `resume-experiences`: із запису досвіду прибираються опис і технології, а «Посада» стає загальною роллю в компанії.
- `resume-preview`: PDF показує проєкти в картці досвіду, приховує невидимі проєкти й використовує новий заголовок картки.

## Impact

- Контейнер `app/Containers/AppSection/Resume`:
  - `Data/Migrations` (змінена міграція досвіду й нова міграція проєктів), `Models` (новий `ResumeExperienceProject`, зміни `ResumeExperience`), `Data/Factories`, `Data/Repositories`;
  - `Tasks/ReplaceResumeExperiencesTask`, `Tasks/DeleteResumeTask`, `Tasks/FindUserResumeTask`, `Tasks/BuildResumeDocumentTask`;
  - `UI/Filament/Resources/Resumes/Schemas/ResumeExperiencesRepeater`, `Pages/EditResumeExperiences`;
  - `Values/Pdf` (новий `ResumeDocumentProject`, зміни `ResumeDocumentExperience`), `UI/WEB/Views/pdf/sections/experience.blade.php`, стилі шаблону `classic`, `Languages/{uk,en}/pdf.php`;
  - тести в `Tests/Unit` і `Tests/Functional`.
- Контейнер `app/Containers/AppSection/Stack`: використовується без змін (`HasStacks`, `stackables`, `StackSelect`).
- Після впровадження локальну базу треба перестворити (`migrate:fresh`).
