<script setup lang="ts">
    import { computed, onMounted, onUnmounted, ref } from 'vue';

    import { Head, Link, useForm } from '@inertiajs/vue3';

    import { CheckCircleIcon } from '@heroicons/vue/24/outline';

    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const props = defineProps<{
        status?: string;
    }>();

    const form = useForm({});

    const justSent = ref(false);
    const cooldown = ref(0);
    let timer: ReturnType<typeof setInterval> | null = null;

    const COOLDOWN_SECONDS = 60;
    const STORAGE_KEY = 'verification-cooldown-until';

    const startCooldown = (seconds = COOLDOWN_SECONDS) => {
        cooldown.value = seconds;
        justSent.value = true;

        sessionStorage.setItem(STORAGE_KEY, String(Date.now() + seconds * 1000));

        if (timer) clearInterval(timer);
        timer = setInterval(() => {
            cooldown.value--;
            if (cooldown.value <= 0 && timer) {
                clearInterval(timer);
                timer = null;
                sessionStorage.removeItem(STORAGE_KEY);
            }
        }, 1000);
    };

    onMounted(() => {
        const until = sessionStorage.getItem(STORAGE_KEY);
        if (!until) return;

        const seconds = Math.max(0, Math.floor((Number(until) - Date.now()) / 1000));
        if (seconds > 0) {
            startCooldown(seconds);
        } else {
            sessionStorage.removeItem(STORAGE_KEY);
        }
    });

    onUnmounted(() => {
        if (timer) clearInterval(timer);
    });

    const submit = () => {
        if (cooldown.value > 0 || form.processing) return;

        form.post(route('verification.send'), {
            onSuccess: () => startCooldown(COOLDOWN_SECONDS),
        });
    };

    const verificationLinkSent = computed(
        () => props.status === 'verification-link-sent' || justSent.value,
    );
</script>

<template>
    <Head title="Подтверждение email" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Подтверждение email</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
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

    <form v-scroll-reveal class="space-y-4" @submit.prevent="submit">
        <div
            class="flex flex-col-reverse items-stretch gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <BaseSubmitButton
                :processing="form.processing"
                :disabled="cooldown > 0"
                :label="cooldown > 0 ? `Повторить через ${cooldown} с` : 'Отправить письмо снова'"
                class="sm:w-auto"
            />

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-center text-sm font-medium text-rancho-ink-500 underline-offset-2 transition-colors hover:text-rancho-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white sm:text-right"
            >
                Выйти
            </Link>
        </div>
    </form>
</template>
