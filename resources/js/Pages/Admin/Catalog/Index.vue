<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Link, router } from '@inertiajs/vue3';

    import { debounce } from 'lodash';
    import {
        AlertCircleIcon,
        PackageIcon,
        PlusIcon,
        SearchIcon,
        SlidersHorizontalIcon,
    } from 'lucide-vue-next';

    import AdminCatalogRow from '@/Components/Admin/UI/AdminCatalogRow.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { AdminProductVariantDTO, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        variants: Paginated<AdminProductVariantDTO>;
        filters: { search?: string; stock?: string };
    }>();

    const search = ref(props.filters.search || '');
    const processingId = ref<number | null>(null);

    const quickUpdate = (id: number, data: object) => {
        processingId.value = id;
        router.patch(route('admin.catalog.quick', id), data, {
            preserveScroll: true,
            onFinish: () => (processingId.value = null),
        });
    };

    const handleSearch = debounce(() => {
        router.get(
            route('admin.catalog.index'),
            { search: search.value },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }, 400);

    watch(search, () => handleSearch());
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black uppercase tracking-widest text-white">Прайс-лист</h1>
    </Teleport>

    <div class="space-y-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="shadow-inner flex h-14 w-14 items-center justify-center rounded-[1.5rem] bg-orange-500/10 text-orange-500"
                >
                    <PackageIcon class="h-8 w-8" />
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-tighter text-white">
                        Каталог вариантов
                    </h2>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                        Быстрое управление ценами и остатками
                    </p>
                </div>
            </div>

            <Link
                :href="route('admin.catalog.create')"
                class="shadow-lg inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-orange-900/20 transition-all hover:bg-orange-500 active:scale-95"
            >
                <PlusIcon class="h-4 w-4" /> Добавить вариант
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="relative lg:col-span-3">
                <SearchIcon
                    class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Название товара, варианта или ID..."
                    class="w-full rounded-[2rem] border-slate-800 bg-slate-900/40 py-5 pl-14 pr-6 text-sm text-white transition-all placeholder:text-slate-600 focus:border-orange-500 focus:ring-orange-500/10"
                />
            </div>
            <button
                class="flex items-center justify-center gap-3 rounded-[2rem] border border-slate-800 bg-slate-900/40 text-[10px] font-black uppercase tracking-widest text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
            >
                <SlidersHorizontalIcon class="h-4 w-4" /> Фильтры
            </button>
        </div>

        <div
            class="hidden grid-cols-12 gap-4 px-8 text-[9px] font-black uppercase tracking-[0.3em] text-slate-600 lg:grid"
        >
            <div class="col-span-5">Товар / Характеристики</div>
            <div class="col-span-2 text-center">Цена за ед.</div>
            <div class="col-span-2 text-center">Складской запас</div>
            <div class="col-span-2 text-center">Актуальность</div>
            <div class="col-span-1"></div>
        </div>

        <div class="relative min-h-[400px]">
            <TransitionGroup tag="div" name="catalog-list" class="space-y-3">
                <AdminCatalogRow
                    v-for="variant in variants.data"
                    :key="variant.id"
                    :variant="variant"
                    :disabled="processingId === variant.id"
                    @quick-update="quickUpdate"
                />
            </TransitionGroup>

            <div
                v-if="variants.data.length === 0"
                class="flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-slate-800 py-32 text-slate-700"
            >
                <AlertCircleIcon class="mb-6 h-16 w-16 opacity-10" />
                <p class="text-xs font-black uppercase tracking-[0.3em]">Результатов не найдено</p>
            </div>
        </div>

        <div v-if="variants.meta.last_page > 1" class="flex justify-center gap-2 pt-10">
            <Pagination :links="variants.meta.links" />
        </div>
    </div>
</template>
