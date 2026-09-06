<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { applyDocumentDirection, getStoredLocale, setLocale } from '@/i18n';

const { t, locale } = useI18n();
const page = usePage();

onMounted(() => {
    applyDocumentDirection(getStoredLocale());
});

function setLang(next: 'ar' | 'en'): void {
    setLocale(next);
}
</script>

<template>
    <Head :title="t('welcome.title')" />

    <div
        class="relative min-h-svh overflow-hidden bg-[#0b1220] text-white"
    >
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.35]"
            style="
                background-image:
                    linear-gradient(
                        to right,
                        rgba(255, 255, 255, 0.06) 1px,
                        transparent 1px
                    ),
                    linear-gradient(
                        to bottom,
                        rgba(255, 255, 255, 0.06) 1px,
                        transparent 1px
                    );
                background-size: 48px 48px;
            "
        />
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(34,197,94,0.12),transparent_55%)]"
        />

        <header
            class="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-6 py-5 md:px-8"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-9 items-center justify-center rounded-md border border-white/15 bg-white/5 text-sm font-semibold tracking-wide"
                >
                    مح
                </div>
                <div>
                    <p class="text-sm font-semibold tracking-tight">
                        {{ t('welcome.title') }}
                    </p>
                    <p class="text-xs text-white/55">Enterprise ERP</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-md px-2.5 py-1.5 text-xs font-medium transition"
                    :class="
                        locale === 'ar'
                            ? 'bg-white text-[#0b1220]'
                            : 'text-white/70 hover:bg-white/10'
                    "
                    @click="setLang('ar')"
                >
                    العربية
                </button>
                <button
                    type="button"
                    class="rounded-md px-2.5 py-1.5 text-xs font-medium transition"
                    :class="
                        locale === 'en'
                            ? 'bg-white text-[#0b1220]'
                            : 'text-white/70 hover:bg-white/10'
                    "
                    @click="setLang('en')"
                >
                    English
                </button>
                <Link
                    v-if="page.props.auth.user"
                    href="/dashboard"
                    class="ms-2 rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-[#04140c] transition hover:bg-emerald-400"
                >
                    {{ t('welcome.dashboard') }}
                </Link>
                <template v-else>
                    <Link
                        href="/login"
                        class="ms-2 rounded-md px-3 py-2 text-sm text-white/80 transition hover:bg-white/10"
                    >
                        {{ t('welcome.login') }}
                    </Link>
                    <Link
                        href="/register"
                        class="rounded-md border border-white/20 px-3 py-2 text-sm font-medium transition hover:bg-white/10"
                    >
                        {{ t('welcome.register') }}
                    </Link>
                </template>
            </div>
        </header>

        <main
            class="relative z-10 mx-auto flex min-h-[calc(100svh-5rem)] w-full max-w-6xl flex-col justify-center px-6 py-16 md:px-8"
        >
            <div class="max-w-2xl">
                <p
                    class="mb-4 text-xs font-medium tracking-[0.18em] text-emerald-300/90 uppercase"
                >
                    LOCAL · IFRS · TAX
                </p>
                <h1
                    class="text-4xl leading-tight font-semibold tracking-tight text-balance md:text-5xl"
                >
                    {{ t('welcome.headline') }}
                </h1>
                <p
                    class="mt-5 max-w-xl text-base leading-relaxed text-white/65 md:text-lg"
                >
                    {{ t('welcome.body') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <Link
                        v-if="page.props.auth.user"
                        href="/dashboard"
                        class="rounded-md bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-[#04140c] transition hover:bg-emerald-400"
                    >
                        {{ t('welcome.enter') }}
                    </Link>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-md bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-[#04140c] transition hover:bg-emerald-400"
                        >
                            {{ t('welcome.enter') }}
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-md border border-white/20 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-white/10"
                        >
                            {{ t('welcome.register') }}
                        </Link>
                    </template>
                </div>
            </div>

            <div
                class="mt-16 grid gap-3 border-t border-white/10 pt-8 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="key in ['ar', 'gl', 'reports', 'books']"
                    :key="key"
                    class="rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3"
                >
                    <p class="text-sm font-medium text-white/90">
                        {{ t(`welcome.features.${key}`) }}
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
