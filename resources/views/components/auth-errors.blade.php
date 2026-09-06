@if ($errors->any())
    <x-ui.alert variant="danger" class="mb-4" :title="__('erp.auth.errors_title')">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
