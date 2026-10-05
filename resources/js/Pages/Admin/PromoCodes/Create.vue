<script setup lang="ts">
    import { PropType } from 'vue';

    import { InertiaForm } from '@inertiajs/vue3';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import type { PromoCodeFormState } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        typeOptions: {
            type: Array as PropType<Array<{ value: string; label: string }>>,
            required: true,
            default: () => [],
        },
        backUrl: {
            type: String,
            required: true,
        },
    });

    const { submitForm } = useAdminForm();

    const handleSubmit = (form: InertiaForm<PromoCodeFormState>) => {
        submitForm(form, 'admin.promocodes', null, {
            onSuccess: () =>
                form.reset('code', 'value', 'expires_at', 'max_discount', 'usage_limit'),
        });
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Новый промокод" />
    </Teleport>

    <BaseCancelButton :href="backUrl" label="Назад" />

    <PromoCodeForm :type-options="typeOptions" @submit="handleSubmit" :return-page="backUrl" />
</template>
