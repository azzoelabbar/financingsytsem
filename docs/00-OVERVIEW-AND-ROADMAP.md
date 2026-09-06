# منصة المحاسبة والإدارة المالية — النظرة العامة وخارطة الطريق

**الرؤية:** Financial Operating System على مستوى المؤسسات، تبدأ من ليبيا وتتوسع دولياً. Accurate · Auditable · Configurable · Extensible · Secure · Multi-entity · Multi-currency · Multi-book · Multi-country · IFRS-aware · Tax-aware · API-first · AI-ready.

**الحزمة التقنية (مكتشفة من المشروع):** Laravel 13.30 · PHP 8.4 · Inertia 2 + Vue 3 · Fortify (Auth + 2FA + Passkeys) · Pest 5 · Vite. قاعدة البيانات: SQLite للتطوير، **PostgreSQL موصى بها للإنتاج** (دقة decimal، تزامن، عزل مستأجرين). الترحيلات تُكتب محايدة للمحرّك.

---

## 1. المبدأ المعماري الحاكم (بند 66)

```
Chart of Accounts   = التصنيف (Classification)
Business Rules      = المعالجة المحاسبية (Accounting Treatment)   ← محرك القواعد
Subledgers          = الأرصدة التشغيلية التفصيلية (AR/AP/Assets/Inventory)
General Ledger      = الحقيقة المحاسبية المرحّلة (Single Source of Truth)
Financial Statements= طبقة العرض (Reporting Layer)
```

القاعدة الذهبية: **لا منطق محاسبي داخل الـ Controllers.** كل معالجة تمر عبر:
`Source Document → Accounting Rules Engine → Balanced Journal → Posting Service → GL`.

## 2. طبقات النظام (Layered Architecture)

1. **Presentation** — Inertia/Vue (Workspaces: Accountant / Chief Accountant / CFO / Auditor) + REST API (بند 45).
2. **Application** — Services, Command handlers, Approval workflows, DTOs.
3. **Domain (Accounting Core)** — الكيانات، الثوابت المحاسبية، Posting Engine، Period Engine، Currency Engine. **لا يُعاد بناؤها عند إضافة Module** (بند 1).
4. **Rules Engine** — قواعد قابلة للإصدار والاختبار لكل مصدر (Sales/Purchase/Payment/Asset/Payroll/Tax/FX/Lease).
5. **Persistence** — Eloquent + مخطط مُطبَّع، قيود سلامة على مستوى القاعدة.
6. **Cross-cutting** — Audit, RBAC/ABAC, Multi-tenant isolation, Multi-book, Multi-currency, Localization (i18n RTL/LTR).

## 3. النموذج متعدد الأبعاد (بند 7، 38)

- **الهرم التنظيمي:** Organization (tenant) → Company (legal entity) → Branch/Division/Department/Cost Center/Profit Center/Project/Location.
- **Dimension Framework:** الأبعاد ليست ثابتة. جدول `dimensions` + `dimension_values` + ربط `journal_line_dimensions`. أي بُعد جديد يُضاف بلا تعديل النواة.
- كل حساب يحدد متطلباته: `requires_cost_center`, `requires_project`, `requires_branch` … ويرفض المحرك القيد إن نقص بُعد إلزامي.

## 4. المحرك المزدوج (بند 9) — عقد لا يُكسر

- كل معاملة → Journal متوازن. **مجموع المدين (بالعملة الوظيفية) = مجموع الدائن**، وإلا تُرفض.
- الترحيل فقط عبر `PostingService`. لا `DELETE` لقيد مرحّل — التصحيح بـ **Reversal / Adjustment** (بند 31).
- لا ترحيل على حساب تجميعي (`is_posting=false`) ولا على فترة مغلقة (بند 26، 57).
- المعادلة المحاسبية `Assets = Liabilities + Equity` تُفحص آلياً (بند 58).

## 5. متعدد الدفاتر (Multi-Book — بند 40)

المعاملة الواحدة قد تُنتج معالجات مختلفة حسب الدفتر: `LOCAL` (GAAP محلي) · `IFRS` (تقارير) · `TAX` (وعاء ضريبي). كل Journal موسوم بـ `book_id`. فصل «الأساس القانوني» عن «أساس التقارير» (بند 6).

## 6. متعدد العملات (بند 8)

Functional / Presentation / Transaction currencies · أنواع أسعار (Historical/Closing/Average/Spot) بمصدر وتاريخ · Realized/Unrealized FX · Revaluation · Translation (OCI). التخزين: مبلغ المعاملة + المبلغ الوظيفي على كل سطر.

## 7. IFRS Mapping Layer (بند 5)

طبقة ربط منفصلة: `Applicable Standards · Accounting Policies · Estimates · Effective Dates · Transition Rules · Disclosure Requirements · Standard Mapping`. **النظام لا يدّعي الامتثال لـ IFRS** ما لم تُستوفَ المتطلبات. الأطر: Full IFRS / IFRS for SMEs / Local GAAP لكل شركة (بند 6).

## 8. الضرائب وطبقة ليبيا (بنود 22–23)

- **Tax Engine قابل للتهيئة** — لا نِسَب Hardcoded. كل Rate/Rule = Configuration مع `Effective From/To · Legal Reference · Authority · Version · Approval · Change Log`.
- **Libya Localization = Module منفصل** (LYD، الضرائب الليبية، التسجيلات، الإقرارات، الاستقطاعات، متطلبات المستندات والاحتفاظ). كل قاعدة قانونية بمرجع رسمي. **لا تُفترض أي نسبة** — تُدخَل بمصدرها.
- إضافة نظام ضريبي لدولة جديدة بلا تعديل النواة (بند 62).

## 9. الأمان وتعدد المستأجرين (بنود 50–51)

RBAC + ABAC · Least Privilege · 2FA (متاح عبر Fortify) · Tenant Isolation (Global Scope على `company_id`) · تشفير · Audit Logs · إخفاء البيانات الحساسة · Segregation of Duties (Maker/Checker/Approver/Poster) + Approval Engine ديناميكي بالمبلغ/القسم/النوع (بنود 32–33).

## 10. الأثر والرقابة (بنود 31, 34)

Audit Trail لكل عملية (Who/What/When/Before/After/IP/Source/Reason). Fraud controls: كشف الفواتير/المدفوعات المكررة، القيود الرجعية، النشر في العطلات، تجاوز الفترات — مع Risk Score.

---

## خارطة الطريق (Phased Delivery — بند 72)

> قاعدة: **لا انتقال لمرحلة قبل اختبار سابقتها** (Definition of Done، بند 73).

| المرحلة | المحتوى | الحالة |
|---|---|---|
| **0. التحليل والمعمارية** | مراجعة الدليل + هذه الوثائق + قرارات المعمارية | ✅ منجز |
| **1. Accounting Core** | Multi-tenant · Currencies/Rates · Books · Fiscal Years/Periods · Dimensions · **Chart of Accounts** · **Journals + Posting Engine** · Audit Trail · Trial Balance | 🔨 قيد التنفيذ |
| **2. GL & Reporting Base** | Account Ledger · GL · Trial Balance UI · Drill-down · Period Close (soft/hard) · Financial Statements v1 (SFP/P&L) | ⏳ |
| **3. Subledgers I** | Accounts Receivable · Accounts Payable · Cash & Bank + Bank Reconciliation | ⏳ |
| **4. Subledgers II** | Inventory · Fixed Assets + Depreciation | ⏳ |
| **5. Tax + Libya Localization** | Tax Engine · VAT/WHT · Libya Module · Statutory reports | ⏳ |
| **6. Revenue/Leases/Instruments** | IFRS 15 · IFRS 16 · IFRS 9 | ⏳ |
| **7. Budgeting · Cost Accounting · Payroll Integration** | | ⏳ |
| **8. Period/Year Close · Consolidation · Multi-book reporting** | | ⏳ |
| **9. Approval/Workflow · Fraud/Risk · Internal Controls** | | ⏳ |
| **10. API · Integrations · AI Assistant (draft→review→approve→post)** | | ⏳ |

كل خلية بند 73: Accounting logic · DB · Validation · Authorization · Audit · Journal generation · Reversal · Period locking · Multi-currency · Tax · Reporting · API · Tests · Edge cases · Reconciled balances.

---

## Phase 1 — نطاق التنفيذ الحالي (مفصّل)

**الجداول:** `organizations · companies · currencies · exchange_rates · accounting_books · fiscal_years · fiscal_periods · dimensions · dimension_values · accounts · journals · journal_lines · journal_line_dimensions · audit_logs`.

**المحرك:** `PostingService` (تحقّق التوازن، صلاحية الحساب، حالة الفترة، الأبعاد الإلزامية، احتساب المبالغ الوظيفية) · `JournalService` (بناء/عكس) · `TrialBalanceService`.

**الثوابت (Enums):** `AccountType · AccountNature · NormalBalance · StatementType · ClosingBehavior · JournalStatus · PeriodStatus · AccountingFramework · BookBasis · RateType`.

**البذور:** استيراد الدليل الأصلي (130 حساب) + شركة تجريبية + عملات LYD/USD/EUR + سنة مالية 2026.

**الاختبارات (Pest):** توازن القيد · رفض غير المتوازن · رفض الترحيل لحساب تجميعي · رفض فترة مغلقة · العكس يُنشئ مرآة · ميزان المراجعة يساوي صفراً · هرمية الدليل سليمة.
