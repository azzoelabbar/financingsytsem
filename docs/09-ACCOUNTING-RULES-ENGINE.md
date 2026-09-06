# محرك القواعد المحاسبية (Accounting Rules Engine)

المنطق المحاسبي **لا يعيش في الـControllers ولا في الـModules** (بند 48). يعيش في قواعد (`AccountingRule`) خالصة، مُصدَّرة، قابلة للاختبار — تُترجم نية عمل (`SourceDocument`) إلى قيد متوازن (`JournalDraft`).

## 1. العقود (Contracts)

```php
interface SourceDocument {                      // نية عمل من Module — لا قيد
    public function type(): string;             // "sales.invoice", "payroll.run", ...
    public function companyId(): int;
    public function date(): string;
    public function currency(): string;
    public function reference(): ?string;
    public function dimensions(): DimensionSet;  // أبعاد افتراضية للمستند
    public function payload(): array;            // بيانات المستند (سطور، ضريبة، مبالغ)
}

interface AccountingRule {
    public function type(): string;             // نوع المستند الذي تخدمه
    public function book(): BookBasis;           // LOCAL | IFRS | TAX
    public function version(): string;           // "1.0" — للإصدار والتتبع
    public function build(SourceDocument $doc, PostingContext $ctx): JournalDraft;
}

final class RuleRegistry {                       // يحلّ القاعدة حسب (نوع، دفتر)
    public function resolve(string $type, BookBasis $book): AccountingRule;
    public function register(AccountingRule $rule): void;
}
```

`PostingContext` يمنح القاعدة وصولاً للخدمات (CurrencyEngine, account resolver, tax engine, company policies) دون أن تلمس القاعدةُ قاعدةَ البيانات مباشرة — فتبقى **خالصة وقابلة للاختبار**.

## 2. دورة حياة القيد
```
Draft → (Validate) → (Approval إن لزم) → Posted → [Reversed | Adjusted]
```
- كل قاعدة تُنتج **Draft** فقط؛ المحرك يتحقق ويُرحّل. القاعدة لا تُرحّل بنفسها.
- التوازن مسؤولية القاعدة (تبنيه)، ويُعاد فرضه في المحرك (دفاعياً).
- **AI-generated journals** تمرّ بنفس المسار: Draft → Review → Approval → Post (بند 47) — لا ترحيل آلي بلا اعتماد.

## 3. الإصدار (Versioning)
- كل قاعدة تحمل `version()`؛ كل `Journal` يخزّن `ruleVersion` المستخدَمة.
- تغيير المعالجة (مثلاً معدل ضريبة، معيار جديد) = **قاعدة بإصدار جديد** بتاريخ سريان، لا تعديل القديمة. القيود التاريخية تبقى مرتبطة بإصدارها (تتبع كامل).
- القواعد مُختبَرة بـ Golden Tests: مستند ثابت → قيد متوقع ثابت.

## 4. تعدد الدفاتر (Multi-Book)
- لكل (نوع مستند) قد توجد عدة قواعد — واحدة لكل دفتر.
- `AccountingEngine::postFrom(document)` يستدعي كل القواعد المطبّقة على دفاتر الشركة النشطة، فيُنتج قيداً في كل دفتر.
- مثال الإيجار: `LeaseRule@IFRS` (RoU + التزام) بينما `LeaseRule@TAX` (مصروف إيجار). نفس المستند، معالجتان.

## 5. القيود التلقائية والمتكررة (Auto & Recurring)
| النوع | الآلية |
|---|---|
| **Auto Journals** | مباشرة من Source Transaction (فاتورة، دفعة) عبر القاعدة المناسبة. |
| **Recurring Journals** | `RecurringJournalTemplate` (قالب حركات + جدول cron) → يولّد Drafts دورياً → اعتماد → ترحيل. (إيجار ثابت، اشتراكات). |
| **Depreciation/Amortization** | جدول أصول → قاعدة إهلاك شهرية → قيد آلي. |
| **Accruals** | قاعدة استحقاق آخر الفترة + **عكس تلقائي** أول الفترة التالية. |
| **Prepayments** | رسملة ثم إطفاء دوري عبر `AccrualSchedule`. |
| **FX Revaluation** | قاعدة إعادة تقييم دورية للبنود النقدية بالأجنبي. |

## 6. الأستاذ المساعد وحسابات المراقبة (Subledger & Control)
- القواعد التي تمسّ AR/AP/Inventory/FA/Payroll تُرحّل إلى **حساب المراقبة** في GL **وتحدّث سطر الأستاذ المساعد** (العميل/المورد/الأصل) في نفس المعاملة (atomic).
- التفاصيل (رصيد كل عميل) في الأستاذ المساعد؛ GL يحمل الإجمالي. مطابقة دورية `Subledger = Control` عبر Reconciliation Engine.

## 7. الضريبة داخل القاعدة (Tax)
- القاعدة تستدعي **Tax Engine** (Configuration) لحساب الضريبة؛ لا نِسَب Hardcoded.
- تفصل **المعالجة المحاسبية** عن **الضريبية**: الدفتر LOCAL/IFRS يُثبت الإيراد؛ الدفتر TAX قد يختلف؛ الفرق المؤقت → ضريبة مؤجلة (IAS 12).

## 8. سجل القواعد (الحد الأدنى للتغطية)
`SalesInvoiceRule · SalesReturnRule · PurchaseInvoiceRule · PurchaseReturnRule · CustomerReceiptRule · SupplierPaymentRule · InventoryReceiptRule · InventoryIssueRule · FixedAssetAcquisitionRule · DepreciationRule · PayrollRule · TaxSettlementRule · LoanDrawdownRule · LoanRepaymentRule · LeaseInitialRule · LeasePaymentRule · FxRevaluationRule · BadDebtWriteOffRule · EclRule · RevenueRecognitionRule · IntercompanyRule · PeriodClosingRule · YearEndClosingRule`.

كل قاعدة: **Testable · Versioned · Book-aware · Balanced-by-construction**. الأمثلة الناتجة في `10-JOURNAL-EXAMPLES.md`.
