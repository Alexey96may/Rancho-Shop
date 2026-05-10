<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import { PlusIcon } from '@heroicons/vue/24/outline';
    import { debounce } from 'lodash';

    import AdminProductCard from '@/Components/Admin/Cards/AdminProductCard.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        products: { data: any[]; meta: any };
        filters?: { search?: string };
    }>();

    const searchQuery = ref(props.filters?.search || '');
    const isDeleteModalOpen = ref(false);
    const productToDelete = ref<number | null>(null);

    // Фильтрация (Inertia request)
    const updateFilters = debounce(() => {
        router.get(
            route('admin.products.index'),
            { search: searchQuery.value },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }, 300);

    watch(searchQuery, () => updateFilters());

    // Удаление
    const confirmDelete = (id: number) => {
        productToDelete.value = id;
        isDeleteModalOpen.value = true;
    };

    const deleteProduct = () => {
        if (!productToDelete.value) return;

        router.delete(route('admin.products.destroy', productToDelete.value), {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                productToDelete.value = null;
            },
        });
    };
</script>

<template>
    <Head title="Товары - Ранчо" />

    <AdminLayout>
        <template #header>Товары</template>

        <section
            class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            aria-label="Управление списком"
        >
            <AdminSearchInput
                v-model="searchQuery"
                placeholder="Поиск по названию или артикулу..."
            />

            <button
                @click="router.get(route('admin.products.create'))"
                class="shadow-lg inline-flex items-center gap-2 rounded-2xl bg-orange-600 px-6 py-3 text-[12px] font-black uppercase tracking-widest text-white shadow-orange-600/20 transition-all hover:bg-orange-500 active:scale-95"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Создать товар
            </button>
        </section>

        <div v-for="num in 6">{{ num }}</div>
        <main
            v-if="products.data.length"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <AdminProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
                @edit="(p) => router.get(route('admin.products.edit', p.id))"
                @delete="confirmDelete"
            />
        </main>

        <div
            v-else
            class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 py-20"
        >
            <p class="text-slate-500">Товары не найдены</p>
        </div>

        <BaseModal
            :show="isDeleteModalOpen"
            title="Подтвердите удаление"
            @close="isDeleteModalOpen = false"
        >
            <div class="space-y-6">
                <p class="text-sm text-slate-400">
                    Вы уверены, что хотите удалить этот товар? Это действие нельзя будет отменить,
                    товар исчезнет из витрины магазина.
                </p>

                <div class="flex gap-3">
                    <button
                        @click="isDeleteModalOpen = false"
                        class="flex-1 rounded-xl border border-slate-800 py-3 text-xs font-bold uppercase text-slate-400 hover:bg-slate-800"
                    >
                        Отмена
                    </button>
                    <button
                        @click="deleteProduct"
                        class="flex-1 rounded-xl bg-red-600 py-3 text-xs font-bold uppercase text-white hover:bg-red-500"
                    >
                        Удалить
                    </button>
                </div>
            </div>
        </BaseModal>
    </AdminLayout>
</template>
