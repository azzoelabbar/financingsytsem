@props([
    'status' => null,
    'size' => 'sm',
])

@php
    use App\Enums\Ar\DocumentStatus;

    // Resolve an enum from a raw string when needed so any document type can pass either.
    $enum = $status instanceof DocumentStatus
        ? $status
        : DocumentStatus::tryFrom(is_string($status) ? $status : '');

    if ($enum !== null) {
        $variant = $enum->badgeVariant();
        $label = $enum->label();
    } else {
        // Fallback for statuses outside the AR lifecycle (e.g. approved/paid strings elsewhere).
        $raw = is_string($status) ? $status : (string) ($status?->value ?? $status);
        $variant = match ($raw) {
            'posted', 'paid', 'approved', 'reconciled', 'validated', 'locked', 'active', 'completed', 'cleared', 'reimbursed', 'balanced', 'closed' => 'success',
            'soft_closed', 'pending', 'submitted', 'partially_paid', 'partially_allocated', 'partially_reimbursed', 'in_progress', 'unreconciled' => 'warning',
            'reversed', 'rejected', 'void', 'disposed', 'unbalanced', 'hard_closed', 'overdue' => 'danger',
            'draft', 'open' => 'outline',
            default => 'default',
        };
        $label = $raw !== '' ? __('erp.doc_status.'.$raw) : '-';
    }
@endphp

<x-ui.badge :variant="$variant" :size="$size" {{ $attributes }}>
    {{ $label }}
</x-ui.badge>
