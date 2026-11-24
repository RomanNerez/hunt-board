# Design

## Context

Контейнер `AppSection/Resume` має модель `Resume` (`bigint` id, `user_id`), `CreateResumeAction` / `UpdateResumeAction` над `CreateResumeTask` / `UpdateResumeTask`, `ResumeForm` на двох колонках і сторінки `CreateResume` / `EditResume`, які пишуть через Actions. Аналог повторюваного списку вже є: `VacancySourcesRepeater` разом із `ReplaceVacancySourcesTask` (повна заміна списку в транзакції Action). Встановлено `blade-ui-kit/blade-heroicons` і `blade-icons`, брендових наборів немає. Див. proposal.md.

## Goals / Non-Goals

**Goals:**
- Окрема таблиця й модель `ResumeLink` у контейнері `Resume`.
- Repeater у формі без `Section`, зі збереженням через Actions.

**Non-Goals:**
- Окремий ресурс Filament для посилань, показ у картці, експорт.

## Decisions

1. **`bigint`, а не `uuid`.** `resume_links.id` це `$table->id()`, `resume_id` це `foreignId('resume_id')->constrained()->cascadeOnDelete()`. Причина: `resumes.id` вже `bigint`, ключ `uuid` не зможе на нього посилатися, а решта проєкту теж використовує `bigint`. Альтернатива: перевести `resumes` на `uuid`: не виправдано обсягом.
2. **Колонки:** `icon` string(100) nullable, `label` string(255), `url` string(2048), `sort_order` unsigned integer, `is_visible` boolean default true, індекс `(resume_id, sort_order)`. `icon` це повне імʼя Blade-іконки (`heroicon-o-envelope`, `fab-github`).
3. **Зберігання повною заміною.** Новий `ReplaceResumeLinksTask` видаляє наявні посилання й створює їх з форми, а `sort_order` береться з позиції в масиві. `CreateResumeAction` / `UpdateResumeAction` виймають ключ `links` з даних і викликають цей Task в одній `DB::transaction`, як у `UpdateVacancyAction`. Якщо ключа `links` у даних немає, список не чіпається. Альтернатива: Filament `->relationship()` на Repeater: порушує правило «усі записи йдуть через Actions». Задачі не викликають одна одну, оркестрація в Action.
4. **Форма.** `ResumeLinksRepeater::make()` у `UI/Filament/Resources/Resumes/Schemas/`: `Repeater::make('links')->label('Посилання')`, `->reorderableWithDragAndDrop()`, `->defaultItems(0)`, `->addActionLabel('Додати посилання')`, `->columnSpanFull()`, без `Section`. Видимий підпис «Посилання» дає сам лейбл Repeater, тож без обгортки він стоїть на рівні основних полів. Поля рядка: `Select icon` (searchable, nullable, групи «Іконки» і «Бренди»), `TextInput label` (required, max 255), `TextInput url` (required, max 2048, правило схем `http|https|mailto|tel` через `starts_with`), `Toggle is_visible` (default true). `sort_order` у формі не показується.
5. **Заповнення форми.** `EditResume` перед заповненням форми підставляє `links` із `$record->links` (відношення `hasMany`, сортоване за `sort_order`), у `mutateFormDataBeforeFill`. Відношення завантажується явно через `->load('links')` через strict-режим моделей.
6. **Іконки.** Варіанти для `Select` беруться з реєстру набору `ResumeLinkIcon` (клас у `Resume/Support/`, імʼя ключа → людська назва): кілька Heroicons (envelope, phone, globe-alt, link, map-pin) плюс бренди (GitHub, LinkedIn, Telegram, ...). Heroicons Filament вже має, бренди приходять із додаткового пакета Blade Icons. Рекомендація: `owenvoke/blade-fontawesome` (префікс `fab-`, має GitHub, LinkedIn, Telegram). Додавання пакета змінює залежності, тож потребує погодження перед apply. Альтернатива: власні SVG у `resources/svg` із `blade-icons` (без нового пакета, але SVG доведеться додавати вручну).
7. **Політика.** Нова `ResumeLinkPolicy` не потрібна: посилання керуються лише через резюме, доступ до якого вже контролює `ResumePolicy`.
8. **Правила коду.** Без коментарів, PHPDoc лише з тегами, `final`, фабрика `ResumeLinkFactory`, `Resume::links()`.

## Risks / Trade-offs

- [Повна заміна змінює `id` посилань при кожному збереженні] → на посилання ніхто не посилається; за потреби пізніше перейти на синхронізацію за `id`.
- [Новий пакет іконок] → погодити перед apply; запасний варіант: власні SVG.
- [Небезпечні схеми (`javascript:`)] → валідація дозволяє лише `http`, `https`, `mailto`, `tel`.
- [Кількість викликів Livewire у Repeater із `Select` іконок] → список іконок короткий, вибір без пошуку по мережі.

## Migration Plan

Одна міграція створює таблицю; відкат `Schema::dropIfExists('resume_links')`. Наявні резюме отримують порожній список.
