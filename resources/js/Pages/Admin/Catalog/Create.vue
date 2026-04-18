<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        InfoIcon,
        LayersIcon,
        PackagePlusIcon,
        SaveIcon,
    } from 'lucide-vue-next';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        products: Array<{ id: number; name: string }>;
        units: Array<{ id: number; short: string; name: string }>;
    }>();

    const form = useForm({
        product_id: '',
        unit_id: '',
        name: '',
        price: null as number | null,
        old_price: null as number | null,
        stock: 0,
        is_default: false,
        position: 0,
        attributes: {} as Record<string, any>,
    });

    const submit = () => {
        form.post(route('admin.catalog.store'));
    };
</script>

<template>
    <Head title="Добавление варианта товара" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.catalog.index')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900/40 text-slate-400 transition-all hover:bg-slate-800 hover:text-white"
                >
                    <ChevronLeftIcon class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                        Новый вариант
                    </h1>
                    <p class="text-[10px] font-black uppercase tracking-widest text-orange-500">
                        Добавление позиции в каталог
                    </p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="shadow-xl rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8">
                    <div class="mb-8 flex items-center gap-3 border-b border-slate-800 pb-6">
                        <PackagePlusIcon class="h-5 w-5 text-orange-500" />
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">
                            Основная информация
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Основной товар</label
                            >
                            <select
                                v-model="form.product_id"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                                :class="{ 'border-rose-500': form.errors.product_id }"
                            >
                                <option value="" disabled>Выберите товар...</option>
                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.product_id" class="ml-2 text-[10px] text-rose-500">
                                {{ form.errors.product_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Название варианта (напр. "2 года", "Композиция")</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Введите название..."
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                                :class="{ 'border-rose-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="ml-2 text-[10px] text-rose-500">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Цена продажи (₽)</label
                            >
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                            />
                        </div>

                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Старая цена (для скидки)</label
                            >
                            <input
                                v-model="form.old_price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                            />
                        </div>
                    </div>
                </div>

                <div class="shadow-xl rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8">
                    <div class="mb-6 flex items-center gap-3">
                        <LayersIcon class="h-5 w-5 text-orange-500" />
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">
                            Дополнительные атрибуты
                        </h2>
                    </div>
                    <p class="mb-6 text-xs text-slate-500">
                        Здесь можно указать специфические характеристики в формате JSON или добавить
                        поля динамически.
                    </p>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
                        <pre class="text-[10px] text-orange-400">
// Настройка атрибутов (в разработке)</pre
                        >
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="shadow-xl rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8">
                    <div class="mb-8 border-b border-slate-800 pb-6">
                        <h2
                            class="text-center text-sm font-bold uppercase tracking-wider text-white"
                        >
                            Статус и Наличие
                        </h2>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Количество на складе</label
                            >
                            <input
                                v-model="form.stock"
                                type="number"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-center text-xl font-black text-white focus:border-orange-500 focus:ring-orange-500/20"
                            />
                        </div>

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Единица измерения</label
                            >
                            <select
                                v-model="form.unit_id"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                            >
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                    {{ unit.name }} ({{ unit.short }})
                                </option>
                            </select>
                        </div>

                        <label
                            class="transition-hover flex cursor-pointer items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4 hover:border-slate-700"
                        >
                            <span class="text-xs font-bold text-slate-300"
                                >Вариант по умолчанию</span
                            >
                            <input
                                v-model="form.is_default"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-800 bg-slate-900 text-orange-600 focus:ring-orange-500"
                            />
                        </label>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-orange-600 py-5 text-xs font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                        >
                            <SaveIcon class="h-5 w-5" />
                            {{ form.processing ? 'Сохранение...' : 'Создать вариант' }}
                        </button>
                    </div>
                </div>

                <div class="rounded-3xl border border-blue-500/10 bg-blue-500/5 p-6">
                    <div class="flex gap-3">
                        <InfoIcon class="h-5 w-5 shrink-0 text-blue-500" />
                        <p class="text-[11px] leading-relaxed text-blue-200/60">
                            Вариант по умолчанию будет первым отображаться в карточке товара в
                            публичной части сайта.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
