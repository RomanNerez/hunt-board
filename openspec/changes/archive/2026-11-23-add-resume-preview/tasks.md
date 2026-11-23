# Tasks

## 1. Інфраструктура

- [x] 1.1 Додати `"spatie/laravel-pdf": "^2.14"` у `app/Containers/AppSection/Profile/composer.json` і виконати `docker compose exec app composer update spatie/laravel-pdf`. Перевірка: `composer show spatie/laravel-pdf` показує 2.14+, `composer.lock` оновлено
- [x] 1.2 Додати сервіс `gotenberg` (`gotenberg/gotenberg:8`) у `docker-compose.yml` і `depends_on` для `app` та `supervisor`; додати `LARAVEL_PDF_DRIVER=gotenberg` і `GOTENBERG_URL=http://gotenberg:3000` у `.env.example` та `.env` (design.md, рішення 8). Перевірка: `docker compose up -d gotenberg`, а `docker compose exec app curl -s http://gotenberg:3000/health` повертає `"status":"up"`

## 2. Підписи й enum-и

- [x] 2.1 Додати `Enums/ResumeTemplate` (`Classic`, мітка «Класичний», `view()`) і тест `ResumeTemplateTest`. Перевірка: тест зелений
- [x] 2.2 Додати `ProfileLocale::fileSuffix()` (`UA` / `EN`) і `LanguageLevel::resumeLabel(ProfileLocale)` («Рідна» / «Native», CEFR-мітки без змін) з тестами в наявних тестах enum-ів. Перевірка: тести зелені
- [x] 2.3 Створити `Languages/uk/resume.php` і `Languages/en/resume.php` з підписами розділів, контактів, «Tech Stack», «Іноземні мови», «Теперішній час» / «Present», «Фріланс-проєкт» / «Freelance project». Перевірка: `php artisan tinker --execute 'echo __("appSection@profile::resume.sections.skills", locale: "en");'` повертає англійський підпис (за потреби уточнити неймспейс перекладів Apiato і зафіксувати в design.md)

## 3. Збирання даних резюме

- [x] 3.1 Створити value-об'єкти `Values/Resume/{Resume,ResumeLink,ResumeSkillRow,ResumeExperienceCard,ResumeEducation}` (design.md, рішення 5) з PHPDoc-тегами. Перевірка: `vendor/bin/phpstan analyse app/Containers/AppSection/Profile/Values` без помилок
- [x] 3.2 Додати `Exceptions/ResumeNotAvailable` (404) і `Tasks/BuildResumeTask` з eager loading усіх зв'язків, fallback перекладів, картками досвіду, періодами, мовами, освітою, `displayUrl`, санітизацією опису й ім'ям файлу. Unit-тест `BuildResumeTaskTest`: шапка й порожні поля; англійська з fallback на українську; порядок груп навичок і рядок мов з «Native»; запис з двома проєктами в порядку від новішого й запис без проєктів; фриланс-підпис обома мовами; періоди «Mar 2024 – Present» і українська назва місяця; освіта з одним роком і без закладу; `<script>` вирізано з опису, а список лишився; ім'я файлу `Roman_Nerez_CV_EN.pdf` з англійського імені й з транслітерації; відсутній профіль дає `ResumeNotAvailable`; дані іншого користувача не потрапляють. Перевірка: тест зелений
- [x] 3.3 Додати `Tasks/RenderResumePdfTask` (A4, поля, `name()`) і `Actions/GenerateResumePdfAction` з unit-тестами на `Pdf::fake()`: використано в'юху шаблону, формат A4, ім'я файлу; без профілю — виняток. Перевірка: тести зелені

## 4. Шаблон «Класичний»

- [x] 4.1 Зверстати `UI/WEB/Views/resume/templates/classic.blade.php` за прикладом (design.md, рішення 7): самодостатній HTML з вбудованим CSS, шапка, «Про себе», «Технічні навички», «Досвід роботи», «Освіта та курси», приховані порожні розділи й поля. Без коментарів у шаблоні. Перевірка: тест рендеру в'юхи з повним і порожнім `Resume` (є очікувані рядки, немає порожніх підписів і розділів)
- [x] 4.2 Ручна перевірка через Gotenberg: згенерувати українське й англійське резюме з реальних даних профілю й порівняти з прикладом (шапка, розриви сторінок, кирилиця, картки не рвуться без потреби). Перевірка: PDF відкривається, вигляд відповідає прикладу; відхилення записати в design.md

## 5. Маршрути

- [x] 5.1 Додати `UI/WEB/Requests/GetResumeRequest`, контролери `PreviewResumeController` (inline) і `DownloadResumeController` (download), маршрути `UI/WEB/Routes/PreviewResume.php` і `DownloadResume.php` з `auth` і `whereIn('locale', …)`; винятки драйвера PDF перетворювати на 503 (design.md, рішення 3 і Risks). Перевірка: `php artisan route:list --path=admin/profile/resume` показує обидва маршрути й сторінку Filament
- [x] 5.2 Функціональний тест `ResumePdfRoutesTest` (`Pdf::fake()`): inline-відповідь і `Content-Disposition: attachment` з `Roman_Nerez_CV_UA.pdf`; HTML англійського PDF містить «Technical Skills»; мова `de` дає 404; без профілю — 404; гостя перенаправлено на `/admin/login`; PDF користувача Б не містить даних користувача А. Перевірка: тест зелений

## 6. Сторінка «Резюме»

- [x] 6.1 Створити `UI/Filament/Pages/ViewResume` (кластер «Профіль», slug `resume`, `navigationSort` 50, трейт `InteractsWithDataLocale`, дії «Відкрити в новій вкладці» й «Завантажити PDF», видимі лише за наявності профілю) і в'юху `UI/WEB/Views/resume/preview-frame.blade.php` з iframe та станом порожнього профілю з посиланням на «Загальне». Генератором Filament не користуватися або прибрати коментарі зі стабу й додати PHPDoc-теги. Перевірка: сторінка відкривається на `/admin/profile/resume`, у `app/Filament` нічого не з'явилося
- [x] 6.2 Тест `ViewResumeTest` (`Livewire::test`): iframe веде на українське `preview`; після `dataLocale_en` iframe й обидві дії ведуть на англійські адреси; без профілю показано повідомлення й посилання на «Загальне», дій завантаження немає; вибору шаблону немає; гостя перенаправлено на логін. Перевірка: тест зелений
- [x] 6.3 Розширити тести субнавігації профілю: «Резюме» стоїть останнім після «Мови», на сторінці «Резюме» активний пункт «Резюме», адреса — `/admin/profile/resume`. Перевірка: тест зелений

## 7. Інтеграційна перевірка

- [x] 7.1 Запустити всі тести контейнера `Profile` на тестовій БД (`DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Profile`). Перевірка: усі зелені
- [x] 7.2 Запустити `vendor/bin/phpstan analyse` і `composer fixer`. Перевірка: PHPStan не додає нових помилок понад наявні в проєкті, fixer не лишає змін
- [x] 7.3 Відкрити `/admin/profile/resume` у браузері: перегляд українською, перемикання на English, завантаження й нова вкладка працюють, `browser-logs` без помилок (зокрема без блокування iframe). Перевірка: усе працює
