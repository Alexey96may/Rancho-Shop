<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';

    import Checkbox from '@/Components/Checkbox.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
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
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Вход</h1>
        <p class="mt-2 text-sm text-slate-500">Введите email и пароль, чтобы продолжить.</p>
    </header>

    <div
        v-scroll-reveal
        v-if="status"
        class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
    >
        {{ status }}
    </div>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-4">
        <div>
            <InputLabel for="email" value="Email" />

            <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
            />

            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div>
            <InputLabel for="password" value="Пароль" />

            <TextInput
                id="password"
                type="password"
                class="mt-1 block w-full"
                v-model="form.password"
                required
                autocomplete="current-password"
            />

            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <label class="flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" />
            <span class="ms-2 text-sm text-slate-600">Запомнить меня</span>
        </label>

        <div class="flex items-center justify-between pt-2">
            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="rounded-md text-sm text-slate-500 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Забыли пароль?
            </Link>

            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Войти
            </PrimaryButton>
        </div>
    </form>

    <div
        v-scroll-reveal
        class="mt-6 border-t border-slate-200 pt-6 text-center text-sm text-slate-500"
    >
        Нет аккаунта?
        <Link
            :href="route('register')"
            class="ml-1 font-semibold text-indigo-600 underline-offset-2 hover:text-indigo-500 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Зарегистрироваться
        </Link>
    </div>
</template>
