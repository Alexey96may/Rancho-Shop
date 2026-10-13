<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link, useForm } from '@inertiajs/vue3';

    import { CheckCircleIcon } from '@heroicons/vue/24/outline';

    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const props = defineProps<{
        status?: string;
    }>();

    const form = useForm({});

    const submit = () => {
        form.post(route('verification.send'));
    };

    const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Подтверждение email" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Подтверждение email</h1>
        <p class="text-rancho-ink-600 mt-2 text-sm leading-relaxed">
            Спасибо за регистрацию! Прежде чем начать, подтвердите, пожалуйста, ваш email — мы
            отправили вам письмо со ссылкой. Если письмо не пришло, мы отправим его снова.
        </p>
    </header>

    <!-- Статус -->
    <Transition name="fade-up">
        <div
            v-if="verificationLinkSent"
            v-scroll-reveal
            class="mb-5 flex items-start gap-3 rounded-xl border border-rancho-pine/30 bg-rancho-pine/10 px-4 py-3"
            role="status"
            aria-live="polite"
        >
            <CheckCircleIcon class="mt-0.5 h-4 w-4 shrink-0 text-rancho-pine" aria-hidden="true" />
            <p class="text-sm font-medium text-rancho-forest">
                Новая ссылка для подтверждения отправлена на email, указанный при регистрации.
            </p>
        </div>
    </Transition>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-4">
        <div
            class="flex flex-col-reverse items-stretch gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <BaseSubmitButton
                :processing="form.processing"
                label="Отправить письмо снова"
                :with-icon="false"
                class="sm:w-auto"
            />

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-rancho-ink-500 text-center text-sm font-medium underline-offset-2 transition-colors hover:text-rancho-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white sm:text-right"
            >
                Выйти
            </Link>
        </div>
    </form>
</template>
