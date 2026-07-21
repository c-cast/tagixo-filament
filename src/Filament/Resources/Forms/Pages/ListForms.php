<?php

namespace Ccast\TagixoFilament\Filament\Resources\Forms\Pages;

use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListForms extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createForm')
                ->label(__('Create new form'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => route('tagixo.forms.new'))
                ->openUrlInNewTab()
                ->tooltip(__('Opens the visual form builder in a new tab and creates a draft')),
        ];
    }
}
