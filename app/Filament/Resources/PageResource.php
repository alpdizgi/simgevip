<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Site Ayarları';
    protected static ?string $navigationLabel = 'Sayfalar';
    protected static ?string $modelLabel = 'Sayfa';
    protected static ?string $pluralModelLabel = 'Sayfalar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Sayfa Başlığı')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('URL (Slug)')
                            ->required()
                            ->maxLength(255)
                            ->unique(Page::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\RichEditor::make('content')
                            ->label('Sayfa İçeriği')
                            ->columnSpan('full'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif / Görünür')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Başlık')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('URL')->searchable(),
                Tables\Columns\BooleanColumn::make('is_active')->label('Durum')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Oluşturulma')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
