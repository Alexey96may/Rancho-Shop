<script setup lang="ts">
    import { ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import {
        CalendarIcon,
        ExclamationTriangleIcon,
        EyeIcon,
        EyeSlashIcon,
        PencilSquareIcon,
        TrashIcon,
        UserGroupIcon,
    } from '@heroicons/vue/24/outline';

    import { useFlash } from '@/composables/useFlash';
    import { AdminPromoCode } from '@/types';
    import { formatDateTime, formatMoney, formatRelativeTime } from '@/utils/format';

    const props = defineProps<{
        promo: AdminPromoCode;
        disabled: boolean;
        returnPage?: string | number;
    }>();

    defineEmits(['edit', 'delete', 'toggle']);

    const { notify } = useFlash();

    const copyToClipboard = (text: string) => {
        if (!window) return;

        navigator.clipboard.writeText(text);

        notify(`Промокод «${text}» скопирован!`, 'success');
    };

    const isShortData = ref(false);

    function toggleDataFormat() {
        isShortData.value = !isShortData.value;
    }
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900/40 p-6 transition-all hover:border-orange-500/50 hover:bg-slate-900/60"
        :class="[!promo.is_valid || !promo.is_active || disabled ? 'opacity-50' : '']"
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
                                : `${formatMoney(promo.value)}`
                        }}
                    </span>
                </div>

                <button
                    @click="$emit('toggle', promo)"
                    :class="[
                        promo.is_active
                            ? 'bg-emerald-500/10 text-emerald-500'
                            : 'bg-slate-800 text-slate-500',
                    ]"
                    class="z-10 rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest transition-all hover:scale-105"
                >
                    <EyeIcon v-if="promo.is_active" class="h-5 w-5" aria-hidden="true" />
                    <EyeSlashIcon v-else class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <div
                class="mb-4 flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-3"
            >
                <template v-if="promo.expires_at">
                    <div
                        class="flex w-10 shrink-0 flex-col items-center overflow-hidden rounded-lg border border-orange-500/30 bg-slate-900"
                    >
                        <div
                            class="w-full bg-orange-600 py-0.5 text-center text-[7px] font-black uppercase text-white"
                        >
                            {{
                                new Date(promo.expires_at).toLocaleString('ru', {
                                    month: 'short',
                                })
                            }}
                        </div>
                        <div class="text-lg font-black leading-tight text-white">
                            {{ new Date(promo.expires_at).getDate() }}
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col">
                        <span
                            class="text-[9px] font-black uppercase tracking-widest transition-colors"
                            :class="
                                new Date(promo.expires_at) < new Date()
                                    ? 'text-red-500'
                                    : 'text-slate-500'
                            "
                        >
                            {{ new Date(promo.expires_at) < new Date() ? 'Истёк' : 'Истекает' }}
                        </span>

                        <span
                            @click="toggleDataFormat"
                            class="cursor-pointer text-xs font-bold transition-colors hover:text-orange-400"
                            :class="
                                new Date(promo.expires_at) < new Date()
                                    ? 'text-red-400/80'
                                    : 'text-white'
                            "
                            :title="
                                isShortData ? 'Показать сколько осталось' : 'Показать полную дату'
                            "
                        >
                            <Transition name="date-fade" mode="out-in">
                                <span :key="isShortData ? 'short' : 'full'">
                                    {{
                                        isShortData
                                            ? formatDateTime(promo.expires_at)
                                            : formatRelativeTime(promo.expires_at)
                                    }}
                                </span>
                            </Transition>
                        </span>
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center gap-3 text-slate-500">
                        <CalendarIcon class="h-5 w-5" />
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase">Срок действия</span>
                            <span class="text-xs font-bold uppercase italic text-white"
                                >Бессрочно</span
                            >
                        </div>
                    </div>
                </template>
            </div>

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
                    <span class="font-mono text-slate-300">{{
                        formatMoney(promo.min_order_amount)
                    }}</span>
                </div>
                <div v-if="promo.max_discount" class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-slate-500"
                        >Макс. скидка:</span
                    >
                    <span class="font-mono text-slate-300">{{
                        formatMoney(promo.max_discount)
                    }}</span>
                </div>
            </div>

            <div
                class="mt-6 flex justify-end gap-2 border-t border-slate-800/50 pt-4 opacity-0 transition-all group-hover:opacity-100"
            >
                <Link
                    :href="
                        route('admin.promocodes.edit', {
                            promocode: promo.id,
                            page: returnPage || '',
                        })
                    "
                    class="rounded-xl bg-slate-800 p-2.5 text-slate-400 transition-all hover:bg-slate-700 hover:text-orange-500"
                >
                    <PencilSquareIcon class="h-5 w-5" />
                </Link>
                <button
                    @click="$emit('delete', promo)"
                    class="rounded-xl bg-red-500/10 p-2.5 text-red-500/60 transition-all hover:bg-red-500/20 hover:text-red-500"
                >
                    <TrashIcon class="h-5 w-5" />
                </button>
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
        transform: translateY(4px); /* Появляется чуть снизу */
    }

    .date-fade-leave-to {
        opacity: 0;
        transform: translateY(-4px); /* Уходит чуть вверх */
    }
</style>
