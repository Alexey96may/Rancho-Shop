<script setup lang="ts">
    import { useCookieConsent } from '@/composables/useCookieConsent';

    const { consent, accept, reject } = useCookieConsent();
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="consent === null"
            class="fixed inset-x-0 bottom-0 z-[9999] p-3 sm:p-4"
            role="dialog"
            aria-live="polite"
            aria-label="Согласие на использование cookie"
        >
            <div
                class="shadow-xl mx-auto flex max-w-4xl flex-col gap-3 rounded-2xl border border-rancho-ink-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5"
            >
                <p class="text-xs leading-relaxed text-rancho-ink-600 sm:text-sm">
                    Мы используем cookie для работы корзины и аналитики. Подробнее — в
                    <a href="/privacy" class="font-semibold text-rancho-pine underline">
                        Политике конфиденциальности</a
                    >.
                </p>

                <div class="flex shrink-0 gap-2">
                    <button
                        type="button"
                        @click="reject"
                        class="rounded-xl border border-rancho-ink-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-rancho-ink-500 transition-colors hover:bg-rancho-ink-50"
                    >
                        Отклонить
                    </button>
                    <button
                        type="button"
                        @click="accept"
                        class="rounded-xl bg-rancho-pine px-4 py-2 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-rancho-forest"
                    >
                        Принять
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
