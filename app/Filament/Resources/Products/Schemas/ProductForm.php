<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                RichEditor::make('description')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'
                    ])
                    ->extraInputAttributes([
                        'style' => 'min-height: 300px;'
                    ])
                    ->columnSpanFull(),
                SpatieTagsInput::make('tags'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }
}
