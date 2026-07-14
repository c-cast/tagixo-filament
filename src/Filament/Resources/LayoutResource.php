<?php

namespace Ccast\TagixoFilament\Filament\Resources;

use Ccast\Tagixo\Models\Layout;
use Ccast\TagixoFilament\Filament\Resources\Layouts\Pages\BuildLayout;
use Filament\Resources\Resource;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Layout');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Layouts');
    }

    public static function getPages(): array
    {
        return [
            'build' => BuildLayout::route('/{record}/build/{section?}'),
        ];
    }
}
