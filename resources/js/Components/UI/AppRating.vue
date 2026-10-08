<script setup lang="ts">
    import { computed, ref } from 'vue';

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

    // Флаг: находится ли пользователь прямо сейчас в состоянии ховера
    const isHovering = computed(() => hoverRating.value !== null);

    const activeRating = computed(() => {
        if (hoverRating.value !== null) {
            return hoverRating.value;
        }
        return Number(props.rating) || 0;
    });

    const stars = computed(() => getStarStats(activeRating.value, props.max));

    // Динамические классы цвета для закрашенной звезды/половинки
    const activeStarColorClass = computed(() => {
        if (isHovering.value) {
            // Цвет при наведении (Янтарный/Желтый)
            return 'fill-amber-400 text-amber-400';
        }
        // Цвет зафиксированного рейтинга (Оранжевый)
        return 'fill-orange-500 text-orange-500';
    });

    const setHover = (value: number) => {
        if (props.readonly || props.disabled) return;
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
        :aria-label="`Rating: ${rating || 0} out of ${max}`"
        @mouseleave="clearHover"
    >
        <!-- РЕЖИМ ТОЛЬКО ДЛЯ ЧТЕНИЯ -->
        <template v-if="readonly">
            <Star
                v-for="n in stars.full"
                :key="'full-' + n"
                :stroke-width="1"
                class="h-4 w-4 fill-orange-500/60 text-orange-500"
                aria-hidden="true"
            />

            <StarHalf
                v-if="stars.half"
                :stroke-width="1"
                class="h-4 w-4 fill-orange-500/60 text-orange-500"
                aria-hidden="true"
            />

            <Star
                v-for="n in stars.empty"
                :key="'empty-' + n"
                :stroke-width="1"
                class="h-4 w-4 text-orange-500/30"
                aria-hidden="true"
            />
        </template>

        <!-- ИНТЕРАКТИВНЫЙ РЕЖИМ -->
        <template v-else>
            <div
                v-for="index in max"
                :key="index"
                class="relative h-6 w-6 cursor-pointer transition-transform duration-150 active:scale-95"
            >
                <!-- Отображаемая иконка звезды -->
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <Star
                        v-if="activeRating >= index"
                        :stroke-width="1"
                        class="h-5 w-5 transition-colors duration-150"
                        :class="activeStarColorClass"
                    />
                    <StarHalf
                        v-else-if="activeRating >= index - 0.5"
                        :stroke-width="1"
                        class="h-5 w-5 transition-colors duration-150"
                        :class="activeStarColorClass"
                    />
                    <Star
                        v-else
                        :stroke-width="1"
                        class="h-5 w-5 text-orange-500/30 transition-colors duration-150"
                    />
                </div>

                <!-- Левая половина (выбор n - 0.5) -->
                <button
                    type="button"
                    :disabled="disabled"
                    :aria-label="`Оценить на ${index - 0.5}`"
                    class="absolute left-0 top-0 h-full w-1/2 focus:outline-none"
                    @mouseenter="setHover(index - 0.5)"
                    @click="selectRating(index - 0.5)"
                />

                <!-- Правая половина (выбор n) -->
                <button
                    type="button"
                    :disabled="disabled"
                    :aria-label="`Оценить на ${index}`"
                    class="absolute right-0 top-0 h-full w-1/2 focus:outline-none"
                    @mouseenter="setHover(index)"
                    @click="selectRating(index)"
                />
            </div>
        </template>
    </div>
</template>
