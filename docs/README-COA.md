# دليل الحسابات Enterprise — فهرس المخرجات (A–K)

إعادة هندسة كاملة لدليل الحسابات اعتماداً على `نموذج دليل الحسابات.xlsx` كـ Baseline.
**النتيجة:** 330 حساباً (168 أساس مُصحَّح + 162 إضافة)، 237 حساب ترحيل، 0 أخطاء تحقق، 11 قالب صناعة. **تصميم فقط — لم تبدأ برمجة التطبيق.**

| المخرَج | أين |
|---|---|
| **A. Final Chart of Accounts** | `database/data/coa/coa_enterprise_libya.json` (+ `.csv` للمراجعة البشرية) |
| **B. Account Hierarchy** | نفس الملف (`parent` + `level`)؛ الاصطلاح في `docs/02-COA-CONVENTIONS.md` |
| **C. Account Metadata** | الملف أعلاه؛ قاموس الحقول في `docs/03-COA-METADATA-DICTIONARY.md` |
| **D. IFRS/IAS Mapping** | `docs/05-COA-IFRS-TAX-MAPPING.md` + حقول `ifrs_mapping/ias_mapping` |
| **E. Tax Mapping** | `docs/05-COA-IFRS-TAX-MAPPING.md` + حقل `tax_mapping` |
| **F. Industry Templates** | `database/data/coa/templates/*.json` + `docs/06-COA-TEMPLATES.md` |
| **G. Libya Template** | `docs/06-COA-TEMPLATES.md` (§G) |
| **H. Validation Rules** | `docs/03-COA-METADATA-DICTIONARY.md` (§H) — مُطبَّقة في المولّد |
| **I. Naming Conventions** | `docs/02-COA-CONVENTIONS.md` (§I) |
| **J. Numbering Convention** | `docs/02-COA-CONVENTIONS.md` (§J) |
| **K. Migration Strategy** | `docs/07-COA-MIGRATION.md` |
| التحليل (ناقص/مكرر/سوء تصنيف/Contra/Control) | `docs/04-COA-BASELINE-ANALYSIS.md` |
| مولّد البيانات (قابل لإعادة التشغيل) | `database/data/coa/build_coa.py` |

## إعادة توليد البيانات
```bash
python database/data/coa/build_coa.py
```
يقرأ `database/data/libya_coa.json`، يطبّق التصحيحات والإضافات، يشتق الـMetadata، **يتحقق** (بنيوياً/محاسبياً)، ويكتب JSON + CSV + قوالب الصناعات. أي أخطاء تحقق تظهر في المخرجات (حالياً = 0).

## قابلية التوسّع المضمونة (بلا Hardcoding)
- إضافة حساب = صف بيانات. إضافة مستوى = إعداد. إضافة صناعة/دولة = قالب Overlay.
- Multi-book (LOCAL/IFRS/TAX): شجرة واحدة، معالجات مختلفة عبر Rules.
- تخصيص كل شركة: الأسماء والقوالب المفعّلة قابلة للتعديل دون تغيير الترميز أو النواة.

> الخطوة التالية بعد اعتمادك: تمديد مخطط `accounts` بأعلام (intercompany/suspense/statistical/oci/subledger_mapping)، تبديل مصدر البذرة إلى هذا الملف، وبناء `CoaMigrationCommand` (Dry-run/Commit/Rollback).
