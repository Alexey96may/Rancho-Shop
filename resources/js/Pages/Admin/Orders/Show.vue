<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

    import { Link, useForm, usePage } from '@inertiajs/vue3';

    import {
        BanknotesIcon,
        ChatBubbleLeftEllipsisIcon,
        ChatBubbleLeftRightIcon,
        CubeIcon,
        HomeModernIcon,
        MapIcon,
        MapPinIcon,
        PhoneIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import DangerZone from '@/Components/Sections/DangerZone.vue';
    import AppImage from '@/Components/UI/AppImage.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import { ORDER_STATUSES } from '@/Constants/statusConfig';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import type { AdminOrder, GeoPoint, ResourceSingle } from '@/types';
    import { formatDistance, formatMoney } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const can = usePage().props.can as {
        editAdminNote: boolean;
    };

    const props = defineProps({
        order: {
            type: Object as PropType<ResourceSingle<AdminOrder>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = !!(val?.data && typeof val.data === 'object' && val.data !== null);

                const dataObj = val?.data as Record<string, unknown>;
                const hasRequiredFields =
                    hasData && 'id' in dataObj && 'status' in dataObj && 'total_price' in dataObj;

                if (!hasRequiredFields) {
                    console.warn(
                        'Runtime Error: The "order" prop is missing core fields or correct resource structure.',
                    );
                }
                return hasRequiredFields;
            },
        },
        backUrl: {
            type: String,
            required: true,
        },
    });

    const form = useForm({
        status: props.order.data.status,
        admin_note: props.order.data.admin_note,
        backUrl: props.backUrl,
    });

    const { submitForm } = useAdminForm();
    const { deleteEntity, isDeleting } = useAdminCrud();

    const deleteOrder = () => {
        if (!can.editAdminNote) return;

        deleteEntity(
            'admin.orders',
            props.order.data.id,
            `Удаление заказа #${props.order.data.id}`,
        );
    };

    const submit = (key?: keyof typeof ORDER_STATUSES) => {
        if (key) {
            form.status = key;
        }

        submitForm(form, 'admin.orders', props.order.data.id, { preserveScroll: true });
    };

    const computedTotalPrice = computed(() => formatMoney(props.order.data.total_price));
    const computedDiscountTotal = computed(() => formatMoney(props.order.data.discount_total));
    const computedDeliveryPrice = computed(() => formatMoney(props.order.data.delivery_price));
    const computedOrderPrice = computed(() =>
        formatMoney(props.order.data.total_price - (props.order.data.delivery_price || 0)),
    );

    const selectedPoint = ref<GeoPoint | null>(null);

    /** Адрес без «Россия, Республика Крым, ...» — только город и ниже */
    const addressRevers = computed<string>(() => {
        const addr = props.order.data.delivery_address;
        if (!addr) return '';

        const rev = addr.split(',').reverse().join(', ');

        return rev;
    });
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader :title="'Заказ #' + order.data.id" subtitle="Просмотр деталей заказа" />
    </Teleport>

    <div class="space-y-10">
        <BaseCancelButton :href="backUrl" label="Назад" theme="dark" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
            <!-- LEFT COLUMN -->
            <div class="flex flex-col gap-6 lg:col-span-2">
                <!-- СОСТАВ ЗАКАЗА -->
                <section
                    class="overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 sm:rounded-3xl"
                >
                    <div
                        class="border-b border-rancho-ink-800 bg-rancho-ink-900/80 px-4 py-4 sm:px-6"
                    >
                        <h3
                            class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-white"
                        >
                            <ShoppingBagIcon class="h-4 w-4 text-rancho-mint" aria-hidden="true" />
                            Состав заказа #{{ order.data.id }}
                        </h3>
                    </div>

                    <div class="divide-y divide-rancho-ink-800">
                        <div
                            v-for="item in order.data.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 p-4 transition-colors hover:bg-rancho-ink-800/30 sm:p-6"
                        >
                            <div class="flex min-w-0 items-center gap-4">
                                <div
                                    class="h-8 w-8 shrink-0 overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 sm:h-14 sm:h-16 sm:w-14 sm:w-16"
                                >
                                    <AppImage
                                        v-if="item.images && item.images.length"
                                        :src="item.images[0].url"
                                        context="product"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center text-rancho-ink-600"
                                    >
                                        <CubeIcon
                                            class="h-4 w-4 sm:h-6 sm:w-6"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <h4 class="truncate text-sm font-bold text-white">
                                        <Link
                                            v-if="item.product_slug"
                                            :href="route('catalog.show', item.product_slug)"
                                            class="transition-colors hover:text-rancho-mint hover:underline"
                                        >
                                            {{ item.product_name }}
                                        </Link>

                                        <span v-else class="text-rancho-ink-300">
                                            {{ item.product_name }}
                                        </span>
                                    </h4>

                                    <p class="text-xs text-rancho-ink-500">
                                        {{ item.quantity }}{{ item.unit.short }} ×
                                        {{ formatMoney(item.unit_price) }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-sm font-black text-rancho-mint">
                                    {{ formatMoney(item.subtotal) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ИТОГИ -->
                    <div class="space-y-3 bg-rancho-ink-950/50 px-4 py-6 sm:p-6">
                        <div
                            class="flex justify-between text-xs font-bold uppercase text-rancho-ink-500"
                        >
                            <span>Сумма товаров:</span>
                            <span class="text-white">{{ computedOrderPrice }}</span>
                        </div>

                        <div
                            class="flex justify-between text-xs font-bold uppercase text-rancho-ink-500"
                        >
                            <span>Доставка:</span>
                            <span class="text-white">{{ computedDeliveryPrice }}</span>
                        </div>

                        <div
                            v-if="order.data.discount_total > 0"
                            class="flex justify-between text-xs font-bold uppercase text-rose-400"
                        >
                            <span>Скидка:</span>
                            <span>-{{ computedDiscountTotal }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-rancho-ink-800 pt-4"
                        >
                            <span class="text-sm font-black uppercase text-white sm:text-lg"
                                >Итого:</span
                            >
                            <span class="text-lg font-black text-rancho-mint sm:text-xl">
                                {{ computedTotalPrice }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ТЕХИНФОРМАЦИЯ ДОСТАВКИ -->
                <section
                    v-if="order.data.delivery_meta"
                    class="space-y-8 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 py-6 sm:rounded-3xl sm:p-6"
                >
                    <div class="flex items-center justify-between gap-2">
                        <h3
                            class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            <TruckIcon class="h-4 w-4 text-rancho-mint" aria-hidden="true" />
                            Техническая информация доставки
                        </h3>

                        <!-- Статус доставки -->
                        <span
                            class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset"
                            :class="
                                order.data.delivery_meta.is_valid
                                    ? 'bg-rancho-mint/10 text-rancho-mint ring-rancho-mint/30'
                                    : 'bg-rose-500/10 text-rose-400 ring-rose-500/30'
                            "
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="
                                    order.data.delivery_meta.is_valid
                                        ? 'bg-rancho-mint'
                                        : 'bg-rose-400'
                                "
                                aria-hidden="true"
                            />
                            {{
                                order.data.delivery_meta.is_valid
                                    ? 'В зоне доставки'
                                    : 'Вне зоны доставки'
                            }}
                        </span>
                    </div>

                    <!-- Параметры -->
                    <dl class="grid grid-cols-1 justify-center gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Зона -->
                        <div
                            class="flex flex-col items-center gap-2 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 sm:items-start"
                        >
                            <dt
                                class="flex gap-2 text-[9px] font-black uppercase tracking-wider text-rancho-ink-500"
                            >
                                <MapPinIcon class="h-3 w-3 shrink-0" aria-hidden="true" />
                                Зона
                            </dt>

                            <dd class="text-sm font-bold text-white">
                                {{ order.data.delivery_meta.zone?.name || '—' }}
                            </dd>

                            <p
                                v-if="order.data.delivery_meta.zone?.free_from"
                                class="mt-1 text-[10px] text-rancho-ink-400"
                            >
                                Бесплатно от
                                {{ formatMoney(order.data.delivery_meta.zone.free_from) }}
                            </p>
                        </div>

                        <!-- Цена доставки -->
                        <div
                            class="flex flex-col items-center gap-2 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 sm:items-start"
                        >
                            <dt
                                class="flex gap-2 text-[9px] font-black uppercase tracking-wider text-rancho-ink-500"
                            >
                                <BanknotesIcon class="h-3 w-3 shrink-0" aria-hidden="true" />
                                Стоимость доставки
                            </dt>
                            <dd
                                class="text-sm font-bold"
                                :class="
                                    order.data.delivery_meta.delivery_price > 0
                                        ? 'text-rancho-ink-200'
                                        : 'text-rancho-buttercup'
                                "
                            >
                                {{
                                    order.data.delivery_meta.delivery_price > 0
                                        ? formatMoney(order.data.delivery_meta.delivery_price)
                                        : 'Бесплатно'
                                }}
                            </dd>
                        </div>

                        <!-- До маршрута -->
                        <div
                            class="flex flex-col items-center gap-2 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 sm:items-start"
                        >
                            <dt
                                class="flex gap-2 text-[9px] font-black uppercase tracking-wider text-rancho-ink-500"
                            >
                                <MapIcon class="h-3 w-3 shrink-0" aria-hidden="true" />
                                До маршрута
                            </dt>
                            <dd class="text-sm font-bold text-white">
                                {{
                                    order.data.delivery_meta.distance_to_route !== null
                                        ? formatDistance(order.data.delivery_meta.distance_to_route)
                                        : '—'
                                }}
                            </dd>
                        </div>

                        <!-- До фермы -->
                        <div
                            class="flex flex-col items-center gap-2 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 sm:items-start"
                        >
                            <dt
                                class="flex gap-2 text-[9px] font-black uppercase tracking-wider text-rancho-ink-500"
                            >
                                <HomeModernIcon class="h-3 w-3 shrink-0" aria-hidden="true" />
                                До фермы
                            </dt>
                            <dd class="text-sm font-bold text-white">
                                {{
                                    order.data.delivery_meta.distance_to_farm !== null
                                        ? formatDistance(order.data.delivery_meta.distance_to_farm)
                                        : '—'
                                }}
                            </dd>
                        </div>
                    </dl>

                    <!-- Карта -->
                    <div class="space-y-2">
                        <div
                            class="shadow-sm relative h-[400px] overflow-hidden rounded-2xl border border-rancho-ink-800"
                        >
                            <MapboxPicker
                                v-model="selectedPoint"
                                :user-coords="{
                                    lng: order.data.delivery_lng || 34.30457,
                                    lat: order.data.delivery_lat || 44.85014,
                                }"
                                @delivery-valid="
                                    (v: boolean) => (order.data.delivery_validated = v)
                                "
                                :draggable="false"
                            />
                        </div>

                        <div class="flex w-full items-center gap-2 p-2 sm:items-baseline">
                            <span class="text-xs font-black uppercase text-rancho-ink-200">
                                Адрес:
                            </span>
                            <span class="text-xs font-bold text-rancho-ink-50">
                                {{ addressRevers }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ЗАМЕТКА АДМИНА -->
                <section
                    class="space-y-4 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:rounded-3xl sm:p-6"
                >
                    <div class="flex items-center gap-2">
                        <ChatBubbleLeftRightIcon
                            class="h-4 w-4 text-rancho-mint"
                            aria-hidden="true"
                        />
                        <h3
                            class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Заметка администратора
                        </h3>
                    </div>

                    <div v-if="can.editAdminNote" class="relative">
                        <BaseTextarea
                            v-model="form.admin_note"
                            @blur="submit"
                            @input-start="form.clearErrors('admin_note')"
                            :error="form.errors.admin_note"
                            placeholder="Напишите что-нибудь о заказе..."
                            :max-height="400"
                            theme="dark"
                        />

                        <div class="absolute bottom-3 right-3 flex items-center gap-2">
                            <span
                                v-if="form.processing"
                                class="animate-pulse text-[9px] font-black uppercase text-rancho-mint"
                            >
                                Сохранение...
                            </span>
                        </div>
                    </div>

                    <div
                        class="min-h-[100px] rounded-2xl border border-dashed border-rancho-ink-800 bg-rancho-ink-950/30 p-4"
                    >
                        <div
                            v-if="order.data.admin_note"
                            class="text-xs italic leading-relaxed text-rancho-ink-200"
                        >
                            «{{ order.data.admin_note }}»
                        </div>
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-[10px] font-bold uppercase tracking-widest text-rancho-ink-500"
                        >
                            Заметок пока нет
                        </div>
                    </div>

                    <p class="px-2 text-[9px] leading-tight text-rancho-ink-500">
                        * Эта информация видна всему персоналу, но редактируется только
                        администратором.
                    </p>
                </section>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="space-y-6">
                <!-- ПОКУПАТЕЛЬ -->
                <section
                    class="rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:rounded-3xl sm:p-6"
                >
                    <h3
                        class="mb-6 text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                    >
                        Покупатель
                    </h3>

                    <div class="space-y-4">
                        <!-- ИМЯ -->
                        <div class="flex items-center gap-2 sm:gap-4">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rancho-mint/10 text-rancho-mint sm:h-10 sm:w-10"
                                aria-hidden="true"
                            >
                                <UserIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-black text-rancho-ink-500 sm:uppercase">
                                    Имя
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ order.data.customer_name }}
                                </p>
                            </div>
                        </div>

                        <!-- ТЕЛЕФОН -->
                        <div class="flex items-center gap-2 sm:gap-4">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rancho-sky/15 text-rancho-sky sm:h-10 sm:w-10"
                                aria-hidden="true"
                            >
                                <PhoneIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <a
                                :href="`tel:${order.data.customer_phone}`"
                                class="block space-y-1 transition-colors hover:text-rancho-mint"
                            >
                                <p class="text-xs font-black text-rancho-ink-500 sm:uppercase">
                                    Телефон
                                </p>
                                <p class="text-sm font-bold text-white">
                                    {{ order.data.customer_phone }}
                                </p>
                            </a>
                        </div>

                        <!-- АДРЕС -->
                        <div class="flex items-start gap-2 sm:gap-4">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rancho-pine/20 text-rancho-mint sm:h-10 sm:w-10"
                                aria-hidden="true"
                            >
                                <MapPinIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-black text-rancho-ink-500 sm:uppercase">
                                    Адрес доставки
                                </p>
                                <p class="text-xs font-bold leading-relaxed text-white">
                                    {{
                                        order.data.is_pickup
                                            ? 'Самовывоз из магазина'
                                            : order.data.delivery_address
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- КОММЕНТАРИЙ -->
                    <div
                        v-if="order.data.customer_comment"
                        class="mt-6 rounded-2xl border border-dashed border-rancho-ink-700 bg-rancho-ink-950 p-4"
                    >
                        <p
                            class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase text-rancho-ink-500"
                        >
                            <ChatBubbleLeftEllipsisIcon class="h-3 w-3" aria-hidden="true" />
                            Комментарий клиента
                        </p>
                        <p class="text-xs italic text-rancho-ink-200">
                            «{{ order.data.customer_comment }}»
                        </p>
                    </div>
                </section>

                <!-- СТАТУС -->
                <section
                    class="space-y-6 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:rounded-3xl sm:p-6"
                >
                    <div>
                        <label
                            class="mb-3 block text-[10px] font-black uppercase text-rancho-ink-500"
                        >
                            Текущий статус: {{ order.data.status_label }}
                        </label>

                        <div class="grid grid-cols-1 gap-2">
                            <button
                                v-for="(info, key) in ORDER_STATUSES"
                                :key="key"
                                type="button"
                                @click="submit(key)"
                                :class="[
                                    'w-full rounded-xl border py-3 text-[10px] font-black uppercase transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40',
                                    order.data.status === key
                                        ? 'border-rancho-mint bg-rancho-mint text-rancho-ink-950'
                                        : 'border-rancho-ink-800 bg-rancho-ink-950 text-rancho-ink-400 hover:border-rancho-ink-600 hover:text-rancho-ink-200',
                                    !can.editAdminNote &&
                                        'cursor-not-allowed opacity-50 hover:border-rancho-ink-800',
                                ]"
                            >
                                {{ info.label }}
                            </button>
                        </div>
                    </div>
                </section>

                <DangerZone
                    :show="can.editAdminNote"
                    description="Удаление заказа приведёт к его полному исчезновению из базы данных и статистики."
                    button-title="Удалить заказ"
                    :disabled="isDeleting(props.order.data.id)"
                    @confirm="deleteOrder"
                />
            </div>
        </div>
    </div>
</template>
