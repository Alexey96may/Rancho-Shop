<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, Link, router } from '@inertiajs/vue3';

    import {
        AlertCircleIcon,
        ArrowUpDownIcon,
        MoreHorizontalIcon,
        PackageIcon,
        PlusIcon,
        SearchIcon,
    } from 'lucide-vue-next';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    interface QuickUpdatePayload {
        price?: number;
        stock?: number;
        is_visible?: boolean;
    }

    const props = defineProps<{
        variants: {
            data: any[];
            links: any[];
            meta: any;
        };
        filters: {
            search?: string;
            stock?: string;
        };
    }>();

    defineOptions({ layout: AdminLayout });

    const search = ref(props.filters.search || '');

    // Быстрое обновление остатка или цены
    const processingId = ref<number | null>(null);

    const quickUpdate = (id: number, data: QuickUpdatePayload) => {
        processingId.value = id;
        router.patch(route('admin.catalog.quick', id), data as any, {
            preserveScroll: true,
            onFinish: () => (processingId.value = null),
        });
    };

    const handleSearch = () => {
        router.get(
            route('admin.catalog.index'),
            { search: search.value },
            {
                preserveState: true,
                replace: true,
            },
        );
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Управление Каталогом</h1>
    </Teleport>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-500"
            >
                <PackageIcon class="h-7 w-7" />
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tighter text-white">Каталог</h1>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">
                    Управление вариантами товаров
                </p>
            </div>
        </div>

        <Link
            :href="route('admin.catalog.create')"
            class="hover:shadow-lg inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500 hover:shadow-orange-900/20 active:scale-95"
        >
            <PlusIcon class="h-4 w-4" /> Добавить товар
        </Link>
    </div>

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="relative md:col-span-3">
                <SearchIcon
                    class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500"
                />
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Поиск по названию или артикулу..."
                    aria-label="Поиск товаров"
                    class="w-full rounded-[1.5rem] border-slate-800 bg-slate-900/40 py-4 pl-12 pr-4 text-sm text-white placeholder:text-slate-600 focus:border-orange-500 focus:ring-orange-500/20"
                />
            </div>
            <div class="flex gap-2">
                <button
                    class="transition-hover flex flex-1 items-center justify-center gap-2 rounded-[1.5rem] border border-slate-800 bg-slate-900/40 text-xs font-bold uppercase text-slate-400 hover:bg-slate-800"
                >
                    <ArrowUpDownIcon class="h-4 w-4" /> Сортировка
                </button>
            </div>
        </div>

        <div
            role="rowgroup"
            class="hidden grid-cols-12 gap-4 px-8 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 lg:grid"
        >
            <div class="col-span-5" role="columnheader">Товар / Вариант</div>
            <div class="col-span-2 text-center" role="columnheader">Цена (₽)</div>
            <div class="col-span-2 text-center" role="columnheader">Остаток</div>
            <div class="col-span-2 text-center" role="columnheader">Статус</div>
            <div class="col-span-1" role="columnheader"></div>
        </div>

        <TransitionGroup tag="div" name="catalog-list" role="list" class="space-y-4">
            <div
                v-for="variant in variants.data"
                :key="variant.id"
                role="listitem"
                class="group relative grid grid-cols-1 items-center gap-4 rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-4 transition-all hover:border-slate-700 hover:bg-slate-900/60 lg:grid-cols-12 lg:px-8 lg:py-4"
                :class="{ 'pointer-events-none opacity-50': processingId === variant.id }"
            >
                <div class="col-span-5 flex items-center gap-4">
                    <div class="relative h-16 w-16 overflow-hidden rounded-2xl bg-slate-800">
                        <img
                            v-if="variant.media?.[0]"
                            :src="variant.media[0].url"
                            :alt="variant.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-slate-600"
                        >
                            <PackageIcon class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h3
                            class="truncate text-sm font-bold text-white transition-colors group-hover:text-orange-500"
                        >
                            {{ variant.product?.name }}
                        </h3>
                        <p class="text-xs font-medium text-slate-500">
                            {{ variant.name }}
                            <span v-if="variant.unit" class="ml-1 text-[10px] uppercase opacity-60"
                                >/ {{ variant.unit.short }}</span
                            >
                        </p>
                    </div>
                </div>

                <div class="col-span-2 flex flex-col items-center justify-center gap-1">
                    <div class="relative">
                        <input
                            type="number"
                            :value="variant.price_rub"
                            @change="
                                (e) =>
                                    quickUpdate(variant.id, {
                                        price:
                                            parseFloat((e.target as HTMLInputElement).value) * 100,
                                    })
                            "
                            class="w-24 rounded-xl border-transparent bg-slate-950/50 text-center text-sm font-black text-orange-500 focus:border-orange-500 focus:ring-0"
                        />
                        <span
                            class="absolute -right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-600"
                            >₽</span
                        >
                    </div>
                </div>

                <div class="col-span-2 flex items-center justify-center">
                    <div class="flex items-center gap-3 rounded-xl bg-slate-950/50 p-1">
                        <button
                            @click="quickUpdate(variant.id, { stock: variant.stock - 1 })"
                            class="transition-hover flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-slate-700"
                            :aria-label="`Уменьшить остаток ${variant.name}`"
                        >
                            -
                        </button>
                        <span class="min-w-[2rem] text-center text-xs font-bold text-white">{{
                            variant.stock
                        }}</span>
                        <button
                            @click="quickUpdate(variant.id, { stock: variant.stock + 1 })"
                            class="transition-hover flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-slate-700"
                            :aria-label="`Увеличить остаток ${variant.name}`"
                        >
                            +
                        </button>
                    </div>
                </div>

                <div class="col-span-2 flex flex-col items-center justify-center gap-2">
                    <div
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest"
                        :class="
                            variant.stock > 0
                                ? 'bg-emerald-500/10 text-emerald-500'
                                : 'bg-rose-500/10 text-rose-500'
                        "
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ variant.stock > 0 ? 'В наличии' : 'Нет на складе' }}
                    </div>
                </div>

                <div class="col-span-1 flex justify-end gap-2">
                    <Link
                        :href="route('admin.catalog.edit', variant.id)"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-800 text-slate-400 transition-all hover:bg-orange-600 hover:text-white"
                        title="Редактировать"
                    >
                        <MoreHorizontalIcon class="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </TransitionGroup>

        <div
            v-if="variants.data.length === 0"
            class="flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-slate-800 py-20 text-slate-500"
        >
            <AlertCircleIcon class="mb-4 h-12 w-12 opacity-20" />
            <p class="text-sm font-bold uppercase tracking-widest">Товары не найдены</p>
        </div>

        <div v-if="variants.meta.last_page > 1" class="flex justify-center gap-2 py-8">
            <Link
                v-for="link in variants.meta.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl border border-slate-800 px-4 text-xs font-bold transition-all"
                :class="
                    link.active
                        ? 'border-orange-500 bg-orange-600 text-white'
                        : 'bg-slate-900 text-slate-500 hover:bg-slate-800'
                "
            />
        </div>
    </div>
</template>

<style scoped>
    .catalog-list-move,
    .catalog-list-enter-active,
    .catalog-list-leave-active {
        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .catalog-list-enter-from,
    .catalog-list-leave-to {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }

    .catalog-list-leave-active {
        position: absolute;
        width: 100%;
    }

    input[type='number']::-webkit-inner-spin-button,
    input[type='number']::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
