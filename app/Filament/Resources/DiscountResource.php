<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = "heroicon-o-currency-euro";

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = "aanbieding";

    protected static ?string $pluralModelLabel = "aanbiedingen";

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\Repeater::make("tiers")
                ->hiddenLabel()
                ->itemLabel("Aanbieding")
                ->label("Aanbiedingen")
                ->columnSpan(2)
                ->relationship("tiers")
                ->schema([
                    Forms\Components\TextInput::make("description")->maxLength(
                        255
                    ),
                    Forms\Components\Split::make([
                        Forms\Components\TextInput::make("amount")->numeric(),
                        Forms\Components\Select::make("unit")->options([
                            "money" => "money",
                            "percentage" => "percentage",
                        ]),
                        Forms\Components\TextInput::make("size")->numeric(),
                    ]),
                ]),
            Forms\Components\Section::make()
                ->columnSpan(1)
                ->extraAttributes(["class" => "sticky top-0"])
                ->schema([
                    Forms\Components\DatePicker::make("start")->required(),
                    Forms\Components\DatePicker::make("end")->required(),
                    Forms\Components\Select::make("product_store_id")
                        ->label("Product in winkel")
                        ->allowHtml()
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(
                            fn(string $search): array => Product::search(
                                $search
                            )
                                ->take(10)
                                ->get()
                                ->filter(
                                    fn($product) => $product->stores?->count() >
                                        0
                                )
                                ->mapWithKeys(
                                    fn($product) => [
                                        $product->stores->first()->pivot
                                            ->id => self::getCleanOptionString(
                                            $product
                                        ),
                                    ]
                                )
                                ->toArray()
                        )
                        ->getOptionLabelUsing(function ($value): string {
                            $product = ProductStore::where("id", $value)
                                ->with("product")
                                ->first()->product;

                            return self::getCleanOptionString($product);
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("start")->date()->sortable(),
                Tables\Columns\TextColumn::make("end")->date()->sortable(),
                Tables\Columns\TextColumn::make("store.name")->sortable(),
                Tables\Columns\TextColumn::make("product.name")->sortable(),
                Tables\Columns\TextColumn::make(
                    "tiers.description"
                )->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([Tables\Actions\EditAction::make()]);
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
            "index" => Pages\ListDiscounts::route("/"),
            "create" => Pages\CreateDiscount::route("/create"),
            "edit" => Pages\EditDiscount::route("/{record}/edit"),
        ];
    }

    public static function getCleanOptionString($product): string
    {
        return new HtmlString(
            Blade::render(
                <<<'blade'
<div class="flex rounded-md relative">
    <div class="flex">
        <div class="px-2 py-3">
            <div class="h-10 w-10">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" role="img" class="h-full w-full object-cover" />
            </div>
        </div>

        <div class="flex flex-col justify-center pl-3 py-2">
            <p class="text-sm font-bold pb-1">{{ $product->name }}</p>
            <div class="flex flex-col items-start">
                <p class="text-xs leading-5">{{ $product->stores?->pluck("name")->join(", ") }}</p>
            </div>
        </div>
    </div>
</div>
blade
                ,
                ["product" => $product]
            )
        );
    }
}
