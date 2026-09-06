import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from '@/lib/api';
import type {
    AccountingBookDto,
    CompanyContextDto,
    MeContextDto,
    PeriodDto,
} from '@/types/api';

const COMPANY_KEY = 'erp.companyId';
const BOOK_KEY = 'erp.bookId';

export const useAccountingContextStore = defineStore('accountingContext', () => {
    const loading = ref(false);
    const error = ref<string | null>(null);
    const user = ref<MeContextDto['user'] | null>(null);
    const companies = ref<CompanyContextDto[]>([]);
    const companyId = ref<number | null>(
        Number(localStorage.getItem(COMPANY_KEY)) || null,
    );
    const bookId = ref<number | null>(
        Number(localStorage.getItem(BOOK_KEY)) || null,
    );
    const revision = ref(0);

    const currentCompany = computed(
        () => companies.value.find((c) => c.id === companyId.value) ?? null,
    );
    const currentBook = computed(
        () =>
            currentCompany.value?.books.find((b) => b.id === bookId.value) ??
            null,
    );
    const currentPeriod = computed<PeriodDto | null>(
        () => currentCompany.value?.current_period ?? null,
    );
    const permissions = computed(
        () => currentCompany.value?.permissions ?? [],
    );
    const currency = computed(
        () => currentCompany.value?.functional_currency ?? 'LYD',
    );

    function can(permission: string): boolean {
        return permissions.value.includes(permission);
    }

    function persist(bumpRevision = true): void {
        if (companyId.value) {
            localStorage.setItem(COMPANY_KEY, String(companyId.value));
        }
        if (bookId.value) {
            localStorage.setItem(BOOK_KEY, String(bookId.value));
        }
        if (bumpRevision) {
            revision.value += 1;
        }
    }

    function setCompany(id: number): void {
        if (companyId.value === id) {
            const company = companies.value.find((c) => c.id === id);
            const primary =
                company?.books.find((b) => b.is_primary) ?? company?.books[0];
            if (primary && bookId.value !== primary.id && !bookId.value) {
                bookId.value = primary.id;
                persist(true);
            } else {
                persist(false);
            }
            return;
        }
        companyId.value = id;
        const company = companies.value.find((c) => c.id === id);
        const primary =
            company?.books.find((b) => b.is_primary) ?? company?.books[0];
        bookId.value = primary?.id ?? null;
        persist(true);
    }

    function setBook(id: number): void {
        if (bookId.value === id) {
            persist(false);
            return;
        }
        bookId.value = id;
        persist(true);
    }

    async function loadContext(): Promise<void> {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get<MeContextDto>('/api/v1/me/context');
            user.value = res.data.user;
            companies.value = res.data.companies;
            if (!companyId.value && companies.value[0]) {
                setCompany(companies.value[0].id);
            } else if (
                companyId.value &&
                !companies.value.some((c) => c.id === companyId.value)
            ) {
                if (companies.value[0]) {
                    setCompany(companies.value[0].id);
                }
            } else if (
                companyId.value &&
                bookId.value &&
                !currentCompany.value?.books.some((b) => b.id === bookId.value)
            ) {
                const primary =
                    currentCompany.value?.books.find((b) => b.is_primary) ??
                    currentCompany.value?.books[0];
                bookId.value = primary?.id ?? null;
                persist(true);
            } else {
                // Sync storage only — do not bump revision (avoids refetch storms).
                persist(false);
            }
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Context failed';
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        error,
        user,
        companies,
        companyId,
        bookId,
        revision,
        currentCompany,
        currentBook,
        currentPeriod,
        permissions,
        currency,
        can,
        setCompany,
        setBook,
        loadContext,
    };
});

export type { AccountingBookDto };
