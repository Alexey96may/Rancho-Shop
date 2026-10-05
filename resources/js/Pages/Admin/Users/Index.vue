<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { FunnelIcon, UserPlusIcon } from '@heroicons/vue/24/outline';

    import AdminUserCard from '@/Components/Admin/Cards/AdminUserCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminRoleFilter from '@/Components/Admin/UI/AdminRoleFilter.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { AdminUser, Paginated, RoleInfo, UserRole } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        users: {
            type: Object as PropType<Paginated<AdminUser>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "users" prop must match the Paginated structure.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        roles: {
            type: Array as PropType<RoleInfo[]>,
            required: true,
            default: () => [],
        },
        filters: {
            type: Object as PropType<{ search: string; role: UserRole | null }>,
            required: true,
            default: () => ({ search: '', role: null }),
        },
    });

    const form = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'customer',
        password: '',
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        role: props.filters.role || null,
    });

    const handleEditClick = (user: AdminUser) => {
        const formData = {
            ...user,
            role: user.role.value,
        };

        openModal(form, formData);
    };

    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();
    const { submitForm, isModalOpen, editMode, currentId, openModal, closeModal } = useAdminForm();

    const submit = () => {
        submitForm(form, 'admin.users', editMode.value ? currentId.value : null, {
            onSuccess: () => closeModal(form),
        });
    };

    watch(
        () => [filterForm.search, filterForm.role],
        () => {
            submitFilters(filterForm, 'admin.users.index');
        },
    );
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
                    v-model="filterForm.search"
                    placeholder="Поиск: имя, почта или телефон..."
                />
            </div>

            <BaseCreateButton @click="openModal(form)" label="Добавить" :icon="UserPlusIcon" />
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
                :selected-role="filterForm.role"
                @change="(role) => (filterForm.role = role)"
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
                        :disabled="isDeleting(user.id) || form.processing"
                        @edit="handleEditClick(user)"
                        @delete="
                            deleteEntity(
                                'admin.users',
                                user.id,
                                `Удаление пользователя «${user.name}»`,
                            )
                        "
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="filterForm.search ? 'Пользователь не найден' : 'Список пользователей пуст'"
                @action="filterForm.search ? clearFilters(filterForm) : openModal(form)"
                :action-text="filterForm.search ? 'Очистить фильтр' : 'Добавить пользователя'"
                :show-action="true"
                :description="
                    filterForm.search
                        ? 'По запросу «' + filterForm.search + '» совпадений нет'
                        : 'Нет ни одного промокода'
                "
        /></Transition>
    </main>

    <Transition name="fade-slide" mode="out-in">
        <AdminPagination v-show="!isFiltering" :links="users.meta.links" />
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
