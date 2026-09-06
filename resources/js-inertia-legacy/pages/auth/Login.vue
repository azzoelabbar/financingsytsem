<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const { t } = useI18n();

defineOptions({
    layout: {
        title: undefined,
        description: undefined,
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const layoutTitle = computed(() => t('auth.loginTitle'));
const layoutDescription = computed(() => t('auth.loginDescription'));
</script>

<template>
    <Head :title="t('auth.loginTitle')" />

    <!-- Keep layout title reactive via invisible sync for AuthLayout props -->
    <div class="sr-only" aria-hidden="true">
        {{ layoutTitle }} {{ layoutDescription }}
    </div>

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-600"
    >
        {{ status }}
    </div>

    <div class="mb-6 space-y-1 text-center lg:text-start">
        <h1 class="text-2xl font-semibold tracking-tight">
            {{ t('auth.loginTitle') }}
        </h1>
        <p class="text-muted-foreground text-sm">
            {{ t('auth.loginDescription') }}
        </p>
    </div>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email">{{ t('auth.email') }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                    dir="ltr"
                    class="text-start"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between gap-3">
                    <Label for="password">{{ t('auth.password') }}</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        {{ t('auth.forgot') }}
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    :placeholder="t('auth.password')"
                    dir="ltr"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center gap-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>{{ t('auth.remember') }}</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-1 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                {{ t('auth.login') }}
            </Button>
        </div>

        <div class="text-muted-foreground text-center text-sm">
            {{ t('auth.noAccount') }}
            <TextLink :href="register()" :tabindex="5">{{
                t('auth.signup')
            }}</TextLink>
        </div>
    </Form>
</template>
