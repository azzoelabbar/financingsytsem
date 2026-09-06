# قاموس بيانات الحساب وقواعد التحقق (Outputs C & H)

مصدر البيانات: `database/data/coa/coa_enterprise_libya.json` (293 حساب) + مراجعة بشرية عبر `coa_enterprise_libya.csv`.

## C. Account Metadata — تعريف كل حقل

| الحقل | النوع | الوصف |
|---|---|---|
| `code` | string | رقم الحساب (فريد داخل الشركة). |
| `name_ar` / `name_en` | string | الاسم العربي (أساسي) والإنجليزي. |
| `parent` | string\|null | رمز الحساب الأب (مشتق من الرمز). `null` للفئات الرئيسية. |
| `level` | int (1–6) | المستوى الهرمي. |
| `account_type` | enum | asset · liability · equity · revenue · cost_of_sales · expense · closing · memo. **canonical** (من أول رقم، لا من الاسم). |
| `account_nature` | ar | الطبيعة المعروضة (أصل/خصم/حقوق ملكية/إيراد/مصروف/ختامي/نظامي). |
| `normal_balance` | enum | debit · credit · none. الحسابات المقابلة تحمل الرصيد المعاكس تلقائياً. |
| `is_posting` | bool | يُرحَّل عليه؟ (أوراق الشجرة فقط). الأب التجميعي لا يُرحَّل عليه أبداً. |
| `control_or_detail` | enum | control (مراقبة مدعومة بأستاذ مساعد) · detail (تفصيلي) · header (تجميعي). |
| `is_contra` | bool | حساب مقابل يُطرح لا يُجمع. |
| `contra_of` | string\|null | رمز الحساب الذي يقابله. |
| `is_control` | bool | حساب مراقبة (AR/AP/Inventory/FA…). |
| `is_bank` | bool | حساب بنكي (يتطلب تسوية بنكية). |
| `is_tax` / `tax_mapping` | bool / code | حساب ضريبي + رمز الضريبة (VAT/CIT/WHT/PAYE/STAMP/DEFERRED…). |
| `is_suspense` | bool | حساب وسيط/معلق (يجب ألا يبقى برصيد غير مبرّر). |
| `is_statistical` | bool | حساب نظامي/خارج الميزانية (لا يدخل ميزان المراجعة المالي). |
| `is_intercompany` / `eliminate_on_consolidation` | bool | رصيد بين شركات يُستبعد عند التوحيد. |
| `financial_statement_line` | string | سطر القائمة المالية (SFP/P&L/OCI/Off-BS). |
| `cash_flow_classification` | enum | operating · investing · financing · none. |
| `ifrs_mapping` / `ias_mapping` | string | ربط المعيار (IFRS 9/15/16… · IAS 2/12/16/19/36/37/38/40/41…). |
| `subledger_mapping` | enum | AR · AP · INV · FA · BANK · TAX · PAYROLL · LEASE · (فارغ = GL مباشر). |
| `reconciliation_required` | bool | يتطلب تسوية دورية. |
| `closing_behavior` | enum | permanent · temporary · retained_earnings · closing_account · oci · none. |
| `industry_applicability` | list | ALL أو صناعات محددة (TRADING/MANUFACTURING/SAAS…). |
| `depreciable` | bool | أصل قابل للاستهلاك (الأراضي والشهرة = false). |
| `manual_journal_allowed` | bool | يُسمح بقيد يدوي؟ (الحسابات الختامية/النظامية = false). |
| `notes` | string\|null | ملاحظة/مرجع. |

## H. قواعد التحقق (Validation Rules) — مطبّقة في المولّد وتُفرض لاحقاً في الطبقة البرمجية

**بنيوية (Structural):**
1. `code` فريد داخل الشركة.
2. كل حساب غير جذري له `parent` موجود فعلاً.
3. `level` و`account_type` متسقان مع طول الرمز وأول رقم.
4. لا حلقات في الهرمية (الأب لا يكون سليلاً لنفسه).

**محاسبية (Accounting):**
5. `normal_balance` يساوي الافتراضي للنوع، **إلا** إذا `is_contra = true` فيكون معاكساً (مفروض آلياً).
6. `is_contra = true` ⇒ `contra_of` مطلوب ويشير إلى حساب موجود.
7. الأب التجميعي `is_posting = false`؛ لا يُرحَّل عليه (بند 57).
8. `account_type = closing|memo` ⇒ `manual_journal_allowed = false` (system-only).
9. `is_statistical = true` ⇒ خارج ميزان المراجعة المالي و`normal_balance = none`.

**تشغيلية (Operational):**
10. `is_bank` أو `is_control` أو `is_suspense` ⇒ `reconciliation_required = true`.
11. `subledger_mapping ≠ فارغ` على حساب مراقبة ⇒ التفاصيل في الأستاذ المساعد لا في GL.
12. `eliminate_on_consolidation = true` على كل حساب `is_intercompany`.
13. حساب معلق/وسيط: تنبيه إذا بقي رصيد بعد إقفال الفترة.

**نتيجة التحقق الحالية:** `0 أخطاء` على 293 حساباً.

## إحصاءات النسخة النهائية
- الإجمالي **293** حساباً (Baseline 168 + إضافات 125)، منها **210** حساب ترحيل.
- **17** حساب مقابل (Contra) · **4** مراقبة (Control) موسومة صراحةً (قابلة للتوسّع) · **4** بين الشركات · **7** وسيطة/معلقة · **6** نظامية/خارج الميزانية.
- **8** قوالب صناعات (Overlays).
