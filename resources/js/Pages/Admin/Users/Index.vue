<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import { FunnelIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import AdminUserCard from '@/Components/Admin/Cards/AdminUserCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminRoleFilter from '@/Components/Admin/UI/AdminRoleFilter.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminUser, Paginated, RoleInfo } from '@/types';

    const props = defineProps<{
        users: Paginated<AdminUser>;
        roles: RoleInfo[];
        filters: { search: string; role: string | null };
    }>();

    const searchQuery = ref(props.filters.search || '');
    const selectedRole = ref(props.filters.role || null);
    const isFiltering = ref(false);

    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentUserId = ref<number | null>(null);

    const { notifyWithUndo, notify } = useFlash();

    defineOptions({ layout: AdminLayout });

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
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    };

    watch(
        [searchQuery, selectedRole],
        debounce(() => {
            isFiltering.value = true;

            updateFilters();
        }, 300),
    );

    const clearFilters = () => {
        searchQuery.value = '';
        selectedRole.value = '';
    };

    const openCreateModal = () => {
        editMode.value = false;
        currentUserId.value = null;
        form.reset();
        form.clearErrors();
        isModalOpen.value = true;
    };

    const openEditModal = (user: AdminUser) => {
        editMode.value = true;
        currentUserId.value = user.id;
        form.clearErrors();
        form.name = user.name;
        form.email = user.email;
        form.phone = user.phone === 'Не указан' ? '' : user.phone;
        form.role = user.role.value;
        form.password = '';
        isModalOpen.value = true;
    };

    const closeModal = () => {
        isModalOpen.value = false;
        form.reset();
    };

    const submit = () => {
        if (editMode.value) {
            form.put(
                route('admin.users.update', currentUserId.value as number) as unknown as string,
                {
                    onSuccess: () => closeModal(),
                    preserveScroll: true,
                },
            );
        } else {
            form.post(route('admin.users.store'), {
                onSuccess: () => closeModal(),
                preserveScroll: true,
            });
        }
    };

    const deletingIds = ref(new Set<number>());

    const deleteUser = async (user: AdminUser) => {
        if (deletingIds.value.has(user.id)) return;
        deletingIds.value.add(user.id);
        const isTimeOut = await notifyWithUndo(`Удаление пользователя «${user.name}»`);

        if (isTimeOut) {
            router.delete(route('admin.users.destroy', user.id), {
                preserveScroll: true,
                onFinish: () => {
                    deletingIds.value.delete(user.id);
                },
                onError: (e) => {
                    notify(e.error, 'error');
                },
            });
        } else {
            deletingIds.value.delete(user.id);
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            title="Модерация пользователей"
            subtitle="Управление кадрами магазина и пользователями сайта"
        />
    </Teleport>

    <section class="mb-8 space-y-6" aria-label="Инструменты поиска и фильтрации">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div class="relative w-full max-w-md">
                <AdminSearchInput
                    v-model="searchQuery"
                    placeholder="Поиск: имя, почта или телефон"
                />
            </div>

            <button
                @click="openCreateModal"
                class="hover:shadow-lg inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 py-4 text-[11px] font-black uppercase tracking-[0.15em] text-white transition-all hover:bg-orange-500 hover:shadow-orange-600/20 active:scale-95"
            >
                <UserPlusIcon class="h-5 w-5" />
                Добавить сотрудника
            </button>
        </div>

        <div class="flex flex-col gap-4 border-t border-slate-800/50 pt-6">
            <div class="flex items-center gap-2 text-slate-500">
                <FunnelIcon class="h-4 w-4" />
                <h2 class="text-[10px] font-black uppercase tracking-[0.2em]">
                    Быстрый фильтр по ролям
                </h2>
            </div>

            <AdminRoleFilter
                :roles="roles"
                :selected-role="selectedRole"
                @change="(role) => (selectedRole = role)"
            />
        </div>
    </section>

    <main class="relative min-h-[400px]">
        <Transition name="fade-slide">
            <div
                v-if="users.data.length"
                key="users"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <TransitionGroup name="user-list">
                    <AdminUserCard
                        v-for="user in users.data"
                        :key="user.id"
                        :user="user"
                        :disabled="deletingIds.has(user.id) || form.processing"
                        @edit="openEditModal(user)"
                        @delete="deleteUser(user)"
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="searchQuery ? 'Пользователь не найден' : 'Список пользователей пуст'"
                @action="searchQuery ? clearFilters() : openCreateModal()"
                :action-text="searchQuery ? 'Очистить фильтр' : 'Добавить пользователя'"
                :show-action="true"
                :description="
                    searchQuery
                        ? 'По запросу «' + searchQuery + '» совпадений нет'
                        : 'Нет ни одного промокода'
                "
        /></Transition>
    </main>

    <Transition name="fade-slide" mode="out-in">
        <AdminPagination :links="users.meta.links" />
    </Transition>

    <BaseModal
        :show="isModalOpen"
        :title="editMode ? 'Обновить данные' : 'Регистрация нового пользователя'"
        @close="closeModal"
    >
        <form @submit.prevent="submit" class="grid gap-5 p-1">
            <div class="grid grid-cols-1 gap-4">
                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="ФИО Пользователя"
                    placeholder="Вася Пупкин"
                    :disabled="form.processing"
                />

                <BaseInput
                    v-model="form.email"
                    v-model:error="form.errors.email"
                    label="Электронная почта"
                    type="mail"
                    placeholder="example@gmail.ru"
                    :disabled="form.processing"
                />

                <BaseInput
                    v-model="form.phone"
                    v-model:error="form.errors.phone"
                    type="tel"
                    label="Телефонный номер"
                    placeholder="+7 (___) ___-__-__"
                    :disabled="form.processing"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <BaseSelect
                    v-model="form.role"
                    v-model:error="form.errors.role"
                    :options="roles"
                    variant="admin"
                    type="password"
                    label="Назначить роль"
                    labelKey="label"
                    valueKey="value"
                />

                <BaseInput
                    v-model="form.password"
                    v-model:error="form.errors.password"
                    label="Пароль (8 и более символов)"
                    type="password"
                    :placeholder="editMode ? 'Оставьте пустым' : '••••••••'"
                    :disabled="form.processing"
                />
            </div>

            <div class="flex gap-3 pt-6">
                <BaseCancelButton @click="closeModal" />

                <BaseSubmitButton
                    :processing="form.processing"
                    :is-edit="editMode"
                    :label="editMode ? 'Сохранить' : 'Создать'"
                />
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
    .user-list-enter-active,
    .user-list-leave-active {
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .user-list-enter-from {
        opacity: 0;
        transform: translateY(40px) scale(0.9);
    }

    .user-list-leave-to {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
    }

    .user-list-move {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .user-list-leave-active {
        position: absolute;
        width: 100%;
        max-width: 350px;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
