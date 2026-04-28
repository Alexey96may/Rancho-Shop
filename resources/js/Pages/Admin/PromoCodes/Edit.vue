<script setup lang="ts">
    import { InertiaForm } from '@inertiajs/vue3';

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
        <h1 class="text-xl font-black text-white">Редактирование: {{ promo.data.code }}</h1>
    </Teleport>

    <PromoCodeForm
        :promo="promo.data"
        :is-edit="true"
        :type-options="typeOptions"
        :return-page="filters.page"
        @submit="handleSubmit"
    />
</template>
