<script setup lang="ts">
    import { PropType } from 'vue';

    import { InertiaForm } from '@inertiajs/vue3';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { AdminPromoCode, PromoCodeFormState, ResourceSingle } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    const props = defineProps({
        promo: {
            type: Object as PropType<ResourceSingle<AdminPromoCode>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = !!(val?.data && typeof val.data === 'object' && val.data !== null);

                const dataObj = val?.data as Record<string, unknown>;
                const hasId = hasData && 'id' in dataObj;

                if (!hasId) {
                    console.warn(
                        'Runtime Error: The "promo" prop is missing a valid "data" object or "data.id" property.',
                    );
                }
                return hasId;
            },
        },
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

    defineOptions({ layout: AdminLayout });

    const { submitForm } = useAdminForm();

    const handleSubmit = (form: InertiaForm<PromoCodeFormState>) => {
        const id = props.promo.data.id;
        submitForm(form, 'admin.promocodes', id);
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Редактирование промокода" :subtitle="promo.data.code" />

        {{ backUrl }}
    </Teleport>

    <BaseCancelButton :href="backUrl" label="Назад" />

    <PromoCodeForm
        :promo="promo.data"
        :is-edit="true"
        :type-options="typeOptions"
        :return-page="backUrl"
        @submit="handleSubmit"
    />
</template>
