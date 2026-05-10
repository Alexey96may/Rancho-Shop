<script setup lang="ts">
    import { computed } from 'vue';

    import {
        ChevronRightIcon,
        PhoneIcon,
        TicketIcon,
        TruckIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import { ORDER_STATUSES } from '@/Constants/statusConfig';
    import type { AdminOrder } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{
        order: AdminOrder;
    }>();

    defineEmits(['open']);

    const computedFormatMoney = computed(() => formatMoney(props.order.total_price));

    const statusInfo = computed(() => {
        if (!props.order) return ORDER_STATUSES.new;
        return ORDER_STATUSES[props.order.status] || ORDER_STATUSES.new;
    });
</script>

<template>
    <div
        class="hover:shadow-2xl group relative flex flex-col gap-4 rounded-3xl border border-slate-800 bg-slate-900 p-5 transition-all hover:border-orange-500/40 hover:shadow-orange-950/20"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-widest text-slate-500">
                #{{ order.id }}
            </span>
            <div
                class="rounded-lg border px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                :class="statusInfo.class"
            >
                {{ statusInfo.label }}
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-slate-400"
                >
                    <UserIcon class="h-4 w-4" />
                </div>
                <div class="overflow-hidden">
                    <p class="truncate text-sm font-bold text-white">{{ order.customer_name }}</p>
                    <p class="text-[9px] text-slate-500">{{ order.created_at }}</p>
                </div>
            </div>

            <a
                :href="`tel:${order.customer_phone}`"
                @click.stop
                class="flex items-center gap-2 rounded-xl bg-slate-800/40 px-3 py-1.5 text-[11px] text-slate-300 hover:text-orange-400"
            >
                <PhoneIcon class="h-3.5 w-3.5" />
                {{ order.customer_phone }}
            </a>
        </div>

        <div class="flex flex-col gap-2">
            <div
                v-if="order.promo_code"
                class="flex items-center gap-2 text-[10px] text-pink-500/80"
            >
                <TicketIcon class="h-3.5 w-3.5" />
                <span>Промо: {{ order.promo_code.code }}</span>
            </div>

            <div class="flex items-start gap-3 rounded-2xl bg-slate-950/50 p-3">
                <TruckIcon class="mt-0.5 h-4 w-4 shrink-0 text-orange-500" />
                <div class="text-[10px] leading-tight text-slate-400">
                    <span v-if="order.is_pickup" class="font-bold text-orange-500">Самовывоз</span>
                    <span v-else class="line-clamp-2">{{
                        order.delivery_address || 'Без адреса'
                    }}</span>
                </div>
            </div>
        </div>

        <div class="mt-auto flex items-center justify-between border-t border-slate-800 pt-4">
            <div class="flex flex-col">
                <span class="text-[9px] font-black uppercase text-slate-600">Итого</span>
                <span class="text-lg font-black text-white">{{ computedFormatMoney }}</span>
            </div>

            <button
                @click="$emit('open', order)"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-600 text-white transition-transform hover:scale-105"
            >
                <ChevronRightIcon class="h-5 w-5" />
            </button>
        </div>
    </div>
</template>
