# Design

## Context

Дані для резюме вже є в контейнері `AppSection/Profile`: `UserProfile` (ім'я, прізвище, email, телефон, локація, заголовок, «Про себе») і зв'язки `links`, `skillGroups` (зі стеками), `languages`, `educations`, `workExperiences` → `projects` (зі стеками). Перекладні поля зберігаються через `spatie/laravel-translatable` (`HasProfileTranslations`), а мови описує enum `ProfileLocale` (`uk` основна, `en`). Опис проєкту — HTML із `RichEditor`. Мітки enum-ів (`LanguageLevel::NATIVE` → «Рідна», `WorkExperienceType::FREELANCE` → «Фриланс-проєкт») зараз лише українські.

Розділ «Профіль» — Filament-кластер `ProfileCluster` зі сторінками з `navigationSort` 10–40. Перемикач мови даних реалізовано трейтом `InteractsWithDataLocale` (властивість `dataLocale`, `dataLocaleActionGroup()`).

Веб-маршрути контейнерів Apiato підвантажує з `UI/WEB/Routes/*.php`, в'юхи — з `UI/WEB/Views` з неймспейсом `appSection@<container>`, переклади — з `Languages/<locale>`. Гостей `bootstrap/app.php` перенаправляє на логін Filament.

У Docker-стеку немає Chromium, а `app`-образ — це PHP-FPM з Node 20.

## Goals / Non-Goals

**Goals:**
- Зібрати резюме з профілю в незалежну від Eloquent структуру, щоб шаблони не робили запитів і не знали про переклади.
- Мати точку розширення для нових шаблонів (enum + Blade-в'юха) без змін сторінки, Action і маршрутів.
- Тестувати вміст резюме без Gotenberg.

**Non-Goals:**
- Кешування або збереження згенерованих PDF: резюме генерується на кожен запит (один користувач, рідкі запити).
- Черги для генерації: синхронний HTTP-запит до Gotenberg триває до кількох секунд, цього достатньо.
- Окрема HTML-версія перегляду: попередній перегляд — це той самий PDF.

## Decisions

### 1. Сторінка `ViewResume` у кластері «Профіль»

`UI/Filament/Pages/ViewResume.php`: `Filament\Pages\Page`, `$cluster = ProfileCluster::class`, `$slug = 'resume'`, `$navigationSort = 50`, заголовок «Резюме». Вона не наслідує `ProfileFormPage`, бо форми й «Зберегти» тут немає.

- Мова резюме — через наявний трейт `InteractsWithDataLocale`: та сама властивість `dataLocale` і група дій «Українська / English» у шапці, тож перемикач виглядає й поводиться як на інших сторінках профілю.
- Шапка: `dataLocaleActionGroup()`, `Action::make('openResume')` («Відкрити в новій вкладці», `->url(...)->openUrlInNewTab()`), `Action::make('downloadResume')` («Завантажити PDF», `->url(...)`). URL залежать від `dataLocale` через замикання, тож після перемикання Livewire перерендерить кнопки з новою мовою. Обидві дії `->visible()` лише за наявності профілю.
- Вміст: `content(Schema)` з `Filament\Schemas\Components\View::make('appSection@profile::resume.preview-frame')`. Ця в'юха — `<iframe>` на маршрут перегляду, висота `calc(100vh - …)`, `wire:key` з мовою, щоб iframe перезавантажувався. Без профілю показується повідомлення з посиланням на `EditProfileGeneral::getUrl()`.

Альтернативи: окремий пункт у головній навігації або лише кнопки в шапці інших сторінок. Користувач вибрав сторінку в «Профілі»: резюме — похідне від профілю, а для майбутніх варіантів можна буде перенести сторінку в окремий ресурс.

### 2. Попередній перегляд — справжній PDF у `<iframe>`

Iframe вантажить той самий PDF, що й завантаження, у режимі inline. Користувач бачить точні розриви сторінок A4 і результат рендеру Gotenberg.

Альтернатива: HTML-версія шаблону в iframe. Вона швидша, але розходиться з PDF у розривах сторінок і полях, а її підтримка подвоює шаблонну роботу. Відкинуто.

### 3. Маршрути й контролери в `UI/WEB`

Два файли маршрутів, один маршрут на файл, з `middleware('auth')` і `whereIn('locale', ProfileLocale::values())` (інша мова дає 404 ще до контролера):

| Файл | Маршрут | Ім'я | Відповідь |
|---|---|---|---|
| `UI/WEB/Routes/PreviewResume.php` | `GET admin/profile/resume/{locale}/preview` | `profile.resume.preview` | inline PDF |
| `UI/WEB/Routes/DownloadResume.php` | `GET admin/profile/resume/{locale}/download` | `profile.resume.download` | attachment PDF |

Префікс `admin/profile/resume/…` тримає адреси поруч зі сторінкою і не перетинається з маршрутом Filament `admin/profile/resume`. Контролери `PreviewResumeController` і `DownloadResumeController` (наслідують `WebController`) приймають `GetResumeRequest`, викликають `GenerateResumePdfAction` з `Auth::id()` і повертають `PdfBuilder` (він `Responsable`): `->inline()` або `->download()`. Користувач береться лише з сесії, параметра «чий профіль» немає, тому чужого резюме отримати неможливо.

Альтернатива: Livewire-дія з `response()->streamDownload()`. Вона працює для завантаження, але не дає URL для iframe чи нової вкладки. Відкинуто.

### 4. Action і Tasks

- `Actions/GenerateResumePdfAction::run(int $userId, ProfileLocale $locale, ResumeTemplate $template = ResumeTemplate::Classic): PdfBuilder` викликає дві Tasks.
- `Tasks/BuildResumeTask::run(int $userId, ProfileLocale $locale): Resume` завантажує `UserProfile` з eager loading (`links`, `skillGroups.stacks`, `languages`, `educations`, `workExperiences.projects.stacks`) і перетворює його на value-об'єкт. Без профілю кидає `Exceptions/ResumeNotAvailable` (наслідує `App\Ship\Parents\Exceptions\HttpException`, статус 404).
- `Tasks/RenderResumePdfTask::run(Resume $resume, ResumeTemplate $template): PdfBuilder` викликає `Pdf::view($template->view(), ['resume' => $resume])->format(Format::A4)->margins(…)->name($resume->fileName)`.

Сторінці потрібно лише знати, чи є профіль, тому вона читає це напряму через Eloquent (`UserProfile::query()->where('user_id', …)->exists()`). Читання з Filament через Eloquent дозволені правилами проєкту.

### 5. Value-об'єкти резюме

`Values/Resume/*`, `final readonly class` на основі `App\Ship\Parents\Values\Value`, де можна:

- `Resume`: `locale`, `fullName`, `headline`, `email`, `phone`, `location`, `summary`, `links: list<ResumeLink>`, `skillRows: list<ResumeSkillRow>`, `languages: list<string>`, `experience: list<ResumeExperienceCard>`, `education: list<ResumeEducation>`, `fileName`;
- `ResumeLink` (`label`, `url`, `displayUrl`), `ResumeSkillRow` (`name`, `items`), `ResumeExperienceCard` (`role`, `organization`, `title|null`, `period`, `descriptionHtml|null`, `stack: list<string>`), `ResumeEducation` (`heading`, `subtitle|null`, `description|null`, `years|null`).

Усю логіку перекладу, fallback на українську, форматування періодів і санітизацію HTML виконує `BuildResumeTask` (за потреби з приватними хелперами або трейтом у `Tasks/Concerns`). Шаблон лише виводить готові рядки, тому новий шаблон не дублює цю логіку.

- Переклад з fallback: `$model->getTranslation($field, $locale->value, useFallbackLocale: false)`, а якщо порожньо — значення `uk`, як у `translationWithFallback()`.
- Картки досвіду: за кожен проєкт `workExperiences` (порядок `started_on desc`) по картці в порядку `projects`, а запис без проєктів дає одну картку з періодом запису. `organization` для `FREELANCE` — перекладений підпис «Фріланс-проєкт» / «Freelance project», інакше `company_name`.
- Періоди: `CarbonImmutable::locale($locale->value)->isoFormat('MMM YYYY')` з першою великою літерою, через « – », з «Теперішній час» / «Present» для відкритого періоду. Роки освіти — `YYYY` або `YYYY – YYYY`; однакові роки дають один рік.
- Мови: `«Мова (рівень) — опис»`. Рівень — новий метод `LanguageLevel::resumeLabel(ProfileLocale)`: для `NATIVE` повертає «Рідна» / «Native», для інших — наявну CEFR-мітку.
- Посилання: `displayUrl` — URL без схеми, `www.` і кінцевого `/`.
- Опис проєкту: `Filament\Forms\Components\RichEditor\RichContentRenderer::make($html)->toHtml()`. Він санітизує HTML так само, як Filament при показі. Якщо цього рендерера немає в установленій версії, взяти `Str::sanitizeHtml()` / Symfony HtmlSanitizer з білим списком `p, ul, ol, li, strong, b, em, i, br, a` і зафіксувати це тут.
- Ім'я файлу: `Str::ascii()` англійських імені й прізвища (fallback на українські, бо `Str::ascii` транслітерує кирилицю), пробіли → `_`, суфікс `_CV_UA` / `_CV_EN` через новий метод `ProfileLocale::fileSuffix()`.

Альтернатива: передавати в Blade моделі й `ProfileLocale`. Так шаблони простіші на старті, але кожен новий шаблон повторює fallback-логіку й робить lazy-запити, які `Model::shouldBeStrict()` забороняє. Відкинуто.

### 6. Шаблони: enum `ResumeTemplate`

`Enums/ResumeTemplate: string implements HasLabel` з одним випадком `Classic = 'classic'` (мітка «Класичний») і методом `view(): string`, що повертає `appSection@profile::resume.templates.classic`. Новий шаблон — це новий case і нова в'юха. Вибір шаблону в UI й параметр `{template}` у маршрутах додамо, коли з'явиться другий шаблон.

Альтернатива: таблиця шаблонів у БД. Шаблон — це код (Blade + CSS), а не дані, тож enum простіший. Варіанти резюме з різним вмістом, коли з'являться, будуть окремою сутністю, яка посилається на `ResumeTemplate`.

### 7. Blade-шаблон «Класичний»

`UI/WEB/Views/resume/templates/classic.blade.php`: самодостатній HTML-документ, увесь CSS вбудований у `<style>`, без Vite і зовнішніх ресурсів, бо Gotenberg отримує лише HTML. Верстка повторює приклад: темна шапка `#0f172a` на всю ширину, блакитні акценти, «Про себе» з лівою смугою, таблиця навичок у дві колонки, картки досвіду з `break-inside: avoid`, A4 із полями ~12 мм, фон сторінки `#f8fafc`. Шрифт — системний sans-serif з кирилицею (`"Liberation Sans", Arial, sans-serif`), він є в образі Gotenberg. Підписи — `__('appSection@profile::resume.<key>', locale: $resume->locale)`, файли `Languages/uk/resume.php` і `Languages/en/resume.php`.

Результати ручної перевірки через Gotenberg (задача 4.2):

- У dev-БД не було профілю, тому вигляд перевірено на `Resume`, зібраному з даних прикладу, через `RenderResumePdfTask` і справжній Gotenberg (українська й англійська). Вигляд відповідає прикладу: кирилиця, розриви сторінок, картки не рвуться.
- Поля сторінки: `RenderResumePdfTask` передає 12 мм зверху й знизу та 0 мм збоку, а шаблон задає `@page { margin: 12mm 0 }` і `@page :first { margin-top: 0 }`. Так темна шапка першої сторінки стоїть упритул до краю, наступні сторінки мають верхній відступ, а фон `#f8fafc` покриває й поля. Бічні відступи задає `.content` (12 мм).
- `isoFormat('MMM')` для `uk` дає «бер» / «кві», тож після першої великої літери маємо «Бер 2024», як у прикладі. Ризик з розділу Risks не справдився, власний список скорочень не потрібен.
- «Про себе» показано без заголовка розділу, як у прикладі, тож ключа `sections.summary` у перекладах немає.
- Кілька мов у рядку «Іноземні мови» розділено «; ». В освіті під закладом іде рядок «назва (роки)», а опис — окремим рядком.
- Базовий шрифт 10pt (у прикладі текст дрібніший, ніж 10.5pt).

### 8. Gotenberg і конфігурація

- `composer.json` контейнера `Profile`: `"spatie/laravel-pdf": "^2.14"`, далі `composer update spatie/laravel-pdf` у контейнері `app`.
- `docker-compose.yml`: сервіс `gotenberg` (`image: gotenberg/gotenberg:8`, без проброшеного порту, у тій самій мережі), а `app` і `supervisor` залежать від нього (`depends_on`).
- `.env.example` і `.env`: `LARAVEL_PDF_DRIVER=gotenberg`, `GOTENBERG_URL=http://gotenberg:3000`. Конфіг пакета не публікуємо, бо вистачає env.
- Тести: `Pdf::fake()` у `setUp()`, тож HTTP до Gotenberg немає. Вміст перевіряється через `Pdf::assertRespondedWithPdf(fn (PdfBuilder $pdf) => str_contains($pdf->html, …))`. Структуру `Resume` перевіряють unit-тести `BuildResumeTask`.

Альтернативи (Browsershot, DomPDF) розглянуто з користувачем. Gotenberg дає повний CSS Chromium і не додає Chromium/Node-залежностей у PHP-образ.

## Risks / Trade-offs

- [Gotenberg не запущено або недоступний] → маршрут повертає 500 і iframe показує помилку. Пом'якшення: у контролерах перехоплювати виняток драйвера й віддавати 503 з коротким українським текстом, а в README контейнера записати `docker compose up -d gotenberg`.
- [Iframe з PDF блокується політикою `X-Frame-Options`] → маршрути на тому ж origin, тож `SAMEORIGIN` пропускає. Перевірити заголовки nginx під час реалізації.
- [Вбудований переглядач PDF у браузері відрізняється (Safari / Chrome / Firefox)] → прийнятно, кнопка «Відкрити в новій вкладці» дає повний переглядач.
- [`isoFormat('MMM')` для `uk` дає «бер.» / «квіт.», а не «Бер» / «Кві», як у прикладі] → спека вимагає лише українську назву місяця. Якщо формат не сподобається, замінити на власний список скорочень у `Languages/uk/resume.php` без зміни специфікації.
- [Довгий опис проєкту розриває картку між сторінками] → `break-inside: avoid` на картці. Для дуже довгої картки Chromium все одно розірве її, що прийнятно.
- [Генерація на кожен запит] → кілька секунд на перемикання мови. Кешування можна додати пізніше через `->cache()` пакета з ключем від `updated_at` профілю.

## Migration Plan

1. `docker compose up -d gotenberg` (або `docker compose up -d` після оновлення compose-файлу).
2. `docker compose exec app composer update spatie/laravel-pdf`.
3. Додати змінні в `.env`. Міграцій БД немає.

Відкат: прибрати сторінку, маршрути й залежність. Дані профілю не змінюються.
