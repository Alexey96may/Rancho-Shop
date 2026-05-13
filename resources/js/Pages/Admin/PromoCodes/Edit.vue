<script setup lang="ts">
    import { InertiaForm } from '@inertiajs/vue3';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminPromoCode, PromoCodeFormState, ResourceSingle } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    const props = defineProps<{
        promo: ResourceSingle<AdminPromoCode>;
        filters: { page: number | string };
        typeOptions: Array<{ value: string; label: string }>;
    }>();

    defineOptions({ layout: AdminLayout });

    const handleSubmit = (form: InertiaForm<PromoCodeFormState>) => {
        form.transform((data: PromoCodeFormState) => ({
            ...data,
            return_page: props.filters.page,
        })).put(route('admin.promocodes.update', props.promo.data.id));
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Редактирование промокода" :subtitle="promo.data.code" />
    </Teleport>

    <BaseCancelButton
        :route-name="'admin.promocodes.index'"
        :route-params="{ page: filters.page }"
        label="Назад"
    />

    <PromoCodeForm
        :promo="promo.data"
        :is-edit="true"
        :type-options="typeOptions"
        :return-page="filters.page"
        @submit="handleSubmit"
    />
</template>
