<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    // Если используешь Inertia, иначе просто <a>
    import { ArrowRight, ChevronDown, HelpCircle } from 'lucide-vue-next';

    interface FaqItem {
        id: string;
        question: string;
        answer: string;
    }

    const props = defineProps<{
        faqs: FaqItem[];
    }>();

    // Ограничиваем список до 5 элементов для главной
    const limitedFaqs = computed(() => props.faqs.slice(0, 5));
</script>

<template>
    <AppContainer tag="section" class="bg-rancho-paper/5 py-24" aria-labelledby="faq-section-title">
        <div class="mx-auto max-w-3xl">
            <header class="mb-16 text-center">
                <div
                    class="text-rancho-buttercup mb-4 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-widest"
                    aria-hidden="true"
                >
                    <HelpCircle :size="16" />
                    Полезно знать
                </div>
                <h2 id="faq-section-title" class="text-rancho-forest text-4xl font-bold">
                    Часто задаваемые вопросы
                </h2>
            </header>

            <div class="space-y-4" role="list">
                <details
                    v-for="faq in limitedFaqs"
                    :key="faq.id"
                    class="border-rancho-paper hover:border-rancho-buttercup/50 focus-within:ring-rancho-buttercup/40 group rounded-2xl border bg-white p-2 transition-all focus-within:ring-2"
                    role="listitem"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between p-4 focus:outline-none"
                        role="button"
                        :aria-controls="`faq-content-${faq.id}`"
                    >
                        <span
                            class="text-rancho-forest group-open:text-rancho-buttercup text-lg font-bold transition-colors"
                        >
                            {{ faq.question }}
                        </span>

                        <div
                            class="bg-rancho-paper flex h-8 w-8 items-center justify-center rounded-full transition-transform group-open:rotate-180"
                            aria-hidden="true"
                        >
                            <ChevronDown :size="18" class="text-rancho-olive" />
                        </div>
                    </summary>

                    <div
                        :id="`faq-content-${faq.id}`"
                        class="text-rancho-olive/80 border-rancho-paper/30 mt-2 border-t px-4 pb-4 pt-2 leading-relaxed"
                        role="region"
                    >
                        <p>{{ faq.answer }}</p>
                    </div>
                </details>
            </div>

            <div class="mt-12 text-center">
                <Link
                    href="/faq"
                    class="border-rancho-forest text-rancho-forest hover:bg-rancho-forest focus:ring-rancho-forest/20 inline-flex items-center gap-2 rounded-full border-2 px-8 py-3 text-sm font-bold transition-all hover:text-white focus:outline-none focus:ring-4"
                    aria-label="Перейти на страницу со всеми вопросами и ответами"
                >
                    <span>Читать все 12 вопросов</span>
                    <ArrowRight :size="18" aria-hidden="true" />
                </Link>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
    summary::-webkit-details-marker {
        display: none;
    }
    summary {
        list-style: none;
    }
</style>
