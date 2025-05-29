<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarStoreResource\Pages;
use App\Filament\Resources\CarStoreResource\RelationManagers;
use App\Filament\Resources\CarStoreResource\RelationManagers\PhotosRelationManager;
use App\Models\CarStore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Models\CarService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarStoreResource extends Resource
{
    protected static ?string $model = CarStore::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->helperText('Masukan nama bisnis anda!')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('cs_name')
                    ->helperText('Masukan nama pemilik')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('is_open')
                    ->options([
                        true => 'Open',
                        false => 'Close'
                    ])
                    ->required(),
                
                Forms\Components\Select::make('is_full')
                    ->options([
                        true => 'Full Booked',
                        false =>  'Available'
                    ])
                    ->required(), 
                    
                
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Repeater::make('storeServices')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('car_service_id')
                            ->relationship('service','name')
                            ->required()
                    ]),

                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->rows(10)
                    ->cols(20),                

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_open')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('is_full')
                    ->boolean()
                    ->falseColor('danger')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueIcon('heroicon-o-check-circle'),
                Tables\Columns\ImageColumn::make('thumbnail'),
                
            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->label('City')
                        ->relationship('city','name'),
                SelectFilter::make('car_service_id')
                    ->label('Service')
                    ->options(CarService::pluck('name','id'))
                    ->query(function (Builder $query , array $data) {
                        if($data['value']) {
                            $query->whereHas('storeServices', function ($query) use ($data){
                                $query->where('car_service_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarStores::route('/'),
            'create' => Pages\CreateCarStore::route('/create'),
            'edit' => Pages\EditCarStore::route('/{record}/edit'),
        ];
    }
}
