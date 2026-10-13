<script setup lang="ts">
    import { type PropType } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import type { Order, Paginated } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: ProfileLayout });

    defineProps({
        orders: {
            type: Object as PropType<Paginated<Order>>,
            required: true,
        },
    });

    const { deleteEntity, isDeleting } = useAdminCrud();

    const cancelOrder = (order: Order) => {
        const orderDate = new Date(order.created_at).toLocaleString();
        const message = 'Отменить заказ от ' + orderDate + '?';
        deleteEntity('profile.orders', order.id, message);
    };

    /** Классы для чипа статуса — в палитре rancho */
    function getStatusStyles(status: string): string {
        switch (status) {
            case 'new':
                return 'bg-rancho-sky/15 text-rancho-sky border border-rancho-sky/30';
            case 'confirmed':
                return 'bg-rancho-buttercup/15 text-rancho-buttercup border border-rancho-buttercup/30';
            case 'completed':
                return 'bg-rancho-pine/20 text-rancho-sky border border-rancho-pine/40';
            case 'cancelled':
                return 'bg-rose-500/10 text-rose-300 border border-rose-500/30 line-through';
            default:
                return 'bg-rancho-ink-800 text-rancho-ink-300 border border-rancho-ink-700';
        }
    }

    /** Человекочитаемый ярлык статуса */
    function getStatusLabel(status: string): string {
        const labels: Record<string, string> = {
            new: 'Принят / Ожидает',
            confirmed: 'Подтверждён',
            completed: 'Выполнен 🎉',
            cancelled: 'Отменён',
        };
        return labels[status] ?? status;
    }
</script>

<template>
    <div class="space-y-6">
        <!-- EMPTY -->
        <div
            v-if="orders.data.length === 0"
            v-scroll-reveal
            class="border-rancho-ink-700 bg-rancho-ink-900/20 rounded-2xl border border-dashed py-12 text-center"
            role="status"
        >
            <p class="text-rancho-ink-300 text-sm">Вы ещё не совершали заказов на нашем сайте.</p>
        </div>

        <!-- LIST -->
        <div v-else class="grid grid-cols-1 gap-4">
            <section
                v-for="order in orders.data"
                :key="order.id"
                v-scroll-reveal
                class="border-rancho-ink-800 bg-rancho-ink-950 rounded-2xl border p-5 transition-all"
                :class="{
                    'opacity-60': order.status === 'cancelled',
                    'scale-95 opacity-60': isDeleting(order.id),
                }"
            >
                <!-- HEADER -->
                <div
                    class="border-rancho-ink-800 mb-4 flex flex-wrap items-center justify-between gap-4 border-b pb-3"
                >
                    <div>
                        <h3 class="text-sm font-black text-white">Заказ №{{ order.id }}</h3>
                        <span class="text-rancho-ink-400 mt-0.5 block text-[10px]">
                            От:
                            <BaseSmartTime :date="order.created_at" theme="dark" />
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            class="rounded-xl px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                            :class="getStatusStyles(order.status)"
                        >
                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>
                </div>

                <!-- ITEMS -->
                <div class="mb-4 space-y-2">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="bg-rancho-ink-900/40 flex items-center justify-between gap-3 rounded-xl p-2 text-xs"
                    >
                        <!-- Есть slug → ссылка, иначе текст -->
                        <Link
                            v-if="item.product_slug"
                            :href="route('catalog.show', item.product_slug)"
                            class="text-rancho-ink-200 min-w-0 font-medium transition-colors hover:text-rancho-buttercup hover:underline"
                        >
                            {{ item.product_name || 'Товар' }}
                            <span class="ml-1 font-black text-white"> ×{{ item.quantity }} </span>
                        </Link>

                        <span v-else class="text-rancho-ink-400 min-w-0 font-medium italic">
                            {{ item.product_name || 'Удалённый товар' }}
                            <span class="text-rancho-ink-200 ml-1 font-black">
                                ×{{ item.quantity }}
                            </span>
                        </span>

                        <span class="shrink-0 font-bold text-white">
                            {{ formatMoney(item.unit_price) }}
                        </span>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="border-rancho-ink-800 mt-2 flex items-center justify-between gap-4 border-t pt-3"
                >
                    <div>
                        <span
                            class="text-rancho-ink-400 block text-[10px] font-bold uppercase tracking-wider"
                        >
                            Итого к оплате
                        </span>
                        <span class="text-base font-black text-rancho-buttercup">
                            {{ formatMoney(order.total_price) }}
                        </span>
                    </div>

                    <button
                        v-if="order.status === 'new'"
                        type="button"
                        @click="cancelOrder(order)"
                        class="rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-rose-300 transition-all hover:border-rose-500 hover:bg-rose-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40 active:scale-95"
                    >
                        Отменить заказ
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
