{{-- Global flash / notification region. Reads Laravel session flashes and
     Livewire-dispatched browser events. Auto-dismisses, keyboard-accessible. --}}
@php
    $flashes = [];
    foreach (['success' => 'success', 'status' => 'info', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $variant) {
        if (session()->has($key)) {
            $flashes[] = ['variant' => $variant, 'message' => session($key)];
        }
    }
@endphp

<div
    x-data="{
        toasts: @js($flashes),
        add(detail) { const id = Date.now() + Math.random(); this.toasts.push({ id, ...detail }); setTimeout(() => this.remove(id), 6000); },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
        tone(v) {
            return {
                success: 'border-[color:var(--success-color)]/25 bg-[var(--success-muted)] text-[var(--success-color)]',
                info: 'border-[color:var(--info-color)]/25 bg-[var(--info-muted)] text-[var(--info-color)]',
                warning: 'border-[color:var(--warning-color)]/25 bg-[var(--warning-muted)] text-[var(--warning-color)]',
                danger: 'border-[color:var(--danger)]/25 bg-[var(--danger-muted)] text-[var(--danger)]',
            }[v] || 'border-border bg-card text-foreground';
        }
    }"
    x-init="toasts.forEach(t => { const id = Date.now() + Math.random(); t.id = id; setTimeout(() => remove(id), 6000); })"
    @notify.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-0 top-3 z-[60] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:end-4 sm:items-end"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex w-full max-w-[calc(100vw-2rem)] items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-elevation-lg sm:max-w-sm"
            :class="tone(toast.variant)"
            role="status"
        >
            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center" x-html="{
                success: `<svg viewBox='0 0 20 20' fill='currentColor' class='h-4 w-4'><path fill-rule='evenodd' d='M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z' clip-rule='evenodd'/></svg>`,
                danger: `<svg viewBox='0 0 20 20' fill='currentColor' class='h-4 w-4'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z' clip-rule='evenodd'/></svg>`,
                warning: `<svg viewBox='0 0 20 20' fill='currentColor' class='h-4 w-4'><path fill-rule='evenodd' d='M8.5 3.6a1.7 1.7 0 013 0l6.3 11.2A1.7 1.7 0 0116.3 17H3.7a1.7 1.7 0 01-1.5-2.2L8.5 3.6zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7.5a1 1 0 100-2 1 1 0 000 2z' clip-rule='evenodd'/></svg>`,
                info: `<svg viewBox='0 0 20 20' fill='currentColor' class='h-4 w-4'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM11 9a1 1 0 10-2 0v4a1 1 0 102 0V9zm-1-4.5a1 1 0 100 2 1 1 0 000-2z' clip-rule='evenodd'/></svg>`,
            }[toast.variant] || ''"></span>
            <p class="flex-1 font-medium leading-snug" x-text="toast.message"></p>
            <button type="button" @click="remove(toast.id)" class="shrink-0 opacity-60 transition-opacity hover:opacity-100" aria-label="Dismiss">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.3 4.3a1 1 0 011.4 0L10 8.6l4.3-4.3a1 1 0 111.4 1.4L11.4 10l4.3 4.3a1 1 0 01-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 01-1.4-1.4L8.6 10 4.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    </template>
</div>
