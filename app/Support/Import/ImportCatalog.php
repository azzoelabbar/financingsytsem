<?php

declare(strict_types=1);

namespace App\Support\Import;

final class ImportCatalog
{
    /** @return array<string, list<string>> */
    public static function schemas(): array
    {
        return [
            'customers' => ['كود العميل', 'اسم العميل', 'الهاتف', 'الرصيد الافتتاحي'],
            'suppliers' => ['كود المورد', 'اسم المورد', 'الهاتف', 'الرصيد الافتتاحي'],
            'sales' => ['رقم الفاتورة', 'التاريخ', 'كود العميل', 'اسم العميل', 'كود الصنف', 'اسم الصنف', 'الكمية', 'سعر البيع', 'الإجمالي', 'نوع السداد', 'المقبوض', 'المتبقي', 'تكلفة الوحدة', 'تكلفة المبيعات', 'الربح'],
            'purchases' => ['رقم الفاتورة', 'التاريخ', 'كود المورد', 'اسم المورد', 'كود الصنف', 'اسم الصنف', 'الكمية', 'سعر التكلفة', 'الإجمالي', 'نوع السداد', 'المدفوع', 'المتبقي'],
            'receipts' => ['رقم السند', 'التاريخ', 'كود العميل', 'اسم العميل', 'البيان', 'المبلغ', 'طريقة القبض'],
            'payments' => ['رقم السند', 'التاريخ', 'كود المورد', 'اسم المورد', 'البيان', 'المبلغ', 'طريقة الدفع'],
            'expenses' => ['رقم المصروف', 'التاريخ', 'البيان', 'التصنيف', 'المبلغ', 'طريقة الدفع'],
            'customer-report' => ['كود العميل', 'اسم العميل', 'رصيد افتتاحي', 'ذمم المبيعات', 'المقبوضات اللاحقة', 'الرصيد المستحق'],
            'supplier-report' => ['كود المورد', 'اسم المورد', 'رصيد افتتاحي', 'ذمم المشتريات', 'المدفوعات اللاحقة', 'الرصيد المستحق'],
            'inventory-report' => ['كود الصنف', 'اسم الصنف', 'رصيد افتتاحي', 'إجمالي المشتريات', 'إجمالي المبيعات', 'الرصيد الحالي', 'قيمة المخزون', 'الحد الأدنى', 'الحالة'],
            'items' => ['كود الصنف', 'اسم الصنف'],
        ];
    }

    public static function reference(string $kind): bool
    {
        return str_ends_with($kind, '-report');
    }

    /** @param list<string> $cells */
    public static function hasData(string $kind, array $cells): bool
    {
        if ($kind === 'expenses') {
            // The supplied template pre-fills numbers and methods for 500 rows.
            return array_filter(array_slice($cells, 1, 4), fn (string $v): bool => $v !== '') !== [];
        }

        return array_filter($cells, fn (string $v): bool => $v !== '' && $v !== '0') !== [];
    }

    public static function permission(string $kind): string
    {
        return match ($kind) {
            'customers', 'sales', 'receipts' => 'ar.write',
            'suppliers', 'purchases', 'payments' => 'ap.write',
            'expenses' => 'expenses.write',
            'items' => 'gl.write',
            default => 'reports.read',
        };
    }
}
