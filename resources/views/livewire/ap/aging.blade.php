<div>
    @include('livewire.partials.aging-report', [
        'report' => $report,
        'bucketKeys' => $bucketKeys,
        'title' => __('erp.nav.ap_aging'),
        'hint' => __('erp.aging_page.ap_hint'),
        'moduleLabel' => __('erp.nav.ap'),
        'totalLabel' => __('erp.aging_page.ap_total'),
        'statementRoute' => route('ap.statement'),
    ])
</div>
