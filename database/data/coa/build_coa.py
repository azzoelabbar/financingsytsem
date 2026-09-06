# -*- coding: utf-8 -*-
"""
Enterprise Chart of Accounts builder (DESIGN artifact, not application code).

Input : database/data/libya_coa.json  (the 168-account supplied baseline)
Output: database/data/coa/coa_enterprise_libya.json   (final baseline + full metadata)
        database/data/coa/coa_enterprise_libya.csv     (human-review matrix A/B/C)
        database/data/coa/templates/<industry>.json    (industry overlay packs)
Also prints an analysis report (findings + counts).

Nothing here touches the Laravel app; it produces the reviewed design data that a
later coding phase will import.
"""
import json, os, csv, collections

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", ".."))
SRC = os.path.join(ROOT, "database", "data", "libya_coa.json")
OUTDIR = os.path.join(ROOT, "database", "data", "coa")
TPLDIR = os.path.join(OUTDIR, "templates")
os.makedirs(TPLDIR, exist_ok=True)

# ---------------------------------------------------------------- English names
EN = {
 "1":"Assets","11":"Current Assets","1101":"Cash and Cash Equivalents",
 "110101":"Cash on Hand","110102":"Main Bank Account","110103":"Reserve Bank Account","110104":"Cheques Under Collection",
 "1102":"Receivables","110201":"Trade Receivables (Customers)","110202":"Notes Receivable","110203":"Sundry Debtors",
 "110204":"Allowance for Expected Credit Losses (Doubtful Debts)",
 "1103":"Inventory","110301":"Merchandise Inventory","110302":"Raw Materials Inventory","110303":"Finished Goods Inventory","110304":"Work-in-Process Inventory",
 "1104":"Prepaid Expenses","110401":"Prepaid Rent","110402":"Prepaid Insurance","110403":"Other Prepaid Expenses",
 "1105":"Short-term Investments","110501":"Marketable Securities","110502":"Short-term Bank Deposits",
 "12":"Non-current Assets","1201":"Property, Plant and Equipment","120101":"Land","120102":"Buildings and Constructions",
 "120103":"Accumulated Depreciation - Buildings","120104":"Machinery and Equipment","120105":"Accumulated Depreciation - Machinery and Equipment",
 "120106":"Furniture and Fixtures","120107":"Accumulated Depreciation - Furniture","120108":"Vehicles","120109":"Accumulated Depreciation - Vehicles",
 "120110":"Computers and IT Equipment","120111":"Accumulated Depreciation - Computers and IT Equipment",
 "1202":"Intangible Assets","120201":"Goodwill","120202":"Patents","120203":"Trademarks","120204":"Software and Licenses",
 "1203":"Long-term Investments","120301":"Investments in Subsidiaries","120302":"Investments in Associates","120303":"Long-term Bank Deposits",
 "2":"Liabilities","21":"Current Liabilities","2101":"Payables","210101":"Trade Payables (Suppliers)","210102":"Notes Payable","210103":"Sundry Creditors",
 "2102":"Accrued Expenses","210201":"Accrued Salaries and Wages","210202":"Accrued Rent","210203":"Other Accrued Expenses",
 "2103":"Deferred / Unearned Revenue","210301":"Unearned Revenue",
 "2104":"Tax Liabilities","210401":"Value Added Tax (VAT)","210402":"Income Tax Payable","210403":"Withholding Tax",
 "2105":"Short-term Loans","210501":"Bank Facilities / Overdraft","210502":"Current Portion of Long-term Loans",
 "22":"Non-current Liabilities","2201":"Long-term Loans","220101":"Long-term Bank Loans","220102":"Bonds Payable",
 "2202":"Long-term Provisions","220201":"End-of-Service Benefits Provision","220202":"Maintenance and Warranty Provision",
 "3":"Equity","31":"Capital","3101":"Paid-in Capital","310101":"Authorized Capital","310102":"Share Premium",
 "32":"Reserves and Retained Earnings","3201":"Reserves","320101":"Legal Reserve","320102":"Voluntary Reserve","320103":"Revaluation Reserve",
 "3202":"Profit and Loss","320201":"Retained Earnings","320202":"Current Year Profit / Loss",
 "3203":"Drawings","320301":"Partners' Drawings","320302":"Dividends Distributed",
 "4":"Revenue","41":"Operating Revenue","4101":"Sales","410101":"Local Sales","410102":"Export Sales","410103":"Sales Returns and Allowances",
 "4102":"Service Revenue","410201":"Primary Service Revenue","410202":"Additional Service Revenue",
 "42":"Other Revenue","4201":"Investment Income","420101":"Interest and Bank Income","420102":"Dividend Income","420103":"Gain on Disposal of Assets",
 "4202":"Miscellaneous Revenue","420201":"Rental Income","420202":"Fines and Compensation Received","420203":"Other Miscellaneous Revenue",
 "5":"Cost of Sales","51":"Cost of Goods Sold","5101":"Purchases","510101":"Local Purchases","510102":"Imported Purchases",
 "510103":"Purchase Returns and Allowances","510104":"Freight and Transportation In","510105":"Customs Duties",
 "5102":"Direct Production Costs","510201":"Raw Materials Consumed","510202":"Direct Production Labor",
 "6":"Operating Expenses","61":"Selling and Distribution Expenses","6101":"Sales Salaries and Wages",
 "610101":"Sales Representatives' Salaries","610102":"Sales Commissions","610103":"Sales Bonuses",
 "6102":"Marketing and Advertising Expenses","610201":"Advertising and Media","610202":"Exhibitions and Events","610203":"Gifts and Promotional Materials",
 "6103":"Delivery and Shipping Expenses","610301":"Local Shipping Fees","610302":"International Shipping Fees",
 "62":"General and Administrative Expenses","6201":"Administrative Salaries and Wages","620101":"Administrative Staff Salaries",
 "620102":"Employee Allowances and Benefits","620103":"End-of-Service Benefits Expense","620104":"Employee Medical Insurance",
 "6202":"Rent and Utilities Expenses","620201":"Office and Warehouse Rent","620202":"Electricity and Water","620203":"Telecommunications and Internet",
 "6203":"Miscellaneous Administrative Expenses","620301":"Office Supplies and Stationery","620302":"Maintenance and Repairs",
 "620303":"Legal and Accounting Fees","620304":"Subscriptions and Memberships","620305":"Travel and Transportation Expenses","620306":"Training and Development",
 "6204":"Depreciation and Amortization","620401":"Depreciation - Buildings","620402":"Depreciation - Machinery","620403":"Depreciation - Furniture",
 "620404":"Depreciation - Vehicles","620405":"Depreciation - Computers","620406":"Amortization - Intangibles",
 "63":"Financial Expenses","6301":"Interest and Bank Charges","630101":"Interest on Bank Loans","630102":"Bank Commissions","630103":"Foreign Exchange Differences",
 "6302":"Other Financial Expenses","630201":"Bad Debts Expense","630202":"Loss on Disposal of Assets",
 "7":"Closing and Contra Accounts","71":"Closing Accounts","7101":"Profit and Loss Account","710101":"Profit and Loss Summary",
 "7102":"Trading Account","710201":"Trading Summary",
}

# ------------------------------------------------- corrections to baseline rows
# code -> dict of overrides applied to the imported record
CORRECTIONS = {
 "120103": {"name_ar":"مجمع استهلاك المباني","contra_of":"120102"},
 "120105": {"name_ar":"مجمع استهلاك الآلات والمعدات","contra_of":"120104"},
 "120107": {"name_ar":"مجمع استهلاك الأثاث والمفروشات","contra_of":"120106"},
 "120109": {"name_ar":"مجمع استهلاك السيارات والمركبات","contra_of":"120108"},
 "120111": {"name_ar":"مجمع استهلاك الأجهزة والحاسبات","contra_of":"120110"},
 "110204": {"name_ar":"مخصص خسائر ائتمانية متوقعة (ديون مشكوك فيها)","contra_of":"110201","ifrs":"IFRS 9","ias":"","subledger":"AR","reconc":True},
 "410103": {"contra_of":"4101"},
 "510103": {"contra_of":"5101"},
 # drawings & dividends are contra-equity (reduce owners' equity)
 "3203": {"contra_of":"31"},
 "320301": {"contra_of":"3101"},
 "320302": {"contra_of":"3101"},
 "320103": {"fs_override":"Equity > Other Comprehensive Income > Revaluation surplus","ias":"IAS 16","oci":True},
 "420201": {"name_ar":"إيرادات إيجار","name_en":"Rental Income"},
 # control / subledger flags
 "110201": {"is_control":True,"subledger":"AR","reconc":True},
 "210101": {"is_control":True,"subledger":"AP","reconc":True},
 "110202": {"subledger":"AR","reconc":True},
 "210102": {"subledger":"AP","reconc":True},
 "110102": {"is_bank":True,"reconc":True,"subledger":"BANK"},
 "110103": {"is_bank":True,"reconc":True,"subledger":"BANK"},
 "110104": {"reconc":True},
 "110301": {"subledger":"INV","is_control":True},"110302":{"subledger":"INV"},"110303":{"subledger":"INV"},"110304":{"subledger":"INV"},
 "120101": {"ias":"IAS 16","subledger":"FA"},"120102":{"ias":"IAS 16","subledger":"FA","depreciable":True},
 "120104": {"ias":"IAS 16","subledger":"FA","depreciable":True},"120106":{"ias":"IAS 16","subledger":"FA","depreciable":True},
 "120108": {"ias":"IAS 16","subledger":"FA","depreciable":True},"120110":{"ias":"IAS 16","subledger":"FA","depreciable":True},
 "120201": {"ias":"IAS 38 / IAS 36","subledger":"FA"},"120202":{"ias":"IAS 38"},"120203":{"ias":"IAS 38"},"120204":{"ias":"IAS 38"},
 "120301": {"ias":"IAS 27 / IFRS 10"},"120302":{"ias":"IAS 28"},
 "110301x": {},
 "210401": {"tax":"VAT","is_tax":True,"subledger":"TAX"},"210402":{"tax":"CIT","ias":"IAS 12","is_tax":True},"210403":{"tax":"WHT","is_tax":True,"subledger":"TAX"},
 "220201": {"ias":"IAS 19"},"220202":{"ias":"IAS 37"},
 "320101": {"tax":"","note":"Legal reserve 10% of net profit (local company law)"},
 "110301_inv":{},
}

# ------------------------------------------------------------------- additions
# a(code, ar, en, ...) ; type/level/parent derived from code length.
ADD = []
def a(code, ar, en, **kw):
    d = {"code":code,"name_ar":ar,"name_en":en}; d.update(kw); ADD.append(d)

# ---- CLASS 1 additions
a("110205","ذمم مدينة تجارية أخرى","Other Trade Receivables", subledger="AR", reconc=True, ias="IFRS 9")
a("110206","أوراق قبض مخصومة","Discounted Notes Receivable", subledger="AR")
a("110207","أصل عقد (إيراد غير مفوتر)","Contract Asset (Unbilled Revenue)", ifrs="IFRS 15", subledger="AR", ind=["SERVICES","CONSTRUCTION","SAAS"])
a("110208","مبالغ محتجزة مدينة","Retention Receivable", ifrs="IFRS 15", ind=["CONSTRUCTION"])
a("110209","ذمم مدينة بين الشركات - متداول","Intercompany Receivables - Current", ias="IAS 24", ic=True, eliminate=True)
a("110210","ضريبة القيمة المضافة على المدخلات","Input VAT (Recoverable)", tax="VAT", is_tax=True, subledger="TAX", reconc=True)
# 1104 details
a("110404","دفعات مقدمة للموردين","Advances to Suppliers", subledger="AP")
a("110405","ذمم موظفين مدينة","Employee Receivables", subledger="PAYROLL")
# 1106 Other current assets (new group)
a("1106","الأصول المتداولة الأخرى","Other Current Assets")
a("110601","سلف وعُهد الموظفين","Employee Advances and Imprest", subledger="PAYROLL", is_control=True)
a("110602","إيرادات مستحقة القبض","Accrued Income Receivable")
a("110603","أدوات مالية بالقيمة العادلة من خلال الأرباح أو الخسائر","Financial Assets at FVTPL", ifrs="IFRS 9", ias="IAS 32")
a("110604","مشتقات مالية - أصول","Derivative Financial Assets", ifrs="IFRS 9 / IFRS 7")
# 1107 Clearing / suspense (asset side) (new group)
a("1107","حسابات وسيطة ومعلقة (مدينة)","Clearing and Suspense (Debit)")
a("110701","حساب وسيط للمقبوضات","Cash Receipts Clearing", reconc=True, suspense=True)
a("110702","حساب معلق مدين","Suspense Account - Debit", suspense=True, reconc=True)
a("110703","حساب وسيط للتحويلات البنكية","Bank Transfer Clearing", reconc=True, suspense=True)
a("110704","حساب وسيط للرواتب","Payroll Clearing", subledger="PAYROLL", reconc=True, suspense=True)
a("110705","حساب وسيط لنقاط البيع","POS / Card Settlement Clearing", reconc=True, suspense=True, ind=["TRADING","RETAIL","HOSPITALITY"])
# 1201 non-current additions (RoU, impairment, CWIP, investment property, biological)
a("120112","حق استخدام أصل - عقارات","Right-of-Use Asset - Property", ifrs="IFRS 16", subledger="LEASE")
a("120113","حق استخدام أصل - مركبات ومعدات","Right-of-Use Asset - Vehicles & Equipment", ifrs="IFRS 16", subledger="LEASE")
a("120114","مجمع استهلاك حق الاستخدام","Accumulated Depreciation - Right-of-Use Assets", ifrs="IFRS 16", contra_of="120112")
a("120115","مجمع انخفاض قيمة الأصول الثابتة","Accumulated Impairment - PPE", ias="IAS 36", contra_of="1201")
a("120116","أعمال رأسمالية تحت التنفيذ","Capital Work-in-Progress (CWIP)", ias="IAS 16")
a("120117","استثمار عقاري","Investment Property", ias="IAS 40", ind=["REAL_ESTATE"])
a("120118","مجمع استهلاك الاستثمار العقاري","Accumulated Depreciation - Investment Property", ias="IAS 40", contra_of="120117", ind=["REAL_ESTATE"])
a("120119","أصول بيولوجية","Biological Assets", ias="IAS 41", ind=["AGRICULTURE"])
# 1202 intangible additions
a("120205","مجمع إطفاء الأصول غير الملموسة","Accumulated Amortization - Intangibles", ias="IAS 38", contra_of="1202")
a("120206","مجمع انخفاض قيمة الشهرة","Accumulated Impairment - Goodwill", ias="IAS 36", contra_of="120201")
a("120207","تكاليف تطوير مرسملة","Capitalized Development Costs", ias="IAS 38", ind=["SAAS","TECHNOLOGY"])
# 1203 investment additions
a("120304","أدوات مالية بالقيمة العادلة من خلال الدخل الشامل","Financial Assets at FVOCI", ifrs="IFRS 9", ias="IAS 32")
a("120305","أدوات دين بالتكلفة المطفأة","Debt Instruments at Amortized Cost", ifrs="IFRS 9")
a("120306","استثمارات في مشاريع مشتركة","Investments in Joint Ventures", ias="IAS 28 / IFRS 11")
# 1204 deferred tax asset (new group)
a("1204","الأصول الضريبية المؤجلة","Deferred Tax Assets")
a("120401","أصل ضريبي مؤجل","Deferred Tax Asset", ias="IAS 12", tax="DEFERRED")
# 1205 intercompany non-current (new group)
a("1205","ذمم مدينة بين الشركات - غير متداول","Intercompany Receivables - Non-current")
a("120501","قروض للشركات التابعة/الزميلة","Loans to Group Companies", ias="IAS 24", ic=True, eliminate=True)

# ---- CLASS 2 additions
a("210104","ذمم دائنة تجارية أخرى","Other Trade Payables", subledger="AP")
a("210105","مصروفات مستحقة - فوائد","Accrued Interest Payable")
a("210302","التزام عقد","Contract Liability", ifrs="IFRS 15", ind=["SERVICES","CONSTRUCTION","SAAS"])
a("210303","إيراد اشتراكات مؤجل","Deferred Subscription Revenue", ifrs="IFRS 15", ind=["SAAS"])
a("210304","دفعات مقدمة من العملاء","Customer Advances / Deposits", ifrs="IFRS 15")
a("210305","مبالغ محتجزة دائنة","Retention Payable", ind=["CONSTRUCTION"])
# 2104 tax expansion
a("210404","ضريبة القيمة المضافة على المخرجات","Output VAT", tax="VAT", is_tax=True, subledger="TAX", reconc=True)
a("210405","ضريبة القيمة المضافة مستحقة الدفع","VAT Payable (Net)", tax="VAT", is_tax=True, subledger="TAX", reconc=True)
a("210406","ضريبة الدمغة","Stamp Tax", tax="STAMP", is_tax=True)
a("210407","مساهمة التضامن الاجتماعي","Social Solidarity Contribution", tax="SOLIDARITY", is_tax=True, note="Libya-specific; rate is configuration with legal reference")
# 2105 current lease liability
a("210503","الجزء المتداول من التزام الإيجار","Current Portion of Lease Liability", ifrs="IFRS 16", subledger="LEASE")
# 2106 provisions (current) new group
a("2106","المخصصات المتداولة","Current Provisions")
a("210601","مخصص الضمان","Warranty Provision - Current", ias="IAS 37")
a("210602","مخصصات أخرى","Other Provisions", ias="IAS 37")
a("210603","مخصص عقود مثقلة","Onerous Contract Provision", ias="IAS 37", ind=["CONSTRUCTION"])
# 2107 payroll liabilities new group
a("2107","التزامات الرواتب","Payroll Liabilities")
a("210701","رواتب صافية مستحقة الدفع","Net Salaries Payable", subledger="PAYROLL")
a("210702","استقطاع ضريبة الدخل من الرواتب","Payroll Income Tax Withheld (PAYE)", tax="PAYE", is_tax=True, subledger="PAYROLL")
a("210703","ضمان اجتماعي - حصة الموظف","Social Security - Employee Share", subledger="PAYROLL", note="Libya social security; rate is configuration")
a("210704","ضمان اجتماعي - حصة صاحب العمل","Social Security - Employer Share", subledger="PAYROLL")
a("210705","استقطاعات أخرى من الرواتب","Other Payroll Deductions", subledger="PAYROLL")
# 2108 clearing/suspense (liability) new group
a("2108","حسابات وسيطة ومعلقة (دائنة)","Clearing and Suspense (Credit)")
a("210801","حساب معلق دائن","Suspense Account - Credit", suspense=True, reconc=True)
a("210802","حساب وسيط للمدفوعات","Payments Clearing", suspense=True, reconc=True)
# 2109 derivative liabilities new group
a("2109","مشتقات مالية - التزامات","Derivative Financial Liabilities")
a("210901","مشتقات مالية - التزامات","Derivative Financial Liabilities", ifrs="IFRS 9 / IFRS 7")
# 22 non-current additions
a("2203","التزامات الإيجار طويلة الأجل","Lease Liabilities - Non-current")
a("220301","التزام إيجار طويل الأجل","Lease Liability - Non-current", ifrs="IFRS 16", subledger="LEASE")
a("2204","التزامات منافع الموظفين","Employee Benefit Obligations")
a("220401","التزام منافع محددة (نهاية الخدمة)","Defined Benefit Obligation", ias="IAS 19")
a("2205","الالتزامات الضريبية المؤجلة","Deferred Tax Liabilities")
a("220501","التزام ضريبي مؤجل","Deferred Tax Liability", ias="IAS 12", tax="DEFERRED")
a("2206","ذمم دائنة بين الشركات - غير متداول","Intercompany Payables - Non-current")
a("220601","قروض من الشركات القابضة/التابعة","Loans from Group Companies", ias="IAS 24", ic=True, eliminate=True)
a("210108","ذمم دائنة بين الشركات - متداول","Intercompany Payables - Current", ias="IAS 24", ic=True, eliminate=True)

# ---- CLASS 3 additions
a("310103","أسهم الخزينة","Treasury Shares", ias="IAS 32", contra_of="3101")
a("310104","حقوق غير مسيطرة (حصص الأقلية)","Non-controlling Interests (NCI)", ifrs="IFRS 10", consolidation=True)
a("320203","تعديلات فترات سابقة (تغير سياسات/تصحيح أخطاء)","Prior Period Adjustments", ias="IAS 8")
# 33 OCI reserves new group
a("33","بنود الدخل الشامل الآخر","Other Comprehensive Income (OCI) Reserves")
a("3301","احتياطيات الدخل الشامل الآخر","OCI Reserves")
a("330101","احتياطي القيمة العادلة - FVOCI","Fair Value Reserve - FVOCI", ifrs="IFRS 9", oci=True)
a("330102","احتياطي ترجمة العملات الأجنبية","Foreign Currency Translation Reserve", ias="IAS 21", oci=True)
a("330103","احتياطي تحوط التدفقات النقدية","Cash Flow Hedge Reserve", ifrs="IFRS 9", oci=True)
a("330104","احتياطي إعادة قياس منافع محددة","Remeasurement of Defined Benefit Plans", ias="IAS 19", oci=True)

# ---- CLASS 4 additions
a("4103","الإيرادات المتكررة (الاشتراكات)","Recurring / Subscription Revenue")
a("410301","إيرادات اشتراكات","Subscription Revenue", ifrs="IFRS 15", ind=["SAAS"])
a("4104","إيرادات العقود طويلة الأجل","Long-term Contract Revenue")
a("410401","إيراد معترف به على مدى الزمن","Revenue Recognized Over Time", ifrs="IFRS 15", ind=["CONSTRUCTION","SERVICES"])
a("420104","أرباح فروق العملة المحققة","Realized Foreign Exchange Gains", ias="IAS 21")
a("420105","أرباح القيمة العادلة - FVTPL","Fair Value Gains - FVTPL", ifrs="IFRS 9")
a("420106","أرباح انعكاس انخفاض القيمة","Reversal of Impairment Losses", ias="IAS 36")

# ---- CLASS 5 additions
a("5103","تكاليف الإنتاج غير المباشرة","Manufacturing Overhead", ind=["MANUFACTURING"])
a("510301","أجور غير مباشرة","Indirect Labor", ind=["MANUFACTURING"])
a("510302","مصروفات المصنع (كهرباء/صيانة)","Factory Overheads", ind=["MANUFACTURING"])
a("510303","استهلاك أصول الإنتاج","Depreciation - Production Assets", ias="IAS 16", ind=["MANUFACTURING"])
a("5104","تكلفة الخدمات المقدمة","Cost of Services Rendered", ind=["SERVICES","SAAS"])
a("510401","تكلفة العمالة المباشرة للخدمات","Direct Service Labor", ind=["SERVICES"])
a("510402","تكاليف الاستضافة والبنية التحتية","Hosting & Infrastructure Costs", ind=["SAAS"])
a("5105","انحرافات التكلفة المعيارية","Standard Cost Variances", ind=["MANUFACTURING"])
a("510501","انحراف المواد","Material Variance", ind=["MANUFACTURING"])
a("510502","انحراف الأجور","Labor Variance", ind=["MANUFACTURING"])
a("510503","انحراف التكاليف غير المباشرة","Overhead Variance", ind=["MANUFACTURING"])

# ---- CLASS 6 additions
a("620407","استهلاك حق استخدام الأصل","Depreciation - Right-of-Use Assets", ifrs="IFRS 16")
a("6205","خسائر انخفاض القيمة","Impairment Losses")
a("620501","خسارة انخفاض قيمة الأصول الثابتة","Impairment Loss - PPE", ias="IAS 36")
a("620502","خسارة انخفاض قيمة الشهرة","Impairment Loss - Goodwill", ias="IAS 36")
a("620503","خسارة انخفاض قيمة المخزون","Inventory Write-down", ias="IAS 2")
a("630104","خسائر فروق العملة المحققة","Realized Foreign Exchange Losses", ias="IAS 21")
a("630105","خسائر فروق العملة غير المحققة","Unrealized Foreign Exchange Losses", ias="IAS 21")
a("630106","فائدة التزام الإيجار","Interest on Lease Liabilities", ifrs="IFRS 16")
a("630203","مصروف الخسائر الائتمانية المتوقعة","Expected Credit Loss Expense", ifrs="IFRS 9")
a("630204","خسائر القيمة العادلة - FVTPL","Fair Value Losses - FVTPL", ifrs="IFRS 9")
# 64 income tax expense (new group)
a("64","ضريبة الدخل","Income Tax Expense")
a("6401","ضريبة الدخل","Income Tax")
a("640101","ضريبة الدخل الجارية","Current Income Tax Expense", ias="IAS 12", tax="CIT")
a("640102","ضريبة الدخل المؤجلة","Deferred Income Tax Expense", ias="IAS 12", tax="DEFERRED")

# ---- CLASS 7 additions
a("7103","حساب ملخص الدخل الشامل","Comprehensive Income Summary")
a("710301","ملخص الدخل الشامل الآخر","Other Comprehensive Income Summary", oci=True)

# ================= INDUSTRY PACKS (tagged; company activates as needed) =======
# Hospitality
a("4105","إيرادات الضيافة","Hospitality Revenue", ind=["HOSPITALITY"])
a("410501","إيرادات الغرف","Room Revenue", ifrs="IFRS 15", ind=["HOSPITALITY"])
a("410502","إيرادات الأطعمة والمشروبات","Food & Beverage Revenue", ifrs="IFRS 15", ind=["HOSPITALITY"])
a("410503","إيرادات القاعات والفعاليات","Banquet & Events Revenue", ifrs="IFRS 15", ind=["HOSPITALITY"])
a("210306","ودائع حجوزات مقدمة","Guest Advance Deposits", ifrs="IFRS 15", ind=["HOSPITALITY"])
a("620307","مستلزمات تشغيلية (OS&E)","Operating Supplies & Equipment", ind=["HOSPITALITY"])
# Healthcare
a("110211","ذمم مرضى مدينة","Patient Receivables", subledger="AR", reconc=True, ind=["HEALTHCARE"])
a("110212","مطالبات تأمين مدينة","Insurance Claims Receivable", subledger="AR", reconc=True, ind=["HEALTHCARE"])
a("110305","مخزون مستلزمات طبية","Medical Supplies Inventory", subledger="INV", ias="IAS 2", ind=["HEALTHCARE"])
a("110306","مخزون أدوية","Pharmaceuticals Inventory", subledger="INV", ias="IAS 2", ind=["HEALTHCARE"])
a("110307","مخصص تلف/انتهاء صلاحية المخزون الطبي","Allowance for Expired/Obsolete Medical Stock", ias="IAS 2", contra_of="110305", ind=["HEALTHCARE"])
a("4106","إيرادات الخدمات الطبية","Medical Service Revenue", ind=["HEALTHCARE"])
a("410601","إيرادات خدمات المرضى","Patient Service Revenue", ifrs="IFRS 15", ind=["HEALTHCARE"])
a("410602","إيراد طبي غير مفوتر","Unbilled Medical Revenue", ifrs="IFRS 15", ind=["HEALTHCARE"])
# Logistics
a("4107","إيرادات الشحن والنقل","Freight & Transport Revenue", ind=["LOGISTICS"])
a("410701","إيرادات الشحن","Freight Revenue", ifrs="IFRS 15", ind=["LOGISTICS"])
a("110308","مخزون بضاعة في الطريق","Goods in Transit", subledger="INV", ind=["LOGISTICS","TRADING"])
a("110706","حساب وسيط للتخليص الجمركي","Customs Clearing", suspense=True, reconc=True, ind=["LOGISTICS"])
a("5106","تكاليف التشغيل اللوجستي","Logistics Operating Costs", ind=["LOGISTICS"])
a("510601","وقود","Fuel", ind=["LOGISTICS"])
a("510602","صيانة الأسطول","Fleet Maintenance", ind=["LOGISTICS"])
a("510603","استهلاك الأسطول","Fleet Depreciation", ias="IAS 16", ind=["LOGISTICS"])
# Trading / Retail
a("210307","التزام برامج الولاء","Customer Loyalty Liability", ifrs="IFRS 15", ind=["TRADING","RETAIL"])
a("210308","التزام بطاقات الهدايا","Gift Card Liability", ifrs="IFRS 15", ind=["TRADING","RETAIL"])
# Non-profit (fund accounting)
a("34","صافي الأصول (منظمات غير ربحية)","Net Assets (Non-profit)", ind=["NON_PROFIT"])
a("3401","صافي الأصول","Net Assets", ind=["NON_PROFIT"])
a("340101","صافي أصول غير مقيّدة","Unrestricted Net Assets", ind=["NON_PROFIT"])
a("340102","صافي أصول مقيّدة","Restricted Net Assets", ind=["NON_PROFIT"])
a("340103","أوقاف وهبات دائمة","Endowment Net Assets", ind=["NON_PROFIT"])
a("4108","المنح والتبرعات","Grants & Donations", ind=["NON_PROFIT"])
a("410801","تبرعات وهبات","Donations & Gifts", ind=["NON_PROFIT"])
a("410802","منح مقيّدة","Restricted Grants", ind=["NON_PROFIT"])
a("65","مصروفات البرامج والأنشطة","Program & Activity Expenses", ind=["NON_PROFIT"])
a("6501","مصروفات البرامج","Program Expenses", ind=["NON_PROFIT"])
a("650101","مصروفات تنفيذ البرامج","Program Delivery Expenses", ind=["NON_PROFIT"])
a("6502","مصروفات الدعم","Support Expenses", ind=["NON_PROFIT"])
a("650201","مصروفات إدارية وجمع تبرعات","Administrative & Fundraising Expenses", ind=["NON_PROFIT"])

# ---- CLASS 8 memo / off-balance-sheet (new class)
a("8","حسابات نظامية وخارج الميزانية","Memo and Off-Balance-Sheet Accounts")
a("81","حسابات نظامية","Memorandum Accounts")
a("8101","التزامات وضمانات محتملة","Contingencies and Guarantees")
a("810101","خطابات ضمان صادرة","Guarantees Issued", ias="IAS 37", statistical=True)
a("810102","خطابات ضمان واردة","Guarantees Received", statistical=True)
a("810103","التزامات محتملة","Contingent Liabilities", ias="IAS 37", statistical=True)
a("810104","التزامات رأسمالية تعاقدية","Capital Commitments", statistical=True)
a("8102","أصول مُدارة لحساب الغير","Assets Held on Behalf of Others")
a("810201","بضاعة أمانة","Consignment Goods", statistical=True, ind=["TRADING"])
a("810202","أصول عملاء مُدارة","Managed Client Assets", statistical=True)

# =============================================================== derivation
def acct_type(code):
    return {"1":"asset","2":"liability","3":"equity","4":"revenue","5":"cost_of_sales","6":"expense","7":"closing","8":"memo"}[code[0]]

NATURE_AR = {"asset":"أصل","liability":"خصم","equity":"حقوق ملكية","revenue":"إيراد","cost_of_sales":"مصروف","expense":"مصروف","closing":"ختامي","memo":"نظامي"}
def level_of(code):
    return {1:1,2:2,4:3,6:4,8:5,10:6}[len(code)]
def parent_of(code):
    return {2:code[:1],4:code[:2],6:code[:4],8:code[:6],10:code[:8]}.get(len(code))
def default_nb(t):
    return {"asset":"debit","liability":"credit","equity":"credit","revenue":"credit","cost_of_sales":"debit","expense":"debit","closing":"none","memo":"none"}[t]
def statement_of(t):
    return {"asset":"balance_sheet","liability":"balance_sheet","equity":"balance_sheet","revenue":"income_statement","cost_of_sales":"income_statement","expense":"income_statement","closing":"none","memo":"off_balance_sheet"}[t]
def closing_behavior(t, code, oci):
    if t=="closing": return "closing_account"
    if code in ("320201","320202"): return "retained_earnings"
    if oci: return "oci"
    if t=="memo": return "none"
    return "temporary" if t in ("revenue","cost_of_sales","expense") else "permanent"
def cashflow_of(t, code):
    if code[:4] in ("1204","2205"): return "none"   # deferred tax is a non-cash item
    if t in ("revenue","cost_of_sales","expense"): return "operating"
    if code.startswith("1101"): return "none"  # cash itself
    if code[:2]=="12" and code[:4] in ("1201","1202","1203","1205"): return "investing"
    if code[:4]=="1105" or code[:4]=="1204": return "investing"
    if code[:2] in ("11",) : return "operating"
    if code[:4] in ("2105","2201","2203","2206") or code[:2]=="31" or code in ("320301","320302","310103"): return "financing"
    if code[:2]=="22": return "financing"
    if code[:2]=="21": return "operating"
    return "none"

def fs_line(t, code, name_en, oci, contra, subledger, statistical):
    if statistical: return "Off-Balance-Sheet > "+name_en
    grp = code[:2]
    cur = "Current" if grp in ("11","21") else "Non-current"
    if t=="asset": base=f"SFP > {cur} Assets"
    elif t=="liability": base=f"SFP > {cur} Liabilities"
    elif t=="equity": base="SFP > Equity" + (" > Other Comprehensive Income" if oci else "")
    elif t=="revenue": base="P&L > Revenue"
    elif t=="cost_of_sales": base="P&L > Cost of Sales"
    elif t=="expense": base="P&L > Operating Expenses" if grp in ("61","62") else ("P&L > Finance Costs" if grp=="63" else ("P&L > Impairment" if grp=="62" else "P&L > Tax"))
    else: base="Memo"
    if grp=="64": base="P&L > Income Tax"
    return base + (" (contra)" if contra else "")

# =============================================================== assemble
records = json.load(open(SRC, encoding="utf-8"))
by = {}
final = {}

def make(code, name_ar, name_en, over):
    t = acct_type(code)
    oci = bool(over.get("oci"))
    contra = bool(over.get("contra_of"))
    statistical = bool(over.get("statistical"))
    flip = {"debit":"credit","credit":"debit","none":"none"}
    if over.get("normal_balance"):
        nb = over["normal_balance"]
    elif over.get("_dc"):
        nb = over["_dc"]
    elif contra:
        nb = flip[default_nb(t)]      # a contra account carries the opposite balance
    else:
        nb = default_nb(t)
    subledger = over.get("subledger","")
    rec = {
        "code": code,
        "name_ar": over.get("name_ar", name_ar),
        "name_en": over.get("name_en", name_en) or name_en,
        "parent": parent_of(code),
        "level": level_of(code),
        "account_type": t,
        "account_nature": NATURE_AR[t],
        "normal_balance": nb,
        "is_posting": None,  # leaf-computed later
        "is_control": bool(over.get("is_control")),
        "is_contra": contra,
        "contra_of": over.get("contra_of"),
        "is_bank": bool(over.get("is_bank")),
        "is_tax": bool(over.get("is_tax")),
        "is_suspense": bool(over.get("suspense")),
        "is_statistical": statistical,
        "is_intercompany": bool(over.get("ic")),
        "eliminate_on_consolidation": bool(over.get("eliminate")) or bool(over.get("consolidation")),
        "financial_statement_line": over.get("fs_override") or fs_line(t, code, over.get("name_en",name_en) or name_en, oci, contra, subledger, statistical),
        "cash_flow_classification": cashflow_of(t, code),
        "ifrs_mapping": over.get("ifrs",""),
        "ias_mapping": over.get("ias",""),
        "tax_mapping": over.get("tax",""),
        "subledger_mapping": subledger,
        "reconciliation_required": bool(over.get("reconc")),
        "closing_behavior": closing_behavior(t, code, oci),
        "industry_applicability": over.get("ind", ["ALL"]),
        "depreciable": bool(over.get("depreciable")),
        "notes": over.get("note") or over.get("notes"),
    }
    return rec

# 1) import baseline (corrected)
dc_map = {"مدين":"debit","دائن":"credit","—":"none"}
for r in records:
    code = r["code"]
    over = dict(CORRECTIONS.get(code, {}))
    over.setdefault("_dc", dc_map.get((r.get("dc_ar") or "").strip(), None))
    if r.get("notes"): over.setdefault("notes", r["notes"])
    final[code] = make(code, r["name_ar"], EN.get(code, r["name_ar"]), over)

# 2) apply additions
for d in ADD:
    code = d["code"]
    over = {k:v for k,v in d.items() if k not in ("code","name_ar","name_en")}
    final[code] = make(code, d["name_ar"], d["name_en"], over)

# 3) leaf detection -> posting
children = collections.defaultdict(int)
for code, rec in final.items():
    if rec["parent"]: children[rec["parent"]] += 1
for code, rec in final.items():
    rec["is_posting"] = (children[code] == 0) and rec["account_type"] not in ("closing_root",)
    # closing accounts remain system-only when posting
    rec["manual_journal_allowed"] = rec["account_type"] not in ("closing","memo") and rec["is_posting"]
    rec["control_or_detail"] = "control" if rec["is_control"] else ("detail" if rec["is_posting"] else "header")

# ================================================================ validate
errors=[]
codes=set(final)
for code, rec in final.items():
    p=rec["parent"]
    if p and p not in codes: errors.append(f"{code}: missing parent {p}")
    if rec["contra_of"] and rec["contra_of"] not in codes: errors.append(f"{code}: contra_of {rec['contra_of']} missing")
    # normal balance sanity vs type default (contra expected to flip)
    exp=default_nb(rec["account_type"])
    if rec["normal_balance"]!="none" and not rec["is_contra"] and rec["normal_balance"]!=exp:
        errors.append(f"{code}: normal_balance {rec['normal_balance']} != {exp} and not flagged contra")
    if rec["is_contra"] and rec["normal_balance"]==exp:
        errors.append(f"{code}: flagged contra but normal_balance not flipped")

# ================================================================ write
os.makedirs(OUTDIR, exist_ok=True)
ordered = [final[c] for c in sorted(final, key=lambda c:(len(c),c))]
json.dump(ordered, open(os.path.join(OUTDIR,"coa_enterprise_libya.json"),"w",encoding="utf-8"), ensure_ascii=False, indent=1)

cols=["code","name_ar","name_en","parent","level","account_type","account_nature","normal_balance",
      "is_posting","control_or_detail","is_contra","contra_of","financial_statement_line","cash_flow_classification",
      "ifrs_mapping","ias_mapping","tax_mapping","subledger_mapping","reconciliation_required","closing_behavior","industry_applicability"]
with open(os.path.join(OUTDIR,"coa_enterprise_libya.csv"),"w",encoding="utf-8-sig",newline="") as f:
    w=csv.writer(f); w.writerow(cols)
    for r in ordered:
        w.writerow([ "|".join(r[c]) if isinstance(r[c],list) else ("" if r[c] is None else r[c]) for c in cols])

# ---- industry template overlay files (accounts tagged with a specific industry)
IND_LABEL={"TRADING":"trading_retail","RETAIL":"trading_retail","MANUFACTURING":"manufacturing","SERVICES":"services",
 "SAAS":"saas","CONSTRUCTION":"construction","REAL_ESTATE":"real_estate","HEALTHCARE":"healthcare",
 "HOSPITALITY":"hospitality","AGRICULTURE":"agriculture","LOGISTICS":"logistics","NON_PROFIT":"non_profit"}
overlays=collections.defaultdict(set)
for r in ordered:
    for ind in r["industry_applicability"]:
        if ind in IND_LABEL:
            overlays[IND_LABEL[ind]].add(r["code"])
for name, codes_ in overlays.items():
    json.dump({"template":name,"adds_account_codes":sorted(codes_)},
              open(os.path.join(TPLDIR,f"{name}.json"),"w",encoding="utf-8"), ensure_ascii=False, indent=1)

# ================================================================ report
base_n=len(records); add_n=len(ADD); total=len(final)
posting=sum(1 for r in ordered if r["is_posting"])
contra=sum(1 for r in ordered if r["is_contra"])
control=sum(1 for r in ordered if r["is_control"])
ic=sum(1 for r in ordered if r["is_intercompany"])
susp=sum(1 for r in ordered if r["is_suspense"])
stat=sum(1 for r in ordered if r["is_statistical"])
print(f"baseline={base_n}  additions={add_n}  total={total}  posting={posting}")
print(f"contra={contra}  control={control}  intercompany={ic}  suspense/clearing={susp}  memo/off-BS={stat}")
print(f"industry overlays: {sorted(overlays)}")
print("VALIDATION ERRORS:", len(errors))
for e in errors[:50]: print("  -",e)
