<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { useAccountingContextStore } from '@/stores/accountingContext';
import { useLocaleStore } from '@/stores/locale';

const { t, locale } = useI18n();
const context = useAccountingContextStore();
const localeStore = useLocaleStore();
const {
    companies,
    companyId,
    bookId,
    currentCompany,
    currentBook,
    currentPeriod,
    loading,
} = storeToRefs(context);

onMounted(() => {
    void context.loadContext();
});

function onCompanyChange(event: Event): void {
    const value = Number((event.target as HTMLSelectElement).value);
    if (value) {
        context.setCompany(value);
    }
}

function onBookChange(event: Event): void {
    const value = Number((event.target as HTMLSelectElement).value);
    if (value) {
        context.setBook(value);
    }
}

function companyLabel(c: {
    code: string;
    name_ar: string;
    name_en?: string | null;
}): string {
    return locale.value === 'ar'
        ? `${c.code} — ${c.name_ar}`
        : `${c.code} — ${c.name_en || c.name_ar}`;
}

function bookLabel(code: string): string {
    if (code === 'LOCAL') {
        return t('context.local');
    }
    if (code === 'IFRS') {
        return t('context.ifrs');
    }
    if (code === 'TAX') {
        return t('context.tax');
    }
    return code;
}
</script>

<template>
    <div
        class="border-border bg-background/95 supports-backdrop-filter:bg-background/80 flex flex-wrap items-center gap-x-4 gap-y-2 border-b px-4 py-2 text-sm backdrop-blur md:px-6"
    >
        <label class="flex items-center gap-2">
            <span class="text-muted-foreground whitespace-nowrap">{{
                t('context.company')
            }}</span>
            <select
                class="border-input bg-background focus-visible:ring-ring max-w-[220px] rounded-md border px-2 py-1.5 text-sm outline-none focus-visible:ring-2"
                :value="companyId ?? ''"
                :disabled="loading"
                @change="onCompanyChange"
            >
                <option disabled value="">{{ t('context.noCompany') }}</option>
                <option
                    v-for="c in companies"
                    :key="c.id"
                    :value="c.id"
                >
                    {{ companyLabel(c) }}
                </option>
            </select>
        </label>

        <label class="flex items-center gap-2">
            <span class="text-muted-foreground whitespace-nowrap">{{
                t('context.book')
            }}</span>
            <select
                class="border-input bg-background focus-visible:ring-ring rounded-md border px-2 py-1.5 text-sm outline-none focus-visible:ring-2"
                :value="bookId ?? ''"
                :disabled="!currentCompany"
                @change="onBookChange"
            >
                <option
                    v-for="b in currentCompany?.books ?? []"
                    :key="b.id"
                    :value="b.id"
                >
                    {{ bookLabel(b.code) }}
                </option>
            </select>
        </label>

        <div class="text-muted-foreground flex items-center gap-2">
            <span>{{ t('context.period') }}:</span>
            <span class="text-foreground font-medium">
                <template v-if="currentPeriod">
                    #{{ currentPeriod.period_no }} ·
                    {{ currentPeriod.start_date }} →
                    {{ currentPeriod.end_date }}
                </template>
                <template v-else>—</template>
            </span>
        </div>

        <div
            v-if="currentBook"
            class="bg-muted text-muted-foreground rounded-md px-2 py-1 text-xs font-medium tracking-wide uppercase"
        >
            {{ t('context.basis') }}: {{ currentBook.code }}
        </div>

        <div class="ms-auto flex items-center gap-1">
            <Button
                size="sm"
                :variant="localeStore.locale === 'ar' ? 'default' : 'ghost'"
                @click="localeStore.set('ar')"
            >
                العربية
            </Button>
            <Button
                size="sm"
                :variant="localeStore.locale === 'en' ? 'default' : 'ghost'"
                @click="localeStore.set('en')"
            >
                English
            </Button>
        </div>
    </div>
</template>
