<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('images')->collection('images')
                    ->multiple()
                    ->enableReordering()
                    ->image()
                    ->maxFiles(5),
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
            ]);
    }
}
