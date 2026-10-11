<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';

    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
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
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Подтверждение пароля</h1>
        <p class="mt-2 text-sm text-slate-500">
            Это защищённая зона приложения. Введите пароль, чтобы продолжить.
        </p>
    </header>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-4">
        <div>
            <InputLabel for="password" value="Пароль" />

            <TextInput
                id="password"
                type="password"
                class="mt-1 block w-full"
                v-model="form.password"
                required
                autocomplete="current-password"
                autofocus
            />

            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="flex justify-end pt-2">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Подтвердить
            </PrimaryButton>
        </div>
    </form>
</template>
