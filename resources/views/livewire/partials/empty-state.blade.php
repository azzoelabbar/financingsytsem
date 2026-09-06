{{-- Compatibility shim: older pages include this partial. New pages use <x-ui.empty-state> directly. --}}
<x-ui.empty-state :title="$title ?? null" :message="$message ?? null" />
