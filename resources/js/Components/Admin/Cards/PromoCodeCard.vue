<script setup lang="ts">
    import { computed } from 'vue';

    import { ExclamationTriangleIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
    import { Infinity as InfinityIcon } from 'lucide-vue-next';

    import WaterWave from '@/Components/Admin/Shared/WaterWave.vue';
    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import BaseDateBadge from '@/Components/UI/BaseDateBadge.vue';
    import BaseVisibilityToggle from '@/Components/UI/BaseVisibilityToggle.vue';
    import { useNotificationsStore } from '@/stores/notifications';
    import type { AdminPromoCode } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{
        promo: AdminPromoCode;
        disabled: boolean;
    }>();

    defineEmits(['edit', 'delete', 'toggle']);

    const notify = useNotificationsStore();

    const copyToClipboard = (text: string) => {
        if (!navigator) return;

        navigator.clipboard.writeText(text);
        notify.success(`Промокод «${text}» скопирован!`);
    };

    const compMinOrderAmount = computed(() => formatMoney(props.promo.min_order_amount));
    const compMaxDiscount = computed(() => formatMoney(props.promo.max_discount));
    const compPromoValue = computed(() => formatMoney(props.promo.value));

    const isUnlimited = computed(() => !props.promo.usage_limit);

    const fillHeight = computed(() => {
        if (isUnlimited.value) return 100; // безлимит = полная заливка
        return Math.min(props.promo.usage_percent ?? 0, 100);
    });

    const hasWater = computed(() => !isUnlimited.value && (props.promo.usage_percent ?? 0) > 0);

    const wavePalette = computed(() => {
        // Безлимит — спокойный синий
        if (isUnlimited.value) {
            return { color: '#60A5FA', deepColor: '#1E40AF' };
        }

        const p = props.promo.usage_percent ?? 0;

        if (p >= 90) return { color: '#F43F5E', deepColor: '#BE123C' }; // красный
        if (p >= 65) return { color: '#FB923C', deepColor: '#C2410C' }; // оранжевый
        if (p >= 35) return { color: '#E3B44B', deepColor: '#B45309' }; // жёлтый
        return { color: '#7AB89A', deepColor: '#3B7558' }; // зелёный
    });
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/40 p-5 transition-all duration-300 hover:border-rancho-mint/50 hover:bg-rancho-ink-900/60 sm:rounded-3xl sm:p-6"
        :class="[
            !promo.is_valid || !promo.is_active ? '!border-rancho-ember' : '',
            disabled ? 'scale-[0.97] opacity-50' : '',
        ]"
    >
        <!-- Декоративное свечение -->
        <div
            v-if="promo.is_valid && promo.is_active && !disabled"
            class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-rancho-mint/5 blur-[50px] transition-all group-hover:bg-rancho-mint/10"
            aria-hidden="true"
        ></div>

        <div class="flex h-full flex-col gap-4">
            <!-- HEADER -->
            <div class="mb-6 flex items-start justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span
                            @click="copyToClipboard(promo.code)"
                            class="text-md cursor-pointer truncate font-black uppercase tracking-widest text-white transition-colors hover:text-rancho-mint sm:text-xl"
                            title="Нажмите, чтобы скопировать"
                        >
                            {{ promo.code }}
                        </span>

                        <div v-if="!promo.is_valid" :title="promo.status.label">
                            <ExclamationTriangleIcon
                                class="h-5 w-5"
                                :class="`text-${promo.status.color}-500`"
                                aria-hidden="true"
                            />
                        </div>
                    </div>

                    <span class="text-xs font-black uppercase tracking-[0.2em] text-rancho-mint">
                        {{
                            promo.type === 'percent'
                                ? `${promo.value}% скидка`
                                : `${compPromoValue}`
                        }}
                    </span>

                    <p
                        v-if="promo.description"
                        class="mt-2 line-clamp-2 text-[11px] italic leading-relaxed text-rancho-ink-400"
                        :title="promo.description"
                    >
                        {{ promo.description }}
                    </p>
                </div>

                <BaseVisibilityToggle
                    :active="promo.is_active"
                    active-title="Виден на сайте"
                    inactive-title="Скрыт"
                    @toggle="$emit('toggle', promo)"
                />
            </div>

            <!-- DATE BADGE -->
            <BaseDateBadge :date="promo.expires_at" label="Крайний срок" eternal-text="Бессрочно" />

            <!-- USAGE -->
            <div class="mb-6 grid grow grid-cols-1 gap-4">
                <div
                    class="group relative overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950/40 p-4 transition-all hover:bg-rancho-ink-900/60 sm:rounded-3xl"
                >
                    <WaterWave
                        v-if="hasWater || isUnlimited"
                        :percent="fillHeight"
                        :color="wavePalette.color"
                        :deep-color="wavePalette.deepColor"
                        :static="isUnlimited"
                        :speed="1"
                        :amplitude="isUnlimited ? 2 : 5"
                    />

                    <!-- Полоска безлимита сверху -->
                    <div
                        v-if="isUnlimited"
                        class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rancho-sky/0 via-rancho-sky/80 to-rancho-sky/0"
                        aria-hidden="true"
                    ></div>

                    <!-- КОНТЕНТ -->
                    <div class="relative z-10 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-rancho-ink-300">
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-lg border border-rancho-ink-800 bg-rancho-ink-900"
                                    aria-hidden="true"
                                >
                                    <UserGroupIcon class="h-3.5 w-3.5" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest">
                                    Использование
                                </span>
                            </div>

                            <!-- % (только для лимитированных) -->
                            <span
                                v-if="!isUnlimited"
                                class="text-[10px] font-black tabular-nums transition-colors"
                                :class="
                                    promo.usage_percent > 90
                                        ? 'text-rose-950'
                                        : promo.usage_percent > 65
                                          ? 'text-orange-200'
                                          : promo.usage_percent > 35
                                            ? 'text-rancho-buttercup'
                                            : 'text-rancho-mint'
                                "
                            >
                                {{ promo.usage_percent }}%
                            </span>

                            <!-- Иконка «бесконечность» для безлимита -->
                            <InfinityIcon
                                v-else
                                class="h-4 w-4 text-rancho-sky"
                                aria-label="Безлимитное использование"
                            />
                        </div>

                        <!-- Значения -->
                        <div v-if="!isUnlimited" class="flex items-baseline gap-1">
                            <span class="text-2xl font-black tabular-nums leading-none text-white">
                                {{ promo.used_count }}
                            </span>
                            <span
                                class="text-xs font-bold uppercase tracking-tighter text-rancho-ink-200"
                            >
                                / {{ promo.usage_limit }}
                            </span>
                        </div>

                        <!-- Безлимит -->
                        <div v-else class="flex items-baseline gap-1">
                            <span
                                class="text-xs font-bold uppercase tracking-tighter text-rancho-sky"
                            >
                                Безлимит
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- META -->
            <div class="mt-auto space-y-2 border-t border-rancho-ink-800/60 pt-4">
                <div class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-rancho-ink-500">
                        Мин. заказ:
                    </span>
                    <span class="font-mono text-rancho-ink-300">{{ compMinOrderAmount }}</span>
                </div>

                <div v-if="promo.max_discount" class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-rancho-ink-500">
                        Макс. скидка:
                    </span>
                    <span class="font-mono text-rancho-ink-300">{{ compMaxDiscount }}</span>
                </div>
            </div>

            <!-- ACTIONS -->
            <div
                class="mt-6 flex justify-end gap-2 border-t border-rancho-ink-800/60 pt-4 transition-all focus-within:opacity-100 group-hover:opacity-100 lg:opacity-0"
            >
                <AdminEditButton
                    @click="$emit('edit', promo)"
                    :title="`Редактировать ${promo.code}`"
                    :disabled="disabled"
                />

                <AdminDeleteButton
                    @click="$emit('delete', promo)"
                    title="Удалить промокод"
                    :disabled="disabled"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
    /* Сжимаем календарь, чтобы он влез в карточку */
    :deep(.mini-preview-calendar) {
        border: none !important;
        background: transparent !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    :deep(.vc-day) {
        min-height: 20px !important;
        height: 20px !important;
    }

    :deep(.vc-day-content) {
        font-size: 9px !important;
        width: 18px !important;
        height: 18px !important;
    }

    :deep(.vc-header) {
        display: none !important;
    }

    :deep(.vc-weeks) {
        padding: 2px !important;
    }

    :deep(.vc-focus) {
        box-shadow: none !important;
    }

    .date-fade-enter-active,
    .date-fade-leave-active {
        transition: all 0.2s ease;
    }

    .date-fade-enter-from {
        opacity: 0;
        transform: translateY(4px);
    }

    .date-fade-leave-to {
        opacity: 0;
        transform: translateY(-4px);
    }

    @media (prefers-reduced-motion: reduce) {
        .date-fade-enter-active,
        .date-fade-leave-active {
            transition: none;
        }
    }
</style>
