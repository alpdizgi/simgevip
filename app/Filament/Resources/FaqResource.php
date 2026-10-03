<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'Site Ayarları';
    protected static ?string $navigationLabel = 'Sıkça Sorulan Sorular';
    protected static ?string $modelLabel = 'Soru';
    protected static ?string $pluralModelLabel = 'Sorular';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->label('Soru')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),
                        
                        Forms\Components\Textarea::make('answer')
                            ->label('Cevap')
                            ->required()
                            ->rows(4)
                            ->columnSpan('full'),
                            
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sıralama')
                            ->numeric()
                            ->default(0),
                            
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
                Tables\Columns\TextColumn::make('question')->label('Soru')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->label('Sıra')->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')->label('Durum')->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
