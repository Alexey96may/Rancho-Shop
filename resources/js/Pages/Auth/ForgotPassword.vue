<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';

    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    defineProps<{
        status?: string;
    }>();

    const form = useForm({
        email: '',
    });

    const submit = () => {
        form.post(route('password.email'));
    };
</script>

<template>
    <Head title="Восстановление пароля" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Восстановление пароля</h1>
        <p class="mt-2 text-sm text-slate-500">
            Забыли пароль? Ничего страшного. Укажите email — мы отправим ссылку для сброса пароля.
        </p>
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

        <div class="flex items-center justify-end pt-2">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Отправить ссылку
            </PrimaryButton>
        </div>
    </form>
</template>
