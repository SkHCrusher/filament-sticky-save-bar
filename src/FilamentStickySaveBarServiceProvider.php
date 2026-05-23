<?php

namespace Cocosmos\FilamentStickySaveBar;

use Illuminate\Support\ServiceProvider;

class FilamentStickySaveBarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'sticky-save-bar');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sticky-save-bar');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/sticky-save-bar'),
        ], 'sticky-save-bar-translations');
    }
}
