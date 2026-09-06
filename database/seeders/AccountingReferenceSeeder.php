<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accounting\Currency;
use Illuminate\Database\Seeder;

/**
 * Reference data every install needs before a company can be set up.
 *
 * Safe to run on a live install at any time:
 *
 *     php artisan db:seed --class=AccountingReferenceSeeder
 *
 * Existing rows keep their `is_active` flag, so a currency an operator switched
 * off is never switched back on by a re-run.
 */
class AccountingReferenceSeeder extends Seeder
{
    /**
     * ISO 4217 codes with their real minor-unit counts — LYD, TND, KWD, BHD,
     * OMR, JOD and IQD carry three decimals, JPY none.
     *
     * @var list<array{code:string, name_ar:string, name_en:string, symbol:string, decimal_places:int}>
     */
    private const CURRENCIES = [
        // Libya and the Maghreb
        ['code' => 'LYD', 'name_ar' => 'دينار ليبي', 'name_en' => 'Libyan Dinar', 'symbol' => 'ل.د', 'decimal_places' => 3],
        ['code' => 'TND', 'name_ar' => 'دينار تونسي', 'name_en' => 'Tunisian Dinar', 'symbol' => 'د.ت', 'decimal_places' => 3],
        ['code' => 'DZD', 'name_ar' => 'دينار جزائري', 'name_en' => 'Algerian Dinar', 'symbol' => 'د.ج', 'decimal_places' => 2],
        ['code' => 'MAD', 'name_ar' => 'درهم مغربي', 'name_en' => 'Moroccan Dirham', 'symbol' => 'د.م.', 'decimal_places' => 2],
        ['code' => 'EGP', 'name_ar' => 'جنيه مصري', 'name_en' => 'Egyptian Pound', 'symbol' => 'ج.م', 'decimal_places' => 2],

        // Gulf and Levant
        ['code' => 'SAR', 'name_ar' => 'ريال سعودي', 'name_en' => 'Saudi Riyal', 'symbol' => 'ر.س', 'decimal_places' => 2],
        ['code' => 'AED', 'name_ar' => 'درهم إماراتي', 'name_en' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimal_places' => 2],
        ['code' => 'QAR', 'name_ar' => 'ريال قطري', 'name_en' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'decimal_places' => 2],
        ['code' => 'KWD', 'name_ar' => 'دينار كويتي', 'name_en' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'decimal_places' => 3],
        ['code' => 'BHD', 'name_ar' => 'دينار بحريني', 'name_en' => 'Bahraini Dinar', 'symbol' => 'د.ب', 'decimal_places' => 3],
        ['code' => 'OMR', 'name_ar' => 'ريال عماني', 'name_en' => 'Omani Rial', 'symbol' => 'ر.ع.', 'decimal_places' => 3],
        ['code' => 'JOD', 'name_ar' => 'دينار أردني', 'name_en' => 'Jordanian Dinar', 'symbol' => 'د.ا', 'decimal_places' => 3],
        ['code' => 'IQD', 'name_ar' => 'دينار عراقي', 'name_en' => 'Iraqi Dinar', 'symbol' => 'د.ع', 'decimal_places' => 3],
        ['code' => 'LBP', 'name_ar' => 'ليرة لبنانية', 'name_en' => 'Lebanese Pound', 'symbol' => 'ل.ل', 'decimal_places' => 2],

        // Majors used for reporting and settlement
        ['code' => 'USD', 'name_ar' => 'دولار أمريكي', 'name_en' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2],
        ['code' => 'EUR', 'name_ar' => 'يورو', 'name_en' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
        ['code' => 'GBP', 'name_ar' => 'جنيه إسترليني', 'name_en' => 'Pound Sterling', 'symbol' => '£', 'decimal_places' => 2],
        ['code' => 'CHF', 'name_ar' => 'فرنك سويسري', 'name_en' => 'Swiss Franc', 'symbol' => 'CHF', 'decimal_places' => 2],
        ['code' => 'TRY', 'name_ar' => 'ليرة تركية', 'name_en' => 'Turkish Lira', 'symbol' => '₺', 'decimal_places' => 2],
        ['code' => 'CNY', 'name_ar' => 'يوان صيني', 'name_en' => 'Chinese Yuan', 'symbol' => '¥', 'decimal_places' => 2],
        ['code' => 'JPY', 'name_ar' => 'ين ياباني', 'name_en' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0],
        ['code' => 'INR', 'name_ar' => 'روبية هندية', 'name_en' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2],
        ['code' => 'CAD', 'name_ar' => 'دولار كندي', 'name_en' => 'Canadian Dollar', 'symbol' => 'C$', 'decimal_places' => 2],
        ['code' => 'AUD', 'name_ar' => 'دولار أسترالي', 'name_en' => 'Australian Dollar', 'symbol' => 'A$', 'decimal_places' => 2],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $attributes) {
            $currency = Currency::query()->firstOrNew(['code' => $attributes['code']]);

            $currency->fill($attributes);

            if (! $currency->exists) {
                $currency->is_active = true;
            }

            $currency->save();
        }
    }
}
