# Design

## Context

Мотивація описана в proposal.md, розділ Why. Вимоги зібрані в специфікаціях `resume-preview`, `resumes` і `resume-form-tabs`.

Звідки беремо зразок. У контейнері Profile PDF уже генерується. Ланцюжок такий: `GenerateResumePdfAction` викликає `BuildResumeTask`, який будує value-обʼєкт `Values\Resume\Resume` з моделі `UserProfile` і перекладів. Далі `RenderResumePdfTask` викликає `Pdf::view()` з `spatie/laravel-pdf`. Сам шаблон зараз один файл: `classic.blade.php` на 329 рядків. Маршрути `profile.resume.preview` і `profile.resume.download` віддають PDF через трейт `RespondsWithResumePdf`. Цей трейт ловить `CouldNotGeneratePdf` і `ConnectionException` і повертає 503. Сторінка `ViewResume` показує `iframe` через `View::make('…preview-frame')`.

Чим відрізняється модель `Resume`:
- вона одномовна, перекладів немає;
- у неї немає email, телефону, локації й навичок;
- кожен запис розділу має `is_visible` і `sort_order`;
- `description` у досвіді й освіті заповнюється через RichEditor;
- Policy `view` уже перевіряє власника, а `ResumeResource::getEloquentQuery()` обмежує вибірку поточним користувачем.

## Goals / Non-Goals

**Goals:**
- Контейнер Resume сам генерує свій PDF і нічого не імпортує з Profile.
- Модальне вікно в списку і вкладка «Перегляд» показують одне й те саме: один Blade-фрейм на ті самі два маршрути.
- Шаблон зібраний із секційних partials, щоб секції було легко змінювати, переставляти й далі додавати нові шаблони.

**Non-Goals:**
- Спільний для Profile і Resume шаблон чи value-обʼєкти в Ship. Моделі даних надто різні, і обʼєднувати їх варто вже тоді, коли зʼявиться третій споживач.
- Вибір шаблону користувачем. Енум шаблону не вводимо, поки шаблон лише один.

## Decisions

### 1. Мова: власний енум `ResumeLocale` у `Resume/Enums`

Енум `ResumeLocale: string implements HasLabel` має значення `English = 'en'` і `Ukrainian = 'uk'`. Методи: `getLabel()` повертає «English» або «Українська», `fileSuffix()` повертає `EN` або `UA`, `default()` повертає `English`. У моделі він працює як cast `locale`. Поле додаємо в `$fillable` і в `@property`.

- Альтернатива: перевикористати `Profile\Enums\ProfileLocale`. Ми від неї відмовилися, бо тоді Resume залежав би від Profile. До того ж `ProfileLocale::primary()` повертає українську, а для резюме за замовчуванням потрібна англійська.

### 2. Поле в наявній міграції

У `2026_11_24_130000_create_resumes_table.php` після `kind` додається `$table->string('locale', 2)->default('en');`. Фабрика за замовчуванням ставить `en` і має стан `ukrainian()`. `CreateResumeTask` ставить `en`, якщо `locale` не передано. На рівні БД значення за замовчуванням теж є.

- Альтернатива: окрема міграція `add_locale_to_resumes`. Користувач її відхилив, бо резюме ще не в продакшені.

### 3. Форма: `ToggleButtons` для мови

У `ResumeForm` зʼявляється спільний метод `localeField()`: `ToggleButtons::make('locale')->options(ResumeLocale::class)->default(en)->required()->inline()`. Його використовують модальне вікно `CreateResumeModalAction` і вкладка «Загальне». Filament сам перевіряє, що значення входить до options, тому `de` дає помилку форми. Записи й далі проходять через `CreateResumeAction` і `UpdateResumeAction`. Їхні Tasks передають `locale` у fill.

- Альтернатива: `Select`. Ми її не обрали, бо для двох значень перемикач показує поточну мову без зайвого кліку.

### 4. Генерація PDF: Action → Tasks у контейнері Resume

```
UI/WEB/Controllers/{Preview,Download}ResumePdfController
        |
Actions/GenerateResumePdfAction::run(int $userId, int $resumeId): PdfBuilder
        |
        +--> Tasks/FindUserResumeTask::run($userId, $resumeId): Resume     (404, якщо не знайдено)
        +--> Tasks/BuildResumeDocumentTask::run(Resume): ResumeDocument
        +--> Tasks/RenderResumePdfTask::run(ResumeDocument): PdfBuilder
```

- `FindUserResumeTask` шукає за `id` і `user_id` через `ResumeRepository` і одразу завантажує `links`, `experiences.stacks`, `educations` і `languages`. Якщо нічого не знайдено, кидає `ResumePdfUnavailable::becauseResumeIsMissing()` (404). Так чуже резюме не відрізнити від неіснуючого, і обидва випадки дають 404.
- `BuildResumeDocumentTask` відкидає записи з `is_visible = false` і зберігає порядок за `sort_order`, бо цей порядок уже задають відношення моделі. Він також форматує періоди з урахуванням `locale` (`isoFormat('MMM YYYY')`, `ucfirst`, роздільник « – », позначка «Present»/«Дотепер»), перетворює rich text на HTML через `RichContentRenderer` і будує імʼя файлу через `Str::ascii` і `fileSuffix()`.
- `RenderResumePdfTask` викликає `Pdf::view('appSection@resume::pdf.classic', ['resume' => $document])`, задає формат A4 і поля 12 мм, як у Profile.
- Альтернатива: один Task на все. Від неї відмовилися, бо побудову документа зручно тестувати юніт-тестом без PDF-сервісу.

### 5. Value-обʼєкти в `Resume/Values/Pdf/`

Тут лежать `ResumeDocument`, `ResumeDocumentLink`, `ResumeDocumentExperience`, `ResumeDocumentEducation` і `ResumeDocumentLanguage`. Усі вони `final readonly` і розширюють `ParentValue`. Префікс `ResumeDocument*` потрібен, щоб назви не збігалися з моделями `Resume*`.

- Досвід містить посаду й компанію, підпис періоду, локацію, підпис типу зайнятості мовою резюме, HTML опису й список технологій.
- Освіта містить заклад, ступінь і спеціальність одним рядком, роки й HTML опису.
- Мова містить готовий рядок: «назва (підпис)», де підпис береться з `level_label`, а якщо його немає, то з `level` мовою резюме. Якщо немає ні того, ні іншого, лишається лише назва.

### 6. Шаблон зібраний із секційних partials

```
Resume/UI/WEB/Views/pdf/
  classic.blade.php            -> <html>, @include секцій по черзі
  sections/
    styles.blade.php           -> <style> (A4, @page, класи)
    header.blade.php           -> імʼя, заголовок (по центру)
    links.blade.php            -> видимі посилання: іконка + label, по центру
    summary.blade.php
    experience.blade.php
    education.blade.php
    languages.blade.php
Resume/UI/WEB/Views/preview-frame.blade.php   -> iframe для Filament
```

Кожна секція отримує `$resume` (тобто `ResumeDocument`) і `$t`, а для порожнього списку нічого не виводить. Стилі беремо з профільного `classic.blade.php` як зразок. Порядок секцій такий: шапка, посилання, опис, досвід, освіта, мови.

Короткий опис виводиться окремою секцією із заголовком `sections.summary` («Summary» / «Про мене») у білій картці з тонкою рамкою, як у карток досвіду. Заголовки всіх секцій вирівняні по центру. У картці досвіду локація й тип зайнятості стоять під періодом, праворуч.

Шапка (імʼя, заголовок і посилання) вирівняна по центру. Посилання показується як іконка й `label`, а клікабельний весь фрагмент. Сама адреса в тексті не виводиться. Іконку з `ResumeLink.icon` `BuildResumeDocumentTask` перетворює на inline-SVG через `svg()` з blade-icons і кладе в `ResumeDocumentLink::$iconSvg`. Якщо іконки немає або вона не входить до `ResumeLinkIcon::all()`, значення `null`, і показується лише `label`.

- Альтернатива: Blade-компоненти `<x-…>`. Їх не обрали, бо для PDF-шаблону без логіки й слотів `@include` простіший і не потребує класів компонентів.

### 7. Переклади в `Resume/Languages/{en,uk}/pdf.php`

У файлах є такі групи ключів: `sections.*` (experience, education, languages), `present`, `tech_stack`, `levels.*` (a1…c2, native) і `employment_types.*`. Шаблон і Task звертаються до них через `__('appSection@resume::pdf.…', locale: $document->locale->value)`. До енумів `ResumeLanguageLevel` і `ResumeEmploymentType` додається метод `pdfLabel(ResumeLocale)`. Їхній `getLabel()` для Filament не змінюється.

### 8. Маршрути: окремі файли в `UI/WEB/Routes`

- `admin/resumes/{resume}/pdf/preview` з іменем `resumes.pdf.preview`
- `admin/resumes/{resume}/pdf/download` з іменем `resumes.pdf.download`

Обидва з `whereNumber('resume')` і `middleware('auth')`. Сегмент `pdf/` вкладений, тому маршрути не перетинаються з Filament-маршрутами `/{record}/edit|links|…|preview`. Контролери приймають `int $resume` без model binding, бо перевірку власника робить Task. Помилки сервісу PDF обробляються власним трейтом `RespondsWithResumePdf`. Він перетворює `CouldNotGeneratePdf` і `ConnectionException` на `ResumePdfUnavailable::becausePdfServiceFailed()` (503). Виняток лежить у `Resume/Exceptions` і розширює `ParentHttpException`, як `ResumeNotAvailable` у Profile. Відповідь отримує `Cache-Control: no-store`, щоб iframe після збереження не показував застарілий PDF.

### 9. Filament: спільний фрейм, дві точки входу

- `Actions/PreviewResumeAction` (у `Resources/Resumes/Actions`) — це `Action::make('preview')`, іконка `OutlinedEye`, `iconButton()`, `slideOver()`, `modalWidth(Width::FiveExtraLarge)`. Вміст задається через `modalContent(view('appSection@resume::preview-frame', …))`, `modalSubmitAction(false)`, а в `extraModalFooterActions` стоять «Завантажити PDF» (url download) і «Відкрити в новій вкладці» (url preview, `openUrlInNewTab`). У `ResumesTable::recordActions()` ця дія стоїть першою.
- `Pages/PreviewResume extends ViewRecord` має шлях `'preview' => PreviewResume::route('/{record}/preview')`, підпис навігації «Перегляд» і іконку `OutlinedEye`. У `getRecordSubNavigation` він стоїть останнім. `content(Schema)` повертає `View::make('appSection@resume::preview-frame')`, а `getHeaderActions()` дає ті самі дві url-дії. `ViewRecord` перевіряє `view` у Policy, а `getEloquentQuery()` віддає 404 на чуже резюме.
- `preview-frame.blade.php` отримує `$previewUrl` і висоту. У модальному вікні висота `calc(100vh - 10rem)`, на сторінці `calc(100vh - 13rem)`, як у Profile.
- Альтернатива: вкладка як `EditRecord` із трейтом `EditsResumeSection`. Ми від неї відмовилися: форми немає, а `EditRecord` додав би кнопку «Зберегти» і попередження про незбережені зміни.

## Risks / Trade-offs

- [Шаблон скопійовано з Profile, тож зʼявилося дублювання] → Profile і Resume мають різні дані, і спільна абстракція зараз була б передчасною. Якщо обидва шаблони почнуть змінювати разом, стилі можна винести в Ship.
- [Iframe може показувати застарілий PDF після збереження] → Ставимо `Cache-Control: no-store`. Livewire заново рендерить фрейм під час переходу між вкладками. Сценарій «Свіжі дані після збереження» перевіряється вручну.
- [Генерація PDF повільна (зовнішній сервіс), і модальне вікно відкривається з затримкою] → Iframe вантажиться асинхронно, тому сама панель відкривається одразу. Кешування не входить у зміну.
- [Змінено вже застосовану міграцію] → У dev-базі запускаємо `migrate:fresh`, а тести використовують окрему БД `hunt-board_testing`. Продакшену поки немає.

## Migration Plan

1. Змінити міграцію, потім виконати `docker compose exec app php artisan migrate:fresh` (або rollback до `resumes` і `migrate`) у dev.
2. Відкат: прибрати колонку з міграції й знову виконати `migrate:fresh`.
