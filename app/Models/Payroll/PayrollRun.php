<?php

declare(strict_types=1);

namespace App\Models\Payroll;

use App\Models\Accounting\AccountingBook;
use App\Models\Accounting\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'run_date' => 'date',
        'gross' => 'decimal:6',
        'employer_ss' => 'decimal:6',
        'paye' => 'decimal:6',
        'employee_ss' => 'decimal:6',
        'net' => 'decimal:6',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<AccountingBook, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(AccountingBook::class, 'book_id');
    }
}
