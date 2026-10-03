<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Models\Integration;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle';

    protected static ?string $navigationLabel = 'Entegrasyonlar';

    protected static ?string $navigationGroup = 'Site Ayarları';

    protected static ?string $modelLabel = 'Entegrasyon';

    protected static ?string $pluralModelLabel = 'Entegrasyonlar';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ad')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->label('Tür')
                    ->required(),
                Forms\Components\TextInput::make('api_key')
                    ->label('API Anahtarı')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Boş bırakılırsa mevcut anahtar korunur.'),
                Forms\Components\TextInput::make('api_secret')
                    ->label('API Şifresi')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Boş bırakılırsa mevcut şifre korunur.'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Textarea::make('config')
                    ->label('Ek Yapılandırma')
                    ->columnSpan('full')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Boş bırakılırsa mevcut yapılandırma korunur.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tür'),
                Tables\Columns\BooleanColumn::make('is_active')->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
            'create' => Pages\CreateIntegration::route('/create'),
            'edit' => Pages\EditIntegration::route('/{record}/edit'),
        ];
    }
}
