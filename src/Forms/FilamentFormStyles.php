<?php

namespace Ccast\TagixoFilament\Forms;

use Ccast\Tagixo\Models\FormSchema;
use Ccast\Tagixo\Support\FormElementsCssGenerator;

/**
 * Generates scoped CSS from form builder element styles for fields rendered
 * inside Filament. Inject the returned string via a <style> tag in the
 * resource/page view.
 */
class FilamentFormStyles
{
    /**
     * Filament v3 field wrapper DOM structure:
     *   [data-tgx-field="name"]  (on the fi-fo-field-wrp or equivalent)
     *     label  (.fi-fo-label or fi-label)
     *     input / .fi-input / textarea / select
     *     .fi-fo-helper-text
     */
    protected const SELECTOR_MAP = [
        'label'          => 'label',
        'input'          => 'input, textarea, select, .fi-input',
        'helper'         => '.fi-fo-helper-text',
        'placeholder'    => 'input::placeholder, textarea::placeholder',
        'checkbox_input' => 'input[type="checkbox"]',
        'radio_input'    => 'input[type="radio"]',
        'select_input'   => 'select, .fi-input',
    ];

    public static function from(string $formSlug): string
    {
        $form = FormSchema::where('slug', $formSlug)->first();

        return $form ? self::resolve($form) : '';
    }

    public static function forForm(int|string $formId): string
    {
        $form = FormSchema::find($formId);

        return $form ? self::resolve($form) : '';
    }

    /**
     * Returns a <script> snippet that injects the form's element styles into
     * <head> via JavaScript. Idempotent: skips if the style tag already exists.
     *
     * Use this in Filament render hooks or view composers so the CSS survives
     * Livewire re-renders (the <head> is outside the morphed area).
     */
    public static function scriptFrom(string $formSlug): string
    {
        return static::buildScript($formSlug, static::from($formSlug));
    }

    public static function scriptForForm(int|string $formId): string
    {
        return static::buildScript((string) $formId, static::forForm($formId));
    }

    private static function buildScript(string $key, string $css): string
    {
        if ($css === '') {
            return '';
        }
        $styleId    = json_encode('tgx-form-styles-' . $key);
        $encodedCss = json_encode($css, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return "<script>(function(){var id={$styleId};if(document.getElementById(id))return;var s=document.createElement('style');s.id=id;s.textContent={$encodedCss};document.head.appendChild(s);})();</script>";
    }

    private static function resolve(FormSchema $form): string
    {
        return FormElementsCssGenerator::forForm($form, static::SELECTOR_MAP);
    }
}
