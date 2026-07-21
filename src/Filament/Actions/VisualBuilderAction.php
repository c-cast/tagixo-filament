<?php

namespace Ccast\TagixoFilament\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

/**
 * The single, reusable "Open Visual Builder" action.
 *
 * Used identically from every builder resource — both the list Table (per row)
 * and the Edit page header — so the label, icon and colour live in one place.
 * Only the target differs, hence two factories:
 *
 *   VisualBuilderAction::make(PageResource::class)   // Filament build page
 *   VisualBuilderAction::forRoute('tagixo.forms.edit')  // standalone builder, new tab
 *
 * The record is injected by Filament in both the table and edit-page contexts.
 */
class VisualBuilderAction
{
    /**
     * Point at a resource's own "build" page (Pages, Mails, Popups, Documents).
     */
    public static function make(string $resource): Action
    {
        return static::base()
            ->url(fn (Model $record): string => $resource::getUrl('build', ['record' => $record]));
    }

    /**
     * Point at a standalone builder route opened in a new tab (Forms, Sliders).
     */
    public static function forRoute(string $routeName): Action
    {
        return static::base()
            ->url(fn (Model $record): string => route($routeName, $record->id))
            ->openUrlInNewTab();
    }

    protected static function base(): Action
    {
        return Action::make('visualBuilder')
            ->label(__('Open Visual Builder'))
            ->icon('heroicon-o-paint-brush')
            ->color('primary')
            ->tooltip(__('Open the visual builder'));
    }
}
