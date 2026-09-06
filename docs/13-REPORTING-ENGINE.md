# محرك التقارير العالمي (Reporting Engine)

Financial + Management + Regulatory Reporting فوق GL. طبقة **قراءة فقط** لا تعدّل الدفتر أبداً؛ مصدر الحقيقة = القيود المرحّلة.

## 1. المبادئ
1. **GL هو المصدر** — كل رقم قابل للاشتقاق من `journal_lines` المرحّلة (+ الأستاذ المساعد للتفاصيل التشغيلية).
2. **Drill-down على كل رقم:** Report → Account → Ledger → Journal → Source → Document.
3. **معاملات موحّدة** لكل تقرير (Period · Comparative · Entity · Branch · Department · Cost Center · Project · Currency · Book · Framework).
4. **Report Builder بلا تعديل Core:** التقارير المخصّصة = بيانات (تعريفات)، لا كود.
5. **يحترم الأمان:** كل تقرير يمرّ عبر Role+Permission+Scope (وثيقة 12) — عزل الشركة/الفرع/الدفتر، read-only، إخفاء الحساس.
6. **متعدد الدفاتر/العملات/الأطر:** نفس المحرك يُنتج LOCAL vs IFRS vs TAX، وبأي عملة عرض، وبأي إطار.

## 2. المعمارية (Pipeline)
```
ReportRequest (params)
   → Security Filter (scope من وثيقة 12)
   → Data Source(s): GL Cube · Subledgers · Budget/Forecast
   → Aggregation Engine (group by account×dimension×period · currency convert · book/framework filter · comparatives)
   → Statement/Report Model (sections · rows · columns · measures + drill anchors)
   → Output-agnostic (screen · PDF · Excel · CSV · JSON · API)   ← بلا نقاش UI
```
المكوّنات: `ReportRequest` (VO) · `ReportDefinitionRegistry` (الـ28 القياسية + المخصّصة) · `AggregationEngine` · `FsLineMapping` (حساب→سطر قائمة) · `DrillDownResolver` · `CurrencyEngine` · `MaterializationCache`.

## 3. المعاملات العامة (ReportRequest) — يدعمها كل تقرير
| المعامل | القيم |
|---|---|
| `period` | فترة/مدى تواريخ/فترة مالية |
| `comparative` | فترة سابقة/سنة سابقة/موازنة/توقع (0..n أعمدة مقارنة) |
| `entity` | شركة \| مجموعة (group) \| قائمة كيانات |
| `branch / department / cost_center / project` | كل القيم \| قيمة/قيم محددة (أبعاد) |
| `currency` | transaction \| functional \| presentation (+ نوع السعر) |
| `book` | LOCAL \| IFRS \| TAX |
| `framework` | Full IFRS \| IFRS for SMEs \| Local GAAP |
| `level_of_detail` | class/group/account/detail |
| `options` | include_zero · rounding · consolidated(on/off) · as_of_date |

القيم غير الممنوحة تُشتق من سياق المستخدم (شركته الافتراضية، دفتره الأساسي).

## 4. مكعّب GL (The Reporting Cube)
كل تقرير مالي = تجميع على مكعّب:
```
Measures:   debit · credit · balance · movement · opening · closing (functional/presentation)
Dimensions: account · company · branch · cost_center · project · department · book · period · currency
```
البناء: `SUM(functional_debit/credit)` من `journal_lines ⋈ journals(status∈{posted,reversed}) ⋈ accounts` مع فلاتر النطاق، ثم إسقاط على أبعاد التقرير. القيود المعكوسة تبقى (تُقاصّها المرايا) — اتساق مع بند 57.

## 5. ربط سطور القوائم (FS-Line Mapping) & تعريفات القوائم
- كل حساب يحمل `statement` (BS/PL/OCI) و`financial_statement_line` و`cash_flow_classification` (من دليل الحسابات، وثيقة 03).
- **Statement Definition** = ترتيب السطور والمجاميع والعناوين لكل قائمة، **قابل للتخصيص** حسب الإطار/الصناعة/الكيان/الدولة (بند 29).
- الحسابات المقابلة (`is_contra`) تُطرح لا تُجمع؛ الحسابات النظامية/الختامية تُستبعد من القوائم.
- تغيير الإطار (IFRS↔Local) = تعريف قائمة مختلف على نفس البيانات، لا إعادة ترحيل.

## 6. العملات / الدفاتر / الأطر / التوحيد
- **العملة:** التخزين بالوظيفية؛ العرض بأي عملة عبر CurrencyEngine (Closing للأصول/الخصوم، Average للدخل، Historical للحقوق — IAS 21). فروق الترجمة → OCI.
- **الدفتر:** فلترة `book_id`؛ تقارير متوازية LOCAL/IFRS/TAX + تسوية Book-to-Book.
- **الإطار:** يحدد تعريف القائمة والإفصاحات؛ لا يدّعي الامتثال ما لم تُستوفَ المتطلبات.
- **التوحيد:** عند `entity=group` — تجميع موازين + استبعاد Intercompany + ترجمة + حصص الأقلية (وثيقة الموديولات §5.6).

## 7. التنقّل التفصيلي (Drill-down) — على كل خلية
كل خلية في أي تقرير تحمل **Anchor** (وصف الاستعلام المنتج لها) يفتح السلسلة:
```
Report cell  →  Account balance (accounts.id, filters)
             →  Ledger lines (journal_lines المطابقة)
             →  Journal (رأس القيد + الحالة + ruleVersion)
             →  Source Transaction (source + source_ref)
             →  Original Document (المستند المرفق + hash)
```
كل مستوى يورّث فلاتر الأب (الفترة/الأبعاد/الدفتر) فيبقى التتبّع متسقاً (بند 11، 68).

## 8. كتالوج التقارير (28) — المصدر · البنية · التنقّل · المعيار

> كلها تدعم المعاملات العامة (§3) والتنقّل التفصيلي (§7). التفاصيل Machine-readable في `docs/reporting/report-catalog.json`.

### A. القوائم المالية القانونية (Financial / Statutory)
| # | التقرير | المصدر | البنية | المعيار |
|---|---|---|---|---|
| 1 | **Trial Balance** | GL Cube | حساب × (افتتاحي/مدين/دائن/ختامي) | — |
| 2 | **General Ledger** | journal_lines | حركة كل حساب مع رصيد جارٍ + drill للقيد | — |
| 3 | **Balance Sheet / SFP** | BS accounts | أصول = خصوم + حقوق ملكية | IAS 1 / IFRS 18 |
| 4 | **Profit or Loss** | PL accounts | إيراد − تكلفة − مصروف = صافي الربح | IAS 1 / IFRS 18 |
| 5 | **Comprehensive Income (OCI)** | PL + OCI reserves | صافي الربح + بنود OCI | IAS 1, IFRS 9, IAS 19/21 |
| 6 | **Changes in Equity (SOCE)** | equity accounts | حركة كل مكوّن حقوق (رأس مال/احتياطيات/مرحّلة/OCI/NCI) | IAS 1 |
| 7 | **Cash Flows** | cash_flow_classification | تشغيلي/استثماري/تمويلي (مباشر/غير مباشر) | IAS 7 |
| 8 | **Notes** | كل المصادر + IFRS mapping | إيضاحات مرتبطة بالسطور والمعايير | IFRS كامل |

### B. تقارير الأستاذ المساعد والتشغيل (Subledger / Operational)
| # | التقرير | المصدر | البنية | المعيار |
|---|---|---|---|---|
| 9 | **AR Aging** | AR subledger | أعمار ذمم العملاء (0-30-60-90+) | IFRS 9 (ECL) |
| 10 | **AP Aging** | AP subledger | أعمار ذمم الموردين + الاستحقاقات | IAS 37 |
| 11 | **Inventory Valuation** | Inventory subledger | كمية × تكلفة (FIFO/متوسط) + LCNRV | IAS 2 |
| 12 | **Fixed Assets Register** | FA subledger | أصل × (تكلفة/مجمع/NBV/موقع/حائز) | IAS 16 |
| 13 | **Depreciation** | FA schedules | إهلاك الفترة بالأصل/الفئة/مركز التكلفة | IAS 16 |
| 14 | **Tax Reports** | tax accounts | Input/Output VAT · WHT · ضريبة مؤجلة | IAS 12 / محلي |
| 24 | **Intercompany** | is_intercompany | أرصدة متقابلة بين الشركات + حالة المطابقة | IAS 24 |

### C. التقارير الإدارية (Management)
| # | التقرير | المصدر | البنية |
|---|---|---|---|
| 15 | **Budget vs Actual** | GL + Budget | فعلي/موازنة/انحراف/% بالحساب/البُعد |
| 16 | **Forecast** | Actual + drivers | توقع متدحرج مقابل موازنة |
| 17 | **Cash Forecast** | AR/AP/Bank/Loans | توقع تدفق نقدي + سيولة |
| 18 | **Working Capital** | AR/AP/Inventory | رأس المال العامل · DSO/DPO/DIO · CCC |
| 19 | **Cost Center P&L** | GL × cost_center | أداء ربحي بمركز التكلفة |
| 20 | **Branch P&L** | GL × branch | أداء ربحي بالفرع |
| 21 | **Project P&L** | GL × project | ربحية المشروع + الميزانية والإنجاز |
| 22 | **Customer Profitability** | GL/AR × customer | إيراد − تكلفة مخصّصة بالعميل |
| 23 | **Product Profitability** | GL/Inventory × product | هامش المنتج |
| 28 | **KPI Reports** | كل المصادر | EBITDA · Margins · ROA/ROE · Liquidity · Debt |

### D. التوحيد والحوكمة (Group / Governance)
| # | التقرير | المصدر | البنية |
|---|---|---|---|
| 25 | **Consolidation** | موازين المجموعة | ميزان/قوائم موحّدة + استبعادات + ترجمة + NCI |
| 26 | **Audit Reports** | audit_logs + journals | أثر المعاملات · نشاط المستخدم · تغييرات الفترات · عيّنات |
| 27 | **Exception Reports** | كل المصادر | قيود شاذة · أرصدة معلّقة · تجاوزات · ازدواجية |

## 9. Report Builder — تقارير مخصّصة بلا تعديل Core
التعريف **بيانات لا كود**؛ المحرك يفسّرها:
```
ReportDefinition (versioned):
  id · name · category · framework
  data_source: gl_cube | subledger | budget | join
  rows:     grouping dimensions (account/branch/project/period…)
  columns:  measures (balance/movement/debit/credit) + comparatives
  filters:  scope + dimension + account ranges + book/currency
  calculated_fields: صيغ آمنة على المقاييس (margin = revenue − cost)
  drill: يرث سلسلة §7 تلقائياً
  sharing/permissions: يحترم وثيقة 12
```
- **مصادر مجرّدة:** GL Cube · Subledger · Budget/Forecast — يضاف مصدر جديد كموصّل دون لمس النواة.
- **طبقة صيغ آمنة:** حسابات على المقاييس فقط (لا تنفيذ كود اعتباطي).
- **إصدار وحوكمة:** كل تعريف Versioned وقابل للمشاركة بصلاحيات.
- **تصدير:** Excel/CSV/PDF/JSON/API (بند 44/45) — بلا نقاش UI.

## 10. الأداء (Materialization & Cache)
- **الفترات المقفلة:** لقطات مادّية (snapshots) ثابتة — سريعة وغير متغيّرة.
- **الفترات المفتوحة:** حساب حيّ من GL مع cache قصير + إبطال عند ترحيل جديد.
- **أرصدة مسبقة:** جدول أرصدة دورية بالحساب×البُعد×الفترة×الدفتر لتسريع القوائم الكبيرة.
- **التوحيد:** يُخزَّن ناتج التوحيد بعد تشغيله (قابل لإعادة التشغيل).

## 11. الأمان (Security)
كل تقرير يمرّ عبر نموذج التفويض (وثيقة 12): فلترة النطاق (company/branch/book)، `read_only` صارم، إخفاء البيانات الحساسة (رواتب أفراد)، وتسجيل كل تصدير في Audit (خاصة Auditor `audit.export`).

## 12. الهيكل البرمجي (Scaffold مُثبِت)
تحقيق مبكر مُختبَر يُنتج **قائمة المركز المالي وقائمة الدخل** فعلياً من GL المرحّل عبر بيانات دليل الحسابات، مع تحقّق `الأصول = الخصوم + حقوق الملكية` ومرساة Drill-down (`account_id`) على كل سطر:
- `app/Services/Accounting/Reporting/Data/ReportRequest.php` — المعاملات العامة (VO).
- `app/Services/Accounting/Reporting/Data/StatementLine.php` — سطر قائمة (VO) بمرساة التنقّل.
- `app/Services/Accounting/Reporting/FinancialStatementService.php` — بناء SFP + P&L من GL.
- اختبارات Pest تُثبت التوازن واشتقاق صافي الربح والمراسي.

