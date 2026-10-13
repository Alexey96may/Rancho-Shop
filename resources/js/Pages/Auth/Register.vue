<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = () => {
        form.post(route('register'), {
            onFinish: () => {
                form.reset('password', 'password_confirmation');
            },
        });
    };
</script>

<template>
    <Head title="Регистрация" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Регистрация</h1>
        <p class="text-rancho-ink-600 mt-2 text-sm leading-relaxed">
            Создайте аккаунт — это займёт меньше минуты.
        </p>
    </header>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-5">
        <BaseInput
            id="name"
            type="text"
            label="Имя"
            placeholder="Как к вам обращаться?"
            v-model="form.name"
            :error="form.errors.name"
            required
            autofocus
            autocomplete="name"
            theme="light"
        />

        <BaseInput
            id="email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            v-model="form.email"
            :error="form.errors.email"
            required
            autocomplete="username"
            theme="light"
        />

        <BaseInput
            id="password"
            type="password"
            label="Пароль"
            placeholder="Придумайте пароль"
            v-model="form.password"
            :error="form.errors.password"
            required
            autocomplete="new-password"
            theme="light"
        />

        <BaseInput
            id="password_confirmation"
            type="password"
            label="Подтверждение пароля"
            placeholder="Повторите пароль"
            v-model="form.password_confirmation"
            :error="form.errors.password_confirmation"
            required
            autocomplete="new-password"
            theme="light"
        />

        <!-- Actions -->
        <div
            class="flex flex-col-reverse items-stretch gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <Link
                :href="route('login')"
                class="text-rancho-ink-500 text-center text-sm font-medium underline-offset-2 transition-colors hover:text-rancho-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white sm:text-left"
            >
                Уже зарегистрированы?
            </Link>

            <BaseSubmitButton
                :processing="form.processing"
                label="Зарегистрироваться"
                :with-icon="false"
                class="sm:w-auto"
            />
        </div>
    </form>
</template>
