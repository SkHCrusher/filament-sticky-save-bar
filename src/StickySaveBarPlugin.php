<?php

namespace Cocosmos\FilamentStickySaveBar;

use Closure;
use Cocosmos\FilamentStickySaveBar\Enums\Position;
use Cocosmos\FilamentStickySaveBar\Enums\ShowOn;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

class StickySaveBarPlugin implements Plugin
{
    protected string $label = 'sticky-save-bar::sticky-save-bar.unsaved_changes';

    protected Position $position = Position::Bottom;

    protected ShowOn $showOn = ShowOn::Dirty;

    protected bool $withCancel = true;

    protected bool $withSaveAndClose = false;

    protected bool $withDiscard = false;

    protected bool|Closure $enabled = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'sticky-save-bar';
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): View => view('sticky-save-bar::sticky-save-bar', [
                'label' => __($this->label),
                'position' => $this->position,
                'showOn' => $this->showOn,
                'withCancel' => $this->withCancel,
                'withSaveAndClose' => $this->withSaveAndClose,
                'withDiscard' => $this->withDiscard,
                'enabled' => (bool) value($this->enabled),
            ]),
        );
    }

    public function boot(Panel $panel): void {}

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function position(Position $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function showOn(ShowOn $showOn): static
    {
        $this->showOn = $showOn;

        return $this;
    }

    public function withCancel(bool $condition = true): static
    {
        $this->withCancel = $condition;

        return $this;
    }

    public function withSaveAndClose(bool $condition = true): static
    {
        $this->withSaveAndClose = $condition;

        return $this;
    }

    public function withDiscard(bool $condition = true): static
    {
        $this->withDiscard = $condition;

        return $this;
    }

    public function enabled(bool|Closure $condition): static
    {
        $this->enabled = $condition;

        return $this;
    }
}
