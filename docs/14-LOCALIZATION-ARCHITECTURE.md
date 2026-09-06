# معمارية التوطين (Localization Architecture)

منصة محاسبة عالمية تبدأ من ليبيا. **القانون لا يعيش في Accounting Core** — بل في **Country Localization Layer** ككيان إضافي (Plugin). كل قاعدة قانونية/ضريبية = بيانات قابلة للتحديث والإصدار، بلا أي Hardcode.

## 1. المبدأ والحدّ (Boundary)

```
┌─────────────────────────────────────────────────────────┐
│ Accounting Core (محايد للدولة — لا يعرف قانوناً واحداً)   │
│  CoA · GL · Journals · Posting · Periods · Dimensions ·   │
│  Multi-Book · Multi-Currency · Reporting Cube             │
└───────────────▲─────────────────────────▲────────────────┘
                │ يسأل عن قاعدة/نسبة        │ يطلب تعريف قائمة
                │ (resolve at runtime)     │
┌───────────────┴─────────────────────────┴────────────────┐
│ Country Localization Layer  (Plugin per country)          │
│  Rule Registry (versioned, effective-dated, audited)      │
│  Tax Rules · Thresholds · Deductions · Contributions ·    │
│  Legal Limits · Reporting Deadlines · Document Rules ·     │
│  Statutory Report Templates · Chart Extensions · Calendar  │
│  ── Packs: LY (first) · AE · SA · EG · UK · EU · US ──     │
└───────────────────────────────────────────────────────────┘
```

**قانون صارم:** المحرك **لا يحتوي** أي `Tax Rate / Threshold / Legal Limit / Deduction / Contribution / Reporting Deadline / Document Requirement`. عند الحاجة يسأل الطبقة: `resolve(country, rule_code, date)`. تغيير القانون = تحديث بيانات القاعدة (إصدار جديد)، **بلا تعديل كود المحرك**.

## 2. نموذج القاعدة (Rule Model) — الحقول الإلزامية

كل قاعدة تحمل بالضبط:

| الحقل | الوصف |
|---|---|
| `rule_code` | معرّف فريد للقاعدة داخل الدولة (VAT_STANDARD، WHT_SERVICES…). |
| `rule_type` | النوع (انظر §3). |
| `rate` | النسبة (لقواعد النِسَب) — **قابلة للتحديث، لا Hardcode**. |
| `amount` | القيمة/الحد (للعتبات/الحدود القانونية). |
| `formula` | صيغة الحساب (DSL آمن) عند تعقّد القاعدة. |
| `effective_from` | تاريخ بدء السريان. |
| `effective_to` | تاريخ انتهاء السريان (فارغ = مفتوح). |
| `authority` | الجهة المصدِرة (مثال: مصلحة الضرائب / صندوق الضمان الاجتماعي). |
| `legal_reference` | المرجع القانوني (رقم القانون/القرار/المنشور). |
| `version` | إصدار القاعدة (v1, v2…). |
| `status` | دورة الحياة (§4). |
| `audit_trail` | من/متى/قبل/بعد لكل تغيير (عبر Audit Engine). |

حقول مساندة: `country` · `name_ar/en` · `currency` · `meta` (بارامترات إضافية: يوم الاستحقاق، قائمة المستندات…) · `approved_by/at` · `source_url`.

## 3. أنواع القواعد (Rule Types) — تغطّي كل ما مُنع تثبيته

| `rule_type` | يمثّل | مثال بارامترات |
|---|---|---|
| `tax_rate` | نسبة ضريبية | `rate` |
| `threshold` | عتبة (حد تسجيل/إعفاء) | `amount` |
| `deduction` | استقطاع | `rate` أو `amount` |
| `contribution` | مساهمة (ضمان اجتماعي…) | `rate` (حصة موظف/صاحب عمل) |
| `legal_limit` | حد قانوني (احتياطي، سقف…) | `amount`/`rate` |
| `reporting_deadline` | موعد تقديم | `meta.frequency` + `meta.due_day` |
| `document_requirement` | مستند إلزامي | `meta.documents[]` + الاحتفاظ |
| `numbering_rule` | ترقيم قانوني للفواتير | `formula` |
| `withholding` | استقطاع ضريبي عند الدفع | `rate` + `threshold` |

## 4. دورة حياة القاعدة والإصدار (Lifecycle & Versioning)

```
draft → pending_approval → active → superseded (بإصدار أحدث) | repealed (أُلغيت)
```
- **الإصدار لا التعديل:** أي تغيير قانوني = **إصدار جديد** بتاريخ سريانه؛ الإصدار القديم يبقى `superseded` (تتبّع تاريخي كامل — القيود القديمة مرتبطة بإصدارها).
- **عدم التداخل:** لكل `(country, rule_code)` قاعدة `active` واحدة في أي تاريخ (يُفرض بالتحقق).
- **Audit:** كل انتقال حالة/إصدار مُسجَّل (who/what/when/before/after/reason).
- **موافقة:** لا تُصبح `active` إلا بعد `pending_approval → approve` من صلاحية Compliance/Tax (وثيقة 12).

## 5. حلّ القاعدة (Rule Resolution) — كيف يسأل المحرك

```
LocalizationResolver::resolve(country, rule_code, on_date):
   SELECT rule WHERE country=? AND rule_code=? AND status='active'
          AND effective_from <= on_date AND (effective_to IS NULL OR effective_to >= on_date)
   → أحدث إصدار مطابق، وإلا خطأ صريح (لا قيمة افتراضية مخفية)
```
- **لا Fallback رقمي مخفي:** غياب القاعدة = خطأ واضح، لا صفر ولا رقم مفترض.
- تُستدعى من **Accounting Rules** (وقت بناء القيد)، ومن **Tax Engine** (حساب الضريبة)، ومن **Reporting** (المواعيد/التقارير القانونية).
- **قابلة للتخزين المؤقت** بمفتاح `(country, rule_code, date)` مع إبطال عند تفعيل إصدار جديد.

## 6. حزمة الدولة (Country Pack) — البنية

كل دولة = حزمة Plugin مستقلة:
```
CountryPack<XX>:
  manifest         : code, name, currency, frameworks_supported, fiscal_calendar
  rules[]          : كل القواعد (§2) بأنواعها
  chart_extension  : حسابات محلية إضافية (Overlay على الأساس — وثيقة 06)
  report_templates : تعريفات التقارير القانونية (Report Builder — وثيقة 13)
  document_rules   : المستندات الإلزامية وقواعد الاحتفاظ
  deadline_calendar: مواعيد الإقرارات/السداد
  numbering        : قواعد ترقيم الفواتير القانونية
  regulators[]     : الجهات الرسمية ومراجعها
```
تفعيل/تعطيل الحزمة لكل شركة؛ الأساس المحاسبي يبقى واحداً.

## 7. إطار إضافة الدول (Multi-Country Framework) — بلا تعديل المحرك

إضافة UAE / Saudi / Egypt / UK / EU / USA = **حزمة بيانات جديدة**، لا كود في النواة:
```
1. أنشئ CountryPack<XX> بالـ manifest.
2. أدخل قواعده (rules) بالحقول الإلزامية + مصادرها الرسمية.
3. أضف chart_extension و report_templates و deadline_calendar.
4. فعّل الحزمة للشركات في تلك الدولة.
   → المحرك يسأل resolve(XX, code, date) تلقائياً — لا يتغيّر سطر واحد فيه.
```

| الحزمة | العملة | ملاحظات (تُملأ من المصدر الرسمي) |
|---|---|---|
| **LY** ليبيا | LYD | الحزمة الأولى — الجهات: مصلحة الضرائب، الضمان الاجتماعي |
| AE الإمارات | AED | VAT، Corporate Tax، FTA |
| SA السعودية | SAR | VAT، Zakat، ZATCA، الفوترة الإلكترونية |
| EG مصر | EGP | VAT، ضريبة الدخل، الفوترة الإلكترونية |
| UK | GBP | VAT، HMRC، Making Tax Digital |
| EU | EUR | VAT (per member state)، تقارير محلية |
| US | USD | Sales Tax (per state)، Federal/State income tax |

> كل حزمة **مستقلة الإصدار**؛ تحديث قانون في دولة لا يمسّ غيرها ولا المحرك (بند 62).

## 8. فصل أُسُس التقارير (Reporting Bases) — تشغيل أكثر من أساس معاً

الشركة الواحدة تُشغّل عدة أُسُس متوازية، كلٌّ في **دفتر** (Multi-Book، وثيقة 08):

| الأساس (Basis) | الدفتر | الغرض | مصدر القواعد |
|---|---|---|---|
| **Local GAAP** | LOCAL | الدفاتر القانونية المحلية | Country Pack (إطار محلي) |
| **IFRS** | IFRS | تقارير دولية (اعتراف/قياس IFRS) | IFRS Mapping Layer |
| **Tax Accounting** | TAX | الوعاء الضريبي | Country Pack (قواعد ضريبية) |
| **Statutory Reporting** | (عرض) | تقارير الجهات الرسمية | Country Pack (report_templates) |

- **الفصل صريح:** الاعتراف المحاسبي (IFRS/Local) مفصول عن المعالجة الضريبية (TAX)؛ الفروق الدائمة/المؤقتة → ضريبة مؤجلة (IAS 12).
- **مثال ليبيا:** `Book of Accounts = Local`، `Reporting Book = IFRS`، `Tax Book = TAX` — لا خلط بين الأساس القانوني وأساس التقارير الدولية (بند 6).
- كل أساس يُنتج قوائمه من نفس القيود عبر تعريف قائمة مختلف (Reporting Engine)، دون إعادة ترحيل.

## 9. حزمة ليبيا (Libya Pack) — كتالوج القواعد

> **مهم جداً (التزام ببند 23):** لم أفترض أي نسبة أو حد. أدناه **كتالوج القواعد التي يجب أن توجد** بحقولها الكاملة؛ قيم `rate/amount` = `null` وحالتها `draft` حتى تُملأ من **المصدر الرسمي** (مصلحة الضرائب/الضمان الاجتماعي) بمرجعها القانوني. الملف: `docs/localization/libya-pack.json`.

القواعد المعرّفة (بلا قيم مفترضة):
- **الضرائب:** `LY_VAT_STANDARD` · `LY_VAT_REGISTRATION_THRESHOLD` · `LY_CIT` (ضريبة دخل الشركات) · `LY_WHT_SERVICES` (الاستقطاع) · `LY_STAMP_DUTY` (الدمغة) · `LY_JIHAD_TAX` / `LY_SOLIDARITY` (مساهمة التضامن/الجهاد).
- **الرواتب والمساهمات:** `LY_PAYE` (استقطاع ضريبة الدخل من الراتب) · `LY_SS_EMPLOYEE` (ضمان اجتماعي — حصة الموظف) · `LY_SS_EMPLOYER` (حصة صاحب العمل).
- **الحدود القانونية:** `LY_LEGAL_RESERVE` (الاحتياطي القانوني — نسبة من صافي الربح) · `LY_EOS_BENEFIT` (مكافأة نهاية الخدمة — صيغة).
- **المواعيد:** `LY_VAT_RETURN_DEADLINE` · `LY_CIT_RETURN_DEADLINE` · `LY_SS_REMITTANCE_DEADLINE` (frequency + due_day في `meta`).
- **المستندات والاحتفاظ:** `LY_INVOICE_REQUIREMENTS` (حقول الفاتورة القانونية) · `LY_DOC_RETENTION` (مدة الاحتفاظ) · `LY_INVOICE_NUMBERING` (تسلسل قانوني).

كل قاعدة أعلاه محمّلة بـ `authority` و`legal_reference` (placeholder) و`version=1` و`status=draft`، جاهزة للتفعيل بعد إدخال القيمة ومصدرها.

## 10. التكامل (Integration)
- **Tax Engine** يستهلك قواعد `tax_rate/withholding/threshold` عبر `resolve()`.
- **Accounting Rules** تسأل الطبقة وقت بناء القيد (مثال: نسبة VAT في `SalesInvoiceRule`).
- **Payroll** يسأل `deduction/contribution` (PAYE، الضمان).
- **Reporting/Compliance** يستهلك `reporting_deadline` و`document_requirement` و`report_templates`.
- **Chart of Accounts** يُوسَّع بـ `chart_extension` للدولة (Overlay).

## 11. الهيكل البرمجي (Scaffold مُثبِت)
تحقيق مبكر مُختبَر للطبقة:
- جدول `localization_rules` (كل الحقول الإلزامية + الإصدار + تواريخ السريان + الحالة).
- `LocalizationRule` (Model) · `RuleResolver` (حلّ القاعدة الفعّالة حسب الدولة/الكود/التاريخ).
- اختبارات Pest تُثبت: الحلّ حسب تاريخ السريان، تفوّق الإصدار الأحدث، تجاهل `draft`، عزل الدول، وخطأ صريح عند الغياب (لا Hardcode).

