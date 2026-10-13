<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';

    import { CheckCircleIcon } from '@heroicons/vue/24/outline';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    defineProps<{
        canResetPassword?: boolean;
        status?: string;
    }>();

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = () => {
        form.post(route('login'), {
            onFinish: () => {
                form.reset('password');
            },
        });
    };
</script>

<template>
    <Head title="Вход" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Вход</h1>
        <p class="text-rancho-ink-600 mt-2 text-sm leading-relaxed">
            Введите email и пароль, чтобы продолжить.
        </p>
    </header>

    <!-- Статус -->
    <Transition name="fade-up">
        <div
            v-if="status"
            v-scroll-reveal
            class="mb-5 flex items-start gap-3 rounded-xl border border-rancho-pine/30 bg-rancho-pine/10 px-4 py-3"
            role="status"
            aria-live="polite"
        >
            <CheckCircleIcon class="mt-0.5 h-4 w-4 shrink-0 text-rancho-pine" aria-hidden="true" />
            <p class="text-sm font-medium text-rancho-forest">
                {{ status }}
            </p>
        </div>
    </Transition>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-5">
        <BaseInput
            id="email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            v-model="form.email"
            :error="form.errors.email"
            required
            autofocus
            autocomplete="username"
            theme="light"
        />

        <BaseInput
            id="password"
            type="password"
            label="Пароль"
            placeholder="Введите пароль"
            v-model="form.password"
            :error="form.errors.password"
            required
            autocomplete="current-password"
            theme="light"
        />

        <!-- Remember me -->
        <label class="flex cursor-pointer select-none items-center gap-2.5">
            <input
                type="checkbox"
                v-model="form.remember"
                class="border-rancho-ink-300 h-4 w-4 cursor-pointer rounded text-rancho-pine transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
            />
            <span class="text-rancho-ink-700 text-sm">Запомнить меня</span>
        </label>

        <!-- Actions -->
        <div
            class="flex flex-col-reverse items-stretch gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <BaseSubmitButton
                :processing="form.processing"
                label="Войти"
                :with-icon="false"
                class="sm:w-auto"
            />
        </div>
    </form>

    <!-- Регистрация -->
    <div
        v-scroll-reveal
        class="border-rancho-ink-200 text-rancho-ink-600 mt-6 border-t pt-6 text-center text-sm"
    >
        Нет аккаунта?
        <Link
            :href="route('register')"
            class="ml-1 font-bold text-rancho-pine underline-offset-2 transition-colors hover:text-rancho-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
        >
            Зарегистрироваться
        </Link>
    </div>
</template>
