<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { LinkIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';

    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseDragHandle from '@/Components/UI/BaseDragHandle.vue';
    import { useMediaQuery } from '@/composables/features/useMediaQuery';
    import type { UnitAdmin } from '@/types';

    const props = defineProps<{
        unit: UnitAdmin;
        disabled?: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'edit', unit: UnitAdmin): void;
        (e: 'delete', unit: UnitAdmin): void;
    }>();

    /** Ширина панели действий для свайпа (px) */
    const ACTIONS_WIDTH = 120;

    /** Есть ли смысл в свайпе — только на мобилке */
    const isMobile = useMediaQuery('(max-width: 639.98px)');

    /** Текущий сдвиг контента (только на мобилке) */
    const offset = ref(0);

    const startX = ref(0);
    const startY = ref(0);
    const isDragging = ref(false);
    const isHorizontalSwipe = ref<boolean | null>(null);

    /** Открыта ли панель действий (только релевантно для мобилки) */
    const isOpen = computed(() => isMobile.value && offset.value < 0);

    /** Начало жеста */
    function onStart(e: TouchEvent | MouseEvent) {
        if (props.disabled) return;
        if (!isMobile.value) return; // ← на десктопе игнорируем

        const x = 'touches' in e ? e.touches[0].clientX : e.clientX;
        const y = 'touches' in e ? e.touches[0].clientY : e.clientY;

        startX.value = x;
        startY.value = y;
        isDragging.value = true;
        isHorizontalSwipe.value = null;
    }

    /** Движение */
    function onMove(e: TouchEvent | MouseEvent) {
        if (!isDragging.value) return;
        if (!isMobile.value) return;

        const x = 'touches' in e ? e.touches[0].clientX : e.clientX;
        const y = 'touches' in e ? e.touches[0].clientY : e.clientY;

        const dx = x - startX.value;
        const dy = y - startY.value;

        if (isHorizontalSwipe.value === null) {
            if (Math.abs(dx) > 5 || Math.abs(dy) > 5) {
                isHorizontalSwipe.value = Math.abs(dx) > Math.abs(dy);
            } else {
                return;
            }
        }

        if (!isHorizontalSwipe.value) return;

        const base = offset.value < 0 ? -ACTIONS_WIDTH : 0;
        let next = base + dx;
        next = Math.max(-ACTIONS_WIDTH, Math.min(0, next));

        offset.value = next;

        if (e.cancelable) e.preventDefault();
    }

    /** Конец жеста */
    function onEnd() {
        if (!isDragging.value) return;
        isDragging.value = false;

        if (!isHorizontalSwipe.value) return;

        offset.value = offset.value < -ACTIONS_WIDTH / 2 ? -ACTIONS_WIDTH : 0;
        isHorizontalSwipe.value = null;
    }

    function close() {
        offset.value = 0;
    }

    /** Стили контента: сдвиг только на мобилке */
    const contentStyle = computed(() => {
        if (!isMobile.value) return {};
        return {
            transform: `translateX(${offset.value}px)`,
            transition: isDragging.value ? 'none' : 'transform 0.25s ease-out',
        };
    });

    /** Ширина панели действий */
    const actionsStyle = computed(() => {
        if (!isMobile.value) return {};
        return { width: `${ACTIONS_WIDTH}px` };
    });
</script>

<template>
    <div
        role="listitem"
        class="group relative flex flex-col gap-4 overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/40 transition-all hover:border-rancho-ink-600 sm:flex-row sm:items-center sm:rounded-3xl"
        :class="[disabled ? 'opacity-50' : '']"
    >
        <!-- КОНТЕНТ -->
        <div
            class="relative z-10 flex flex-1 flex-col gap-4 bg-rancho-ink-900/90 p-4 sm:flex-row sm:items-center"
            :class="[isMobile ? 'cursor-grab active:cursor-grabbing' : '']"
            :style="contentStyle"
            @touchstart.passive="onStart"
            @touchmove="onMove"
            @touchend="onEnd"
            @touchcancel="onEnd"
            @mousedown="onStart"
            @mousemove="onMove"
            @mouseup="onEnd"
            @mouseleave="onEnd"
            @click="isOpen && close()"
        >
            <div class="flex flex-1 items-center gap-4">
                <BaseDragHandle />

                <div class="relative flex-1 flex-col gap-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <div class="flex flex-wrap items-center gap-2 sm:flex-col sm:gap-1">
                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Название
                        </span>
                        <span class="line-clamp-1 text-sm font-bold text-white">
                            {{ unit.name }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:flex-col sm:gap-1">
                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Сокращение
                        </span>
                        <span class="line-clamp-1 text-sm font-bold text-rancho-mint">
                            {{ unit.short }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:flex-col sm:gap-1">
                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Slug
                        </span>
                        <div class="flex items-center gap-1 text-rancho-ink-400">
                            <LinkIcon class="h-3 w-3" aria-hidden="true" />
                            <span class="line-clamp-1 font-mono text-[11px]">
                                {{ unit.slug }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ПАНЕЛЬ ДЕЙСТВИЙ -->
        <!-- Мобилка: absolute справа, z-0, выезжает по свайпу -->
        <!-- sm+: static, в конце flex, всегда видна -->
        <div
            class="absolute inset-y-0 right-0 z-0 flex items-center gap-2 bg-rancho-ink-950 px-4 sm:static sm:inset-auto sm:z-10 sm:bg-transparent sm:px-4 sm:py-4"
            :style="actionsStyle"
            :aria-hidden="isMobile && !isOpen"
        >
            <AdminEditButton
                @click="emit('edit', unit)"
                :title="`Редактировать ${unit.name}`"
                :disabled="disabled"
                :aria-label="`Редактировать ${unit.name}`"
                :icon="PencilIcon"
            />

            <BaseDeleteButton
                :disabled="!unit.can_delete || disabled"
                :title="!unit.can_delete ? 'Нельзя удалить: используется в товарах' : ''"
                @confirm="emit('delete', unit)"
                :aria-label="`Удалить ${unit.name}`"
            >
                <Trash2Icon class="h-4 w-4" aria-hidden="true" />
            </BaseDeleteButton>
        </div>
    </div>
</template>
