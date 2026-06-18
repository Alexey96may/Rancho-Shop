<script setup lang="ts">
    import { router } from '@inertiajs/vue3';

    import ProfileLayout from '@/Layouts/ProfileLayout.vue';

    defineOptions({ layout: ProfileLayout });

    defineProps<{
        orders: {
            data: Array<any>;
            links: Array<any>;
        };
    }>();

    const cancelOrder = (orderId: number) => {
        if (confirm('Вы уверены, что хотите отменить этот заказ?')) {
            router.patch(
                route('profile.orders.cancel', orderId),
                {},
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const getStatusStyles = (status: string) => {
        switch (status) {
            case 'new':
                return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
            case 'confirmed':
                return 'bg-orange-500/10 text-orange-400 border border-orange-500/20';
            case 'completed':
                return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            case 'cancelled':
                return 'bg-red-500/10 text-red-400 border border-red-500/20 text-slate-500 line-through';
            default:
                return 'bg-slate-800 text-slate-400';
        }
    };

    const getStatusLabel = (status: string) => {
        const labels: Record<string, string> = {
            new: 'Принят / Ожидает',
            confirmed: 'Подтвержден',
            completed: 'Выполнен 🎉',
            cancelled: 'Отменен',
        };
        return labels[status] || status;
    };
</script>

<template>
    <div class="space-y-6">
        <div v-if="orders.data.length === 0" class="py-12 text-center text-sm text-slate-500">
            Вы еще не совершали заказов на нашем сайте.
        </div>

        <div class="grid grid-cols-1 gap-4">
            <section
                v-for="order in orders.data"
                :key="order.id"
                class="rounded-2xl border border-slate-800 bg-slate-950 p-5 transition-all"
                :class="{ 'opacity-60': order.status === 'cancelled' }"
            >
                <div
                    class="mb-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-900 pb-3"
                >
                    <div>
                        <h3 class="text-sm font-black text-white">Заказ №{{ order.id }}</h3>
                        <span class="mt-0.5 block text-[10px] text-slate-500"
                            >От даты: {{ order.created_at }}</span
                        >
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

                <div class="mb-4 space-y-2">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between rounded-xl bg-slate-900/40 p-2 text-xs text-slate-300"
                    >
                        <span class="font-medium text-slate-400">
                            {{ item.product?.name || 'Удаленный товар' }}
                            <span class="ml-1 font-black text-white">x{{ item.quantity }}</span>
                        </span>
                        <span class="font-bold text-white">{{ item.price }} ₽</span>
                    </div>
                </div>

                <div
                    class="mt-2 flex items-center justify-between gap-4 border-t border-slate-900 pt-3"
                >
                    <div>
                        <span
                            class="block text-[10px] font-bold uppercase tracking-wider text-slate-500"
                            >Итого к оплате</span
                        >
                        <span class="text-base font-black text-orange-400"
                            >{{ order.total_price || 0 }} ₽</span
                        >
                    </div>

                    <button
                        v-if="['new', 'confirmed'].includes(order.status)"
                        @click="cancelOrder(order.id)"
                        class="rounded-xl border border-red-500/30 px-4 py-2 text-[11px] font-black uppercase tracking-wider text-red-400 transition-all hover:bg-red-500 hover:text-black"
                    >
                        Отменить заказ
                    </button>
                </div>
            </section>
        </div>

        <div v-if="orders.links && orders.links.length > 3" class="flex justify-center gap-1 pt-4">
            <Component
                :is="link.url ? 'Link' : 'span'"
                v-for="(link, k) in orders.links"
                :key="k"
                :href="link.url"
                v-html="link.label"
                class="rounded-lg px-3 py-1.5 text-xs transition-all"
                :class="{
                    'bg-slate-800 font-bold text-white ring-1 ring-slate-700': link.active,
                    'text-slate-500': !link.url,
                    'text-slate-400 hover:bg-slate-900': link.url && !link.active,
                }"
            />
        </div>
    </div>
</template>
