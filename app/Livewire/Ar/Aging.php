<?php

declare(strict_types=1);

namespace App\Livewire\Ar;

use App\Application\Api\Ar\ArApplicationService;
use App\Livewire\Concerns\InteractsWithAccountingContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.erp')]
class Aging extends Component
{
    use InteractsWithAccountingContext;

    public string $asOf = '';

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    public function render(): View
    {
        $company = $this->company();
        $book = $this->book();
        $report = $company && $book
            ? app(ArApplicationService::class)->aging($company, Carbon::parse($this->asOf), book: $book)
            : ['buckets' => [], 'total' => '0'];

        return view('livewire.ar.aging', [
            'report' => $report,
            'bucketKeys' => ['current', '1_30', '31_60', '61_90', '90_plus', 'credits'],
        ]);
    }
}
