<script setup lang="ts">
    import { ref } from 'vue';

    import { Head, useForm } from '@inertiajs/vue3';

    import { PhotoIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        product?: any;
        categories: any[];
        animals: any[];
        units: any[];
    }>();

    const tabs = [
        { id: 'main', name: 'Основное' },
        { id: 'variants', name: 'Цены и фасовка' },
        { id: 'seo', name: 'SEO оптимизация' },
    ];

    const activeTab = ref('main');

    const form = useForm({
        name: props.product?.name ?? '',
        category_id: props.product?.category_id ?? '',
        description: props.product?.description ?? '',
        availability_type: props.product?.availability_type ?? 'in_stock',
        // SEO
        seo: {
            title: props.product?.seo?.title ?? '',
            description: props.product?.seo?.description ?? '',
            keywords: props.product?.seo?.keywords ?? '',
        },
        // Динамические варианты
        variants: props.product?.variants ?? [
            { price: '', old_price: '', weight: '', unit_id: '' },
        ],
    });

    const addVariant = () => {
        form.variants.push({ price: '', old_price: '', weight: '', unit_id: '' });
    };

    const removeVariant = (index: number) => {
        form.variants.splice(index, 1);
    };

    const submit = () => {
        if (props.product) {
            form.put(route('admin.products.update', props.product.id));
        } else {
            form.post(route('admin.products.store'));
        }
    };
</script>

<template>
    <Head :title="product ? 'Редактирование' : 'Новый товар'" />

    <AdminLayout>
        <template #header>
            {{ product ? 'Редактировать товар' : 'Добавить товар' }}
        </template>

        <form @submit.prevent="submit" class="max-w-5xl">
            <nav class="mb-8 flex gap-8 border-b border-slate-800" role="tablist">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.id"
                    @click="activeTab = tab.id"
                    class="pb-4 text-xs font-black uppercase tracking-widest transition-colors"
                    :class="
                        activeTab === tab.id
                            ? 'border-b-2 border-orange-500 text-white'
                            : 'text-slate-500 hover:text-slate-300'
                    "
                >
                    {{ tab.name }}
                </button>
            </nav>

            <div v-show="activeTab === 'main'" class="space-y-6" role="tabpanel">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Название товара</label
                            >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-2xl border-slate-800 bg-slate-900 text-white focus:border-orange-500 focus:ring-orange-500/20"
                            />
                        </div>

                        <div>
                            <label
                                for="category"
                                class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Категория</label
                            >
                            <select
                                id="category"
                                v-model="form.category_id"
                                class="w-full rounded-2xl border-slate-800 bg-slate-900 text-white"
                            >
                                <option value="">Выберите категорию</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            for="desc"
                            class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                            >Описание</label
                        >
                        <textarea
                            id="desc"
                            v-model="form.description"
                            rows="5"
                            class="w-full rounded-2xl border-slate-800 bg-slate-900 text-white"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'seo'" class="space-y-6" role="tabpanel">
                <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-8">
                    <div class="space-y-4">
                        <div>
                            <label
                                for="seo_title"
                                class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Meta Title</label
                            >
                            <input
                                id="seo_title"
                                v-model="form.seo.title"
                                type="text"
                                class="w-full rounded-2xl border-slate-800 bg-slate-900 text-white"
                                aria-describedby="seo_title_hint"
                            />
                            <p id="seo_title_hint" class="mt-2 text-[10px] text-slate-600">
                                Рекомендуется до 60 символов.
                            </p>
                        </div>
                        <div>
                            <label
                                for="seo_desc"
                                class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Meta Description</label
                            >
                            <textarea
                                id="seo_desc"
                                v-model="form.seo.description"
                                rows="3"
                                class="w-full rounded-2xl border-slate-800 bg-slate-900 text-white"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'variants'" class="space-y-6" role="tabpanel">
                <TransitionGroup name="list" tag="div" class="space-y-6">
                    <div
                        v-for="(variant, index) in form.variants"
                        :key="index"
                        class="relative rounded-3xl border border-slate-800 bg-slate-900 p-6 transition-all"
                    >
                        <button
                            v-if="form.variants.length > 1"
                            @click="removeVariant(index as number)"
                            type="button"
                            class="shadow-lg absolute -right-2 -top-2 z-10 rounded-full bg-red-500 p-1 text-white transition-transform hover:bg-red-600 active:scale-90"
                            aria-label="Удалить вариант"
                        >
                            <TrashIcon class="h-4 w-4" />
                        </button>

                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-black uppercase text-slate-500"
                                    >Цена</label
                                >
                                <input
                                    v-model="variant.price"
                                    type="number"
                                    class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:ring-orange-500/20"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-black uppercase text-slate-500"
                                    >Вес/Объем</label
                                >
                                <input
                                    v-model="variant.weight"
                                    type="text"
                                    class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:ring-orange-500/20"
                                />
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <button
                    @click="addVariant"
                    type="button"
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-orange-500 transition-colors hover:text-orange-400"
                >
                    <PlusIcon class="h-4 w-4" />
                    Добавить вариант цены
                </button>
            </div>

            <div class="mt-12 flex gap-4 border-t border-slate-800 pt-8">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-2xl bg-orange-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white hover:bg-orange-500 disabled:opacity-50"
                >
                    {{ product ? 'Сохранить изменения' : 'Создать товар' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
    .list-enter-active,
    .list-leave-active {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .list-enter-from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }

    .list-leave-to {
        opacity: 0;
        transform: scale(0.95);
    }

    .list-move {
        transition: transform 0.4s ease;
    }
</style>
