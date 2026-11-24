# Tasks

## 1. Форма контакту без секції «Джерела»

- [x] 1.1 У `app/Containers/AppSection/Contact/UI/Filament/Resources/Contacts/Schemas/ContactForm.php` прибрати обгортку `Section::make('Джерела')`: `VacancySourcesRepeater::make()` має стояти прямо в `components()` після поля `name`. Прибрати непотрібний `use Filament\Schemas\Components\Section;`. Перевірити, що файл не імпортує `Section`.
- [x] 1.2 У `ManageContactsTest` додати тест сценарію «Джерела без окремої секції»: у модалці створення контакту є поле `sources` і кнопка «Додати джерело», а секції «Джерела» в схемі форми немає. Перевірити, що тест падає до зміни 1.1 і проходить після неї.
- [x] 1.3 Запустити `DB_DATABASE=hunt-board_testing vendor/bin/phpunit app/Containers/AppSection/Contact/Tests` і тести вакансії зі створенням контакту з форми вакансії (`--filter Contact` у `app/Containers/AppSection/Vacancy/Tests`). Перевірити, що всі тести зелені.
- [x] 1.4 Запустити `vendor/bin/phpstan analyse` і `composer fixer` для зміненого файлу. Перевірити, що помилок немає.
