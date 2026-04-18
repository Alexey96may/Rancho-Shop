<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import { MagnifyingGlassIcon, PlusIcon, TicketIcon } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    // Твоя стандартная модалка
    import PromoCodeCard from '@/Components/Admin/Cards/PromoCodeCard.vue';
    import Modal from '@/Components/UI/BaseModal.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        promoCodes: { data: any[] };
        filters: any;
    }>();

    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        code: '',
        type: 'fixed',
        value: 0,
        min_order_amount: 0,
        max_discount: null,
        usage_limit: null,
        expires_at: '',
        is_active: true,
    });

    // Фильтры
    const search = ref(props.filters.search || '');
    watch(
        search,
        debounce(() => {
            router.get(
                route('admin.promo-codes.index'),
                { search: search.value },
                { preserveState: true, replace: true },
            );
        }, 300),
    );

    const openModal = (promo: any = null) => {
        editMode.value = !!promo;
        if (promo) {
            currentId.value = promo.id;
            form.code = promo.code;
            form.type = promo.type;
            form.value = promo.value;
            form.min_order_amount = promo.min_order_amount;
            form.max_discount = promo.max_discount;
            form.usage_limit = promo.usage_limit;
            form.expires_at = promo.expires_at ? promo.expires_at.replace(' ', 'T') : '';
            form.is_active = promo.is_active;
        } else {
            form.reset();
        }
        isModalOpen.value = true;
    };

    const submit = () => {
        const action = editMode.value
            ? route('admin.promo-codes.update', currentId.value!)
            : route('admin.promo-codes.store');

        form.post(action, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    };
</script>

<template>
    <Head title="Промокоды" />

    <AdminLayout>
        <template #header>
            <h1 class="text-2xl font-black uppercase tracking-tighter">Промокоды</h1>
        </template>

        <div class="animate-in fade-in space-y-8 duration-500">
            <div class="flex items-center justify-between gap-4">
                <div class="relative w-full max-w-md">
                    <MagnifyingGlassIcon
                        class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Найти по коду..."
                        class="w-full rounded-[1.5rem] border-slate-800 bg-slate-900/50 py-3.5 pl-12 text-white transition-all focus:border-orange-500"
                    />
                </div>
                <button
                    @click="openModal()"
                    class="shadow-lg flex h-[54px] items-center gap-2 rounded-[1.5rem] bg-orange-600 px-8 text-xs font-black uppercase tracking-[0.2em] text-white shadow-orange-900/20 transition-all hover:bg-orange-500"
                >
                    <PlusIcon class="h-5 w-5" /> Создать
                </button>
            </div>

            <div
                v-if="promoCodes.data.length"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <PromoCodeCard
                    v-for="promo in promoCodes.data"
                    :key="promo.id"
                    :promo="promo"
                    @edit="openModal"
                    @toggle="(p) => router.patch(route('admin.promo-codes.toggle', p.id))"
                    @delete="(p) => router.delete(route('admin.promo-codes.destroy', p.id))"
                />
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-slate-800 py-20"
            >
                <TicketIcon class="mb-4 h-16 w-16 text-slate-800" />
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Промокоды не найдены
                </p>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="2xl">
            <div class="p-8">
                <h3 class="mb-8 text-xl font-black uppercase tracking-tighter">
                    {{ editMode ? 'Редактировать код' : 'Новый промокод' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Код (Капсом)</label
                            >
                            <input
                                v-model="form.code"
                                type="text"
                                required
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold uppercase text-white focus:border-orange-500"
                                placeholder="AUTUMN2026"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Тип скидки</label
                            >
                            <select
                                v-model="form.type"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            >
                                <option value="fixed">Фиксированная (₽)</option>
                                <option value="percent">Процент (%)</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Значение (в копейках/%)</label
                            >
                            <input
                                v-model="form.value"
                                type="number"
                                required
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Мин. сумма заказа</label
                            >
                            <input
                                v-model="form.min_order_amount"
                                type="number"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>

                        <div class="space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Лимит использований</label
                            >
                            <input
                                v-model="form.usage_limit"
                                type="number"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                                placeholder="Без лимита"
                            />
                        </div>

                        <div class="col-span-2 space-y-1">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Срок действия</label
                            >
                            <input
                                v-model="form.expires_at"
                                type="datetime-local"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 rounded-2xl bg-orange-600 py-4 text-[10px] font-black uppercase tracking-widest text-white hover:bg-orange-500"
                        >
                            {{ editMode ? 'Сохранить изменения' : 'Создать промокод' }}
                        </button>
                        <button
                            @click="isModalOpen = false"
                            type="button"
                            class="flex-1 rounded-2xl bg-slate-800 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>
