export type ApiErrorBody = {
    code?: string;
    message?: string;
};

export type ApiSuccess<T> = {
    success: true;
    data: T;
    meta?: {
        pagination?: PaginationMeta;
        [key: string]: unknown;
    };
};

export type ApiFailure = {
    success: false;
    error: ApiErrorBody;
    message?: string;
    errors?: Record<string, string[]>;
};

export type PaginationMeta = {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
};

export type AccountingBookDto = {
    id: number;
    code: string;
    name_ar: string;
    name_en?: string;
    basis: string;
    is_primary: boolean;
};

export type PeriodDto = {
    id: number;
    period_no: number;
    start_date: string | null;
    end_date: string | null;
    status: string;
};

export type CompanyContextDto = {
    id: number;
    code: string;
    name_ar: string;
    name_en?: string | null;
    functional_currency: string;
    permissions: string[];
    books: AccountingBookDto[];
    current_period: PeriodDto | null;
};

export type MeContextDto = {
    user: { id: number; name: string; email: string };
    companies: CompanyContextDto[];
};

export type CustomerDto = {
    id: number;
    code: string;
    name_ar: string;
    currency?: string;
    is_active?: boolean;
};

export type SupplierDto = {
    id: number;
    code: string;
    legal_name: string;
    currency?: string;
    is_active?: boolean;
};

export type SalesInvoiceDto = {
    id: number;
    number?: string | null;
    invoice_date?: string;
    status: string;
    currency?: string;
    total?: string | number;
    journal_id?: number | null;
    customer_id?: number;
    customer?: CustomerDto;
};

export type AccountDto = {
    id: number;
    code: string;
    name_ar: string;
    account_type?: string;
    is_posting?: boolean;
};

export type JournalDto = {
    id: number;
    number?: string | null;
    journal_date?: string;
    status: string;
    source?: string;
    reference?: string | null;
    total_debit?: string | number;
    total_credit?: string | number;
    lines?: JournalLineDto[];
};

export type JournalLineDto = {
    id: number;
    account_id: number;
    debit?: string | number;
    credit?: string | number;
    description?: string | null;
    account?: AccountDto;
};
