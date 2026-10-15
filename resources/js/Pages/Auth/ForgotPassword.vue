<script setup lang="ts">
    import { Head, useForm } from '@inertiajs/vue3';

    import { CheckCircleIcon } from '@heroicons/vue/24/outline';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
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
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Восстановление пароля</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
            Забыли пароль? Ничего страшного. Укажите email — мы отправим ссылку для сброса пароля.
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

        <div class="flex items-center justify-end pt-2">
            <BaseSubmitButton :processing="form.processing" label="Отправить ссылку" />
        </div>
    </form>
</template>
