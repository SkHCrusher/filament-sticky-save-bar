@php
use Cocosmos\FilamentStickySaveBar\Enums\Position;
@endphp

@if($enabled)
<style>
    [x-cloak] { display: none !important; }

    .ssb-bar {
        position: fixed;
        left: var(--ssb-left, 0px);
        right: var(--ssb-right, 0px);
        z-index: 60;
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
        opacity: 0;
    }
    .ssb-bar--bottom {
        bottom: 0;
        transform: translateY(0.5rem);
    }
    .ssb-bar--top {
        top: var(--ssb-top, 0px);
        transform: translateY(-0.5rem);
    }
    .ssb-bar--visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .ssb-bar__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.625rem 1.5rem;
        background: white;
        box-shadow: 0 -2px 8px 0 rgb(0 0 0 / 0.07), 0 -1px 0 0 rgb(0 0 0 / 0.04);
    }
    .ssb-bar--top .ssb-bar__inner {
        box-shadow: 0 2px 8px 0 rgb(0 0 0 / 0.07), 0 1px 0 0 rgb(0 0 0 / 0.04);
    }
    .dark .ssb-bar__inner {
        background: rgb(17 24 39);
        box-shadow: 0 -2px 8px 0 rgb(0 0 0 / 0.35), 0 -1px 0 0 rgb(255 255 255 / 0.04);
    }
    .ssb-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: rgb(107 114 128);
        white-space: nowrap;
    }
    .dark .ssb-label {
        color: rgb(156 163 175);
    }
    .ssb-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .ssb-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        padding: 0.4375rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.25rem;
        transition: background-color 0.15s, box-shadow 0.15s;
        cursor: pointer;
        border: none;
        outline: none;
        white-space: nowrap;
        font-family: inherit;
    }
    .ssb-btn:focus-visible {
        outline: 2px solid;
        outline-offset: 2px;
    }
    .ssb-btn--primary {
        background-color: var(--primary-600);
        color: white;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.1);
    }
    .ssb-btn--primary:hover {
        background-color: var(--primary-500);
    }
    .ssb-btn--primary:focus-visible {
        outline-color: var(--primary-600);
    }
    .ssb-btn--gray {
        background-color: transparent;
        color: rgb(55 65 81);
        box-shadow: inset 0 0 0 1px rgb(209 213 219);
    }
    .ssb-btn--gray:hover {
        background-color: rgb(249 250 251);
    }
    .ssb-btn--gray:focus-visible {
        outline-color: rgb(107 114 128);
    }
    .dark .ssb-btn--gray {
        color: rgb(209 213 219);
        box-shadow: inset 0 0 0 1px rgb(75 85 99);
    }
    .dark .ssb-btn--gray:hover {
        background-color: rgb(31 41 55);
    }
</style>

<div
    x-data="window.__stickySaveBar('{{ $showOn->value }}', '{{ $position->value }}')"
    x-cloak
    class="ssb-bar ssb-bar--{{ $position->value }}"
    :class="{ 'ssb-bar--visible': visible }"
>
    <div class="ssb-bar__inner">
        <span class="ssb-label">{{ $label }}</span>

        <div class="ssb-actions">
            @if($withDiscard)
            <button type="button" x-on:click="discard()" class="ssb-btn ssb-btn--gray">
                {{ __('sticky-save-bar::sticky-save-bar.discard') }}
            </button>
            @endif

            @if($withCancel)
            <button type="button" x-on:click="cancel()" class="ssb-btn ssb-btn--gray">
                {{ __('sticky-save-bar::sticky-save-bar.cancel') }}
            </button>
            @endif

            @if($withSaveAndClose)
            <button type="button" x-on:click="saveAndClose()" class="ssb-btn ssb-btn--primary">
                {{ __('sticky-save-bar::sticky-save-bar.save_and_close') }}
            </button>
            @endif

            <button type="button" x-on:click="save()" class="ssb-btn ssb-btn--primary">
                {{ __('sticky-save-bar::sticky-save-bar.save') }}
            </button>
        </div>
    </div>
</div>

<script>
window.__stickySaveBar = function (showOn, position) {
    return {
        visible: false,
        isDirty: false,
        buttonsOffscreen: false,

        _form: null,
        _component: null,
        _componentId: null,
        _initialData: null,
        _observer: null,
        _mainCtnObserver: null,
        _dirtyHandler: null,
        _submitHandler: null,
        _commitUnsub: null,
        _savePending: false,

        init() {
            // Livewire may or may not be ready when Alpine calls init().
            if (window.Livewire) {
                this._setup();
            }

            document.addEventListener('livewire:init', () => {
                if (! this._form) {
                    this._setup();
                }
            });

            document.addEventListener('livewire:navigated', () => {
                this._cleanup();
                this._setup();
            });
        },

        _setup() {
            this._form = document.querySelector('form[wire\\:submit]');

            if (! this._form) {
                return;
            }

            this._componentId = this._findFormWireId();
            this._component = this._componentId ? Livewire.find(this._componentId) : null;

            // Per-page opt-out via HasStickySaveBarDisabled trait.
            if (this._component) {
                try {
                    if (this._component.stickySaveBarEnabled === false) {
                        return;
                    }
                } catch {
                    // Property does not exist — proceed.
                }
            }

            // Snapshot the initial data so we can detect a true revert-to-clean state.
            if (this._componentId) {
                let snapshotEl = document.querySelector('[wire\\:id="' + this._componentId + '"]');

                if (snapshotEl) {
                    try {
                        let snap = JSON.parse(snapshotEl.getAttribute('wire:snapshot') || '{}');
                        this._initialData = JSON.stringify(snap.data ?? {});
                    } catch {}
                }
            }

            // Track dirty state: any input or change inside the form marks it dirty.
            this._dirtyHandler = (e) => {
                if (e.target.closest('form[wire\\:submit]')) {
                    this.isDirty = true;
                    this._updateVisibility();
                }
            };

            document.addEventListener('input', this._dirtyHandler);
            document.addEventListener('change', this._dirtyHandler);

            // Detect form submit so we can clear dirty state after the commit succeeds.
            this._submitHandler = () => {
                this._savePending = true;
            };

            this._form.addEventListener('submit', this._submitHandler);

            if (this._componentId) {
                this._commitUnsub = Livewire.hook('commit', ({ component, succeed }) => {
                    if (component.id !== this._componentId) {
                        return;
                    }

                    if (this._savePending) {
                        succeed(() => {
                            this._savePending = false;
                            this.isDirty = false;
                            this._updateVisibility();
                        });
                    } else {
                        // Optimistically mark dirty, then correct once we have the updated snapshot.
                        this.isDirty = true;
                        this._updateVisibility();

                        succeed(() => {
                            if (this._initialData !== null) {
                                this.isDirty = JSON.stringify(component.snapshot.data ?? {}) !== this._initialData;
                                this._updateVisibility();
                            }
                        });
                    }
                });
            }

            this._setupObserver();
            this._setupMainCtnObserver();
        },

        _setupMainCtnObserver() {
            let mainCtn = document.querySelector('.fi-main-ctn');

            if (! mainCtn) {
                return;
            }

            this._updateBarOffset(mainCtn);

            this._mainCtnObserver = new ResizeObserver(() => {
                this._updateBarOffset(mainCtn);
            });

            this._mainCtnObserver.observe(mainCtn);
        },

        _updateBarOffset(mainCtn) {
            let rect = mainCtn.getBoundingClientRect();
            this.$el.style.setProperty('--ssb-left', rect.left + 'px');
            this.$el.style.setProperty('--ssb-right', (window.innerWidth - rect.right) + 'px');
            this.$el.style.setProperty('--ssb-top', rect.top + 'px');
        },

        _findFormWireId() {
            const form = document.querySelector('form[wire\\:submit]');

            if (! form) {
                return null;
            }

            // Walk up the DOM to find the nearest Livewire component root (wire:id).
            let el = form.parentElement;

            while (el) {
                const wireId = el.getAttribute('wire:id');

                if (wireId) {
                    return wireId;
                }

                el = el.parentElement;
            }

            return null;
        },

        _setupObserver() {
            // Filament v5 renders the schema actions container with class fi-sc-actions.
            const target =
                document.querySelector('.fi-sc-actions') ||
                document.querySelector('form[wire\\:submit] button[type="submit"]');

            if (! target) {
                // No anchored target — show whenever dirty.
                this.buttonsOffscreen = true;
                this._updateVisibility();
                return;
            }

            // Seed initial state synchronously before the observer fires.
            const rect = target.getBoundingClientRect();
            this.buttonsOffscreen = rect.bottom > window.innerHeight || rect.top < 0;
            this._updateVisibility();

            this._observer = new IntersectionObserver(
                ([entry]) => {
                    this.buttonsOffscreen = ! entry.isIntersecting;
                    this._updateVisibility();
                },
                { threshold: 0.1 }
            );

            this._observer.observe(target);
        },

        _updateVisibility() {
            const shouldShow = showOn === 'always'
                ? this.buttonsOffscreen
                : showOn === 'dirty-always'
                    ? this.isDirty
                    : this.buttonsOffscreen && this.isDirty;

            // Never overlay an open modal.
            const modalOpen = !! document.querySelector('.fi-modal-window');

            this.visible = shouldShow && ! modalOpen;
        },

        _cleanup() {
            this._observer?.disconnect();
            this._observer = null;

            this._mainCtnObserver?.disconnect();
            this._mainCtnObserver = null;

            this._commitUnsub?.();
            this._commitUnsub = null;

            if (this._dirtyHandler) {
                document.removeEventListener('input', this._dirtyHandler);
                document.removeEventListener('change', this._dirtyHandler);
                this._dirtyHandler = null;
            }

            if (this._form && this._submitHandler) {
                this._form.removeEventListener('submit', this._submitHandler);
                this._submitHandler = null;
            }

            this._form = null;
            this._component = null;
            this._componentId = null;
            this._initialData = null;
            this._savePending = false;
            this.visible = false;
            this.isDirty = false;
            this.buttonsOffscreen = false;
        },

        save() {
            this._form?.requestSubmit();
        },

        saveAndClose() {
            if (! this._form) {
                return;
            }

            const componentId = this._componentId;

            const unsub = Livewire.hook('commit', ({ component, succeed }) => {
                if (component.id !== componentId) {
                    return;
                }

                succeed(() => {
                    unsub?.();
                    window.history.back();
                });
            });

            this._form.requestSubmit();
        },

        cancel() {
            window.history.back();
        },

        discard() {
            window.location.reload();
        },
    };
};
</script>
@endif
