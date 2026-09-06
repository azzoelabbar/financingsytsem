<div class="mizan-document-rail">
    <ol class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs" aria-label="{{ __('erp.mizan.document_journey') }}">
        @foreach (['prepare', 'review', 'post'] as $step)
            <li class="flex items-center gap-2 {{ $loop->first ? 'font-semibold text-brand' : 'text-muted-foreground' }}" @if($loop->first) aria-current="step" @endif>
                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $loop->first ? 'bg-brand text-white' : 'border border-border-strong bg-white' }}" dir="ltr">{{ $loop->iteration }}</span>
                {{ __('erp.mizan.'.$step) }}
                @unless($loop->last)<span class="ms-3 hidden h-px w-8 bg-border sm:block" aria-hidden="true"></span>@endunless
            </li>
        @endforeach
    </ol>
</div>
