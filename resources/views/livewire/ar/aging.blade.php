<div>
    @include('livewire.partials.aging-report', [
        'report' => $report,
        'bucketKeys' => $bucketKeys,
        'title' => __('erp.nav.ar_aging'),
        'hint' => __('erp.aging_page.ar_hint'),
        'moduleLabel' => __('erp.nav.ar'),
        'totalLabel' => __('erp.aging_page.ar_total'),
        'statementRoute' => route('ar.statement'),
    ])
</div>
