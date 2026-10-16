<script setup lang="ts">
    import { computed, onMounted, onUnmounted, ref } from 'vue';

    import { Star, StarHalf } from 'lucide-vue-next';

    import { getStarStats } from '@/utils/math';

    interface Props {
        rating: number | string | undefined | null;
        max?: number;
        readonly?: boolean;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        rating: 0,
        max: 5,
        readonly: true,
        disabled: false,
    });

    const emit = defineEmits<{
        (e: 'update:rating', value: number): void;
        (e: 'change', value: number): void;
    }>();

    const hoverRating = ref<number | null>(null);

    const canHover = ref(true); // на SSR — true, потом уточним

    onMounted(() => {
        if (typeof window === 'undefined') return;
        canHover.value = window.matchMedia('(hover: hover)').matches;
    });

    // Флаг: находится ли пользователь прямо сейчас в состоянии ховера
    const isHovering = computed(() => hoverRating.value !== null);

    const activeRating = computed(() => {
        if (hoverRating.value !== null) {
            return hoverRating.value;
        }
        return Number(props.rating) || 0;
    });

    const stars = computed(() => getStarStats(activeRating.value, props.max));

    /**
     * Классы для активной звезды (полностью или половины):
     * - при ховере — buttercup (жёлтый, тёплый)
     * - фиксированный рейтинг — ember (тёплый кирпичный)
     */
    const activeStarColorClass = computed(() => {
        if (isHovering.value && Number(props.rating) < (hoverRating.value ?? 0)) {
            return 'fill-rancho-buttercup text-rancho-buttercup';
        }
        return 'fill-rancho-ember text-rancho-ember';
    });

    /** Классы для пустой звезды */
    const emptyStarColorClass = computed(() => 'text-rancho-ink-300');

    const setHover = (value: number) => {
        if (props.readonly || props.disabled) return;
        if (!canHover.value) return;
        hoverRating.value = value;
    };

    const clearHover = () => {
        if (props.readonly || props.disabled) return;
        hoverRating.value = null;
    };

    const selectRating = (value: number) => {
        if (props.readonly || props.disabled) return;
        emit('update:rating', value);
        emit('change', value);
    };
</script>

<template>
    <div
        class="rating-display flex items-center gap-1"
        :class="{ 'cursor-not-allowed opacity-60': disabled }"
        :role="readonly ? 'img' : 'radiogroup'"
        :aria-label="`Рейтинг: ${rating || 0} из ${max}`"
        @mouseleave="clearHover"
    >
        <!-- readonly -->
        <template v-if="readonly">
            <Star
                v-for="n in stars.full"
                :key="'full-' + n"
                :stroke-width="1"
                class="h-3 w-3 fill-rancho-ember text-rancho-ember sm:h-4 sm:w-4"
                aria-hidden="true"
            />

            <div v-if="stars.half" class="relative h-3 w-3 sm:h-4 sm:w-4">
                <Star
                    :stroke-width="1"
                    class="absolute inset-0 h-3 w-3 text-rancho-ink-300 sm:h-4 sm:w-4"
                    aria-hidden="true"
                />
                <StarHalf
                    :stroke-width="1"
                    class="h-3 w-3 fill-rancho-ember text-rancho-ember sm:h-4 sm:w-4"
                    aria-hidden="true"
                />
            </div>

            <Star
                v-for="n in stars.empty"
                :key="'empty-' + n"
                :stroke-width="1"
                class="h-3 w-3 text-rancho-ink-300 sm:h-4 sm:w-4"
                aria-hidden="true"
            />
        </template>

        <!-- ineractive -->
        <template v-else>
            <div
                v-for="index in max"
                :key="index"
                class="relative h-5 w-5 cursor-pointer transition-transform duration-150 active:scale-95"
            >
                <!-- Отображаемая иконка звезды -->
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <Star
                        v-if="activeRating >= index"
                        :stroke-width="1"
                        class="h-5 w-5 transition-colors duration-150"
                        :class="activeStarColorClass"
                    />

                    <div v-else-if="activeRating >= index - 0.5" class="relative h-5 w-5">
                        <Star
                            :stroke-width="1"
                            class="absolute inset-0 h-5 w-5 transition-colors duration-150"
                            aria-hidden="true"
                            :class="emptyStarColorClass"
                        />
                        <StarHalf
                            :stroke-width="1"
                            class="h-5 w-5 transition-colors duration-150"
                            aria-hidden="true"
                            :class="activeStarColorClass"
                        />
                    </div>

                    <Star
                        v-else
                        :stroke-width="1"
                        class="h-5 w-5 transition-colors duration-150"
                        :class="emptyStarColorClass"
                    />
                </div>

                <button
                    type="button"
                    :disabled="disabled"
                    :aria-label="`Оценить на ${index - 0.5}`"
                    class="absolute left-0 top-0 h-full w-1/2 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine"
                    @mouseenter="setHover(index - 0.5)"
                    @click="selectRating(index - 0.5)"
                />

                <!-- Правая половина (выбор n) -->
                <button
                    type="button"
                    :disabled="disabled"
                    :aria-label="`Оценить на ${index}`"
                    class="absolute right-0 top-0 h-full w-1/2 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine"
                    @mouseenter="setHover(index)"
                    @click="selectRating(index)"
                />
            </div>
        </template>
    </div>
</template>
