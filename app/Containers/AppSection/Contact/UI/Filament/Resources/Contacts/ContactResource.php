<?php

namespace App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts;

use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Pages\ManageContacts;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Schemas\ContactForm;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Tables\ContactsTable;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ContactResource extends Resource
{
    /**
     * @var class-string<Model>|null
     */
    protected static string|null $model = Contact::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    /**
     * @var int|null
     */
    protected static int|null $navigationSort = 30;

    /**
     * @var string|null
     */
    protected static string|null $recordTitleAttribute = 'name';

    /**
     * @var string|null
     */
    protected static string|null $modelLabel = 'Контакт';

    /**
     * @var string|null
     */
    protected static string|null $pluralModelLabel = 'Контакти';

    /**
     * @var string|null
     */
    protected static string|null $navigationLabel = 'Контакти';

    /**
     * @var bool
     */
    protected static bool $hasTitleCaseModelLabel = false;

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageContacts::route('/'),
        ];
    }
}
