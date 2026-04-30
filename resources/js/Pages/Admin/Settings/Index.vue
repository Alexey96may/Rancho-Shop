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
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
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
            onSuccess: () => {
                // Уведомление об успехе
            },
        });
    };

    const labels: Record<string, string> = {
        site_name: 'Название сайта',
        site_description: 'Описание (SEO)',
        header_announcement: 'Текст в шапке',
        contact_phone: 'Телефон',
        contact_email: 'Email',
        contact_telegram: 'Telegram (username)',
        contact_vk: 'VK (ID или ник)',
        address_farm: 'Адрес фермы',
        farm_coords: 'Координаты (JSON)',
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

    const ensurePrimitive = (value: any): string | number => {
        if (typeof value === 'string' || typeof value === 'number') {
            return value;
        }
        return String(value);
    };

    const asAny = (obj: any) => obj;
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Настройки</h1>
    </Teleport>

    <div class="space-y-6">
        <!-- Верхняя навигация (Табсы) -->
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

        <!-- Основная область формы -->
        <main class="min-h-[500px] rounded-3xl border border-slate-800 bg-slate-900/50 p-6 md:p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <TransitionGroup name="fade-slide">
                    <div
                        v-for="field in currentFields"
                        :key="field.key"
                        class="group flex flex-col gap-2 border-b border-slate-800/50 pb-6 last:border-0"
                    >
                        <label
                            :for="field.key"
                            class="text-xs font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-orange-500"
                        >
                            {{ formatLabel(field.key) }}
                        </label>

                        <!-- Поля ввода (логика без изменений) -->
                        <div v-if="field.type === 'boolean'" class="flex items-center gap-3 py-1">
                            <button
                                type="button"
                                @click="field.value = !field.value"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent ring-offset-slate-900 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-orange-500"
                                :class="field.value ? 'bg-orange-600' : 'bg-slate-700'"
                            >
                                <span
                                    class="shadow pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white ring-0 transition duration-200 ease-in-out"
                                    :class="field.value ? 'translate-x-5' : 'translate-x-0'"
                                />
                            </button>
                            <span class="text-sm text-slate-300">
                                {{ field.value ? 'Включено' : 'Выключено' }}
                            </span>
                        </div>

                        <select
                            v-else-if="field.key === 'shop_status'"
                            v-model="field.value"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500"
                        >
                            <option value="open">Открыт (Принимаем заказы)</option>
                            <option value="closed">Закрыт</option>
                            <option value="maintenance">Технические работы</option>
                        </select>

                        <div v-else-if="field.key === 'delivery_zones'">
                            <DeliveryZoneManager v-model="field.value" />
                        </div>

                        <textarea
                            v-else-if="field.type === 'json'"
                            :id="field.key"
                            v-model="field.value as string"
                            rows="5"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 font-mono text-sm text-orange-400 focus:border-orange-500 focus:ring-orange-500"
                        ></textarea>

                        <AdminNumberInput
                            v-else-if="field.type === 'integer'"
                            v-model="asAny(field).value"
                            :id="field.key"
                            v-model:error="form.errors.settings"
                            :min="0"
                            :disabled="form.processing"
                        />

                        <BaseInput
                            v-else
                            :id="field.key"
                            v-model="asAny(field).value"
                            v-model:error="form.errors.settings"
                            :disabled="form.processing"
                        />

                        <p class="text-[10px] italic text-slate-600">Ключ в БД: {{ field.key }}</p>
                    </div>
                </TransitionGroup>

                <!-- Подвал -->
                <div
                    class="mt-8 flex flex-col gap-4 border-t border-slate-800 pt-8 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-xs text-slate-500">
                        Изменения применяются сразу ко всей системе
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="hover:shadow-lg flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500 hover:shadow-orange-900/20 disabled:opacity-50"
                    >
                        <CloudArrowUpIcon class="h-5 w-5" />
                        {{ form.processing ? 'Сохраняем...' : 'Сохранить всё' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
    /* Плавность переключения */
    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.25s ease;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(10px);
    }
    .fade-slide-leave-to {
        opacity: 0;
        transform: translateY(-10px);
    }
    .fade-slide-leave-active {
        position: absolute;
        width: 100%;
        pointer-events: none;
    }

    /* Скрытие стандартного скроллбара для табсов, сохраняя функционал */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
