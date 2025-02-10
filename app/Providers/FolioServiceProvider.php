<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Folio::path(resource_path('views/pages'))->middleware([
            '*' => [
                //
            ],
        ]);

        Folio::renderUsing(function ($request, $matchedView) {
            $matchedViewPath = str_replace('/vendor/devdojo/auth/', '/', $matchedView->path);
            if (! File::exists($matchedViewPath)) {
                $matchedViewPath = $matchedView->path;
            }

            $view = View::file($matchedViewPath, $matchedView->data);

            return Route::toResponse($request, app()->call(
                $matchedView->renderUsing(),
                ['view' => $view, ...$view->getData()]
            ) ?? $view);
        });
    }
}
