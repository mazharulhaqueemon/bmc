<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plan_name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('profile_picture_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('phone_request_limit')
                    ->tel()
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('chat_duration_days')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
