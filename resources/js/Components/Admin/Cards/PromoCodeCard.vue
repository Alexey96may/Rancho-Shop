<script setup lang="ts">
    import {
        CalendarIcon,
        ExclamationTriangleIcon,
        PencilSquareIcon,
        TicketIcon,
        TrashIcon,
        UserGroupIcon,
    } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        promo: any;
    }>();

    defineEmits(['edit', 'delete', 'toggle']);

    const formatCurrency = (val: number) => (val / 100).toLocaleString('ru-RU');
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-900/40 p-6 transition-all hover:border-orange-500/50 hover:bg-slate-900/60"
        :class="[!promo.is_valid || !promo.is_active ? 'opacity-75' : '']"
    >
        <div
            v-if="promo.is_valid && promo.is_active"
            class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-orange-500/5 blur-[50px] transition-all group-hover:bg-orange-500/10"
        ></div>

        <div class="flex h-full flex-col">
            <div class="mb-6 flex items-start justify-between">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black uppercase tracking-widest text-white">{{
                            promo.code
                        }}</span>
                        <div v-if="!promo.is_valid" title="Недействителен">
                            <ExclamationTriangleIcon class="h-5 w-5 text-red-500" />
                        </div>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500">
                        {{
                            promo.type === 'percent'
                                ? `${promo.value}% скидка`
                                : `${formatCurrency(promo.value)} ₽`
                        }}
                    </span>
                </div>

                <button
                    @click="$emit('toggle', promo)"
                    :class="
                        promo.is_active
                            ? 'bg-emerald-500/10 text-emerald-500'
                            : 'bg-slate-800 text-slate-500'
                    "
                    class="rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest transition-all"
                >
                    {{ promo.is_active ? 'Active' : 'Off' }}
                </button>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <UserGroupIcon class="h-3.5 w-3.5" />
                        <span class="text-[9px] font-black uppercase tracking-tighter"
                            >Использовано</span
                        >
                    </div>
                    <p class="text-sm font-bold text-white">
                        {{ promo.used_count }}
                        <span class="text-[11px] text-slate-600"
                            >/ {{ promo.usage_limit || '∞' }}</span
                        >
                    </p>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <CalendarIcon class="h-3.5 w-3.5" />
                        <span class="text-[9px] font-black uppercase tracking-tighter"
                            >Истекает</span
                        >
                    </div>
                    <p
                        class="text-sm font-bold text-white"
                        :class="{ 'text-red-400': !promo.is_valid && promo.expires_at }"
                    >
                        {{ promo.expires_human || 'Бессрочно' }}
                    </p>
                </div>
            </div>

            <div class="mt-auto space-y-2 border-t border-slate-800/50 pt-4">
                <div class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-slate-500"
                        >Мин. заказ:</span
                    >
                    <span class="font-mono text-slate-300"
                        >{{ formatCurrency(promo.min_order_amount) }} ₽</span
                    >
                </div>
                <div v-if="promo.max_discount" class="flex justify-between text-[10px]">
                    <span class="font-bold uppercase tracking-widest text-slate-500"
                        >Макс. скидка:</span
                    >
                    <span class="font-mono text-slate-300"
                        >{{ formatCurrency(promo.max_discount) }} ₽</span
                    >
                </div>
            </div>

            <div
                class="mt-6 flex justify-end gap-2 border-t border-slate-800/50 pt-4 opacity-0 transition-all group-hover:opacity-100"
            >
                <button
                    @click="$emit('edit', promo)"
                    class="rounded-xl bg-slate-800 p-2.5 text-slate-400 transition-all hover:bg-slate-700 hover:text-orange-500"
                >
                    <PencilSquareIcon class="h-5 w-5" />
                </button>
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
