<script setup lang="ts">
    import { computed } from 'vue';

    import { ArrowRight, ChevronDown, HelpCircle } from 'lucide-vue-next';

    import type { Faq } from '@/types';

    const props = defineProps<{
        faqs: Faq[];
        limit?: number;
    }>();

    // Берем только нужные для главной страницы
    const displayFaqs = computed(() =>
        props.limit ? props.faqs.slice(0, props.limit) : props.faqs,
    );
</script>

<template>
    <AppContainer tag="section" class="bg-rancho-paper/5 py-24" aria-labelledby="faq-title">
        <div class="mx-auto max-w-3xl">
            <header class="mb-16 text-center">
                <div
                    class="mb-4 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-rancho-buttercup"
                    aria-hidden="true"
                >
                    <HelpCircle :size="16" /> Полезно знать
                </div>
                <h2 id="faq-title" class="text-4xl font-bold text-rancho-forest">
                    Вопросы и ответы
                </h2>
            </header>

            <div class="space-y-4">
                <details
                    v-for="faq in displayFaqs"
                    :key="faq.id"
                    class="bg-white group rounded-2xl border border-rancho-paper p-2 transition-all focus-within:ring-2 focus-within:ring-rancho-buttercup/40 hover:border-rancho-buttercup/50"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between p-4 focus:outline-none"
                        role="button"
                        aria-expanded="false"
                    >
                        <span
                            class="text-lg font-bold text-rancho-forest transition-colors group-open:text-rancho-buttercup"
                        >
                            {{ faq.question }}
                        </span>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-rancho-paper transition-transform group-open:rotate-180"
                            aria-hidden="true"
                        >
                            <ChevronDown :size="18" />
                        </div>
                    </summary>
                    <div
                        class="mt-2 border-t border-rancho-paper/30 px-4 pb-4 pt-2 leading-relaxed text-rancho-olive/80"
                        role="region"
                    >
                        <p>{{ faq.answer }}</p>
                    </div>
                </details>
            </div>

            <div v-if="limit && faqs.length > limit" class="mt-12 text-center">
                <a
                    href="/faq"
                    class="inline-flex items-center gap-2 font-bold text-rancho-forest underline decoration-rancho-buttercup decoration-2 underline-offset-8 transition-colors hover:text-rancho-buttercup"
                >
                    Смотреть все вопросы <ArrowRight :size="20" />
                </a>
            </div>
        </div>
    </AppContainer>
</template>
