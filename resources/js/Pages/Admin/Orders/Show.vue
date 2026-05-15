<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Link, router, useForm, usePage } from '@inertiajs/vue3';

    import {
        ChatBubbleLeftEllipsisIcon,
        ChatBubbleLeftRightIcon,
        MapPinIcon,
        PhoneIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AppImage from '@/Components/UI/AppImage.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import { ORDER_STATUSES } from '@/Constants/statusConfig';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminOrder, OrderStatus, ResourceSingle } from '@/types';
    import { formatMoney } from '@/utils/format';

    const can = usePage().props.can as {
        editAdminNote: boolean;
    };

    const props = defineProps<{
        order: ResourceSingle<AdminOrder>;
        backUrl: string;
    }>();

    const form = useForm({
        status: props.order.data.status,
        admin_note: props.order.data.admin_note,
        backUrl: props.backUrl,
    });

    defineOptions({ layout: AdminLayout });

    const updateStatus = (newStatus: OrderStatus) => {
        form.status = newStatus;
        form.patch(route('admin.orders.update', props.order.data.id), {
            preserveScroll: true,
        });
    };

    const debouncedUpdateNote = debounce(() => {
        form.patch(route('admin.orders.update', props.order.data.id), {
            preserveScroll: true,
            preserveState: true,
        });
    }, 600);

    const handleNoteInput = (e: Event) => {
        if (!can.editAdminNote) return;

        const target = e.target as HTMLTextAreaElement;
        form.admin_note = target.value;
        debouncedUpdateNote();
    };

    const { notify, notifyWithUndo } = useFlash();
    const isDeleting = ref(false);

    const deleteOrder = async () => {
        if (!can.editAdminNote) return;
        isDeleting.value = true;

        const orderDeleted = await notifyWithUndo(`Удаление заказа #${props.order.data.id}`, 5000);

        if (orderDeleted) {
            router.delete(route('admin.orders.destroy', props.order.data.id), {
                onError: () => notify('Не удалось удалить заказ!', 'error'),
                onFinish: () => (isDeleting.value = false),
            });
        } else {
            isDeleting.value = false;
        }
    };

    const computedTotalPrice = computed(() => formatMoney(props.order.data.total_price));
    const computedDiscountTotal = computed(() => formatMoney(props.order.data.discount_total));
    const computedDeliveryPrice = computed(() => formatMoney(props.order.data.delivery_price));
    const computedOrderPrice = computed(() =>
        formatMoney(props.order.data.total_price - (props.order.data.delivery_price || 0)),
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader :title="'Заказ #' + order.data.id" subtitle="Просмотр деталей заказа" />
    </Teleport>

    <div class="p-4 sm:p-8">
        <div class="mb-6">
            <BaseCancelButton :href="backUrl" label="Назад" />
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/50"
                >
                    <div class="border-b border-slate-800 bg-slate-900/80 px-6 py-4">
                        <h3
                            class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white"
                        >
                            <ShoppingBagIcon class="h-4 w-4 text-orange-500" />
                            Состав заказа #{{ order.data.id }}
                        </h3>
                    </div>

                    <div class="divide-y divide-slate-800">
                        <div
                            v-for="item in order.data.items"
                            :key="item.id"
                            class="flex items-center justify-between p-6 transition-colors hover:bg-slate-800/30"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950"
                                >
                                    <AppImage
                                        v-if="item.images && item.images.length"
                                        :src="item.images[0].url"
                                        context="product"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center text-2xl"
                                    >
                                        📦
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">
                                        <Link
                                            :href="
                                                route('catalog.show', {
                                                    product: item.product_slug,
                                                })
                                            "
                                        >
                                            {{ item.product_name }}
                                        </Link>
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        {{ item.quantity }}{{ item.unit.short }} ×
                                        {{ formatMoney(item.unit_price) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-orange-500">{{
                                    formatMoney(item.subtotal)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 bg-slate-950/50 p-6">
                        <div
                            class="flex justify-between text-xs font-bold uppercase text-slate-500"
                        >
                            <span>Сумма товаров:</span>
                            <span class="text-white">{{ computedOrderPrice }}</span>
                        </div>
                        <div
                            v-if="order.data.delivery_price"
                            class="flex justify-between text-xs font-bold uppercase text-slate-500"
                        >
                            <span>Доставка:</span>
                            <span class="text-white">{{ computedDeliveryPrice }}</span>
                        </div>
                        <div
                            v-if="order.data.discount_total > 0"
                            class="flex justify-between text-xs font-bold uppercase text-red-500"
                        >
                            <span>Скидка:</span>
                            <span>-{{ computedDiscountTotal }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-800 pt-4">
                            <span class="text-lg font-black uppercase text-white">Итого:</span>
                            <span class="text-2xl font-black text-orange-500">{{
                                computedTotalPrice
                            }}</span>
                        </div>
                    </div>
                </section>

                <section
                    v-if="order.data.delivery_meta"
                    class="rounded-3xl border border-slate-800 bg-slate-900/50 p-6"
                >
                    <h3
                        class="mb-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Техническая информация доставки
                    </h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div
                            v-for="(value, key) in order.data.delivery_meta"
                            :key="key"
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <p
                                class="mb-1 text-[9px] font-black uppercase text-slate-400 opacity-50"
                            >
                                {{ key }}
                            </p>
                            <p class="break-words text-xs font-bold text-white">
                                {{ value || '—' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    v-if="can.editAdminNote"
                    class="space-y-4 rounded-3xl border border-red-500/20 bg-red-500/5 p-6"
                >
                    <div class="flex items-start gap-3">
                        <ExclamationTriangleIcon
                            class="h-5 w-5 shrink-0 text-red-400"
                            aria-hidden="true"
                        />
                        <div>
                            <h3
                                class="text-[10px] font-black uppercase tracking-widest text-red-400"
                            >
                                Опасная зона
                            </h3>
                            <p class="mt-1 text-[10px] leading-tight text-red-300/60">
                                Удаление заказа приведет к его полному исчезновению из базы данных и
                                статистики.
                            </p>
                        </div>
                    </div>

                    <AdminDeleteButton
                        @click="deleteOrder"
                        title="Удалить заказ"
                        :disabled="isDeleting"
                    />
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-800 bg-slate-900/50 p-6">
                    <h3
                        class="mb-6 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Покупатель
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 text-orange-500"
                            >
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-500">Имя</p>
                                <p class="text-sm font-bold text-white">
                                    {{ order.data.customer_name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 text-blue-500"
                            >
                                <PhoneIcon class="h-5 w-5" />
                            </div>
                            <a
                                :href="`tel:${order.data.customer_phone}`"
                                class="block transition-colors hover:text-orange-500"
                            >
                                <p class="text-xs font-black uppercase text-slate-500">Телефон</p>
                                <p class="text-sm font-bold text-white">
                                    {{ order.data.customer_phone }}
                                </p>
                            </a>
                        </div>
                        <div class="flex items-start gap-4 pt-2">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-500/10 text-green-500"
                            >
                                <MapPinIcon class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-500">
                                    Адрес доставки
                                </p>
                                <p class="text-sm font-bold leading-relaxed text-white">
                                    {{
                                        order.data.is_pickup
                                            ? 'Самовывоз из магазина'
                                            : order.data.delivery_address
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="order.data.customer_comment"
                        class="mt-6 rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-4"
                    >
                        <p
                            class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase text-slate-500"
                        >
                            <ChatBubbleLeftEllipsisIcon class="h-3 w-3" />
                            Комментарий клиента
                        </p>
                        <p class="text-xs italic text-slate-300">
                            «{{ order.data.customer_comment }}»
                        </p>
                    </div>
                </section>

                <section class="space-y-6 rounded-3xl border border-slate-800 bg-slate-900/50 p-6">
                    <div>
                        <label class="mb-3 block text-[10px] font-black uppercase text-slate-500"
                            >Текущий статус: {{ order.data.status_label }}</label
                        >
                        <div class="grid grid-cols-1 gap-2">
                            <button
                                v-for="(info, key) in ORDER_STATUSES"
                                :key="key"
                                @click="updateStatus(key)"
                                :class="[
                                    'w-full rounded-xl border py-3 text-[10px] font-black uppercase transition-all',
                                    order.data.status === key
                                        ? 'border-white bg-white text-slate-950'
                                        : 'border-slate-800 bg-slate-950 text-slate-500 hover:border-slate-600',
                                    !can.editAdminNote &&
                                        'cursor-not-allowed opacity-50 hover:border-slate-800',
                                ]"
                            >
                                {{ info.label }}
                            </button>
                        </div>
                    </div>
                </section>

                <section class="space-y-4 rounded-3xl border border-slate-800 bg-slate-900/50 p-6">
                    <div class="flex items-center gap-2">
                        <ChatBubbleLeftRightIcon class="h-4 w-4 text-orange-500" />
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            Заметка администратора
                        </h3>
                    </div>

                    <div class="relative" v-if="can.editAdminNote">
                        <BaseTextarea
                            v-model="form.admin_note"
                            :error="form.errors.admin_note"
                            placeholder="Напишите что-нибудь о заказе..."
                            :max-height="400"
                        />

                        <div class="absolute bottom-3 right-3 flex items-center gap-2">
                            <span
                                v-if="form.processing"
                                class="animate-pulse text-[9px] font-black uppercase text-orange-500"
                            >
                                Сохранение...
                            </span>
                        </div>
                    </div>

                    <div
                        class="min-h-[100px] rounded-2xl border border-dashed border-slate-800 bg-slate-950/30 p-4"
                    >
                        <div
                            v-if="order.data.admin_note"
                            class="text-xs italic leading-relaxed text-slate-300"
                        >
                            «{{ order.data.admin_note }}»
                        </div>
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-[10px] font-bold uppercase tracking-widest text-slate-600"
                        >
                            Заметок пока нет
                        </div>
                    </div>

                    <p class="px-2 text-[9px] leading-tight text-slate-500">
                        * Эта информация видна всему персоналу, но редактируется только
                        администратором.
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
