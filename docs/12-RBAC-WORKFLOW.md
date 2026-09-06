# تجربة المستخدم من منظور Business Workflow + نموذج التفويض
> بلا أي نقاش UI/تصميم. المحتوى: مسارات العمل لكل دور مالي، ونموذج صلاحيات دقيق **Role + Permission + Scope + Segregation of Duties**.

## 1. نموذج قرار الوصول (Authorization Decision)

الوصول **ليس** «دور فقط». كل عملية تُقيَّم بأربع طبقات معاً:

```
ALLOW(user, action, resource) =
    RBAC   : المستخدم يملك Permission للـ action        (ماذا يستطيع)
  ∧ SCOPE  : الـ resource داخل نطاق المستخدم (ABAC)      (على ماذا)
  ∧ SoD    : لا يخالف فصل المهام                          (بأي دور نزاهة)
  ∧ COND   : شروط السياق (فترة مفتوحة، ضمن حد المبلغ…)     (تحت أي شرط)
```
رفض أي طبقة = رفض العملية. القرار يُسجَّل في Audit Trail (مسموح/مرفوض + السبب).

## 2. نموذج النطاق (Scope / ABAC)

لكل تعيين دور، نطاق مكوّن من الأبعاد التالية:

| بُعد النطاق | القيم | مثال |
|---|---|---|
| `org_scope` | tenant واحد | المؤسسة |
| `company_scope` | `all` \| قائمة شركات \| `group` (أم+تابعات) | Accountant → [Company A] · CFO → group |
| `dimension_scope` | كل الأبعاد \| فرع/مركز تكلفة/مشروع محدد | محاسب فرع طرابلس فقط |
| `book_scope` | `all` \| LOCAL \| IFRS \| TAX | Tax Officer → TAX |
| `data_mode` | `read_write` \| `read_only` \| `approve_only` | Auditor → read_only |
| `amount_band` | حد أعلى للاعتماد | حسب Approval Matrix |
| `record_ownership` | `own` \| `team` \| `all` | لفحص self-approval |

عزل المستأجر (`company_id` scope) مفروض على مستوى الاستعلام — لا تسريب بيانات بين الشركات.

## 3. تصنيف الصلاحيات (Permission Taxonomy) — `resource.action`

> القائمة الكاملة (Machine-readable) في `docs/authz/permissions.json`. ملخص بالمجموعة:

- **GL/Core:** `coa.view/manage` · `journal.view/create/submit/approve/post/reverse/adjust` · `gl.view` · `trialbalance.view` · `period.view/close_soft/close_hard/reopen` · `dimension.manage` · `book.manage` · `rules.manage` · `policy.manage` · `fx.rate.manage/revalue`
- **AR:** `ar.customer.view/manage` · `ar.credit.approve` · `ar.invoice.view/create/approve` · `ar.receipt.create/allocate` · `ar.writeoff.request/approve` · `ar.statement.view`
- **AP:** `ap.supplier.view/manage` · `ap.supplier.bank.manage` · `ap.bill.view/create/match/approve` · `ap.payment.create/approve/execute`
- **Treasury:** `bank.view/reconcile/reconcile_lock` · `cash.view/manage` · `payment.batch.create/approve` · `payment.execute`
- **Expenses:** `expense.create/approve/reimburse`
- **Control:** `recon.perform/review/lock` · `closing.task_perform/review/execute` · `budget.view/manage/approve` · `forecast.view/manage` · `cashflow.view` · `cost.view/manage` · `consolidation.view/run`
- **Reporting:** `report.financial.view` · `report.management.view` · `report.regulatory.view` · `report.custom.build` · `analytics.view` · `risk.view`
- **Governance:** `audit.view/export` · `controls.test/manage` · `role.manage` · `permission.manage` · `workflow.manage` · `user.manage`

---

## 4. الأدوار — المهمة، النطاق، الصلاحيات، فصل المهام، قوائم العمل، المسارات

### 4.1 Accountant (محاسب) — التنفيذ التشغيلي
- **المهمة:** تسجيل العمليات اليومية وتحضيرها للاعتماد.
- **النطاق:** `company_scope = [Company A]` (شركة واحدة، قد تُقيَّد بفرع) · `data_mode = read_write` · `amount_band = 0` (لا يعتمد) · `record_ownership = own`.
- **الصلاحيات:** `journal.create/submit/view` · `ar.invoice.create` · `ar.receipt.create/allocate` · `ar.customer.view` · `ap.bill.create/match` · `ap.supplier.view` · `expense.create` · `bank.reconcile` (تنفيذ) · `recon.perform` · `closing.task_perform` · `gl.view` · `report.financial.view` (شركته).
- **لا يملك:** `*.approve` · `*.post` · `period.close_*` · `*.execute` · `policy.manage`.
- **فصل المهام:** يُنشئ فقط؛ لا يعتمد ولا يرحّل. قيوده تذهب لقائمة Senior.
- **قوائم العمل:** «مسوداتي» · «مرفوضة لإعادة العمل» · «مهام الإقفال المسندة» · «بنود التسوية غير المطابقة».
- **المسارات:**
  - تسجيل قيد: Create draft → attach documents → **Submit** → (ينتقل لـ Senior).
  - فاتورة عميل: Create invoice → (Engine ينشئ قيد AR draft) → Submit.
  - متابعة عميل/مورد: عرض كشف الحساب والأعمار (view فقط).
  - تسوية بنكية: Import statement → auto/manual match → **يترك القفل لـ Senior**.
  - إقفال: تنفيذ مهام الإقفال المسندة (تسويات AR/AP/مخزون) → Submit للمراجعة.

### 4.2 Senior Accountant (محاسب أول) — المراجعة والاعتماد الأول
- **المهمة:** Review · Approve · Adjust · Reconcile · Close (مبدئي).
- **النطاق:** `company_scope = [Company A (+B)]` · `data_mode = read_write` · `amount_band = Tier-1` · `record_ownership = team`.
- **الصلاحيات:** كل صلاحيات المحاسب **+** `journal.approve/post/adjust` · `ar.invoice.approve` · `ar.writeoff.request` · `ap.bill.approve` (ضمن الحد) · `recon.review/lock` · `bank.reconcile_lock` · `period.close_soft`.
- **لا يملك:** `period.close_hard` · `policy.manage` · `payment.approve/execute` (تُفصل للخزينة/المدير) · اعتماد فوق حده.
- **فصل المهام:** **لا يعتمد قيداً أنشأه بنفسه** (SoD-1). إن لزم إنشاء وتعديل، الاعتماد يصعد لـ Chief.
- **قوائم العمل:** «بانتظار اعتمادي» · «تسويات للمراجعة» · «مهام إقفال للمراجعة».
- **المسارات:**
  - اعتماد قيد: Review draft → Approve → **Post** (إن لم يكن منشئه) → GL.
  - تسوية/تعديل: إنشاء Adjustment entry (لا Delete) → post.
  - قفل التسوية: مراجعة المطابقة → Lock reconciliation.
  - إقفال مبدئي: تنفيذ Soft Close للفترة بعد اكتمال المهام.

### 4.3 Chief Accountant (رئيس الحسابات) — التحكم بالدفتر والإقفال والسياسات
- **المهمة:** التحكم بـ GL، ميزان المراجعة، اعتماد القيود، مراجعة التسويات، إدارة الإقفال، مراجعة القوائم، إدارة السياسات المحاسبية.
- **النطاق:** `company_scope = all (كيانات المجموعة المسندة)` · `data_mode = read_write` · `amount_band = Tier-2` · `book_scope = all`.
- **الصلاحيات:** كل صلاحيات Senior **+** `journal.approve` (عالٍ)/`post`/`reverse` · `trialbalance.view` · `period.close_hard/reopen` (باعتماد) · `closing.review/execute` · `report.financial.view` · `policy.manage` · `rules.manage` · `book.manage` · `fx.revalue` · `dimension.manage`.
- **فصل المهام:** لا يعتمد ما أنشأه؛ إعادة فتح الفترة تتطلب تسجيلاً وسبباً (SoD-5).
- **قوائم العمل:** «قيود عالية القيمة للاعتماد» · «التسويات الجوهرية» · «حالة الإقفال» · «القوائم للمراجعة».
- **المسارات:**
  - التحكم بالدفتر: مراجعة Trial Balance → drill-down → إصدار Adjustments.
  - الإقفال: إدارة Closing Checklist → مراجعة كل المهام → **Hard Close** → إعداد القوائم.
  - السياسات: تعريف/إصدار سياسة (تقييم مخزون/إهلاك/اعتراف إيراد…) بتاريخ سريان.
  - القواعد: اعتماد إصدار جديد لقاعدة محاسبية/ضريبية.

### 4.4 Finance Manager (مدير مالي) — التخطيط والتحكم
- **المهمة:** Budget · Forecast · Cash · Working Capital · Department Performance · Controls.
- **النطاق:** `company_scope = group/assigned` · `data_mode = read_write (planning) / read_only (GL)` · `amount_band = Tier-3`.
- **الصلاحيات:** `budget.view/manage/approve` · `forecast.view/manage` · `cashflow.view` · `cost.view/manage` · `report.management.view` · `analytics.view` · `controls.test/manage` · `gl.view` · `trialbalance.view` · `report.financial.view`.
- **لا يملك:** إنشاء/ترحيل قيود تشغيلية · `policy.manage` · `period.close_hard` (مهمة رئيس الحسابات).
- **فصل المهام:** يعتمد الموازنات لا القيود؛ منفصل عن التنفيذ المحاسبي.
- **قوائم العمل:** «موازنات للاعتماد» · «انحرافات جوهرية» · «تنبيهات السيولة» · «اختبارات الضوابط».
- **المسارات:**
  - الموازنة: إعداد/مراجعة/اعتماد الموازنة (سنوية/قسم/مشروع/CAPEX) → قفل الإصدار.
  - التوقع: rolling forecast → مقارنة Forecast vs Actual vs Budget.
  - السيولة/رأس المال العامل: مراجعة توقع النقد، AR/AP aging، دورة التحويل النقدي.
  - أداء الأقسام: P&L بالقسم/الفرع (عبر الأبعاد) → متابعة الانحرافات.
  - الضوابط: تشغيل اختبارات الضوابط ومتابعة المعالجة.

### 4.5 CFO (المدير المالي التنفيذي) — الرؤية الاستراتيجية (قراءة + اعتمادات عليا)
- **المهمة:** رؤية Revenue · Profit · Cash · EBITDA · Working Capital · Debt · Liquidity · Budget vs Actual · Forecast · Risk · Consolidated Results.
- **النطاق:** `company_scope = group (كامل)` · `data_mode = read_only (بيانات) + approve_only (اعتمادات عليا)` · `amount_band = Tier-4 (الأعلى)` · `book_scope = all`.
- **الصلاحيات:** `report.financial.view` · `report.management.view` · `analytics.view` · `consolidation.view` · `budget.view` · `forecast.view` · `cashflow.view` · `risk.view` · `trialbalance.view` · `gl.view` · `journal.approve` (بند الأعلى فقط) · `period.reopen` (اعتماد) · `payment.batch.approve` (الأعلى).
- **لا يملك:** إنشاء قيود/فواتير تشغيلية · `*.execute` (لا ينفّذ بنفسه — فصل مهام).
- **فصل المهام:** يعتمد ولا يُنشئ ولا ينفّذ؛ اعتماداته لا تكون لمعاملات أنشأها (لا يُنشئ أصلاً).
- **قوائم العمل:** «لوحة الأداء التنفيذية» · «اعتمادات عليا معلّقة» · «تنبيهات المخاطر/السيولة» · «النتائج الموحّدة».
- **المسارات:**
  - المراجعة التنفيذية: KPIs (EBITDA/Margins/ROE/Liquidity/Debt) → drill-down عند الحاجة.
  - اعتماد عالٍ: اعتماد المعاملات فوق حد Tier-3 (قادمة من المدير المالي/رئيس الحسابات).
  - التوحيد: مراجعة النتائج الموحّدة للمجموعة، Budget vs Actual، Forecast.
  - المخاطر: مراجعة سجل المخاطر ودرجاتها.

### 4.6 Auditor (المدقّق) — قراءة فقط + أدلة
- **المهمة:** Trace transactions · Review audit logs/journals/approvals/adjustments/documents · Test controls · Export evidence.
- **النطاق:** `company_scope = assigned/group` · `data_mode = read_only (صارم)` · `amount_band = 0` · `book_scope = all`.
- **الصلاحيات:** `audit.view/export` · `journal.view` · `gl.view` · `trialbalance.view` · `report.*.view` · `controls.test` · عرض المستندات الداعمة · عرض الموافقات.
- **لا يملك (إطلاقاً):** أي `create/manage/approve/post/execute/close` — يُمنع تعييناً وتشغيلاً (SoD-6: read_only حصري).
- **فصل المهام:** لا يشارك في أي عملية تشغيلية؛ يراقب فقط.
- **قوائم العمل:** «عينات المعاملات» · «سجل النشاط» · «تغييرات الفترات» · «التسويات» · «تقارير الاستثناءات».
- **المسارات:**
  - التتبّع: أي رقم → Statement → Account → Ledger → Journal → Source → Document.
  - فحص الأثر: مراجعة Audit Log (who/what/when/before/after)، الموافقات، إعادة الفتح.
  - اختبار الضوابط: عيّنات، اختبار فصل المهام، تقارير الاستثناءات.
  - الأدلة: **Export** حزمة أدلة (قيود + مستندات + موافقات) بلا تعديل.

### 4.7 أدوار النظام (System Roles)
| الدور | المهمة | صلاحيات مميزة |
|---|---|---|
| **Super Admin** | إدارة المنصة | `*` (بلا وصول لبيانات محاسبية للتشغيل — إداري) |
| **Organization Owner** | مالك المستأجر | إدارة الشركات/المستخدمين داخل مؤسسته |
| **Security Admin** | الحوكمة | `role.manage` · `permission.manage` · `workflow.manage` · `user.manage` (لا يملك صلاحيات محاسبية — SoD-7) |
| **Tax Officer** | الضرائب | `tax.*` · `book_scope=TAX` |
| **Treasury Officer** | الخزينة | `bank.*` · `payment.batch.create` · `payment.execute` · `cash.manage` |
| **Read-Only** | اطّلاع | `*.view` فقط |

> الأدوار **قابلة للتخصيص**: يمكن إنشاء أدوار جديدة بتجميع صلاحيات، لكن مع فرض قيود فصل المهام عند التعيين.

---

## 5. المسارات المتقاطعة (Cross-Role Workflows) — الحدّ والتسليم

### 5.1 دورة القيد (Journal Lifecycle)
```
Accountant (create/submit) → Senior (review/approve) → Senior/Chief (post) → GL
                                     ↓ رفض                         ↓ خطأ لاحق
                              يعود للمحاسب               Chief (Reverse/Adjust) — لا Delete
```
SoD: المنشئ ≠ المعتمد ≠ (غالباً) المرحّل.

### 5.2 من الفاتورة إلى التحصيل (Invoice → Cash)
```
Accountant: Sales Invoice → (AR entry) → Senior approve/post
Customer pays → Accountant: Receipt → allocate → Senior post
Overdue → Collections → (Chief) Write-off request → (Finance Mgr/Chief) approve
```

### 5.3 من الشراء إلى الدفع (Procure → Pay)
```
Accountant: Bill → 3-way match → Senior approve
Payment: Treasury create batch → Approver (band) approve → Treasury execute → Bank recon
```
SoD-3: مدير بيانات المورد البنكية ≠ معتمد/منفّذ الدفع له.

### 5.4 الإقفال (Period Close)
```
Accountant: closing tasks → Senior: review + Soft Close →
Chief: adjustments + review FS + Hard Close → CFO: review consolidated
```

### 5.5 التدقيق (Audit)
```
Auditor (read-only): sample → trace → review logs/approvals/docs → test controls → export evidence
```

## 6. مصفوفة الاعتماد (Approval Matrix) — حدود المبالغ

> الحدود **توضيحية وقابلة للتكوين** (بند 33) — تُضبط لكل شركة بلا Hardcode.

| النطاق (Band) | حد المبلغ (مثال) | المعتمد |
|---|---|---|
| Tier-1 | < 1,000 | Senior Accountant |
| Tier-2 | 1,000 – 10,000 | Chief Accountant |
| Tier-3 | 10,000 – 100,000 | Finance Manager |
| Tier-4 | > 100,000 | CFO |

الاعتماد قد يكون **متعدد المستويات** (مبلغ كبير يمر بأكثر من معتمد)، وقابلاً للتوجيه حسب القسم/النوع/المورد/المخاطر.

## 7. كتالوج قواعد فصل المهام (SoD Rules) — مفروضة تشغيلياً + عند التعيين

| # | القاعدة | الفرض |
|---|---|---|
| **SoD-1** | لا يعتمد/يرحّل شخص معاملة **أنشأها بنفسه** (`actor ≠ created_by`) | تشغيلي — يُرفض ويصعد |
| **SoD-2** | فصل Create / Approve / Post / Execute للقيود والمدفوعات | تعييني + تشغيلي |
| **SoD-3** | من يدير **بيانات المورد البنكية** لا يعتمد/ينفّذ الدفع له | تشغيلي (مراقبة احتيال) |
| **SoD-4** | من يرحّل حركات البنك لا يقفل تسويته البنكية (اختياري) | تعييني (قابل للتكوين) |
| **SoD-5** | **إعادة فتح فترة** تتطلب دوراً أعلى + سبباً + Audit | تشغيلي |
| **SoD-6** | **Auditor = read_only حصري**؛ لا يجتمع مع أي صلاحية كتابة | تعييني (يُرفض التعيين) |
| **SoD-7** | **Security Admin** لا يجمع صلاحيات محاسبية تشغيلية | تعييني |
| **SoD-8** | من ينشئ المورد/العميل لا يعتمد فواتيره (تعارض تكوين) | تشغيلي (قابل للتكوين) |

**آلية الفرض:**
- **عند التعيين (Assignment-time):** رفض تجميع صلاحيات متعارضة في دور/مستخدم (SoD-2/4/6/7).
- **وقت التشغيل (Runtime):** فحص `actor ≠ created_by` وتعارضات السياق (SoD-1/3/5/8) قبل تنفيذ العملية، مع تسجيل الرفض.

## 8. مصفوفة الأدوار × الصلاحيات (ملخص)

`C=Create · S=Submit · A=Approve · P=Post · X=Execute · V=View · M=Manage · —=لا`

| Resource | Acct | Senior | Chief | FinMgr | CFO | Auditor |
|---|---|---|---|---|---|---|
| Journals | C·S·V | A·P·Adjust | A·P·Reverse | V | A(الأعلى)·V | V |
| Trial Balance / GL | V | V | V·Control | V | V | V |
| AR (invoice/receipt) | C | A | A | V | V | V |
| AP (bill) | C·Match | A | A | V | V | V |
| Payments | — | — | — | A(band) | A(الأعلى) | V |
| Bank Reconciliation | Perform | Review·Lock | Lock | V | V | V |
| Reconciliation (عام) | Perform | Review·Lock | Review | V | V | V |
| Period Close | Task | Soft | Hard·Reopen | V | Reopen(approve) | V |
| Accounting Policies | — | — | M | V | V | V |
| Budget / Forecast | V | V | V | M·A | V | V |
| Cash / Working Capital | V | V | V | V | V | V |
| Financial Statements | V | V | Review | V | V | V |
| Consolidation | — | — | V | V | V | V |
| Reports (mgmt/analytics) | V(شركته) | V | V | V | V(group) | V |
| Audit Log | — | — | V | V | V | V·Export |
| Controls | — | — | V | Test·M | V | Test |
| Roles/Permissions | — | — | — | — | — | — |

> النطاق يقيّد كل صف: Accountant على [Company A]، CFO على group، Auditor read_only. التفصيل الكامل (Machine-readable) في `docs/authz/permissions.json`.

