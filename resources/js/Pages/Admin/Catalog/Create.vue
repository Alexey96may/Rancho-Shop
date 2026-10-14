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
                    class="border-rancho-ink-800 bg-rancho-ink-900/40 text-rancho-ink-400 hover:bg-rancho-ink-800 focus-visible:ring-rancho-mint/40 flex h-10 w-10 items-center justify-center rounded-xl border transition-all hover:text-white focus:outline-none focus-visible:ring-2"
                >
                    <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                        Новый вариант
                    </h1>
                    <p class="text-rancho-mint text-[10px] font-black uppercase tracking-widest">
                        Добавление позиции в каталог
                    </p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
            <!-- LEFT COLUMN -->
            <div class="space-y-6 lg:col-span-2">
                <!-- ОСНОВНАЯ ИНФОРМАЦИЯ -->
                <div
                    class="border-rancho-ink-800 bg-rancho-ink-900/40 shadow-xl rounded-2xl border p-4 sm:rounded-3xl sm:p-6 md:p-8"
                >
                    <div
                        class="border-rancho-ink-800 mb-6 flex items-center gap-3 border-b pb-4 sm:mb-8 sm:pb-6"
                    >
                        <PackagePlusIcon class="text-rancho-mint h-5 w-5" aria-hidden="true" />
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">
                            Основная информация
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                        <!-- PRODUCT -->
                        <div class="space-y-2">
                            <label
                                for="variant-product"
                                class="text-rancho-ink-400 ml-2 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Основной товар
                            </label>
                            <select
                                id="variant-product"
                                v-model="form.product_id"
                                class="border-rancho-ink-800 bg-rancho-ink-950 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-sm text-white transition-all focus:outline-none focus:ring-2"
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
                            <p
                                v-if="form.errors.product_id"
                                class="ml-2 text-[10px] font-bold uppercase text-rose-500"
                                role="alert"
                            >
                                {{ form.errors.product_id }}
                            </p>
                        </div>

                        <!-- NAME -->
                        <div class="space-y-2">
                            <label
                                for="variant-name"
                                class="text-rancho-ink-400 ml-2 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Название варианта
                            </label>
                            <input
                                id="variant-name"
                                v-model="form.name"
                                type="text"
                                placeholder="Напр. «2 года», «Композиция»"
                                class="border-rancho-ink-800 bg-rancho-ink-950 placeholder:text-rancho-ink-600 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-sm text-white transition-all focus:outline-none focus:ring-2"
                                :class="{ 'border-rose-500': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="ml-2 text-[10px] font-bold uppercase text-rose-500"
                                role="alert"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- PRICE -->
                        <div class="space-y-2">
                            <label
                                for="variant-price"
                                class="text-rancho-ink-400 ml-2 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Цена продажи (₽)
                            </label>
                            <input
                                id="variant-price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="border-rancho-ink-800 bg-rancho-ink-950 placeholder:text-rancho-ink-600 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-sm text-white transition-all focus:outline-none focus:ring-2"
                            />
                        </div>

                        <!-- OLD PRICE -->
                        <div class="space-y-2">
                            <label
                                for="variant-old-price"
                                class="text-rancho-ink-400 ml-2 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Старая цена (для скидки)
                            </label>
                            <input
                                id="variant-old-price"
                                v-model="form.old_price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                class="border-rancho-ink-800 bg-rancho-ink-950 placeholder:text-rancho-ink-600 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-sm text-white transition-all focus:outline-none focus:ring-2"
                            />
                        </div>
                    </div>
                </div>

                <!-- АТРИБУТЫ -->
                <div
                    class="border-rancho-ink-800 bg-rancho-ink-900/40 shadow-xl rounded-2xl border p-4 sm:rounded-3xl sm:p-6 md:p-8"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <LayersIcon class="text-rancho-mint h-5 w-5" aria-hidden="true" />
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">
                            Дополнительные атрибуты
                        </h2>
                    </div>

                    <p class="text-rancho-ink-400 mb-6 text-xs">
                        Здесь можно указать специфические характеристики в формате JSON или добавить
                        поля динамически.
                    </p>

                    <div class="border-rancho-ink-800 bg-rancho-ink-950 rounded-2xl border p-4">
                        <pre class="text-rancho-mint text-[10px]">
// Настройка атрибутов (в разработке)</pre
                        >
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="space-y-6">
                <!-- СТАТУС И НАЛИЧИЕ -->
                <div
                    class="border-rancho-ink-800 bg-rancho-ink-900/40 shadow-xl rounded-2xl border p-4 sm:rounded-3xl sm:p-6 md:p-8"
                >
                    <div class="border-rancho-ink-800 mb-6 border-b pb-4 sm:mb-8 sm:pb-6">
                        <h2
                            class="text-center text-sm font-bold uppercase tracking-wider text-white"
                        >
                            Статус и Наличие
                        </h2>
                    </div>

                    <div class="space-y-5 sm:space-y-6">
                        <!-- STOCK -->
                        <div class="space-y-2">
                            <label
                                for="variant-stock"
                                class="text-rancho-ink-400 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Количество на складе
                            </label>
                            <input
                                id="variant-stock"
                                v-model="form.stock"
                                type="number"
                                class="border-rancho-ink-800 bg-rancho-ink-950 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-center text-xl font-black text-white transition-all focus:outline-none focus:ring-2"
                            />
                        </div>

                        <!-- UNIT -->
                        <div class="space-y-2">
                            <label
                                for="variant-unit"
                                class="text-rancho-ink-400 text-[10px] font-black uppercase tracking-[0.15em]"
                            >
                                Единица измерения
                            </label>
                            <select
                                id="variant-unit"
                                v-model="form.unit_id"
                                class="border-rancho-ink-800 bg-rancho-ink-950 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border px-4 py-3 text-sm text-white transition-all focus:outline-none focus:ring-2"
                            >
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                    {{ unit.name }} ({{ unit.short }})
                                </option>
                            </select>
                        </div>

                        <!-- IS DEFAULT -->
                        <label
                            class="border-rancho-ink-800 bg-rancho-ink-950 hover:border-rancho-ink-700 flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-colors"
                        >
                            <span class="text-rancho-ink-200 text-xs font-bold">
                                Вариант по умолчанию
                            </span>
                            <input
                                v-model="form.is_default"
                                type="checkbox"
                                class="border-rancho-ink-800 bg-rancho-ink-900 text-rancho-mint focus:ring-rancho-mint/40 h-5 w-5 rounded"
                            />
                        </label>

                        <!-- SUBMIT -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-rancho-mint text-rancho-ink-950 shadow-md hover:bg-rancho-mint/90 hover:shadow-lg focus-visible:ring-rancho-mint/40 disabled:hover:bg-rancho-mint disabled:hover:shadow-md flex w-full items-center justify-center gap-3 rounded-2xl py-4 text-xs font-black uppercase tracking-[0.2em] transition-all focus:outline-none focus-visible:ring-4 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:py-5"
                        >
                            <SaveIcon class="h-5 w-5" aria-hidden="true" />
                            {{ form.processing ? 'Сохранение...' : 'Создать вариант' }}
                        </button>
                    </div>
                </div>

                <!-- HINT -->
                <div
                    class="rounded-2xl border border-rancho-sky/20 bg-rancho-sky/5 p-5 sm:rounded-3xl sm:p-6"
                >
                    <div class="flex gap-3">
                        <InfoIcon class="h-5 w-5 shrink-0 text-rancho-sky" aria-hidden="true" />
                        <p class="text-rancho-ink-300 text-[11px] leading-relaxed">
                            Вариант по умолчанию будет первым отображаться в карточке товара в
                            публичной части сайта.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
