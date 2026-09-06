@props([
    // An Illuminate paginator instance.
    'paginator' => null,
])

{{-- The result count lives in the toolbar, so this renders links only. --}}
@if ($paginator !== null && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-4']) }}>
        {{ $paginator->links() }}
    </div>
@endif
