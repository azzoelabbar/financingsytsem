# خريطة موديولات ERP المالي الكاملة (Enterprise Module Map)

نظام مالي متكامل — **ليس** GL + Sales + Purchases فقط. 63 موديولاً عبر 10 مجموعات.

## طبقات التبعية (Dependency Layering)
```
PLATFORM  (multi-tenant · API · security · documents · i18n)   ← الأساس التقني
   └── GOVERNANCE (roles · approvals · audit · controls · compliance)
        └── CORE ACCOUNTING (CoA · GL · Journals · Periods · Rules · Dimensions · Multi-Book · Multi-Currency)  ← مصدر الحقيقة
             └── SUBLEDGERS (AR · AP · Inventory · Fixed Assets · Payroll · Loans · Leases · Investments)
                  └── OPERATIONS (Sales · Purchases · Expenses · Procurement · Projects)
                       └── TREASURY (Cash · Bank · Reconciliation · Payments · Collections · Forecast)
             └── TAX (Engine · Returns · Withholding · Reconciliation · Localization)
        └── FINANCIAL CONTROL (Budget · Forecast · Cost · Reconciliation · Closing · Consolidation)
             └── REPORTING (Financial Statements · Management · Regulatory · Custom · Analytics)
                  └── AI (Assistant · Anomaly · Suggestions · Forecast · Analysis)
```
القاعدة (بند 66): كل موديول يمرّ عبر **Accounting Engine** (Source → Rule → Journal → GL). لا موديول يكتب قيداً مباشرة.

## دليل البطاقة (Legend)
كل موديول: **Purpose · Entities · Processes · Inputs → Outputs · Accounting Impact · Reports · Permissions · Workflows · Audit · Integrations · IFRS · Tax · Dependencies**، وأولوية **🔴 Critical / 🟡 Important / 🟢 Optional** + حالة التنفيذ (`✅ built` / `◼ designed` / `▫ planned`).

---

# 1. CORE ACCOUNTING

### 1.1 Chart of Accounts — 🔴 Critical `✅ built`
- **Purpose:** التصنيف الهرمي متعدد المستويات لكل الحسابات (مصدر التصنيف لا الحقيقة).
- **Entities:** `Account` (330 حساباً)، Account hierarchy، contra links، ~40 حقل metadata.
- **Processes:** إنشاء/تعديل حساب، تفعيل قوالب صناعة، كشف Contra/Control، leaf-posting detection.
- **Inputs → Outputs:** استيراد Excel/JSON، قوالب → شجرة حسابات جاهزة للترحيل.
- **Accounting Impact:** يحدد طبيعة كل حساب (type/normal balance/statement/closing).
- **Reports:** دليل الحسابات، شجرة الحسابات، خريطة IFRS/Tax.
- **Permissions:** Chart Admin (إنشاء)، Accountant (قراءة).
- **Workflows:** إنشاء → مراجعة → تفعيل حساب جديد.
- **Audit:** كل تغيير تصنيفي مُسجَّل؛ لا حذف لحساب له حركة.
- **Integrations:** يغذّي كل الموديولات.
- **IFRS:** IFRS Mapping Layer على مستوى الحساب. **Tax:** tax_mapping لكل حساب.
- **Dependencies:** —（الجذر）.

### 1.2 General Ledger — 🔴 Critical `✅ built`
- **Purpose:** السجل المرحّل النهائي — Single Source of Truth.
- **Entities:** `Journal`, `JournalLine`, `JournalLineDimension`, running balances.
- **Processes:** Trial Balance، Account Ledger، Drill-down، أرصدة افتتاحية/ختامية.
- **Inputs → Outputs:** قيود مرحّلة من كل الموديولات → ميزان مراجعة + أرصدة.
- **Accounting Impact:** هو الأثر نفسه؛ يحفظ `Debits = Credits` والمعادلة المحاسبية.
- **Reports:** Trial Balance، GL، Journal Register، Account Activity، Period Comparison.
- **Permissions:** Accountant (قراءة/ترحيل)، Chief Accountant (تعديلات).
- **Workflows:** — (يستقبل من Journals).
- **Audit:** كل حركة مرتبطة بمصدرها؛ لا حذف.
- **Integrations:** يغذّي Reporting/Consolidation.
- **IFRS:** أساس كل القوائم. **Tax:** أساس الوعاء الضريبي عبر دفتر TAX.
- **Dependencies:** CoA, Journals, Periods.

### 1.3 Journals & Posting Engine — 🔴 Critical `✅ built`
- **Purpose:** محرك القيد المزدوج — إنشاء/ترحيل/عكس متوازن.
- **Entities:** `Journal` (draft/posted/reversed)، `JournalLine`، `LineInput`/`JournalDraft` (VO).
- **Processes:** createDraft → validate (balance/account/period/dimensions) → post → reverse/adjust، ترقيم آلي.
- **Inputs → Outputs:** JournalDraft من القواعد → قيد مرحّل غير قابل للتعديل.
- **Accounting Impact:** ينشئ كل الحركات؛ يفرض التوازن.
- **Reports:** Journal Register، القيود المعلّقة/المسودات.
- **Permissions:** Maker (إنشاء)، Approver (اعتماد)، Poster (ترحيل)، Reviewer.
- **Workflows:** Draft → Pending → Approved → Posted → Reversed/Adjusted.
- **Audit:** created/posted/reversed مع who/what/when/before/after؛ **لا Delete**.
- **Integrations:** يُستدعى من Accounting Engine حصراً.
- **IFRS:** — (محايد). **Tax:** سطور ضريبية ضمن القيد.
- **Dependencies:** CoA, Periods, Dimensions, Multi-Currency.

### 1.4 Periods & Fiscal Calendar — 🔴 Critical `✅ built`
- **Purpose:** إدارة السنة/الفترات المالية وأقفالها.
- **Entities:** `FiscalYear`, `FiscalPeriod` (open/soft/hard closed), adjustment periods.
- **Processes:** فتح/إقفال مبدئي/نهائي/إعادة فتح، حل الفترة من تاريخ الترحيل.
- **Inputs → Outputs:** تعريف السنة → فترات مقفلة تمنع الترحيل الخاطئ.
- **Accounting Impact:** يمنع الترحيل على فترة مغلقة (بند 26/57).
- **Reports:** حالة الفترات، سجل الأقفال/الفتح.
- **Permissions:** Chief Accountant (إقفال)، CFO (إعادة فتح).
- **Workflows:** Open → Soft Close → Hard Close (→ Reopen بصلاحية + Audit).
- **Audit:** كل إقفال/إعادة فتح مُسجَّل بالمستخدم والسبب.
- **Integrations:** يحكم Journals/Closing.
- **IFRS:** IAS 10 (أحداث بعد الفترة). **Tax:** فترات ضريبية مستقلة.
- **Dependencies:** —.

### 1.5 Accounting Rules Engine — 🔴 Critical `◼ designed`
- **Purpose:** ترجمة نية العمل إلى قيد متوازن؛ لا منطق محاسبي في الـControllers.
- **Entities:** `AccountingRule` (interface)، `RuleRegistry`، `SourceDocument`، `AccountingEngine`.
- **Processes:** resolve(type,book) → build(draft) → post؛ إصدار القواعد (versioning).
- **Inputs → Outputs:** SourceDocument → JournalDraft متوازن.
- **Accounting Impact:** يحدد المعالجة لكل معاملة مصدرية.
- **Reports:** سجل القواعد وإصداراتها، Golden tests.
- **Permissions:** Rule Admin (تسجيل/إصدار).
- **Workflows:** Draft rule → test → version → activate.
- **Audit:** ruleVersion مخزّن على كل قيد.
- **Integrations:** نقطة تكامل كل الموديولات.
- **IFRS/Tax:** قواعد منفصلة لكل دفتر (IFRS vs Tax).
- **Dependencies:** Journals, CoA, Tax Engine, Multi-Currency.

### 1.6 Dimensions Framework — 🔴 Critical `✅ built`
- **Purpose:** تحليل متعدد الأبعاد (فرع/مركز تكلفة/مشروع…) خارج رقم الحساب.
- **Entities:** `Dimension`, `DimensionValue`, `JournalLineDimension`.
- **Processes:** تعريف أبعاد، إلزام بُعد لحساب، اشتقاق افتراضي، تحقق عند الترحيل.
- **Inputs → Outputs:** أبعاد المستند → وسم كل سطر قيد.
- **Accounting Impact:** يرفض الترحيل عند نقص بُعد إلزامي.
- **Reports:** تقارير مقسّمة بأي بُعد (P&L بالفرع/المشروع).
- **Permissions:** Dimension Admin.
- **Workflows:** — (تكوين).
- **Audit:** الأبعاد جزء من القيد الثابت.
- **Integrations:** كل الموديولات + Reporting/Cost.
- **IFRS:** IFRS 8 (القطاعات). **Tax:** أبعاد ضريبية اختيارية.
- **Dependencies:** Journals.

### 1.7 Multi-Book Accounting — 🟡 Important `◼ designed`
- **Purpose:** معالجات متوازية LOCAL/IFRS/TAX لنفس الحدث.
- **Entities:** `AccountingBook` (basis), قيود موسومة بالدفتر.
- **Processes:** قاعدة لكل دفتر، ترحيل متعدد الدفاتر، مطابقة بين الدفاتر.
- **Inputs → Outputs:** SourceDocument → عدة JournalDrafts.
- **Accounting Impact:** يفصل الأساس القانوني عن أساس التقارير عن الوعاء الضريبي.
- **Reports:** قوائم لكل دفتر، تسوية Book-to-Book.
- **Permissions:** Chief Accountant/CFO.
- **Workflows:** —.
- **Audit:** كل قيد موسوم بدفتره.
- **IFRS:** دفتر IFRS كامل. **Tax:** دفتر TAX منفصل + ضريبة مؤجلة (IAS 12).
- **Dependencies:** Journals, Rules, CoA.

### 1.8 Multi-Currency — 🔴 Critical `◼ designed (scaffold)`
- **Purpose:** معاملات/تقارير بعملات متعددة (Functional/Presentation/Transaction).
- **Entities:** `Currency`, `ExchangeRate` (spot/closing/average/historical)، `Money` VO.
- **Processes:** حل السعر، التحويل للوظيفية، Realized/Unrealized FX، Revaluation، Translation (OCI).
- **Inputs → Outputs:** أسعار الصرف + مبالغ بالأجنبي → مبالغ وظيفية + فروق عملة.
- **Accounting Impact:** يولّد مكاسب/خسائر الصرف واحتياطي الترجمة.
- **Reports:** تقرير التعرّض للعملات، أرباح/خسائر الصرف.
- **Permissions:** Treasury/Accountant (إدخال الأسعار).
- **Workflows:** إدخال سعر → مراجعة → إعادة تقييم دورية.
- **Audit:** مصدر وتاريخ كل سعر.
- **Integrations:** Bank feeds لأسعار الصرف.
- **IFRS:** IAS 21، IAS 29 (تضخم مفرط عند الحاجة). **Tax:** معالجة ضريبية للفروق.
- **Dependencies:** CoA, Journals.

---

# 2. SUBLEDGERS

### 2.1 Accounts Receivable (AR) — 🔴 Critical `▫ planned`
- **Purpose:** إدارة ذمم العملاء التفصيلية خلف حساب المراقبة.
- **Entities:** `Customer`, Customer Group, Credit Limit/Terms, `Invoice`, Credit/Debit Note, `Receipt`, Allocation.
- **Processes:** فوترة، تخصيص مقبوضات، أعمار الديون، تحصيل، ديون معدومة، ECL (IFRS 9).
- **Inputs → Outputs:** فواتير/مقبوضات → أرصدة عملاء + كشوف حساب.
- **Accounting Impact:** Dr AR/Cr Revenue+VAT؛ التحصيل Dr Bank/Cr AR؛ ECL/Write-off.
- **Reports:** AR Aging، كشف حساب العميل، تحليل التحصيل، DSO.
- **Permissions:** AR Accountant، Credit Controller، Approver (حدود ائتمان).
- **Workflows:** Invoice: Created→Approved→Posted؛ Write-off: Requested→Approved.
- **Audit:** كل فاتورة/تخصيص متتبع لمصدره.
- **Integrations:** Sales، Bank (تحصيل)، Tax.
- **IFRS:** IFRS 15 (إيراد)، IFRS 9 (ECL). **Tax:** VAT على المخرجات.
- **Dependencies:** CoA, Journals, Sales, Tax Engine, Bank.

### 2.2 Accounts Payable (AP) — 🔴 Critical `▫ planned`
- **Purpose:** إدارة ذمم الموردين التفصيلية.
- **Entities:** `Supplier`, Supplier Group, `Bill`, Credit/Debit Note, `Payment`, Allocation.
- **Processes:** استلام فواتير، مطابقة ثلاثية (PO/GRN/Invoice)، جدولة سداد، خصم السداد المبكر، استقطاع ضريبي.
- **Inputs → Outputs:** فواتير موردين → أرصدة موردين + جدول استحقاق.
- **Accounting Impact:** Dr Expense/Inventory + Input VAT / Cr AP؛ السداد Dr AP/Cr Bank؛ WHT.
- **Reports:** AP Aging، كشف المورد، الاستحقاقات، DPO.
- **Permissions:** AP Accountant، Approver (حسب المبلغ)، Payment Officer.
- **Workflows:** Bill: Created→Matched→Approved→Posted؛ Payment: Created→Approved→Executed.
- **Audit:** مطابقة ثلاثية + موافقات مسجّلة؛ مراقبة تغيير حساب المورد البنكي (احتيال).
- **Integrations:** Purchases، Procurement، Bank، Tax.
- **IFRS:** IAS 37 (مخصصات)، IAS 2. **Tax:** Input VAT، Withholding.
- **Dependencies:** CoA, Journals, Purchases, Tax Engine, Bank.

### 2.3 Inventory Accounting — 🟡 Important `▫ planned`
- **Purpose:** تقييم المخزون ومحاسبته (subledger) مقابل حساب المراقبة.
- **Entities:** Item, Category, Warehouse, Location, UoM, Batch/Serial, Valuation Layer.
- **Processes:** تقييم (FIFO/متوسط مرجّح/تحديد نوعي)، استلام/صرف/تحويل/تسوية، جرد، هبوط/تقادم.
- **Inputs → Outputs:** حركات المخزون → تكلفة البضاعة + قيمة المخزون.
- **Accounting Impact:** Dr Inventory/Cr GRNI؛ Dr COGS/Cr Inventory؛ Write-down (IAS 2).
- **Reports:** Inventory Valuation، حركة الصنف، بطء الحركة، Stock Aging.
- **Permissions:** Inventory Accountant، Warehouse (حركات)، Approver (تسويات).
- **Workflows:** Adjustment: Counted→Reviewed→Approved→Posted.
- **Audit:** كل حركة مخزون متتبعة؛ منع الرصيد السالب (إلا بتكوين).
- **Integrations:** Inventory Ops، Purchases، Sales، Manufacturing.
- **IFRS:** IAS 2 (LCNRV). **Tax:** أثر التقييم على الوعاء.
- **Dependencies:** CoA, Journals, Inventory Ops, Cost Accounting.

### 2.4 Fixed Assets — 🔴 Critical `▫ planned`
- **Purpose:** سجل الأصول الثابتة والإهلاك والاستبعاد.
- **Entities:** `Asset`, Category, Location, Custodian, Depreciation Schedule, Revaluation, Disposal.
- **Processes:** رسملة، إهلاك (قسط ثابت/متناقص/وحدات إنتاج)، إعادة تقييم، انخفاض قيمة، استبعاد/تحويل، RoU (IFRS 16).
- **Inputs → Outputs:** فواتير أصول → بطاقات أصول + قيود إهلاك آلية.
- **Accounting Impact:** Dr Asset/Cr AP؛ Dr Deprec/Cr Accum Deprec؛ Impairment؛ Disposal gain/loss.
- **Reports:** Fixed Asset Register، جدول الإهلاك، NBV، Impairment.
- **Permissions:** Asset Accountant، Approver (استبعاد/إعادة تقييم).
- **Workflows:** Acquisition→Capitalize؛ Disposal: Requested→Approved→Posted.
- **Audit:** دورة حياة كل أصل متتبعة.
- **Integrations:** AP، Procurement، Projects (CWIP).
- **IFRS:** IAS 16، IAS 36، IAS 40، IFRS 16، IAS 23. **Tax:** إهلاك ضريبي مختلف → ضريبة مؤجلة.
- **Dependencies:** CoA, Journals, AP, Recurring Engine.

### 2.5 Payroll Accounting — 🟡 Important `▫ planned`
- **Purpose:** التكامل المحاسبي للرواتب (لا نظام موارد بشرية كامل بالضرورة).
- **Entities:** Employee (accounting view)، Payroll Run، Payslip lines، Contributions، Advances/Loans، EOS.
- **Processes:** توليد مسير، حساب استقطاعات/مساهمات، قيد مسير آلي، سداد، نهاية الخدمة.
- **Inputs → Outputs:** بيانات المسير → Payroll Journal + التزامات.
- **Accounting Impact:** Dr Salary Exp + Employer contrib / Cr Net payable + PAYE + SS + deductions.
- **Reports:** ملخص المسير، التزامات الرواتب، تكلفة الموظف بالقسم.
- **Permissions:** Payroll Accountant، Approver، Confidential access.
- **Workflows:** Run: Draft→Reviewed→Approved→Posted→Paid.
- **Audit:** مسير معتمد؛ بيانات حساسة مُقنّعة.
- **Integrations:** HR/Payroll system، Bank (رواتب)، Tax (PAYE).
- **IFRS:** IAS 19 (منافع الموظفين). **Tax:** PAYE، الضمان الاجتماعي.
- **Dependencies:** CoA, Journals, Bank, Tax Engine, Dimensions.

### 2.6 Loans — 🟡 Important `▫ planned`
- **Purpose:** محاسبة القروض الممنوحة/المقترضة (تكلفة مطفأة).
- **Entities:** Loan, Repayment Schedule, Interest, Covenants.
- **Processes:** سحب، جدول سداد، فائدة فعّالة (EIR)، إعادة تصنيف الجزء المتداول.
- **Inputs → Outputs:** عقد قرض → جدول سداد + قيود فائدة/أصل.
- **Accounting Impact:** Dr Bank/Cr Loan؛ Dr Interest+Principal/Cr Bank؛ EIR.
- **Reports:** جدول القروض، مصروف الفائدة، الاستحقاقات.
- **Permissions:** Treasury، CFO (اعتماد).
- **Workflows:** Drawdown→Approved؛ Repayment (recurring).
- **Audit:** كل دفعة متتبعة.
- **Integrations:** Bank، Treasury.
- **IFRS:** IFRS 9 (تكلفة مطفأة/EIR)، IFRS 7. **Tax:** خصم الفائدة.
- **Dependencies:** CoA, Journals, Bank, Multi-Currency.

### 2.7 Leases — 🟡 Important `▫ planned`
- **Purpose:** محاسبة الإيجارات وفق IFRS 16.
- **Entities:** Lease Contract, Payment Schedule, RoU Asset, Lease Liability, Modification.
- **Processes:** إثبات أولي (PV)، فائدة + إطفاء الالتزام، إهلاك RoU، تعديل/تجديد/إنهاء.
- **Inputs → Outputs:** عقد إيجار → RoU + التزام + جداول.
- **Accounting Impact:** Dr RoU/Cr Lease Liab؛ Dr Interest+Principal/Cr Bank؛ Dr Deprec/Cr Accum.
- **Reports:** جدول الإيجارات، التزامات الإيجار، تحليل الاستحقاق.
- **Permissions:** Accountant، Approver.
- **Workflows:** Initial→Approved؛ Payment/Depreciation (recurring).
- **Audit:** تعديلات العقد متتبعة.
- **Integrations:** Fixed Assets، Treasury، Multi-Book (IFRS vs Tax).
- **IFRS:** IFRS 16. **Tax:** غالباً إيجار تشغيلي ضريبياً → فرق مؤقت.
- **Dependencies:** CoA, Journals, Fixed Assets, Multi-Book.

### 2.8 Investments — 🟢 Optional `▫ planned`
- **Purpose:** محاسبة الاستثمارات والأدوات المالية.
- **Entities:** Investment, Instrument (FVTPL/FVOCI/Amortized), Dividend, Fair Value.
- **Processes:** تصنيف، قياس القيمة العادلة، توزيعات، انخفاض قيمة/ECL، إلغاء اعتراف.
- **Inputs → Outputs:** صفقات استثمار → أرصدة + تغيرات القيمة العادلة (P&L/OCI).
- **Accounting Impact:** Dr/Cr Investment، Fair value → P&L أو OCI.
- **Reports:** محفظة الاستثمارات، الأداء، تغيرات القيمة العادلة.
- **Permissions:** Treasury/CFO.
- **Workflows:** Trade→Approved→Posted؛ Revaluation (periodic).
- **Audit:** كل صفقة/تقييم متتبع.
- **Integrations:** Market data feeds، Bank.
- **IFRS:** IFRS 9، IFRS 13 (قياس القيمة العادلة)، IAS 28. **Tax:** أرباح رأسمالية.
- **Dependencies:** CoA, Journals, Multi-Currency, Multi-Book.

---

# 3. TREASURY

### 3.1 Cash Management — 🔴 Critical `▫ planned`
- **Purpose:** إدارة النقد والصناديق والعُهد.
- **Entities:** Cash Account, Petty Cash, Cash Receipt/Payment, Imprest.
- **Processes:** قبض/صرف نقدي، عهد مستديمة، جرد الصندوق، تسوية النقد.
- **Inputs → Outputs:** إيصالات نقدية → أرصدة نقدية.
- **Accounting Impact:** Dr/Cr Cash؛ فروق الصندوق (Over/Short).
- **Reports:** حركة الصندوق، رصيد النقدية، تقرير العُهد.
- **Permissions:** Cashier، Treasury، Approver (صرف كبير).
- **Workflows:** Payment: Requested→Approved→Paid.
- **Audit:** كل حركة نقدية متتبعة؛ فروق الجرد مسجّلة.
- **Integrations:** POS، AR/AP.
- **IFRS:** IAS 7 (نقد وما في حكمه). **Tax:** —.
- **Dependencies:** CoA, Journals, Bank.

### 3.2 Bank Management — 🔴 Critical `▫ planned`
- **Purpose:** إدارة الحسابات البنكية والحركات.
- **Entities:** Bank Account, Deposit/Withdrawal/Transfer, Bank Charge/Interest, Cheque.
- **Processes:** إيداع/سحب/تحويل، رسوم/فوائد، إدارة الشيكات، حسابات متعددة العملات.
- **Inputs → Outputs:** حركات بنكية → أرصدة بنكية.
- **Accounting Impact:** Dr/Cr Bank؛ رسوم/فوائد؛ تحويلات (clearing).
- **Reports:** أرصدة البنوك، حركة الحساب، الشيكات المعلّقة.
- **Permissions:** Treasury، Bank Officer، Approver.
- **Workflows:** Transfer: Created→Approved→Executed.
- **Audit:** كل حركة؛ مراقبة تغيير بيانات المستفيد.
- **Integrations:** Bank feeds/API، Payments، Collections.
- **IFRS:** IAS 7. **Tax:** ضريبة على الفوائد.
- **Dependencies:** CoA, Journals, Multi-Currency.

### 3.3 Bank Reconciliation — 🔴 Critical `▫ planned`
- **Purpose:** مطابقة كشف البنك مع دفتر البنك.
- **Entities:** Bank Statement, Statement Line, Reconciliation, Match, Exception.
- **Processes:** استيراد كشف، مطابقة آلية/يدوية، شيكات معلّقة، إيداعات بالطريق، تسويات، قفل التسوية.
- **Inputs → Outputs:** كشف بنكي → تسوية مقفلة + قيود تسوية.
- **Accounting Impact:** قيود لرسوم/فوائد/فروق مكتشفة.
- **Reports:** تقرير التسوية البنكية، البنود غير المطابقة.
- **Permissions:** Treasury، Reviewer، Approver (قفل).
- **Workflows:** Import→Match→Review→Adjust→Lock.
- **Audit:** التسوية المقفلة غير قابلة للتعديل إلا بإعادة فتح مسجّلة.
- **Integrations:** Bank API (MT940/CSV)، Reconciliation Engine.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Bank, Reconciliation Engine, Journals.

### 3.4 Payments — 🔴 Critical `▫ planned`
- **Purpose:** تنفيذ المدفوعات (موردين/رواتب/ضرائب).
- **Entities:** Payment Batch, Payment Instruction, Payment Method, Beneficiary.
- **Processes:** اقتراح سداد، دفعات مجمّعة، اعتماد متعدد، تنفيذ، تسوية.
- **Inputs → Outputs:** فواتير مستحقة → تعليمات دفع منفّذة.
- **Accounting Impact:** Dr AP/Payroll/Tax / Cr Bank؛ عبر Payment Clearing.
- **Reports:** جدول المدفوعات، الدفعات المعلّقة، تحليل السداد.
- **Permissions:** Payment Officer، Approver (بالمبلغ)، Executor — فصل المهام.
- **Workflows:** Proposed→Approved (multi-level)→Executed→Reconciled.
- **Audit:** فصل صارم Maker/Checker/Approver؛ كشف الدفعات المكررة.
- **Integrations:** Bank API، AP، Payroll، Tax.
- **IFRS:** — . **Tax:** Withholding عند الدفع.
- **Dependencies:** AP, Bank, Approval Workflows.

### 3.5 Collections — 🟡 Important `▫ planned`
- **Purpose:** إدارة تحصيل الذمم المدينة.
- **Entities:** Collection Case, Reminder, Promise-to-Pay, Dunning Level.
- **Processes:** جدولة تذكيرات، متابعة المتأخرات، خطط سداد، تصعيد.
- **Inputs → Outputs:** أعمار الديون → مقبوضات + حالات تحصيل.
- **Accounting Impact:** Dr Bank/Cr AR؛ ECL على المتأخر.
- **Reports:** تقرير التحصيل، المتأخرات، فعالية التحصيل، DSO.
- **Permissions:** Collector، Credit Controller.
- **Workflows:** Overdue→Reminder→Escalate→Legal/Write-off.
- **Audit:** كل اتصال/وعد مسجّل.
- **Integrations:** AR، Notifications، AI (تنبؤ التعثر).
- **IFRS:** IFRS 9 (ECL). **Tax:** —.
- **Dependencies:** AR, Notifications, Bank.

### 3.6 Cash Forecasting — 🟡 Important `▫ planned`
- **Purpose:** التنبؤ بالتدفقات النقدية والسيولة.
- **Entities:** Cash Flow Forecast, Scenario, Liquidity Position.
- **Processes:** توقع المقبوضات/المدفوعات، سيناريوهات، فجوة السيولة، رأس المال العامل.
- **Inputs → Outputs:** AR/AP/جداول + تاريخ → توقع نقدي.
- **Accounting Impact:** — (تحليلي، لا قيود).
- **Reports:** توقع التدفق النقدي، السيولة، رأس المال العامل، Burn/Runway.
- **Permissions:** Treasury، CFO.
- **Workflows:** —.
- **Audit:** إصدارات التوقع محفوظة.
- **Integrations:** AR، AP، Bank، AI Forecasting.
- **IFRS:** — . **Tax:** توقيت المدفوعات الضريبية.
- **Dependencies:** AR, AP, Bank, Loans.

---

# 4. OPERATIONS

### 4.1 Sales — 🔴 Critical `◼ designed (rule)`
- **Purpose:** دورة المبيعات من عرض السعر إلى الفاتورة.
- **Entities:** Quotation, Sales Order, Delivery Note, Sales Invoice, Return.
- **Processes:** عرض→أمر→تسليم→فوترة→مردودات، تسعير، خصومات.
- **Inputs → Outputs:** طلب العميل → فاتورة + قيد إيراد آلي.
- **Accounting Impact:** Dr AR/Cr Revenue+VAT؛ Dr COGS/Cr Inventory (SalesInvoiceRule).
- **Reports:** المبيعات بالمنتج/العميل/الفرع، هامش الربح، مردودات.
- **Permissions:** Sales، AR Accountant، Approver (خصم/ائتمان).
- **Workflows:** Quote→Order→Deliver→Invoice→Post.
- **Audit:** الفاتورة متتبعة للأمر والتسليم.
- **Integrations:** AR، Inventory، Tax، Projects.
- **IFRS:** IFRS 15 (التزامات الأداء). **Tax:** Output VAT.
- **Dependencies:** AR, Inventory, Tax Engine, Accounting Rules.

### 4.2 Purchases — 🔴 Critical `▫ planned`
- **Purpose:** دورة المشتريات من الطلب إلى الفاتورة.
- **Entities:** Purchase Requisition, PO, Goods Receipt (GRN), Purchase Invoice, Return.
- **Processes:** طلب→أمر شراء→استلام→فوترة، مطابقة ثلاثية.
- **Inputs → Outputs:** حاجة الشراء → فاتورة مورد + قيد آلي.
- **Accounting Impact:** Dr Inventory/Expense + Input VAT / Cr AP (عبر GRNI).
- **Reports:** المشتريات بالمورد/الصنف، أوامر معلّقة، تحليل الشراء.
- **Permissions:** Buyer، AP Accountant، Approver (بالمبلغ).
- **Workflows:** Requisition→PO Approved→Receive→Invoice→Post.
- **Audit:** مطابقة ثلاثية مسجّلة.
- **Integrations:** AP، Inventory، Procurement، Tax.
- **IFRS:** IAS 2، IAS 16. **Tax:** Input VAT، Withholding.
- **Dependencies:** AP, Inventory, Procurement, Tax Engine.

### 4.3 Expenses — 🔴 Critical `▫ planned`
- **Purpose:** إدارة مصروفات الموظفين والمطالبات.
- **Entities:** Expense Claim, Expense Category, Receipt, Corporate Card, Per Diem.
- **Processes:** مطالبة→اعتماد→سداد، مصاريف سفر، بدلات، بطاقات شركة.
- **Inputs → Outputs:** إيصالات → مطالبة معتمدة + قيد مصروف.
- **Accounting Impact:** Dr Expense + Input VAT / Cr Employee payable/Bank.
- **Reports:** المصروفات بالفئة/الموظف/المشروع، المطالبات المعلّقة.
- **Permissions:** Employee (تقديم)، Manager (اعتماد)، AP (سداد).
- **Workflows:** Submit→Approve→Reimburse.
- **Audit:** إيصالات مرفقة (hash)، كشف المطالبات المكررة.
- **Integrations:** AP، Payroll، Projects، Documents.
- **IFRS:** — . **Tax:** Input VAT على المصاريف المؤهلة.
- **Dependencies:** AP, Documents, Approval Workflows.

### 4.4 Inventory Operations — 🟡 Important `▫ planned`
- **Purpose:** الإدارة التشغيلية للمخزون (كمّي) مقابل المحاسبي.
- **Entities:** Item Master, Warehouse, Stock Movement, Stock Count, Transfer.
- **Processes:** استلام/صرف/تحويل/جرد، حد إعادة الطلب، Batch/Serial/Expiry.
- **Inputs → Outputs:** حركات مادية → أرصدة كمّية → تغذّي Inventory Accounting.
- **Accounting Impact:** يُطلق قيود عبر Inventory Accounting.
- **Reports:** أرصدة المخزون، حركة الصنف، تنبيهات إعادة الطلب، الصلاحيات.
- **Permissions:** Warehouse، Inventory Manager.
- **Workflows:** Movement→Post؛ Count→Approve→Adjust.
- **Audit:** كل حركة كمّية متتبعة.
- **Integrations:** Sales، Purchases، Manufacturing، Inventory Accounting.
- **IFRS:** IAS 2. **Tax:** —.
- **Dependencies:** Inventory Accounting, Purchases, Sales.

### 4.5 Procurement — 🟡 Important `▫ planned`
- **Purpose:** إدارة المشتريات الاستراتيجية والموردين والعقود.
- **Entities:** Vendor, RFQ, Contract, Approval Matrix, Vendor Evaluation.
- **Processes:** تأهيل موردين، مناقصات، عقود إطارية، تقييم أداء.
- **Inputs → Outputs:** احتياجات → عقود/أوامر شراء معتمدة.
- **Accounting Impact:** التزامات محتملة (Off-BS)، أوامر الشراء (encumbrance اختياري).
- **Reports:** أداء الموردين، الإنفاق، العقود المنتهية.
- **Permissions:** Procurement، Approver (بالمبلغ).
- **Workflows:** RFQ→Award→Contract→PO.
- **Audit:** قرارات الترسية مسجّلة (منع تعارض المصالح).
- **Integrations:** Purchases، AP، Documents.
- **IFRS:** IAS 37 (التزامات). **Tax:** —.
- **Dependencies:** Purchases, Documents, Approval Workflows.

### 4.6 Projects — 🟡 Important `▫ planned`
- **Purpose:** محاسبة المشاريع والعقود طويلة الأجل.
- **Entities:** Project, Task/WBS, Budget, Milestone, Timesheet, Project Cost.
- **Processes:** تكاليف المشروع، فوترة بالمرحلة، اعتراف إيراد على مدى الزمن، CWIP.
- **Inputs → Outputs:** تكاليف/إنجاز → إيراد معترف + ربحية المشروع.
- **Accounting Impact:** Contract Asset/Liability (IFRS 15)، Dr WIP، اعتراف إيراد.
- **Reports:** ربحية المشروع، الميزانية مقابل الفعلي، نسبة الإنجاز.
- **Permissions:** Project Accountant، PM، Approver.
- **Workflows:** Setup→Budget→Execute→Bill→Close.
- **Audit:** التكاليف مخصّصة للمشروع (بُعد).
- **Integrations:** Dimensions، AR، AP، Payroll، Cost Accounting.
- **IFRS:** IFRS 15 (over time)، IAS 23. **Tax:** توقيت الإيراد.
- **Dependencies:** Dimensions, AR, Cost Accounting.

---

# 5. FINANCIAL CONTROL

### 5.1 Budget — 🟡 Important `▫ planned`
- **Purpose:** إعداد ومراقبة الموازنات.
- **Entities:** Budget, Budget Version, Budget Line (بالحساب/البُعد/الفترة).
- **Processes:** موازنة سنوية/شهرية/قسم/مشروع/CAPEX، اعتماد، مراقبة الالتزام (encumbrance).
- **Inputs → Outputs:** خطط الإدارات → موازنة معتمدة.
- **Accounting Impact:** — (رقابي)؛ اختياري: التزام الموازنة قبل الصرف.
- **Reports:** Budget vs Actual، الانحرافات، استهلاك الموازنة.
- **Permissions:** Budget Owner، Finance Manager، CFO (اعتماد).
- **Workflows:** Draft→Review→Approve→Lock version.
- **Audit:** كل إصدار محفوظ؛ التعديلات مسجّلة.
- **Integrations:** GL، Dimensions، Forecast، Cost.
- **IFRS:** — . **Tax:** موازنة الضرائب.
- **Dependencies:** CoA, Dimensions, GL.

### 5.2 Forecast — 🟡 Important `▫ planned`
- **Purpose:** التنبؤ المتدحرج بالأداء المالي.
- **Entities:** Forecast, Rolling Forecast, Driver, Scenario.
- **Processes:** توقع الإيراد/المصروف/النقد، سيناريوهات، rolling forecast.
- **Inputs → Outputs:** فعلي + محركات → توقعات محدّثة.
- **Accounting Impact:** — (تحليلي).
- **Reports:** Forecast vs Actual vs Budget، تحليل الاتجاه.
- **Permissions:** Finance Manager، CFO.
- **Workflows:** —.
- **Audit:** إصدارات التوقع.
- **Integrations:** Budget، GL، AI Forecasting.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Budget, GL, AI.

### 5.3 Cost Accounting — 🟡 Important `▫ planned`
- **Purpose:** توزيع التكاليف وتحليلها.
- **Entities:** Cost Center, Cost Pool, Cost Driver, Allocation Rule, Standard Cost.
- **Processes:** تكاليف مباشرة/غير مباشرة، توزيع overhead، ABC، تكلفة معيارية، تحليل الانحرافات.
- **Inputs → Outputs:** تكاليف GL → تكاليف موزّعة بالمنتج/المشروع.
- **Accounting Impact:** قيود توزيع/إعادة تصنيف، انحرافات التكلفة المعيارية.
- **Reports:** تكلفة المنتج، تقرير مركز التكلفة، تحليل الانحرافات، الربحية.
- **Permissions:** Cost Accountant، Controller.
- **Workflows:** Setup rules→Run allocation→Review→Post.
- **Audit:** قواعد التوزيع مُصدَّرة ومتتبعة.
- **Integrations:** GL، Dimensions، Inventory، Manufacturing، Projects.
- **IFRS:** IAS 2 (تكلفة التحويل). **Tax:** —.
- **Dependencies:** GL, Dimensions, Inventory.

### 5.4 Reconciliation (Framework) — 🟡 Important `◼ designed`
- **Purpose:** إطار مطابقة عام لكل المجالات.
- **Entities:** Reconciliation, Source A/B, Match, Exception, Adjustment.
- **Processes:** مطابقة GL↔Subledger، Bank، AR/AP، Tax، Payroll، Intercompany؛ آلي/يدوي.
- **Inputs → Outputs:** مصدران → نتائج Matched/Unmatched/Exception + قيود تسوية.
- **Accounting Impact:** قيود تسوية للفروق المكتشفة.
- **Reports:** حالة التسويات، البنود المعلّقة، الاستثناءات.
- **Permissions:** Accountant، Reviewer، Approver (قفل).
- **Workflows:** Load→Match→Review→Adjust→Lock.
- **Audit:** تنبيه لأي رصيد وسيط/معلّق غير مبرّر (بند 35).
- **Integrations:** كل الموديولات ذات الأرصدة.
- **IFRS:** — . **Tax:** تسوية ضريبية.
- **Dependencies:** GL, Subledgers, Bank.

### 5.5 Closing (Period/Year-End) — 🔴 Critical `▫ planned`
- **Purpose:** أتمتة إقفال الفترة والسنة.
- **Entities:** Closing Checklist, Closing Task, Adjusting Entry, Year-End Run.
- **Processes:** قائمة إقفال (تسويات بنكية/AR/AP/مخزون/إهلاك/استحقاقات/FX/ضريبة/رواتب/intercompany)، قيود تسوية، إقفال الأرباح للمرحّلة.
- **Inputs → Outputs:** ميزان مراجعة → فترة مقفلة + قوائم مالية.
- **Accounting Impact:** قيود تسوية + قيود إقفال (system-only على 7101/7102).
- **Reports:** قائمة الإقفال، حالة المهام، ميزان بعد التسوية.
- **Permissions:** Chief Accountant، CFO (اعتماد نهائي).
- **Workflows:** Reconcile→Adjust→Review→Approve→Close→Lock.
- **Audit:** كل مهمة إقفال متتبعة؛ الإقفال يُسجَّل.
- **Integrations:** كل الموديولات، Periods، Reconciliation.
- **IFRS:** IAS 10، عرض القوائم (IFRS 18). **Tax:** إقرار الفترة.
- **Dependencies:** GL, Periods, Reconciliation, Subledgers.

### 5.6 Consolidation — 🟡 Important `▫ planned`
- **Purpose:** توحيد القوائم لمجموعة الشركات.
- **Entities:** Group, Consolidation Ledger, Elimination Entry, NCI, Chart Mapping.
- **Processes:** ربط الدلائل، مطابقة intercompany، استبعاد، ترجمة العملة، حصص الأقلية، قيود التوحيد.
- **Inputs → Outputs:** موازين الشركات → ميزان/قوائم موحّدة.
- **Accounting Impact:** قيود استبعاد وترجمة في دفتر التوحيد.
- **Reports:** ميزان موحّد، قوائم موحّدة، تقرير الاستبعادات.
- **Permissions:** Group Finance، CFO.
- **Workflows:** Collect→Map→Eliminate→Translate→Consolidate→Review.
- **Audit:** كل قيد استبعاد متتبع.
- **Integrations:** Multi-Entity، Multi-Currency، Intercompany.
- **IFRS:** IFRS 10/11/12، IAS 21/28، IFRS 3 (اندماج). **Tax:** إقرار موحّد حيث ينطبق.
- **Dependencies:** GL, Multi-Entity, Multi-Currency, Intercompany.

---

# 6. TAX

### 6.1 Tax Engine — 🔴 Critical `▫ planned`
- **Purpose:** حساب الضرائب القابل للتهيئة (لا نِسَب Hardcoded).
- **Entities:** Tax Type, Tax Rate, Tax Rule, Tax Code, Exemption, Tax Period (كلها Configuration بتاريخ سريان ومرجع قانوني).
- **Processes:** حساب VAT/WHT/CIT، ضرائب مدخلات/مخرجات، إعفاءات، قواعد لكل دولة.
- **Inputs → Outputs:** مبلغ + كود ضريبي → مبلغ ضريبي + سطور قيد.
- **Accounting Impact:** يولّد سطور الضريبة (Input/Output/Payable).
- **Reports:** ملخص الضرائب، الضريبة بالكود/الفترة.
- **Permissions:** Tax Officer، Admin (تكوين النِسَب + اعتماد).
- **Workflows:** Configure rate→Approve→Effective date؛ إصدار جديد لكل تغيير.
- **Audit:** كل نسبة/قاعدة versioned بمرجعها القانوني (بند 22).
- **Integrations:** Sales، Purchases، AR، AP، Payroll، Localization.
- **IFRS:** فصل المحاسبي عن الضريبي. **Tax:** جوهر الموديول.
- **Dependencies:** CoA, Accounting Rules, Country Localization.

### 6.2 Tax Returns — 🟡 Important `▫ planned`
- **Purpose:** إعداد وتقديم الإقرارات الضريبية.
- **Entities:** Tax Return, Return Line, Filing, Payment.
- **Processes:** تجميع بيانات الفترة، إعداد الإقرار، تقديم، سداد.
- **Inputs → Outputs:** حركات ضريبية → إقرار جاهز للتقديم.
- **Accounting Impact:** Dr Output VAT/Cr Input VAT + Payable؛ السداد.
- **Reports:** الإقرار الضريبي، تاريخ التقديم، الالتزامات.
- **Permissions:** Tax Officer، CFO (اعتماد التقديم).
- **Workflows:** Prepare→Review→Approve→File→Pay.
- **Audit:** كل إقرار وتقديم مسجّل.
- **Integrations:** Tax Engine، Government portals (اختياري)، Bank.
- **IFRS:** IAS 12. **Tax:** جوهر الموديول.
- **Dependencies:** Tax Engine, GL, Bank.

### 6.3 Withholding Tax — 🟡 Important `▫ planned`
- **Purpose:** إدارة ضريبة الاستقطاع على المدفوعات.
- **Entities:** WHT Rule, WHT Certificate, WHT Line.
- **Processes:** حساب الاستقطاع عند الدفع، إصدار شهادات، توريد.
- **Inputs → Outputs:** دفعة مورد → مبلغ مستقطع + شهادة.
- **Accounting Impact:** Dr AP / Cr Bank + WHT Payable.
- **Reports:** الاستقطاعات، الشهادات، الالتزام المستحق.
- **Permissions:** Tax Officer، AP Accountant.
- **Workflows:** Calculate→Withhold→Certificate→Remit.
- **Audit:** كل استقطاع وشهادة مسجّل.
- **Integrations:** AP، Payments، Tax Returns.
- **IFRS:** — . **Tax:** جوهر الموديول.
- **Dependencies:** Tax Engine, AP, Payments.

### 6.4 Tax Reconciliation — 🟡 Important `▫ planned`
- **Purpose:** مطابقة الضريبة المحاسبية مع المقدّمة، وتسوية الفروق.
- **Entities:** Tax Reconciliation, Permanent/Temporary Difference, Deferred Tax.
- **Processes:** مطابقة GL الضريبي مع الإقرارات، حساب الضريبة المؤجلة (IAS 12).
- **Inputs → Outputs:** GL + إقرارات → فروق + ضريبة مؤجلة.
- **Accounting Impact:** Dr/Cr Deferred Tax Asset/Liability + Expense.
- **Reports:** تسوية الضريبة، الفروق الدائمة/المؤقتة، الضريبة الفعّالة.
- **Permissions:** Tax Officer، Chief Accountant.
- **Workflows:** Reconcile→Compute deferred→Post→Review.
- **Audit:** الفروق موثّقة.
- **Integrations:** Tax Engine، GL، Multi-Book (دفتر TAX).
- **IFRS:** IAS 12. **Tax:** جوهر الموديول.
- **Dependencies:** Tax Engine, Multi-Book, Reconciliation.

### 6.5 Country Localization — 🔴 Critical (Libya) `▫ planned`
- **Purpose:** طبقة امتثال لكل دولة (ليبيا أولاً) — Plugin مستقل.
- **Entities:** Country Pack, Legal Reference, Statutory Report, Invoice Format, Retention Rule.
- **Processes:** نِسَب/قواعد محلية، إقرارات رسمية، متطلبات الفوترة، الاحتفاظ بالمستندات.
- **Inputs → Outputs:** تشريعات محلية → قواعد + تقارير قانونية.
- **Accounting Impact:** حسابات وقواعد محلية (VAT/دمغة/تضامن/ضمان اجتماعي).
- **Reports:** التقارير القانونية للجهات الرسمية.
- **Permissions:** Compliance Officer، Tax Officer.
- **Workflows:** Update law→Approve→Version→Effective.
- **Audit:** كل قاعدة بمرجعها الرسمي وإصدارها (بند 23).
- **Integrations:** Tax Engine، Regulatory Reports، Documents.
- **IFRS:** الإطار المحلي مقابل IFRS. **Tax:** جوهر الموديول — لا نسبة مفترضة.
- **Dependencies:** Tax Engine, Compliance, Reporting.

---

# 7. REPORTING

### 7.1 Financial Statements — 🔴 Critical `▫ planned`
- **Purpose:** إنتاج القوائم المالية القانونية.
- **Entities:** Statement Definition, FS Line, Note, Mapping.
- **Processes:** SFP، P&L، الدخل الشامل، التغير في الحقوق، التدفقات النقدية، الإيضاحات.
- **Inputs → Outputs:** GL + FS mapping → قوائم مالية.
- **Accounting Impact:** — (طبقة عرض).
- **Reports:** القوائم الخمس + الإيضاحات، قابلة للتخصيص (كيان/صناعة/إطار/دولة).
- **Permissions:** Chief Accountant، CFO، Auditor (قراءة).
- **Workflows:** Generate→Review→Approve→Publish.
- **Audit:** Drill-down: Statement→Account→Ledger→Journal→Document.
- **Integrations:** GL، Consolidation، Notes.
- **IFRS:** IAS 1/IFRS 18، IAS 7، IFRS كامل. **Tax:** —.
- **Dependencies:** GL, Closing, Consolidation.

### 7.2 Management Reports — 🟡 Important `▫ planned`
- **Purpose:** تقارير إدارية للقرار.
- **Entities:** Report Pack, KPI, Dashboard.
- **Processes:** P&L بالقسم/الفرع/المنتج/العميل/المشروع، هوامش، EBITDA، رأس المال العامل.
- **Inputs → Outputs:** GL + Dimensions → تقارير إدارية.
- **Accounting Impact:** —.
- **Reports:** ربحية المنتج/العميل، تحليل الإيراد/المصروف، النسب (ROA/ROE/Current/Quick).
- **Permissions:** Managers، CFO.
- **Workflows:** —.
- **Audit:** مصدر كل رقم متتبع.
- **Integrations:** GL، Dimensions، Budget، Analytics.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** GL, Dimensions, Cost Accounting.

### 7.3 Regulatory Reports — 🟡 Important `▫ planned`
- **Purpose:** التقارير للجهات الرسمية.
- **Entities:** Regulatory Report, Submission, Format Template.
- **Processes:** تجميع البيانات وفق متطلبات الجهة، تنسيق، تقديم.
- **Inputs → Outputs:** GL + قواعد محلية → تقارير قانونية.
- **Accounting Impact:** —.
- **Reports:** حسب الجهة (ضرائب، ضمان اجتماعي، إحصاء…).
- **Permissions:** Compliance، Tax Officer، CFO.
- **Workflows:** Prepare→Review→Approve→Submit.
- **Audit:** كل تقديم مسجّل.
- **Integrations:** Localization، Tax، Government systems.
- **IFRS:** — . **Tax:** متطلبات محلية.
- **Dependencies:** GL, Country Localization, Compliance.

### 7.4 Custom Reports (Report Builder) — 🟡 Important `▫ planned`
- **Purpose:** بناء تقارير مخصّصة بلا برمجة.
- **Entities:** Report Definition, Data Source, Filter, Layout (versioned).
- **Processes:** اختيار حقول/أبعاد/فترات، فلاتر، جدولة، تصدير.
- **Inputs → Outputs:** تعريف المستخدم → تقرير مخصّص.
- **Accounting Impact:** —.
- **Reports:** أي تقرير يبنيه المستخدم.
- **Permissions:** Report Author، Viewer.
- **Workflows:** Design→Save version→Share.
- **Audit:** تعريفات التقارير versioned.
- **Integrations:** كل مصادر البيانات، Export.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** GL, Dimensions, Import/Export.

### 7.5 Analytics — 🟢 Optional `▫ planned`
- **Purpose:** تحليلات مالية متقدمة ومؤشرات.
- **Entities:** Metric, Trend, Benchmark, Data Cube.
- **Processes:** نمو الإيراد، الهوامش، EBITDA، DSO/DPO، أيام المخزون، Burn/Runway، النسب.
- **Inputs → Outputs:** GL + تاريخ → لوحات تحليلية ومقارنات.
- **Accounting Impact:** —.
- **Reports:** لوحات KPI، مقارنات (شهر/ربع/سنة/موازنة/توقع).
- **Permissions:** CFO، Analyst.
- **Workflows:** —.
- **Audit:** —.
- **Integrations:** BI platforms، AI Analysis.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** GL, Reporting, AI.

---

# 8. GOVERNANCE

### 8.1 Roles — 🔴 Critical `▫ planned (auth ✅)`
- **Purpose:** تعريف الأدوار الوظيفية القابلة للتخصيص.
- **Entities:** Role, Role Assignment (Super Admin…CFO…Auditor…Read Only).
- **Processes:** إنشاء أدوار، تعيين للمستخدمين، أدوار لكل كيان.
- **Inputs → Outputs:** تعريف الدور → صلاحيات مجمّعة.
- **Accounting Impact:** — (رقابي).
- **Reports:** مصفوفة الأدوار، المستخدمون بالدور.
- **Permissions:** Security Admin.
- **Workflows:** Create→Assign→Review.
- **Audit:** كل تعيين مسجّل.
- **Integrations:** Permissions، Multi-tenancy.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Multi-tenancy, Permissions.

### 8.2 Permissions — 🔴 Critical `▫ planned`
- **Purpose:** التحكم الدقيق بالوصول (RBAC + ABAC).
- **Entities:** Permission, Policy, Resource Scope.
- **Processes:** RBAC، ABAC (بالمبلغ/الكيان/البُعد)، أقل صلاحية، إخفاء بيانات حساسة.
- **Inputs → Outputs:** سياسات → قرارات وصول.
- **Accounting Impact:** يحمي كل عملية محاسبية.
- **Reports:** مصفوفة الصلاحيات، تعارض المهام (SoD).
- **Permissions:** Security Admin.
- **Workflows:** Define→Test→Activate.
- **Audit:** قرارات الوصول مسجّلة.
- **Integrations:** كل الموديولات.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Roles, Multi-tenancy.

### 8.3 Approval Workflows — 🔴 Critical `▫ planned`
- **Purpose:** محرك موافقات ديناميكي (بند 33).
- **Entities:** Workflow, Approval Step, Rule (بالمبلغ/القسم/النوع/المخاطر), Delegation.
- **Processes:** مسارات موافقة متعددة المستويات، تفويض، تصعيد، فصل المهام.
- **Inputs → Outputs:** معاملة → سلسلة موافقات معتمدة.
- **Accounting Impact:** يحكم متى يُرحّل القيد.
- **Reports:** الموافقات المعلّقة، زمن الدورة، سجل الموافقات.
- **Permissions:** Workflow Admin، Approvers.
- **Workflows:** — (هو المحرك نفسه): مثال Invoice: Created→Reviewed→Approved→Posted.
- **Audit:** كل خطوة موافقة مسجّلة بالمستخدم/الوقت.
- **Integrations:** كل الموديولات المعاملاتية، Notifications.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Permissions, Notifications.

### 8.4 Audit Trail — 🔴 Critical `✅ built`
- **Purpose:** سجل غير قابل للتغيير لكل عملية.
- **Entities:** `AuditLog` (who/what/when/before/after/IP/reason).
- **Processes:** التقاط كل حدث، منع حذف السجلات المحاسبية.
- **Inputs → Outputs:** أحداث النظام → سجل تدقيق.
- **Accounting Impact:** التصحيح بـ Reversal لا Delete.
- **Reports:** أثر التدقيق، نشاط المستخدم، تغييرات الفترات، Exception reports.
- **Permissions:** Auditor (قراءة)، لا أحد (حذف).
- **Workflows:** —.
- **Audit:** هو الموديول نفسه.
- **Integrations:** كل الموديولات.
- **IFRS:** متطلب حوكمة. **Tax:** أثر ضريبي مطلوب.
- **Dependencies:** —.

### 8.5 Internal Controls — 🟡 Important `▫ planned`
- **Purpose:** ضوابط داخلية وفصل المهام.
- **Entities:** Control, Control Test, Segregation Matrix (Maker/Checker/Approver/Poster).
- **Processes:** تعريف الضوابط، اختبارها، فصل المهام، ضوابط الحسابات الوسيطة.
- **Inputs → Outputs:** إطار الضوابط → ضوابط مفعّلة ومختبرة.
- **Accounting Impact:** يمنع القيود غير المصرّح بها.
- **Reports:** فعالية الضوابط، تعارض المهام، الاستثناءات.
- **Permissions:** Internal Auditor، Controller.
- **Workflows:** Define→Test→Remediate.
- **Audit:** نتائج اختبار الضوابط مسجّلة.
- **Integrations:** Approval، Permissions، Risk.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Permissions, Approval Workflows, Audit Trail.

### 8.6 Risk & Fraud — 🟡 Important `▫ planned`
- **Purpose:** كشف المخاطر والاحتيال (بند 34).
- **Entities:** Risk Rule, Risk Score, Alert, Case.
- **Processes:** كشف الفواتير/المدفوعات المكررة، القيود الرجعية/غير المعتادة، النشر في العطلات، تغيير بنك المورد.
- **Inputs → Outputs:** معاملات → تنبيهات + درجة مخاطر.
- **Accounting Impact:** يوقف/يعلّم المعاملات المشبوهة.
- **Reports:** تقرير المخاطر، التنبيهات، الحالات المفتوحة.
- **Permissions:** Risk Officer، Internal Auditor.
- **Workflows:** Detect→Alert→Investigate→Resolve.
- **Audit:** كل تنبيه/قرار مسجّل.
- **Integrations:** كل الموديولات المعاملاتية، AI Anomaly.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Audit Trail, AI Anomaly Detection.

### 8.7 Compliance — 🟡 Important `▫ planned`
- **Purpose:** الامتثال التنظيمي والاحتفاظ بالمستندات.
- **Entities:** Compliance Requirement, Policy, Retention Rule, Attestation.
- **Processes:** تتبع المتطلبات، سياسات الاحتفاظ، الإقرارات، متطلبات التدقيق.
- **Inputs → Outputs:** تشريعات → التزامات متتبعة.
- **Accounting Impact:** — (رقابي).
- **Reports:** حالة الامتثال، المتطلبات المتأخرة.
- **Permissions:** Compliance Officer، CFO.
- **Workflows:** Track→Attest→Report.
- **Audit:** كل إقرار امتثال مسجّل.
- **Integrations:** Localization، Documents، Regulatory Reports.
- **IFRS:** الإفصاحات. **Tax:** الامتثال الضريبي.
- **Dependencies:** Country Localization, Documents.

---

# 9. PLATFORM

### 9.1 Multi-tenancy — 🔴 Critical `▫ planned (schema ✅)`
- **Purpose:** عزل المؤسسات/الشركات/المستخدمين.
- **Entities:** `Organization`, `Company`, Tenant, User membership.
- **Processes:** عزل بيانات المستأجر (company_id scope)، عدة شركات/فروع/أدوار.
- **Inputs → Outputs:** تعريف المستأجر → بيئة معزولة.
- **Accounting Impact:** كل قيد موسوم بالكيان.
- **Reports:** استخدام المستأجرين، الكيانات.
- **Permissions:** Super Admin، Org Owner.
- **Workflows:** Provision→Configure→Activate.
- **Audit:** عمليات المستأجر مسجّلة.
- **Integrations:** كل الموديولات.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** —.

### 9.2 API — 🔴 Critical `▫ planned`
- **Purpose:** كل موديول API-first (بند 45).
- **Entities:** API Key, Token, Webhook, Rate Limit, Idempotency Key.
- **Processes:** REST، مصادقة/تفويض، OAuth، rate limiting، idempotency، audit logging.
- **Inputs → Outputs:** طلبات API → عمليات آمنة + webhooks.
- **Accounting Impact:** واجهة برمجية لكل عملية محاسبية.
- **Reports:** استخدام API، الأخطاء.
- **Permissions:** API scopes.
- **Workflows:** —.
- **Audit:** كل استدعاء مسجّل.
- **Integrations:** كل الأنظمة الخارجية.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** Permissions, Multi-tenancy.

### 9.3 Integrations — 🟡 Important `▫ planned`
- **Purpose:** إطار ربط الأنظمة الخارجية (بند 46).
- **Entities:** Connector, Mapping, Sync Job, Integration Log.
- **Processes:** ربط بنوك/POS/تجارة إلكترونية/رواتب/CRM/حكومة/BI.
- **Inputs → Outputs:** أنظمة خارجية → بيانات مزامنة.
- **Accounting Impact:** يغذّي القيود عبر القواعد.
- **Reports:** حالة التكاملات، سجل المزامنة.
- **Permissions:** Integration Admin.
- **Workflows:** Configure→Test→Activate→Monitor.
- **Audit:** كل مزامنة مسجّلة.
- **Integrations:** جوهر الموديول.
- **IFRS:** — . **Tax:** بوابات ضريبية حكومية.
- **Dependencies:** API.

### 9.4 Notifications — 🟡 Important `▫ planned`
- **Purpose:** التنبيهات والإشعارات.
- **Entities:** Notification, Channel (email/SMS/in-app), Subscription, Template.
- **Processes:** تنبيهات الموافقات/الاستحقاقات/الأرصدة الشاذة/المتأخرات.
- **Inputs → Outputs:** أحداث → إشعارات.
- **Accounting Impact:** —.
- **Reports:** سجل الإشعارات.
- **Permissions:** User preferences.
- **Workflows:** —.
- **Audit:** الإشعارات الحرجة مسجّلة.
- **Integrations:** Approval، Collections، Risk.
- **IFRS:** — . **Tax:** تذكيرات المواعيد الضريبية.
- **Dependencies:** —.

### 9.5 Documents — 🟡 Important `▫ planned`
- **Purpose:** إدارة المستندات والأدلة (بند 36–37).
- **Entities:** Document, Version, Metadata, Hash, Access Control.
- **Processes:** ربط مستند بكل معاملة، إصدارات، hash، صلاحيات، نوع/تاريخ/مصدر.
- **Inputs → Outputs:** مستندات → أدلة مرتبطة بالقيود.
- **Accounting Impact:** دليل داعم لكل قيد.
- **Reports:** المستندات المفقودة، انتهاء الصلاحية.
- **Permissions:** حسب المستند/الدور.
- **Workflows:** Upload→Link→Version.
- **Audit:** رافع/وقت/hash لكل مستند.
- **Integrations:** كل الموديولات، Compliance (الاحتفاظ).
- **IFRS:** أدلة الإفصاح. **Tax:** الاحتفاظ القانوني بالمستندات.
- **Dependencies:** —.

### 9.6 Import / Export — 🔴 Critical `▫ planned`
- **Purpose:** استيراد/تصدير البيانات مع تحقق (بند 44).
- **Entities:** Import Template, Mapping, Validation Result, Export Job.
- **Processes:** Excel/CSV/PDF/JSON/XML/API، استيراد الدليل/الأرصدة/العملاء/الأصناف/الحركات، dry-run/commit/rollback.
- **Inputs → Outputs:** ملفات → بيانات مُتحقَّقة أو تقرير أخطاء.
- **Accounting Impact:** استيراد الأرصدة الافتتاحية والترحيل.
- **Reports:** نتائج الاستيراد، الأخطاء.
- **Permissions:** Data Admin.
- **Workflows:** Map→Validate→Preview→Commit/Rollback.
- **Audit:** كل استيراد مسجّل.
- **Integrations:** Migration Engine، كل الموديولات.
- **IFRS:** — . **Tax:** —.
- **Dependencies:** CoA, Journals.

### 9.7 Localization (i18n) — 🟡 Important `▫ planned`
- **Purpose:** تعدد اللغات RTL/LTR والمصطلحات المحاسبية.
- **Entities:** Locale, Translation, Terminology.
- **Processes:** عربي/إنجليزي أولاً، RTL/LTR، مصطلحات محاسبية ثنائية.
- **Inputs → Outputs:** locale → واجهة/تقارير مترجمة.
- **Accounting Impact:** أسماء الحسابات ثنائية اللغة.
- **Reports:** قوائم بأي لغة.
- **Permissions:** Admin.
- **Workflows:** —.
- **Audit:** —.
- **Integrations:** كل الواجهات/التقارير.
- **IFRS:** مصطلحات معيارية. **Tax:** مصطلحات محلية.
- **Dependencies:** —.

---

# 10. AI (كلها 🟢 Optional — لا صلاحية ترحيل مباشر؛ Draft→Review→Approve→Post، بند 47)

### 10.1 Accounting Assistant — 🟢 Optional `▫ planned`
- **Purpose:** مساعد محاسبي يجيب ويشرح ويقترح (بلا ترحيل مباشر).
- **Entities:** Conversation, Suggestion, Context.
- **Processes:** اقتراح حساب، شرح معاملة/قائمة، إجابة أسئلة محاسبية.
- **Inputs → Outputs:** سؤال + سياق GL → إجابة/اقتراح.
- **Accounting Impact:** اقتراحات فقط → تمر بالاعتماد.
- **Reports:** سجل الاقتراحات.
- **Permissions:** حسب دور المستخدم؛ AI بلا صلاحية ترحيل.
- **Workflows:** Suggest→Review→Approve→Post.
- **Audit:** كل اقتراح AI مُعلَّم ومتتبع.
- **Integrations:** GL، Reporting.
- **Dependencies:** GL, Governance.

### 10.2 Anomaly Detection — 🟢 Optional `▫ planned`
- **Purpose:** كشف الشذوذ في القيود والأرصدة.
- **Processes:** قيم شاذة، أنماط غير معتادة، ازدواجية، توقيت مريب.
- **Inputs → Outputs:** حركات → تنبيهات شذوذ.
- **Accounting Impact:** يعلّم لا يمنع (يغذّي Risk).
- **Reports:** الشذوذات المكتشفة.
- **Permissions:** Auditor، Risk.
- **Audit:** التنبيهات مسجّلة. **Dependencies:** Audit Trail, Risk.

### 10.3 Journal Suggestions — 🟢 Optional `▫ planned`
- **Purpose:** اقتراح قيود من مستندات/أنماط.
- **Processes:** اقتراح توزيع الحسابات، قيود متكررة، تصنيف تلقائي.
- **Inputs → Outputs:** مستند → مسودة قيد مقترحة.
- **Accounting Impact:** Draft فقط → اعتماد.
- **Permissions:** Accountant. **Audit:** مصدر الاقتراح. **Dependencies:** Accounting Rules, Documents.

### 10.4 Forecasting (AI) — 🟢 Optional `▫ planned`
- **Purpose:** تنبؤ ذكي بالتدفق النقدي/الأداء.
- **Processes:** نماذج تنبؤ، سيناريوهات، اكتشاف الاتجاهات.
- **Inputs → Outputs:** تاريخ مالي → توقعات.
- **Accounting Impact:** —. **Reports:** توقعات. **Dependencies:** Cash Forecasting, Forecast.

### 10.5 Financial Analysis (AI) — 🟢 Optional `▫ planned`
- **Purpose:** تحليل وشرح الأداء المالي.
- **Processes:** شرح تغيّر الأرباح، تحليل الانحرافات، ملخص الشهر، مصروف غير معتاد.
- **Inputs → Outputs:** GL + تقارير → رؤى مفسَّرة.
- **Accounting Impact:** —. **Reports:** تحليلات مفسَّرة. **Dependencies:** Reporting, Analytics.

---

# مصفوفة الأولويات (Priority Matrix)

### 🔴 Critical (النواة التي لا يعمل النظام بدونها) — 28 موديول
Chart of Accounts✅ · General Ledger✅ · Journals✅ · Periods✅ · Accounting Rules◼ · Dimensions✅ · Multi-Currency◼ · AR · AP · Fixed Assets · Cash · Bank · Bank Reconciliation · Payments · Sales◼ · Purchases · Expenses · Tax Engine · Country Localization(Libya) · Closing · Financial Statements · Roles · Permissions · Approval Workflows · Audit Trail✅ · Multi-tenancy · API · Import/Export

### 🟡 Important (لازم لـ ERP كامل — الموجة الثانية) — 28 موديول
Multi-Book◼ · Inventory Accounting · Inventory Operations · Payroll Accounting · Loans · Leases · Collections · Cash Forecasting · Procurement · Projects · Budget · Forecast · Cost Accounting · Reconciliation◼ · Consolidation · Tax Returns · Withholding · Tax Reconciliation · Management Reports · Regulatory Reports · Custom Reports · Internal Controls · Risk & Fraud · Compliance · Integrations · Notifications · Documents · Localization

### 🟢 Optional (قيمة مضافة / لاحقاً) — 7 موديولات
Investments · Analytics · AI Assistant · AI Anomaly Detection · AI Journal Suggestions · AI Forecasting · AI Financial Analysis

> الرموز: ✅ مبني (المرحلة 1) · ◼ مُصمَّم (وثائق/هيكل) · بقيتها ▫ مخطط.

# تسلسل البناء المقترح (Build Sequence) — يخدم خارطة الطريق `00`

| الموجة | الموديولات | الحالة |
|---|---|---|
| **W0 — النواة** ✅ | CoA · GL · Journals · Periods · Dimensions · Multi-Book/Currency (schema) · Audit · Accounting Engine + Rules(scaffold) | منجز |
| **W1 — الحوكمة + المنصة الدنيا** | Multi-tenancy scoping · Roles · Permissions · Approval Workflows · Import/Export · Documents | التالي |
| **W2 — الدفاتر المساعدة التشغيلية** | AR · AP · Sales · Purchases · Expenses · Cash · Bank · Bank Reconciliation · Payments · Tax Engine + Libya | |
| **W3 — الأصول والمخزون** | Inventory (Acct+Ops) · Fixed Assets + Depreciation · Procurement | |
| **W4 — الإقفال والتقارير** | Closing · Financial Statements · Reconciliation framework · Management/Regulatory/Custom Reports | |
| **W5 — المعايير المتقدمة** | Revenue(IFRS 15) · Leases(IFRS 16) · Loans/Investments(IFRS 9) · Deferred Tax · Payroll | |
| **W6 — التخطيط والتحكم** | Budget · Forecast · Cost Accounting · Projects · Cash Forecasting · Collections | |
| **W7 — المجموعة والامتثال** | Consolidation · Internal Controls · Risk/Fraud · Compliance · Tax Returns/WHT/Reconciliation | |
| **W8 — التكامل والذكاء** | API (full) · Integrations · Notifications · Analytics · AI (كل الموديولات) | |

**قاعدة الانتقال (بند 72–73):** لا موجة تبدأ قبل اجتياز سابقتها Definition of Done (Accounting logic · DB · Validation · Authorization · Audit · Journal generation · Reversal · Period locking · Multi-currency · Tax · Reporting · API · Tests · Edge cases · Reconciled balances).

**الفهرس البرمجي:** `docs/module-map-index.json` (المجموعة/الأولوية/الحالة/التبعيات لكل موديول — لبناء رسم التبعيات وتتبع التقدّم).

