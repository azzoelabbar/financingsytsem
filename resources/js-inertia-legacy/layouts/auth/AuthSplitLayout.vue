<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { applyDocumentDirection, getStoredLocale, setLocale } from '@/i18n';

defineProps<{
    title?: string;
    description?: string;
}>();

const { t, locale } = useI18n();
const page = usePage();
const appName = page.props.name as string;

onMounted(() => {
    applyDocumentDirection(getStoredLocale());
});

function setLang(next: 'ar' | 'en'): void {
    setLocale(next);
}
</script>

<template>
    <div class="grid min-h-svh lg:grid-cols-2">
        <aside
            class="relative hidden flex-col justify-between overflow-hidden bg-[#0b1220] px-10 py-10 text-white lg:flex"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-30"
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
                    background-size: 40px 40px;
                "
            />
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(16,185,129,0.18),transparent_50%)]"
            />

            <Link href="/" class="relative z-10 flex items-center gap-3">
                <div
                    class="flex size-9 items-center justify-center rounded-md border border-white/15 bg-white/5 text-sm font-semibold"
                >
                    مح
                </div>
                <div>
                    <p class="text-sm font-semibold">{{ t('app.name') }}</p>
                    <p class="text-xs text-white/50">{{ appName }}</p>
                </div>
            </Link>

            <div class="relative z-10 max-w-md space-y-5">
                <h2 class="text-3xl leading-snug font-semibold tracking-tight">
                    {{ t('auth.panelTitle') }}
                </h2>
                <p class="text-sm leading-relaxed text-white/65">
                    {{ t('auth.panelBody') }}
                </p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li
                        v-for="key in ['ar', 'gl', 'books']"
                        :key="key"
                        class="flex items-start gap-2"
                    >
                        <span
                            class="mt-1 size-1.5 shrink-0 rounded-full bg-emerald-400"
                        />
                        <span>{{ t(`auth.panelPoints.${key}`) }}</span>
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-xs text-white/40">
                LOCAL · IFRS · TAX
            </p>
        </aside>

        <div
            class="bg-background relative flex flex-col justify-center px-6 py-10 sm:px-10"
        >
            <div
                class="absolute inset-x-0 top-0 flex items-center justify-between px-6 py-4 sm:px-10"
            >
                <Link
                    href="/"
                    class="text-muted-foreground hover:text-foreground text-sm lg:invisible"
                >
                    {{ t('app.name') }}
                </Link>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="rounded-md px-2.5 py-1.5 text-xs font-medium"
                        :class="
                            locale === 'ar'
                                ? 'bg-foreground text-background'
                                : 'text-muted-foreground hover:bg-muted'
                        "
                        @click="setLang('ar')"
                    >
                        العربية
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-2.5 py-1.5 text-xs font-medium"
                        :class="
                            locale === 'en'
                                ? 'bg-foreground text-background'
                                : 'text-muted-foreground hover:bg-muted'
                        "
                        @click="setLang('en')"
                    >
                        English
                    </button>
                </div>
            </div>

            <div class="mx-auto w-full max-w-[380px] space-y-8">
                <div class="space-y-2 text-center lg:text-start">
                    <h1
                        v-if="title"
                        class="text-2xl font-semibold tracking-tight"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-muted-foreground text-sm leading-relaxed"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
