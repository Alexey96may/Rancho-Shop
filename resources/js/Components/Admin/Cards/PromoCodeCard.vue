<script setup lang="ts">
    import { computed } from 'vue';

    import { ExclamationTriangleIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditLink from '@/Components/Admin/UI/AdminEditLink.vue';
    import BaseDateBadge from '@/Components/UI/BaseDateBadge.vue';
    import BaseVisibilityToggle from '@/Components/UI/BaseVisibilityToggle.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminPromoCode } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{
        promo: AdminPromoCode;
        disabled: boolean;
        returnPage?: string | number;
    }>();

    defineEmits(['edit', 'delete', 'toggle']);

    const { notify } = useFlash();

    const copyToClipboard = (text: string) => {
        if (!navigator) return;

        navigator.clipboard.writeText(text);
        notify(`Промокод «${text}» скопирован!`, 'success');
    };

    const compMinOrderAmount = computed(() => formatMoney(props.promo.min_order_amount));
    const compMaxDiscount = computed(() => formatMoney(props.promo.max_discount));
    const compPromoValue = computed(() => formatMoney(props.promo.value));
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900/40 p-6 transition-all duration-300 hover:border-emerald-500/50 hover:bg-slate-900/60"
        :class="[
            !promo.is_valid || !promo.is_active ? '!border-red-800' : '',
            disabled ? 'scale-[0.97] opacity-50' : '',
        ]"
    >
        <div
            v-if="promo.is_valid && promo.is_active && !disabled"
            class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-orange-500/5 blur-[50px] transition-all group-hover:bg-orange-500/10"
        ></div>

        <div class="flex h-full flex-col">
            <div class="mb-6 flex items-start justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span
                            @click="copyToClipboard(promo.code)"
                            class="cursor-pointer text-nowrap text-xl font-black uppercase tracking-widest text-white transition-colors hover:text-orange-400"
                            title="Нажмите, чтобы скопировать"
                        >
                            {{ promo.code }}
                        </span>
                        <div v-if="!promo.is_valid" :title="promo.status.label">
                            <ExclamationTriangleIcon
                                class="h-5 w-5"
                                :class="`text-${promo.status.color}-500`"
                            />
                        </div>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500">
                        {{
                            promo.type === 'percent'
                                ? `${promo.value}% скидка`
                                : `${compPromoValue}`
                        }}
                    </span>
                    <p
                        v-if="promo.description"
                        class="mt-2 line-clamp-2 text-[11px] italic leading-relaxed text-slate-400"
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

            <BaseDateBadge :date="promo.expires_at" label="Крайний срок" eternalText="Бессрочно" />

            <div class="mb-6 grid grid-cols-1 gap-4">
                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-950/40 p-4 transition-all hover:bg-slate-900/60"
                >
                    <div
                        class="absolute -right-4 -top-4 h-16 w-16 bg-orange-600/5 blur-[40px] transition-all group-hover:bg-orange-600/10"
                    ></div>

                    <div class="relative space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-slate-500">
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-lg border border-slate-800 bg-slate-900"
                                >
                                    <UserGroupIcon class="h-3.5 w-3.5" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest"
                                    >Использование</span
                                >
                            </div>

                            <span
                                v-if="promo.usage_limit"
                                class="text-[10px] font-black tabular-nums transition-colors"
                                :class="
                                    promo.usage_percent > 90 ? 'text-red-500' : 'text-orange-500'
                                "
                            >
                                {{ promo.usage_percent }}%
                            </span>
                        </div>

                        <div v-if="promo.usage_limit" class="flex items-baseline gap-1">
                            <span class="text-2xl font-black tabular-nums leading-none text-white">
                                {{ promo.used_count }}
                            </span>
                            <span
                                class="text-xs font-bold uppercase tracking-tighter text-slate-600"
                            >
                                из {{ promo.usage_limit || '∞ лимит' }}
                            </span>
                        </div>

                        <div
                            v-if="promo.usage_limit"
                            class="relative h-2 w-full overflow-hidden rounded-full bg-slate-800/50"
                        >
                            <div
                                class="absolute inset-0 bg-[linear-gradient(90deg,transparent_95%,#000_95%)] bg-[length:10%_100%] opacity-10"
                            ></div>

                            <div
                                class="h-full rounded-full bg-gradient-to-r from-orange-600 to-orange-400 shadow-[0_0_10px_rgba(234,88,12,0.3)] transition-all duration-1000 ease-out"
                                :style="{ width: `${Math.min(promo.usage_percent, 100)}%` }"
                                :class="{
                                    '!from-red-600 !to-red-400 !shadow-red-900/30':
                                        promo.usage_percent > 90,
                                }"
                            ></div>
                        </div>

                        <div v-else class="flex items-baseline gap-1">
                            <span
                                class="text-xs font-bold uppercase tracking-tighter text-slate-600"
                            >
                                Безлимит
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto space-y-2 border-t border-slate-800/50 pt-4">
                <div class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-slate-500"
                        >Мин. заказ:</span
                    >
                    <span class="font-mono text-slate-300">{{ compMinOrderAmount }}</span>
                </div>
                <div v-if="promo.max_discount" class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-slate-500"
                        >Макс. скидка:</span
                    >
                    <span class="font-mono text-slate-300">{{ compMaxDiscount }}</span>
                </div>
            </div>

            <div
                class="mt-6 flex justify-end gap-2 border-t border-slate-800/50 pt-4 opacity-0 transition-all group-hover:opacity-100"
            >
                <AdminEditLink
                    :href="
                        route('admin.promocodes.edit', {
                            promocode: promo.id,
                            page: returnPage || '',
                        })
                    "
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

    /* Уменьшаем ячейки дней */
    :deep(.vc-day) {
        min-height: 20px !important;
        height: 20px !important;
    }

    /* Уменьшаем размер текста чисел */
    :deep(.vc-day-content) {
        font-size: 9px !important;
        width: 18px !important;
        height: 18px !important;
    }

    /* Прячем навигацию и лишние отступы */
    :deep(.vc-header) {
        display: none !important;
    }

    :deep(.vc-weeks) {
        padding: 2px !important;
    }

    /* Убираем синюю обводку фокуса */
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
</style>
