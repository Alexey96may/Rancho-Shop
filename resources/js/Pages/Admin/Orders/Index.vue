<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import { ListBulletIcon, Squares2X2Icon } from '@heroicons/vue/20/solid';

    import AdminOrderCard from '@/Components/Admin/Cards/AdminOrderCard.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        orders: { data: any[] };
    }>();

    const isModalOpen = ref(false);
    const selectedOrder = ref<any>(null);

    const openOrder = (order: any) => {
        selectedOrder.value = order;
        isModalOpen.value = true;
    };

    const updateStatus = (newStatus: string) => {
        if (!selectedOrder.value) return;

        router.patch(
            route('admin.orders.update', selectedOrder.value.id),
            {
                status: newStatus,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isModalOpen.value = false;
                    // Здесь можно добавить уведомление, если оно у тебя есть в Layout
                },
            },
        );
    };
</script>

<template>
    <Head title="Заказы - Админ" />

    <AdminLayout>
        <template #header>Управление заказами</template>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <TransitionGroup name="order-list">
                <AdminOrderCard
                    v-for="order in orders.data"
                    :key="order.id"
                    :order="order"
                    @open="openOrder"
                />
            </TransitionGroup>
        </div>

        <BaseModal
            :show="isModalOpen"
            :title="selectedOrder ? `Заказ #${selectedOrder.id}` : ''"
            @close="isModalOpen = false"
        >
            <div v-if="selectedOrder" class="space-y-6">
                <section>
                    <h4
                        class="mb-4 text-center text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Товары в заказе
                    </h4>
                    <div class="custom-scrollbar max-h-60 space-y-2 overflow-y-auto pr-2">
                        <div
                            v-for="item in selectedOrder.items"
                            :key="item.id"
                            class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-3"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-white">{{
                                    item.product_name
                                }}</span>
                                <span class="text-[10px] text-slate-500"
                                    >{{ item.quantity }} шт. x {{ item.price / 100 }} ₽</span
                                >
                            </div>
                            <span class="text-sm font-black text-orange-500"
                                >{{ (item.price * item.quantity) / 100 }} ₽</span
                            >
                        </div>
                    </div>
                </section>

                <div class="border-t border-slate-800 pt-4">
                    <p
                        class="mb-3 text-center text-[10px] font-black uppercase tracking-widest text-slate-600"
                    >
                        Изменить статус
                    </p>
                    <div class="grid grid-cols-2 gap-2" role="group">
                        <button
                            @click="updateStatus('processing')"
                            class="rounded-xl bg-orange-600/10 py-3 text-[10px] font-black uppercase tracking-widest text-orange-500 transition-all hover:bg-orange-600 hover:text-white"
                        >
                            В работу
                        </button>
                        <button
                            @click="updateStatus('completed')"
                            class="rounded-xl bg-green-600/10 py-3 text-[10px] font-black uppercase tracking-widest text-green-500 transition-all hover:bg-green-600 hover:text-white"
                        >
                            Завершить
                        </button>
                    </div>
                </div>
            </div>
        </BaseModal>
    </AdminLayout>
</template>

<style scoped>
    .order-list-enter-active,
    .order-list-leave-active {
        transition: all 0.5s ease;
    }
    .order-list-enter-from,
    .order-list-leave-to {
        opacity: 0;
        transform: scale(0.9);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 10px;
    }
</style>
