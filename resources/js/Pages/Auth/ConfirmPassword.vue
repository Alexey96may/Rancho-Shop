<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const form = useForm({
        password: '',
    });

    const submit = () => {
        form.post(route('password.confirm'), {
            onFinish: () => {
                form.reset();
            },
        });
    };
</script>

<template>
    <Head title="Подтверждение пароля" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Подтверждение пароля</h1>
        <p class="text-rancho-ink-600 mt-2 text-sm leading-relaxed">
            Это защищённая зона приложения. Введите пароль, чтобы продолжить.
        </p>
    </header>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-5">
        <BaseInput
            id="password"
            type="password"
            label="Пароль"
            placeholder="Введите пароль"
            v-model="form.password"
            :error="form.errors.password"
            required
            autocomplete="current-password"
            autofocus
            theme="light"
        />

        <div class="flex justify-end pt-2">
            <BaseSubmitButton
                :processing="form.processing"
                label="Подтвердить"
                :with-icon="false"
            />
        </div>
    </form>
</template>
