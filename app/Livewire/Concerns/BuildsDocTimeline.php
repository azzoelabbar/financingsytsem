<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Model;

trait BuildsDocTimeline
{
    /**
     * Build a document audit timeline from its own lifecycle fields — real,
     * traceable data (created / posted / allocated), never fabricated.
     *
     * @return list<array<string, mixed>>
     */
    protected function docTimeline(Model $doc, bool $withAllocations = false): array
    {
        $events = [];

        $events[] = [
            'label' => __('erp.audit.created'),
            'at' => $doc->getAttribute('created_at'),
            'tone' => 'default',
        ];

        $postedAt = $doc->getAttribute('posted_at');
        if ($postedAt !== null) {
            $events[] = [
                'label' => __('erp.audit.posted'),
                'at' => $postedAt,
                'tone' => 'success',
            ];
        }

        if ($withAllocations && $doc->relationLoaded('allocations')) {
            $allocations = $doc->getRelation('allocations');
            if (is_iterable($allocations)) {
                foreach ($allocations as $allocation) {
                    $events[] = [
                        'label' => __('erp.audit.allocated'),
                        'at' => $allocation instanceof Model
                            ? ($allocation->getAttribute('created_at') ?? $allocation->getAttribute('allocation_date'))
                            : null,
                        'tone' => 'default',
                    ];
                }
            }
        }

        return $events;
    }
}
