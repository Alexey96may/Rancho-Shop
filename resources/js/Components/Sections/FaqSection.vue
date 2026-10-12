<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { ArrowRight, ChevronDown, HelpCircle } from 'lucide-vue-next';

    import type { Faq } from '@/types';

    const props = defineProps<{
        faqs: Faq[];
        limit?: number;
        /** Разрешить несколько открытых одновременно */
        multiple?: boolean;
    }>();

    const displayFaqs = computed(() =>
        props.limit ? props.faqs.slice(0, props.limit) : props.faqs,
    );

    const openIds = ref<Set<number>>(new Set());

    function toggle(id: number) {
        const next = new Set(openIds.value);

        if (next.has(id)) {
            next.delete(id);
        } else {
            if (!props.multiple) next.clear();
            next.add(id);
        }

        openIds.value = next;
    }

    function isOpen(id: number): boolean {
        return openIds.value.has(id);
    }
</script>

<template>
    <AppContainer
        tag="section"
        class="bg-rancho-paper/5 py-12 sm:py-20"
        aria-labelledby="faq-title"
    >
        <div class="mx-auto max-w-3xl">
            <header class="mb-16 text-center">
                <div
                    class="mb-4 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-rancho-buttercup sm:text-sm"
                    aria-hidden="true"
                >
                    <HelpCircle :size="14" /> Полезно знать
                </div>
                <h2 id="faq-title" class="text-xl font-bold text-rancho-forest sm:text-4xl">
                    Вопросы и ответы
                </h2>
            </header>

            <div class="space-y-4">
                <div
                    v-for="faq in displayFaqs"
                    :key="faq.id"
                    class="group rounded-2xl border border-rancho-paper bg-white p-2 transition-colors hover:border-rancho-buttercup/50"
                    :class="{ 'border-rancho-buttercup/50': isOpen(faq.id) }"
                >
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between px-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40"
                        :aria-expanded="isOpen(faq.id)"
                        :aria-controls="`faq-panel-${faq.id}`"
                        @click="toggle(faq.id)"
                    >
                        <span
                            class="text-sm font-bold text-rancho-forest transition-colors sm:text-lg"
                            :class="{ 'text-rancho-buttercup': isOpen(faq.id) }"
                        >
                            {{ faq.question }}
                        </span>

                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rancho-paper transition-transform duration-300"
                            :class="{ 'rotate-180': isOpen(faq.id) }"
                            aria-hidden="true"
                        >
                            <ChevronDown :size="16" />
                        </div>
                    </button>

                    <div
                        :id="`faq-panel-${faq.id}`"
                        class="grid transition-[grid-template-rows] duration-300 ease-out"
                        :style="{ gridTemplateRows: isOpen(faq.id) ? '1fr' : '0fr' }"
                        role="region"
                    >
                        <div class="overflow-hidden">
                            <div
                                class="mt-2 border-t border-rancho-paper/30 px-4 pb-4 pt-2 text-xs leading-relaxed text-rancho-olive/80 sm:text-sm"
                            >
                                <p>{{ faq.answer }}</p>
                            </div>
                        </div>
                    </div>
                </div>
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
