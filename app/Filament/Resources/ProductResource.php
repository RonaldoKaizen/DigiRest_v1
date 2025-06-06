<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                        Section::make('Información del Producto')->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre del Producto')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled() 
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        MarkdownEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->label('Descripción')
                            ->fileAttachmentsDirectory('products'),
                        ])->columns(2),

                    Section::make('Imágenes')->schema([
                        FileUpload::make('images')
                            ->label('Imágenes del Producto')
                            ->image()
                            ->multiple()
                            ->required()
                            ->maxFiles(5)
                            ->directory('products'),
                        ])
                    ])->columnSpan(2),
                    Group::make()->Schema([
                        Section::make('Precio')->schema([
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->label('Precio')
                                ->prefix('S/.'),
                        ]),
                        Section::make('Asociaciones')->schema([
                            Select::make('category_id')
                                ->label('Categoría')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),
                        Section::make('Estado del Producto')->schema([
                            Toggle::make('is_active')
                                ->label('Activo')
                                ->default(true)
                                ->required(),
                            Toggle::make('in_stock')
                                ->label('En Stock')
                                ->default(true)
                                ->required(),
                            /* Toggle::make('is_featured')
                                ->label('Destacado')
                                ->default(false),
                            Toggle::make('on_sale')
                                ->label('En Oferta')
                                ->default(false), */
                            /* Select::make('is_active')
                                ->label('Activo')
                                ->options([
                                    true => 'Sí',
                                    false => 'No',
                                ])
                                ->default(true)
                                ->required(),
                            Select::make('in_stock')
                                ->label('En Stock')
                                ->options([
                                    true => 'Sí',
                                    false => 'No',
                                ])
                                ->default(true),
                            Select::make('is_featured')
                                ->label('Destacado')
                                ->options([
                                    true => 'Sí',
                                    false => 'No',
                                ])
                                ->default(false),
                            Select::make('on_sale')
                                ->label('En Oferta')
                                ->options([
                                    true => 'Sí',
                                    false => 'No',
                                ])
                                ->default(false), */
                        ]),
                    ])->columnSpan(1)

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                /* Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(), */
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->searchable() // searchable sirve para que el campo sea buscable en la tabla
                    ->money('PEN', true) // true para mostrar el símbolo de la moneda
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->label('En Stock')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                

                /* Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('on_sale')  
                    ->label('En Oferta')
                    ->boolean()
                    ->sortable(), */
                /* Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), */
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable() 
                    ->preload(), 
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                /* Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]), */
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
