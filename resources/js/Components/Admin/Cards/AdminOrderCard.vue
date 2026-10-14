<script setup lang="ts">
    import { computed } from 'vue';

    import {
        ChevronRightIcon,
        PhoneIcon,
        TicketIcon,
        TruckIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import { ORDER_STATUSES } from '@/Constants/statusConfig';
    import type { AdminOrder } from '@/types';
    import { formatMoney } from '@/utils/format';
    import { getInitials } from '@/utils/user';

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
        class="border-rancho-ink-800 bg-rancho-ink-900 hover:border-rancho-mint/40 hover:shadow-2xl group relative flex flex-col gap-4 rounded-3xl border p-5 transition-all hover:shadow-black/20"
    >
        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <span class="text-rancho-ink-500 text-xs font-black uppercase tracking-widest">
                #{{ order.id }}
            </span>

            <div
                class="rounded-lg border px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                :class="statusInfo.class"
            >
                {{ order.status_label }}
            </div>
        </div>

        <!-- CUSTOMER -->
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div
                    class="bg-rancho-ink-800 h-12 w-12 shrink-0 overflow-hidden rounded-2xl ring-1 ring-rancho-forest transition-transform group-hover:scale-110"
                    aria-hidden="true"
                >
                    <AppImage v-if="order.user?.avatar" :src="order.user.avatar" alt="Аватар" />

                    <div
                        v-else
                        class="from-rancho-ink-700 to-rancho-ink-800 text-rancho-ink-400 flex h-full w-full items-center justify-center bg-gradient-to-br text-lg font-black uppercase"
                    >
                        {{ getInitials(order?.user?.name ?? '') }}
                    </div>
                </div>

                <div class="min-w-0 overflow-hidden">
                    <p class="truncate text-sm font-bold text-white">
                        {{ order.customer_name }}
                    </p>
                    <BaseSmartTime :date="order.created_at" theme="dark" />
                </div>
            </div>

            <a
                :href="`tel:${order.customer_phone}`"
                @click.stop
                class="bg-rancho-ink-800/50 text-rancho-ink-300 hover:text-rancho-mint flex items-center gap-2 rounded-xl px-3 py-1.5 text-[11px] transition-colors"
            >
                <PhoneIcon class="h-3.5 w-3.5" aria-hidden="true" />
                {{ order.customer_phone }}
            </a>
        </div>

        <!-- DELIVERY / PROMO -->
        <div class="flex flex-col gap-2">
            <div
                v-if="order.promo_code"
                class="text-rancho-ember flex items-center gap-2 text-[10px]"
            >
                <TicketIcon class="h-3.5 w-3.5" aria-hidden="true" />
                <span>Промо: {{ order.promo_code.code }}</span>
            </div>

            <div class="bg-rancho-ink-950/50 flex items-center gap-3 rounded-2xl p-3">
                <TruckIcon class="text-rancho-mint h-4 w-4 shrink-0" aria-hidden="true" />
                <div class="text-rancho-ink-300 text-[10px] leading-tight">
                    <span v-if="order.is_pickup" class="text-rancho-mint font-bold">
                        Самовывоз
                    </span>
                    <span v-else class="line-clamp-2">
                        {{ order.delivery_address || 'Без адреса' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="border-rancho-ink-800 mt-auto flex items-center justify-between border-t pt-4">
            <div class="flex flex-col">
                <span class="text-rancho-ink-500 text-[9px] font-black uppercase"> Итого </span>
                <span class="text-lg font-black text-white">
                    {{ computedFormatMoney }}
                </span>
            </div>

            <button
                type="button"
                @click="$emit('open', order)"
                class="bg-rancho-mint text-rancho-ink-950 focus-visible:ring-rancho-mint/40 flex h-8 w-8 items-center justify-center rounded-xl transition-transform hover:scale-105 focus:outline-none focus-visible:ring-2 active:scale-95 sm:h-10 sm:w-10"
                aria-label="Открыть заказ"
            >
                <ChevronRightIcon class="h-4 w-4 sm:h-5 sm:w-5" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>
