<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const props = defineProps<{
        email: string;
        token: string;
    }>();

    const form = useForm({
        token: props.token,
        email: props.email,
        password: '',
        password_confirmation: '',
    });

    const submit = () => {
        form.post(route('password.store'), {
            onFinish: () => {
                form.reset('password', 'password_confirmation');
            },
        });
    };
</script>

<template>
    <Head title="Новый пароль" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Новый пароль</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
            Придумайте новый пароль для вашего аккаунта.
        </p>
    </header>

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

        <div class="flex justify-end pt-2">
            <BaseSubmitButton :processing="form.processing" label="Сохранить пароль" />
        </div>
    </form>
</template>
