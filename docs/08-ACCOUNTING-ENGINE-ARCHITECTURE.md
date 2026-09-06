# محرك المحاسبة — التصميم المعماري (Accounting Engine)

محرك قيد مزدوج حقيقي، **ليس CRUD**. القيود لا تُنشأ عشوائياً: كل Module يبني `SourceDocument` ويمرّره لقاعدة محاسبية (`AccountingRule`) تُنتج `JournalDraft` متوازناً، ثم يتولّى المحرك التحقق والترحيل والأثر والعكس.

## 1. الحدود (The Boundary) — قانون لا يُكسر

```
Module (Sales/Purchasing/Inventory/Assets/Payroll/Tax/Leases/Loans…)
        │  يبني فقط SourceDocument (نية عمل، لا قيد)
        ▼
AccountingRule  (منطق محاسبي خالص، قابل للإصدار والاختبار)
        │  يُنتج JournalDraft (حركات دفترية متوازنة)
        ▼
AccountingEngine  (نقطة الدخول الوحيدة)
        │  Validate → Balance → Period → Dimensions → Tax → Post
        ▼
General Ledger  (journals + journal_lines — مصدر الحقيقة)
        ▼
Subledgers · Trial Balance · Financial Statements · Audit Trail
```

**قواعد صارمة (مفروضة برمجياً):**
- لا Module يستدعي `new Journal` أو يكتب في `journal_lines` مباشرة. الوصول الوحيد عبر `AccountingEngine::post()`.
- كل Journal: **Balanced · Validated · Traceable · Auditable · Reversible**.
- **لا Delete لقيد منشور.** التصحيح = Reversal أو Adjustment (قيد جديد).
- الترحيل ممنوع على فترة مغلقة، وعلى حساب تجميعي/نظامي، وبلا بُعد إلزامي.

## 2. الطبقات ونموذج النطاق (Domain Model)

| الطبقة | المكوّنات |
|---|---|
| **Entities** (كيانات ثابتة في القاعدة) | `Account` · `Journal` · `JournalLine` · `JournalLineDimension` · `AccountingBook` · `FiscalYear` · `FiscalPeriod` · `Dimension` · `DimensionValue` · `ExchangeRate` · `RecurringJournalTemplate` · `AccrualSchedule` · `Reconciliation` / `ReconciliationLine` · `SourceDocumentRef` · `AuditLog` |
| **Value Objects** (غير قابلة للتغيير) | `Money(amount, currency)` · `FxRate` · `LedgerMovement` · `JournalDraft` · `AccountRef` · `DimensionSet` · `PostingRequest` · `PeriodRef` · `FxConversion` |
| **Domain Services / Engines** | Posting · Reversal · Period · Currency · Dimension · Audit · Reconciliation · Rules · Recurring/Auto · Accrual/Prepayment · Subledger |
| **Application** | Module services تبني `SourceDocument` وتستدعي المحرك |

### Value Objects الأساسية (عقود التصميم)
```php
final readonly class Money {                    // مبلغ + عملة، لا floats
    public function __construct(public string $amount, public string $currency) {}
}

final readonly class LedgerMovement {           // سطر دفتري واحد (نية)
    public function __construct(
        public string $accountCode,             // من دليل الحسابات
        public Money $debit,
        public Money $credit,
        public DimensionSet $dimensions,
        public ?string $memo = null,
        public ?FxRate $rate = null,
    ) {}
}

final readonly class JournalDraft {             // مجموعة حركات + رأس، متوازنة أو ترفض
    /** @param list<LedgerMovement> $movements */
    public function __construct(
        public BookBasis $book,
        public string $source,                  // sales|purchase|payroll|...
        public string $date,
        public ?string $reference,
        public string $description,
        public array $movements,
        public SourceRef $source_ref,           // للتتبع الرجعي
        public string $ruleVersion,
    ) {}
}
```

## 3. المحرّكات الفرعية (Sub-Engines)

> ما هو ✅ موجود من المرحلة 1 يُعاد استخدامه؛ ما هو 🔨 يُضاف.

### 3.1 Posting Engine ✅ (`JournalService`)
نقطة الترحيل الوحيدة. يفرض: التوازن بالعملة الوظيفية (`Σ debit = Σ credit`)، حساب قابل للترحيل ونشط وداخل الشركة، فترة مفتوحة، الأبعاد الإلزامية، ترقيم آلي، وحالة `POSTED` غير قابلة للتعديل. المدخل الحالي `LineInput[]`؛ يُغلَّف بـ `AccountingEngine` ليقبل `JournalDraft`.

### 3.2 Reversal & Adjustment Engine ✅🔨 (`JournalService::reverse` + `AdjustmentService`)
- **Reversal:** قيد مرآة (مدين↔دائن) يربط الأصل ويحوّل حالته إلى `REVERSED`؛ كلاهما يبقى في الدفتر (لا حذف).
- **Adjustment:** قيد تصحيحي جديد بدل التعديل (لتسويات الإقفال، فروق التقدير).
- **Storno vs. Reversal:** خيار قابل للتكوين (عكس بالقيمة السالبة أو بمرآة).

### 3.3 Period Engine ✅ (`PeriodService`)
`OPEN → SOFT_CLOSED → HARD_CLOSED`. يحلّ الفترة من تاريخ الترحيل، يمنع الترحيل على المغلق (soft يتطلب صلاحية override)، وأي Reopen يُسجَّل في Audit. يدعم فترات التسوية (Adjustment Periods 13+).

### 3.4 Currency Engine 🔨 (`CurrencyEngine`)
- **Resolve:** سعر الصرف حسب (from,to,type,date,source) من `exchange_rates` مع fallback.
- **Convert:** كل حركة تُحوَّل للعملة الوظيفية للكيان (Money → functional) على مستوى السطر.
- **Realized FX:** عند تسوية بند نقدي بعملة أجنبية، الفرق بين سعر الإثبات وسعر التسوية → مكسب/خسارة محققة (`420104`/`630104`).
- **Unrealized FX (Revaluation):** إعادة تقييم أرصدة البنود النقدية بالأجنبي في تاريخ التقرير بسعر الإقفال → `630105`/`420104` (IAS 21).
- **Translation:** ترجمة نتائج شركة تابعة من الوظيفية إلى عملة العرض؛ الفرق → احتياطي ترجمة OCI (`330102`).

### 3.5 Dimension Engine ✅🔨 (`DimensionEngine`)
يتحقق من الأبعاد الإلزامية لكل حساب (`requires_cost_center/project/branch/department`)، يشتق الأبعاد الافتراضية (من الفرع/المشروع في `SourceDocument`)، ويمنع الترحيل عند نقص بُعد إلزامي. الأبعاد ليست في رقم الحساب (بند 38).

### 3.6 Audit Engine ✅ (`AuditLogger`)
سجل غير قابل للتغيير: who/what/when/before/after/IP/reason على كل حدث (created/posted/reversed/reopened/reconciled). لا حذف للسجلات المحاسبية.

### 3.7 Reconciliation Engine 🔨 (`ReconciliationEngine`)
إطار عام يُستخدم لـ Bank/AR/AP/Inventory/Tax/Payroll/Intercompany/GL-vs-Subledger. لكل تسوية: مصدران (مثلاً كشف بنكي ↔ GL)، محرك مطابقة (آلي بالمبلغ/التاريخ/المرجع + يدوي)، ونتائج `Matched / Unmatched / Exception / Adjustment-Required`، مع قفل التسوية.

### 3.8 Rules Engine 🔨 (`RuleRegistry` + `AccountingRule`)
سجل يربط (نوع المستند، أساس الدفتر) → قاعدة. القواعد خالصة، مُصدَّرة (`version()`), وقابلة للاختبار بمعزل. تفاصيلها في `09-ACCOUNTING-RULES-ENGINE.md`.

### 3.9 Recurring / Auto-Journal Engine 🔨
`RecurringJournalTemplate` (جدول + قالب حركات) → يولّد Drafts في مواعيدها → Draft → (Approval) → Post. يُستخدم للإيجارات الثابتة، الاشتراكات، الإهلاك الدوري.

### 3.10 Accrual / Prepayment Engine 🔨
- **Accrual:** إثبات مصروف/إيراد مستحق آخر الفترة، مع **عكس تلقائي** أول الفترة التالية (Auto-reverse).
- **Prepayment:** رسملة الدفعة المقدمة ثم إطفاؤها دورياً عبر جدول (`AccrualSchedule`).

### 3.11 Subledger Engine 🔨
حسابات المراقبة (`is_control`: AR/AP/Inventory/FA/Payroll) تحمل الإجمالي في GL؛ التفاصيل (كل عميل/مورد/أصل) في الأستاذ المساعد. كل قيد مراقبة يحدّث الأستاذ المساعد، وتُطابَق الأرصدة دورياً (Subledger = Control).

## 4. تعدد الدفاتر (Multi-Book) وتعدد الكيانات (Multi-Entity)
- **Multi-Book:** `SourceDocument` واحد قد يُنتج عدة `JournalDraft` (واحد لكل دفتر LOCAL/IFRS/TAX) بمعالجة مختلفة — مثلاً الإيجار: تمويلي (RoU + التزام) في IFRS، تشغيلي (مصروف إيجار) في TAX/LOCAL. المحرك يرحّل كلاً في دفتره.
- **Multi-Entity:** كل قيد موسوم بـ `company_id`؛ المعاملات بين الشركات تُنشئ طرفين متقابلين (IC receivable/payable) موسومين `eliminate_on_consolidation` للاستبعاد عند التوحيد.

## 5. تدفق نموذجي (End-to-End)
```
SalesModule::invoice($order)
  → SalesInvoiceDocument (customer, lines, tax, currency, date, dims)
  → RuleRegistry::resolve(document, book=LOCAL) → SalesInvoiceRule@v1
  → JournalDraft [ Dr AR 110201 | Cr Sales 410101 | Cr Output VAT 210404 ]
       (+ COGS draft: Dr COGS 5101 | Cr Inventory 110301)
  → AccountingEngine::post(draft)
       CurrencyEngine.convert → DimensionEngine.validate → PostingEngine.post
  → GL updated · AR subledger updated · AuditLog(created,posted)
  → Trial Balance balanced · SFP(AR↑, Inventory↓) · P&L(Revenue↑, COGS↑)
```

الأمثلة الكاملة (18 نوعاً) في `10-JOURNAL-EXAMPLES.md`.
