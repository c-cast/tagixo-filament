<?php

namespace Ccast\TagixoFilament\FormBuilder\Modules;

use Ccast\Tagixo\Core\ModuleDefinition;
use Ccast\Tagixo\Core\Props\EditorProp;
use Ccast\Tagixo\Core\Props\TextProp;
use Ccast\Tagixo\Core\Props\ToggleProp;
use Ccast\Tagixo\FormBuilder\FormModule;

class ToggleModule extends FormModule
{
    public static function getLabel(): string
    {
        return __('Toggle');
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-power';
    }

    public static function getFieldType(): string
    {
        return 'toggle';
    }

    public static function getTypeId(): string
    {
        return 'toggle';
    }

    public static function getCanvasView(): string
    {
        return 'tagixo-filament::partials.form-embed-toggle';
    }

    public static function define(): ModuleDefinition
    {
        return ModuleDefinition::make()
            ->tab('content', __('Content'), [
                TextProp::make('name')
                    ->setLabel(__('Field Name'))
                    ->placeholder('e.g. is_active'),
                EditorProp::make('label')
                    ->setLabel(__('Label'))
                    ->placeholder(__('Field label')),
                TextProp::make('helper_text')
                    ->setLabel(__('Helper Text'))
                    ->placeholder(__('Help text below the field')),
                ToggleProp::make('default_on')
                    ->setLabel(__('On by Default'))
                    ->default(false),
                ToggleProp::make('inline')
                    ->setLabel(__('Inline label'))
                    ->default(true),
                static::columnSpanProp(),
            ])
            ->tab('validation', __('Validation'), static::commonValidationProps())
            ->tab('layout', __('Layout'), [
                static::filamentSlotsProp(),
            ])
            ->tab('reactivity', __('Reactivity'), static::reactivityProps())
            ->tab('visibility', __('Visibility'), static::visibilityProps())
            ->subElements(...static::checkboxElements())
            ->design('typography', 'spacing')
            ->contexts(['form'])
            ->meta(['isFormField' => true, 'mainDesignElement' => 'label'])
            ->canvas('cards');
    }

    public static function toSchema(array $props): array
    {
        $content = static::extractContent($props);
        $schema  = parent::toSchema($props);

        if (! empty($content['default_on'])) {
            $schema['default_checked'] = true;
        }

        if (array_key_exists('inline', $content)) {
            $schema['inline'] = (bool) $content['inline'];
        }

        return $schema;
    }
}
