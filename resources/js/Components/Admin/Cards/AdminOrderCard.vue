<script setup lang="ts">
    import { computed } from 'vue';

    import {
        ChevronRightIcon,
        PhoneIcon, // Добавили иконку телефона
        ShoppingBagIcon,
        TruckIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        order: any;
    }>();

    defineEmits(['open']);

    const statusMap = {
        new: { label: 'Новый', class: 'bg-blue-500/10 text-blue-500 border-blue-500/20' },
        processing: {
            label: 'В работе',
            class: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
        },
        completed: {
            label: 'Завершен',
            class: 'bg-green-500/10 text-green-500 border-green-500/20',
        },
        cancelled: {
            label: 'Отменен',
            class: 'bg-slate-700/10 text-slate-500 border-slate-700/20',
        },
    };

    const currentStatus = computed(() => statusMap[props.order.status] || statusMap['new']);

    const formattedPrice = (price: number) => {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            maximumFractionDigits: 0,
        }).format(price / 100);
    };
</script>

<template>
    <div
        class="hover:shadow-2xl group relative flex flex-col gap-4 rounded-3xl border border-slate-800 bg-slate-900 p-5 transition-all hover:border-orange-500/40 hover:shadow-orange-950/20"
        role="article"
        :aria-labelledby="`order-title-${order.id}`"
    >
        <div class="flex items-center justify-between">
            <span
                :id="`order-title-${order.id}`"
                class="text-xs font-black uppercase tracking-widest text-slate-500"
            >
                Заказ #{{ order.id }}
            </span>
            <div
                class="rounded-lg border px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                :class="currentStatus.class"
            >
                {{ currentStatus.label }}
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-400"
                >
                    <UserIcon class="h-5 w-5" />
                </div>
                <div class="overflow-hidden">
                    <p class="truncate text-sm font-bold text-white">{{ order.customer_name }}</p>
                    <p class="text-[10px] text-slate-500">{{ order.created_at }}</p>
                </div>
            </div>

            <a
                :href="`tel:${order.customer_phone}`"
                class="flex items-center gap-2 rounded-xl bg-slate-800/40 px-3 py-2 text-xs font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-orange-400"
            >
                <PhoneIcon class="h-4 w-4 text-orange-500/70" />
                {{ order.customer_phone }}
            </a>
        </div>

        <div class="flex items-start gap-3 rounded-2xl bg-slate-950/50 p-3">
            <TruckIcon class="mt-0.5 h-4 w-4 shrink-0 text-orange-500" />
            <div class="text-[11px] leading-relaxed text-slate-400">
                <span v-if="order.is_pickup" class="font-bold text-orange-500/80">Самовывоз</span>
                <span v-else>{{ order.delivery_address || 'Адрес не указан' }}</span>
            </div>
        </div>

        <div class="mt-auto flex items-center justify-between border-t border-slate-800 pt-4">
            <div class="flex flex-col">
                <span class="text-[9px] font-black uppercase tracking-tighter text-slate-600"
                    >Итого</span
                >
                <span class="text-lg font-black text-white">{{
                    formattedPrice(order.total_price)
                }}</span>
            </div>

            <button
                @click="$emit('open', order)"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-600 text-white transition-transform hover:scale-105 active:scale-95"
                aria-label="Открыть детали заказа"
            >
                <ChevronRightIcon class="h-5 w-5" />
            </button>
        </div>
    </div>
</template>
