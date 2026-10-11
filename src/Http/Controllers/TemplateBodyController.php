<?php

namespace Tagixo\Filament\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Tagixo\Filament\Pages\ThemeBuilder;
use Tagixo\Filament\Resources\LayoutResource;
use Tagixo\Filament\Resources\PageResource;
use Tagixo\PageBuilder\Builder\LayoutFrameBuilder;
use Tagixo\PageBuilder\Facades\PageBuilder;

/**
 * Opens the body of a template scoped to a model. The page it edits — that
 * model's archive or single — is created here if it is missing, which is the lazy
 * step that lets a template exist before its body does. Then the browser goes to
 * that page's builder.
 */
class TemplateBodyController extends Controller
{
    public function __invoke(int|string $layout): RedirectResponse
    {
        $record = LayoutResource::getModel()::query()->findOrFail($layout);
        LayoutResource::builderType()->authorize('update', $record);

        $target = LayoutFrameBuilder::modelPageTarget(is_array($record->conditions) ? $record->conditions : []);

        if ($target === null) {
            // The body of an ordinary template is each page's own.
            return redirect()->to(ThemeBuilder::getUrl());
        }

        [$modelKey, $templateType] = $target;
        $page = PageBuilder::ensureRoutePagesForModel($modelKey)[$templateType] ?? null;

        return redirect()->to(
            $page !== null ? PageResource::builderUrl($page) : ThemeBuilder::getUrl(),
        );
    }
}
