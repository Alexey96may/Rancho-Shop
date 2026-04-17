<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import { PencilSquareIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

    import AdminUserCard from '@/Components/Admin/Cards/AdminUserCard.vue';
    import AdminRoleFilter from '@/Components/Admin/UI/AdminRoleFilter.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    // Наш новый компонент
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { User } from '@/types';

    const props = defineProps<{
        users: { data: User[] };
        roles: Array<{ value: string; label: string }>;
        filters: { search: string; role: string | null };
    }>();

    const searchQuery = ref(props.filters.search || '');
    const selectedRole = ref(props.filters.role || null);
    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentUserId = ref<number | null>(null);

    const form = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'customer',
        password: '',
    });

    const updateFilters = () => {
        router.get(
            route('admin.users.index'),
            { search: searchQuery.value, role: selectedRole.value },
            { preserveState: true, replace: true },
        );
    };

    watch([searchQuery, selectedRole], () => updateFilters());

    const openCreateModal = () => {
        editMode.value = false;
        form.reset();
        isModalOpen.value = true;
    };

    const openEditModal = (user: User) => {
        editMode.value = true;
        currentUserId.value = user.id;
        form.name = user.name;
        form.email = user.email;
        form.phone = user.phone;
        form.role = user.role;
        form.password = '';
        isModalOpen.value = true;
    };

    const submit = () => {
        if (editMode.value) {
            if (currentUserId.value === null) return;

            form.put(route('admin.users.update', currentUserId.value), {
                onSuccess: () => closeModal(),
            });
        } else {
            form.post(route('admin.users.store'), {
                onSuccess: () => closeModal(),
            });
        }
    };

    const closeModal = () => {
        isModalOpen.value = false;
        form.reset();
    };
</script>

<template>
    <Head title="Управление персоналом" />

    <AdminLayout>
        <template #header>Пользователи</template>

        <section class="mb-8 space-y-6" aria-label="Фильтры и поиск">
            <div class="flex items-center justify-between gap-4">
                <AdminSearchInput v-model="searchQuery" />

                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-2xl bg-orange-600 px-6 py-3 text-[12px] font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500 active:scale-95"
                    aria-haspopup="dialog"
                >
                    <UserPlusIcon class="h-5 w-5" aria-hidden="true" />
                    Добавить
                </button>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-800/50 pt-6">
                <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600">
                    Фильтр по ролям
                </h2>
                <AdminRoleFilter
                    :roles="roles"
                    :selected-role="selectedRole"
                    @change="(role) => (selectedRole = role)"
                />
            </div>
        </section>

        <main class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <AdminUserCard v-for="user in users.data" :key="user.id" :user="user">
                <template #actions>
                    <button
                        @click="openEditModal(user)"
                        class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors hover:text-white"
                        :aria-label="'Редактировать пользователя ' + user.name"
                    >
                        <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                        Изменить
                    </button>
                </template>
            </AdminUserCard>
        </main>

        <BaseModal
            :show="isModalOpen"
            :title="editMode ? 'Редактировать профиль' : 'Новый сотрудник'"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div v-for="field in ['name', 'email', 'phone'] as const" :key="field">
                    <label
                        :for="field"
                        class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        {{ field === 'name' ? 'Имя' : field === 'email' ? 'Email' : 'Телефон' }}
                    </label>
                    <input
                        :id="field"
                        v-model="form[field]"
                        :type="field === 'email' ? 'email' : 'text'"
                        :aria-invalid="!!form.errors[field]"
                        :aria-describedby="form.errors[field] ? `${field}-error` : ''"
                        class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:ring-orange-500"
                    />
                    <p
                        v-if="form.errors[field]"
                        :id="`${field}-error`"
                        class="mt-1 text-xs text-red-500"
                        role="alert"
                    >
                        {{ form.errors[field] }}
                    </p>
                </div>

                <div>
                    <label
                        for="user-role"
                        class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Роль</label
                    >
                    <select
                        id="user-role"
                        v-model="form.role"
                        class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:ring-orange-500"
                    >
                        <option v-for="role in roles" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Пароль
                        <span v-if="editMode" class="font-normal lowercase text-slate-600"
                            >(оставь пустым, если не меняешь)</span
                        >
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:ring-orange-500"
                    />
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        @click="closeModal"
                        class="flex-1 rounded-xl border border-slate-800 px-6 py-3 text-xs font-bold uppercase text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 rounded-xl bg-orange-600 px-6 py-3 text-xs font-bold uppercase text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                    >
                        {{ editMode ? 'Сохранить' : 'Создать' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </AdminLayout>
</template>
