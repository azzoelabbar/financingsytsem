# استراتيجية الاختبار المحاسبية الشاملة (Accounting Test Strategy)

الهدف: اختبار **صحة المحاسبة نفسها**، لا HTTP/API فقط. كل معاملة تُختبر على مستوى القيد الناتج، والأرصدة، والثوابت المحاسبية. **أي اختلاف يظهر كـ Exception** (Integrity Engine).

## 1. طبقات الاختبار (Test Pyramid المحاسبي)
| الطبقة | تختبر | أدوات |
|---|---|---|
| **Unit** | Decimal (bcmath، لا floats) · Value Objects · Enums · منطق القاعدة الخالص | Pest |
| **Integration** | Posting/Reversal/Adjustment · Period lock · Currency · Dimensions | Pest + DB (sqlite :memory:) |
| **Accounting Correctness** | الثوابت: `Debits=Credits` · `Assets=Liab+Equity` | Integrity Engine |
| **Reconciliation** | Subledger = Control · Bank = Ledger · Aging = Control · Valuation = GL | Integrity Engine |
| **Golden (Rules)** | مستند مصدري → قيد متوقّع **بالضبط** (Dr/Cr وأكواد الحسابات) | Golden fixtures |
| **Security** | ترحيل/اعتماد/إعادة فتح/تعديل غير مصرّح | Policy/SoD tests |
| **Regression** | كل خطأ محاسبي مُصلَح يُثبَّت باختبار | Pest |

**قاعدة:** لا يُعتبر أي Module منجزاً (بند 73) حتى تُختبر: المنطق المحاسبي · القيد · العكس · قفل الفترة · العملة · الضريبة · التقارير · **الحالات الحدّية** · **الأرصدة متطابقة (Reconciled)**.

## 2. كتالوج الثوابت (Integrity Invariants) — تُفرض كـ Exceptions

| # | الثابت | متى يُفحص | مصدر البيانات | الحالة |
|---|---|---|---|---|
| INV-1 | **Total Debits = Total Credits** (لكل قيد ولكل الدفتر) | كل ترحيل + عند الطلب | GL | ✅ مُنفّذ |
| INV-2 | **Assets = Liabilities + Equity** (+ نتيجة الفترة) | عند الطلب/الإقفال | GL | ✅ مُنفّذ |
| INV-3 | لا قيد غير متوازن مخزّن | فحص سلامة دوري | journals | ✅ مُنفّذ |
| INV-4 | لا ترحيل على حساب تجميعي/نظامي | كل ترحيل | accounts | ✅ مُنفّذ (Engine) |
| INV-5 | **Subledger = GL Control** (AR/AP/Inventory/FA) | إقفال/دوري | subledger vs control | ◼ إطار جاهز (ينشط مع الموديول) |
| INV-6 | **Bank Reconciliation = Bank Ledger** | بعد التسوية | bank stmt vs ledger | ◼ إطار جاهز |
| INV-7 | **AR Aging = AR Control** | إقفال | AR aging vs 110201 | ◼ إطار جاهز |
| INV-8 | **AP Aging = AP Control** | إقفال | AP aging vs 210101 | ◼ إطار جاهز |
| INV-9 | **Inventory Valuation = Inventory GL** | إقفال | valuation vs 1103 | ◼ إطار جاهز |
| INV-10 | **Fixed Asset Register = FA GL** | إقفال | register vs 1201 | ◼ إطار جاهز |
| INV-11 | لا رصيد في حساب وسيط/معلّق بعد الإقفال | إقفال | suspense/clearing | ◼ إطار جاهز |

`IntegrityService::assert()` يشغّل كل الفحوص المنطبقة، وأي إخفاق → `IntegrityViolationException` يحمل قائمة الفروق (expected/actual/difference). لا فرق «يُبتلع».

## 3. مصفوفة اختبارات الموديولات (Test Matrix)

> لكل حالة: **الفعل → القيد المتوقّع (Dr/Cr) → الأرصدة → الثابت المفحوص**. الحالة: ✅ مُنفّذ · ◼ جاهز للتفعيل مع الموديول.

### Core Accounting ✅
- **Debit=Credit:** قيد متوازن يُقبل؛ غير متوازن يُرفض (`UnbalancedJournalException`). → INV-1.
- **Posting:** الحالة POSTED، ترقيم، غير قابل للتعديل.
- **Reversal:** مرآة (Dr↔Cr)، الأصل REVERSED، صافي الأثر صفر، كلاهما في الدفتر.
- **Adjustment:** قيد تصحيحي جديد (لا Delete).
- **Period Lock:** ترحيل على فترة مغلقة يُرفض؛ Soft يتطلب صلاحية.
- **Opening Balance:** قيد افتتاحي متوازن ينقل الأرصدة؛ TB بعده = قبله. → INV-2.
- **Closing:** إقفال الإيراد/المصروف إلى الأرباح والخسائر ثم المرحّلة (system-only).

### AR ◼
- **Invoice:** Dr 110201 / Cr 410101 + Cr Output VAT → AR↑, Revenue↑. → INV-7.
- **Payment/Allocation:** Dr Bank / Cr AR + تخصيص على الفواتير.
- **Credit Note:** عكس/تخفيض AR والإيراد.
- **Aging:** مجموع الأعمار = رصيد 110201. → INV-7.
- **Bad Debt:** Dr مخصص 110204 / Cr AR (لا أثر P&L).
- **ECL (IFRS 9):** Dr 630203 / Cr 110204؛ صافي AR ↓.

### AP ◼
- **Bill:** Dr Inventory/Expense + Input VAT / Cr 210101. → INV-8.
- **Payment/Allocation:** Dr AP / Cr Bank + تخصيص.
- **Credit Note:** تخفيض AP.
- **Aging:** مجموع الأعمار = رصيد 210101. → INV-8.

### Inventory ◼
- **Receipt:** Dr 110301 / Cr GRNI. → INV-9.
- **Issue/COGS:** Dr COGS / Cr 110301.
- **Transfer:** بين المستودعات (كمّي، أثر GL صفر).
- **Return/Adjustment:** تسويات كمّية+قيمية.
- **Valuation (FIFO/متوسط):** تقييم = رصيد 1103 GL. → INV-9.

### Assets ◼
- **Capitalization:** Dr 120104 / Cr AP. → INV-10.
- **Depreciation:** Dr 620402 / Cr 120105 (Contra).
- **Disposal:** إخراج التكلفة والمجمع + ربح/خسارة.
- **Transfer:** تغيير موقع/مركز تكلفة.
- **Impairment (IAS 36):** Dr خسارة / Cr مجمع انخفاض.
- **Register = FA GL:** سجل الأصول = رصيد 1201. → INV-10.

### FX ◼
- **Transaction Currency:** تحويل للوظيفية على السطر.
- **Revaluation:** إعادة تقييم بند نقدي بسعر الإقفال.
- **Realized Gain/Loss:** عند التسوية (420104/630104).
- **Unrealized Gain/Loss:** آخر الفترة (630105).
- **Translation:** ترجمة تابعة → احتياطي OCI (330102).

### Tax ◼
- **Input/Output:** فصل 110210/210404.
- **Withholding:** استقطاع عند الدفع.
- **Adjustment/Reconciliation:** تسوية GL الضريبي مع الإقرار؛ ضريبة مؤجلة (IAS 12).

### Closing ◼
- **Month End:** قائمة الإقفال + Soft Close + INV-5..11.
- **Year End:** إقفال النتيجة → المرحّلة، Hard Close.
- **Retained Earnings / Opening Balance:** ترحيل الأرصدة للسنة الجديدة.

### Security ◼ (يعتمد على موجة W1)
- ترحيل غير مصرّح → رفض (403/PolicyException).
- اعتماد غير مصرّح / اعتماد الذات (SoD-1) → رفض.
- إعادة فتح فترة غير مصرّح → رفض + Audit.
- تعديل قيد منشور → مستحيل (لا مسار Update؛ Reversal فقط).

## 4. بيانات الاختبار (Fixtures & Golden)
- **Seeders:** `AccountingReferenceSeeder` + `DemoCompanySeeder` (شركة + دفاتر + فترات 2026 + 168 حساباً) كأساس ثابت لكل اختبار محاسبي.
- **Golden Fixtures (للقواعد):** مستند مصدري ثابت (JSON) → قيد متوقّع ثابت؛ أي تغيير في القاعدة يجب أن يعكسه تحديث Golden متعمّد (يمنع الانحراف الصامت).
- **Factories:** لتوليد عملاء/موردين/أصناف/أصول مستقبلاً.
- **`RefreshDatabase`** لكل اختبار (عزل تام)، DB = sqlite `:memory:`.

## 5. Golden Tests لقواعد المحاسبة
لكل `AccountingRule`: `build(document)` تُقارن بقيد متوقّع بالضبط — الأكواد، الاتجاهات، المبالغ، عدد السطور، والتوازن. مثال مُنفّذ: `SalesInvoiceRule` (Dr 110201 / Cr 410101 / Cr VAT) — 3 سطور، متوازن، بلا ضريبة → سطران.

## 6. فحوص المطابقة كـ Exceptions (Reconciliation)
`IntegrityService`:
- **مُنفّذ الآن:** INV-1 (TB balanced) · INV-2 (المعادلة المحاسبية) · INV-3 (لا قيد غير متوازن) · مُقارِن عام `controlEqualsSubledger(controlCode, subledgerTotal)`.
- **جاهز للتفعيل:** INV-5..11 — كل فحص يُسجَّل كـ Check، يُصبح `applicable` عند وجود الموديول، وإلا `skipped` (لا يُخفق زوراً).
- `assert()` → يرمي `IntegrityViolationException` بقائمة الفروق عند أي إخفاق.

## 7. اختبارات الأمان (Security) — موجة W1
- مصفوفة Role+Permission+Scope+SoD (وثيقة 12): كل صلاحية تُختبر إيجاباً وسلباً.
- self-approval (SoD-1)، عزل الشركة (Scope)، read-only (Auditor)، إعادة الفتح — كلها اختبارات رفض صريحة.

## 8. بوابة CI و Definition of Done
```
composer test  =  Pint (format) + PHPStan level 7 (types) + Pest (كل الاختبارات)
```
- **بوابة الدمج:** لا دمج والاختبارات حمراء أو PHPStan غير نظيف.
- **تغطية محاسبية إلزامية:** كل موديول جديد يضيف: اختبارات القيد + العكس + الحالات الحدّية + **فحص Integrity** ذي الصلة.
- **بند 73 مفروض:** Reconciled balances شرط اكتمال.

## 9. الحالة الحالية (مُنفّذ) مقابل الخريطة
- **مُنفّذ الآن — 70+ اختبار أخضر:**
  - Core: توازن/عدم توازن، حساب تجميعي، حساب ختامي، فترة مغلقة، عكس، ميزان مراجعة، أبعاد إلزامية، Audit.
  - Engine boundary: Sales عبر المحرك، رفض قاعدة مفقودة/حساب مفقود، Draft متوازن.
  - Reporting: SFP متوازنة، P&L، مراسي Drill-down، as-of.
  - Localization: حلّ القاعدة، إصدار، draft، عزل الدول، خطأ صريح.
  - **Integrity: INV-1/2/3 + كشف الفساد المُحقَن + مُقارِن Control=Subledger** (هذه الجولة).
- **جاهز للتفعيل مع موديولاته:** AR/AP/Inventory/Assets/FX/Tax/Closing/Security (INV-5..11).

المصفوفة الكاملة (Machine-readable) في `docs/testing/test-matrix.json`.

