<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link, useForm } from '@inertiajs/vue3';

    import PrimaryButton from '@/Components/PrimaryButton.vue';
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
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Подтверждение email</h1>
        <p class="mt-2 text-sm text-slate-500">
            Спасибо за регистрацию! Прежде чем начать, подтвердите, пожалуйста, ваш email — мы
            отправили вам письмо со ссылкой. Если письмо не пришло, мы отправим его снова.
        </p>
    </header>

    <div
        v-scroll-reveal
        v-if="verificationLinkSent"
        class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
    >
        Новая ссылка для подтверждения отправлена на email, указанный при регистрации.
    </div>

    <form v-scroll-reveal @submit.prevent="submit" class="space-y-4">
        <div class="flex items-center justify-between pt-2">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Отправить письмо снова
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="rounded-md text-sm text-slate-500 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Выйти
            </Link>
        </div>
    </form>
</template>
