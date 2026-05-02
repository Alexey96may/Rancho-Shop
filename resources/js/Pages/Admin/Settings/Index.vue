<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import {
        CloudArrowUpIcon,
        Cog6ToothIcon,
        DevicePhoneMobileIcon,
        GlobeAltIcon,
        PresentationChartLineIcon,
        TruckIcon,
    } from '@heroicons/vue/24/outline';

    import DeliveryZoneManager from '@/Components/Admin/Shared/DeliveryZoneManager.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { DeliveryZone, ResourceCollection } from '@/types';

    type SettingType = 'string' | 'integer' | 'boolean' | 'json';

    interface Setting {
        key: string;
        value: string | number | boolean | DeliveryZone[];
        type: SettingType;
    }

    const props = defineProps<{
        settings: ResourceCollection<Setting>;
    }>();

    defineOptions({ layout: AdminLayout });

    const sections = [
        {
            id: 'general',
            label: 'Общие',
            icon: GlobeAltIcon,
            keys: ['site_name', 'site_description', 'header_announcement'],
        },
        {
            id: 'contacts',
            label: 'Контакты',
            icon: DevicePhoneMobileIcon,
            keys: [
                'contact_phone',
                'contact_email',
                'contact_telegram',
                'contact_vk',
                'address_farm',
                'farm_coords',
            ],
        },
        {
            id: 'shop',
            label: 'Магазин',
            icon: Cog6ToothIcon,
            keys: ['shop_status', 'is_accepting_orders', 'min_order_amount'],
        },
        {
            id: 'delivery',
            label: 'Доставка',
            icon: TruckIcon,
            keys: ['delivery_schedule', 'delivery_info', 'delivery_zones'],
        },
        {
            id: 'ui',
            label: 'Интерфейс',
            icon: PresentationChartLineIcon,
            keys: [
                'products_per_page',
                'animals_per_page',
                'comments_per_page',
                'users_per_page',
                'orders_per_page',
                'featured_animals_limit',
                'featured_products_limit',
                'featured_comments_limit',
            ],
        },
    ];

    const activeSection = ref('general');

    const form = useForm({
        settings: props.settings.data.map((s) => ({
            key: s.key,
            value: s.value,
            type: s.type,
        })),
    });

    const currentFields = computed(() => {
        const section = sections.find((s) => s.id === activeSection.value);
        return form.settings.filter((s) => section?.keys.includes(s.key));
    });

    const submit = () => {
        form.post(route('admin.settings.bulk'), {
            preserveScroll: true,
        });
    };

    const labels: Record<string, string> = {
        site_name: 'Название сайта',
        site_description: 'Описание (SEO)',
        header_announcement: 'Текст в шапке',
        contact_phone: 'Телефон',
        contact_email: 'Email',
        contact_telegram: 'Telegram (ссылка)',
        contact_vk: 'VK (ссылка)',
        address_farm: 'Адрес фермы',
        farm_coords: 'Координаты (Через запятую: шир., дол.)',
        shop_status: 'Статус магазина',
        is_accepting_orders: 'Прием заказов',
        min_order_amount: 'Мин. сумма заказа (коп.)',
        delivery_schedule: 'График доставки',
        delivery_info: 'Информация о доставке',
        delivery_zones: 'Зоны доставки',
        products_per_page: 'Товаров на страницу',
        animals_per_page: 'Животных на страницу',
        comments_per_page: 'Отзывов на страницу',
        users_per_page: 'Пользователей на страницу',
        orders_per_page: 'Заказов на страницу',
        featured_animals_limit: 'Лимит "Популярные животные"',
        featured_products_limit: 'Лимит "Популярные товары"',
        featured_comments_limit: 'Лимит отзывов на главной',
    };

    const formatLabel = (key: string) => labels[key] || key;

    const typeOptions = [
        { value: 'open', label: 'Открыт (Принимаем заказы)' },
        { value: 'closed', label: 'Закрыт' },
        { value: 'maintenance', label: 'Технические работы' },
    ];

    const asZones = (value: any): DeliveryZone[] => {
        return value as DeliveryZone[];
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Настройки</h1>
    </Teleport>

    <div class="space-y-6">
        <nav class="no-scrollbar flex items-center gap-2 overflow-x-auto pb-2" role="tablist">
            <button
                v-for="section in sections"
                :key="section.id"
                @click="activeSection = section.id"
                role="tab"
                :aria-selected="activeSection === section.id"
                class="flex items-center gap-2 whitespace-nowrap rounded-2xl px-5 py-3 text-sm font-bold transition-all"
                :class="
                    activeSection === section.id
                        ? 'shadow-lg bg-orange-600 text-white shadow-orange-900/20'
                        : 'border border-slate-800/50 bg-slate-800/40 text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                "
            >
                <component :is="section.icon" class="h-4 w-4" />
                {{ section.label }}
            </button>
        </nav>

        <main class="min-h-[500px] rounded-3xl border border-slate-800 bg-slate-900/50 p-6 md:p-8">
            <form @submit.prevent="submit" class="space-y-6 overflow-hidden">
                <Transition name="fade-slide">
                    <div :key="activeSection" class="space-y-6">
                        <div
                            v-for="(field, index) in currentFields"
                            :key="field.key"
                            class="group flex flex-col gap-2 border-b border-slate-800/50 pb-6 last:border-0"
                        >
                            <BaseSwitch
                                v-if="field.type === 'boolean'"
                                :model-value="!!field.value"
                                @update:model-value="field.value = $event"
                                :label="formatLabel(field.key)"
                                active-text="Включено"
                                inactive-text="Выключено"
                                :disabled="form.processing"
                            />

                            <BaseSelect
                                v-else-if="field.key === 'shop_status'"
                                v-model="field.value"
                                :options="typeOptions"
                                :label="formatLabel(field.key)"
                                valueKey="value"
                                labelKey="label"
                                variant="admin"
                                class="lg:w-64"
                            />

                            <div v-else-if="field.key === 'delivery_zones'">
                                <DeliveryZoneManager
                                    :model-value="asZones(field.value)"
                                    @update:model-value="field.value = $event"
                                />
                            </div>

                            <AdminBaseTextarea
                                v-else-if="field.type === 'json'"
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                :model-value="String(field.value)"
                                @update:model-value="field.value = $event"
                                :error="form.errors[`settings.${index}.value`]"
                                :disabled="form.processing"
                                rows="6"
                            />

                            <AdminNumberInput
                                v-else-if="field.type === 'integer'"
                                :model-value="Number(field.value)"
                                @update:model-value="field.value = Number($event)"
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                v-model:error="form.errors.settings"
                                :min="0"
                                :disabled="form.processing"
                            />

                            <BaseInput
                                v-else
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                :model-value="String(field.value)"
                                @update:model-value="field.value = String($event)"
                                v-model:error="form.errors.settings"
                                :disabled="form.processing"
                            />

                            <p class="text-[10px] italic text-slate-600">
                                Ключ в БД: {{ field.key }}
                            </p>
                        </div>
                    </div>
                </Transition>

                <div
                    class="mt-8 flex flex-col gap-4 border-t border-slate-800 pt-8 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-xs text-slate-500">
                        Изменения применяются сразу ко всей системе
                    </div>

                    <BaseCreateButton
                        type="submit"
                        :label="form.processing ? 'Сохраняем...' : 'Сохранить всё'"
                        :disabled="form.processing"
                    />
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.3s ease;
    }

    .fade-slide-leave-active {
        width: 100%;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(20px);
    }

    .fade-slide-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
