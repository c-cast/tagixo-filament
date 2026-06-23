<?php

use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Ccast\TagixoFilament\Filament\Resources\MediaResource;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Resources (Builders)
    |--------------------------------------------------------------------------
    |
    | Each entry below registers one Tagixo builder as a Filament resource
    | (its own navigation item + list/create/edit screens).
    |
    | To HIDE a builder from the panel, simply COMMENT OUT its line. The
    | underlying feature keeps working everywhere else (rendering, the visual
    | builder, the database) — you only remove its dedicated admin section.
    |
    | The order of this array is the order resources are registered.
    |
    */
    'resources' => [

        PageResource::class,
        LayoutResource::class,
        MenuResource::class,
        FormResource::class,
        SliderResource::class,
        MailResource::class,

        /*
        | Optional resources — disabled by default.
        |
        | Uncomment to enable, or use the equivalent ->withMediaGallery()
        | plugin method. Enabling both is safe (registered only once).
        */
        // MediaResource::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Form Mapper Extensions
    |--------------------------------------------------------------------------
    |
    | Map Tagixo runtime schema field/wrapper types to custom Filament
    | mapper classes.
    |
    | Example:
    | 'fields' => [
    |     'money' => \App\VisualBuilder\Form\MoneyFieldMapper::class,
    | ],
    |
    */
    'form_mapper' => [
        'fields' => [],
        'wrappers' => [],
    ],
];
