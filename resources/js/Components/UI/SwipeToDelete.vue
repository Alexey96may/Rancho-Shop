<script setup lang="ts">
    import { computed, ref } from 'vue';

    interface Props {
        threshold?: number;
        maxOffset?: number;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        threshold: 120,
        maxOffset: 200,
        disabled: false,
    });

    const emit = defineEmits<{
        (e: 'delete'): void;
    }>();

    const offset = ref(0);
    const startX = ref(0);
    const startY = ref(0);
    const isDragging = ref(false);
    const isHorizontal = ref<boolean | null>(null);

    const progress = computed(() => Math.min(Math.abs(offset.value) / props.threshold, 1));
    const willDelete = computed(() => Math.abs(offset.value) >= props.threshold);

    const wrapperStyle = computed(() => ({
        transform: `translateX(${offset.value}px)`,
        transition: isDragging.value ? 'none' : 'transform 0.3s ease',
    }));

    function onTouchStart(e: TouchEvent) {
        if (props.disabled) return;
        startX.value = e.touches[0].clientX;
        startY.value = e.touches[0].clientY;
        isDragging.value = true;
        isHorizontal.value = null;
    }

    function onTouchMove(e: TouchEvent) {
        if (!isDragging.value || props.disabled) return;

        const dx = e.touches[0].clientX - startX.value;
        const dy = e.touches[0].clientY - startY.value;

        if (isHorizontal.value === null && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) {
            isHorizontal.value = Math.abs(dx) > Math.abs(dy);
        }

        if (isHorizontal.value === false) return;

        offset.value = Math.min(0, Math.max(-props.maxOffset, dx));
    }

    function onTouchEnd() {
        if (!isDragging.value) return;

        isDragging.value = false;
        isHorizontal.value = null;

        if (Math.abs(offset.value) >= props.threshold) {
            offset.value = -400;
            emit('delete');
            return;
        }

        offset.value = 0;
    }

    function close() {
        offset.value = 0;
    }

    defineExpose({ close });

    const backgroundStyle = computed(() => ({
        opacity: progress.value,
    }));

    const iconStyle = computed(() => {
        // Сдвиг иконки: 0% → -100% по мере прогресса
        const shift = -progress.value * 100;
        // Скейл: 1 → 1.5
        const scale = 1 + progress.value * 0.5;

        return {
            transform: `translateX(${shift}%) scale(${scale})`,
        };
    });
</script>

<template>
    <div class="relative">
        <!-- Красная зона -->
        <div
            class="pointer-events-none absolute inset-0 flex items-center justify-end overflow-hidden rounded-2xl bg-rancho-ember pr-[7%] text-white"
            :style="backgroundStyle"
            aria-hidden="true"
        >
            <svg
                class="h-4 w-4 shrink-0 transition-transform duration-75"
                :style="iconStyle"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"
                />
            </svg>
        </div>

        <!-- Карточка -->
        <div
            class="relative"
            :style="wrapperStyle"
            @touchstart.passive="onTouchStart"
            @touchmove.passive="onTouchMove"
            @touchend="onTouchEnd"
            @touchcancel="onTouchEnd"
        >
            <slot />
        </div>
    </div>
</template>
