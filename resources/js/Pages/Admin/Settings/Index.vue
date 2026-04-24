<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, useForm } from '@inertiajs/vue3';

    import {
        CloudArrowUpIcon,
        Cog6ToothIcon,
        CurrencyDollarIcon,
        DevicePhoneMobileIcon,
        GlobeAltIcon,
        PresentationChartLineIcon,
        TruckIcon,
    } from '@heroicons/vue/24/outline';

    import DeliveryZoneManager from '@/Components/Admin/Shared/DeliveryZoneManager.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        settings: { data: any[] };
    }>();

    // Группируем настройки для отображения в табах
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

    // Подготавливаем форму для bulkUpdate
    const form = useForm({
        settings: props.settings.data.map((s) => ({
            key: s.key,
            value: s.value,
            type: s.type,
        })),
    });

    // Фильтруем поля для текущей секции
    const currentFields = computed(() => {
        const section = sections.find((s) => s.id === activeSection.value);
        return form.settings.filter((s) => section?.keys.includes(s.key));
    });

    const submit = () => {
        form.post(route('admin.settings.bulk'), {
            preserveScroll: true,
            onSuccess: () => {
                // Можно добавить уведомление
            },
        });
    };

    // Хелпер для красивых названий ключей
    const formatLabel = (key: string) => {
        const labels: Record<string, string> = {
            site_name: 'Название сайта',
            site_description: 'Описание (SEO)',
            contact_phone: 'Телефон',
            is_accepting_orders: 'Прием заказов',
            min_order_amount: 'Мин. сумма заказа (коп.)',
            delivery_zones: 'Зоны доставки (JSON)',
            // ... добавь остальные по вкусу
        };
        return labels[key] || key;
    };
</script>

<template>
    <Head title="Настройки системы" />

    <AdminLayout>
        <template #header>Настройки «Молочной Долины»</template>

        <div class="flex flex-col gap-8 lg:flex-row">
            <aside class="w-full lg:w-64">
                <nav class="flex flex-col gap-1" role="tablist">
                    <button
                        v-for="section in sections"
                        :key="section.id"
                        @click="activeSection = section.id"
                        role="tab"
                        :aria-selected="activeSection === section.id"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition-all"
                        :class="
                            activeSection === section.id
                                ? 'shadow-lg bg-orange-600 text-white shadow-orange-900/20'
                                : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                        "
                    >
                        <component :is="section.icon" class="h-5 w-5" />
                        {{ section.label }}
                    </button>
                </nav>
            </aside>

            <main class="flex-1 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <TransitionGroup name="fade-slide" mode="out-in">
                        <div
                            v-for="(field, index) in currentFields"
                            :key="field.key"
                            class="group flex flex-col gap-2 border-b border-slate-800/50 pb-6 last:border-0"
                        >
                            <label
                                :for="field.key"
                                class="text-xs font-black uppercase tracking-widest text-slate-500 transition-colors group-focus-within:text-orange-500"
                            >
                                {{ formatLabel(field.key) }}
                            </label>

                            <div v-if="field.type === 'boolean'" class="flex items-center gap-3">
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
                                <span class="text-sm text-slate-300">{{
                                    field.value ? 'Включено' : 'Выключено'
                                }}</span>
                            </div>

                            <div v-if="field.key === 'delivery_zones'">
                                <DeliveryZoneManager v-model="field.value" />
                            </div>

                            <textarea
                                v-else-if="field.type === 'json'"
                                :id="field.key"
                                v-model="field.value"
                                rows="8"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 font-mono text-sm text-orange-400 focus:border-orange-500 focus:ring-orange-500"
                                @input="
                                    (e) => (field.value = (e.target as HTMLTextAreaElement).value)
                                "
                            ></textarea>

                            <input
                                v-else-if="field.type === 'integer'"
                                type="number"
                                :id="field.key"
                                v-model="field.value"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500"
                            />

                            <input
                                v-else
                                type="text"
                                :id="field.key"
                                v-model="field.value"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500"
                            />

                            <p class="text-[10px] italic text-slate-600">Ключ: {{ field.key }}</p>
                        </div>
                    </TransitionGroup>

                    <div
                        class="mt-8 flex items-center justify-between border-t border-slate-800 pt-8"
                    >
                        <div class="text-xs text-slate-500">
                            Последнее сохранение: <span class="text-slate-300">только что</span>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="hover:shadow-lg flex items-center gap-2 rounded-2xl bg-orange-600 px-8 py-4 text-sm font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500 hover:shadow-orange-900/20 disabled:opacity-50"
                        >
                            <CloudArrowUpIcon class="h-5 w-5" />
                            {{ form.processing ? 'Сохраняем...' : 'Сохранить всё' }}
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </AdminLayout>
</template>

<style scoped>
    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.3s ease;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateX(20px);
    }
    .fade-slide-leave-to {
        opacity: 0;
        transform: translateX(-20px);
    }
</style>
