<script setup lang="ts">
    import { InertiaForm } from '@inertiajs/vue3';

    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { PromoCodeFormState } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        typeOptions: Array<{ value: string; label: string }>;
        filters: { page: number | string };
    }>();

    const handleSubmit = (form: InertiaForm<PromoCodeFormState>) => {
        form.transform((data: PromoCodeFormState) => ({
            ...data,
            return_page: props.filters.page,
        })).post(route('admin.promocodes.store'));
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Новый промокод</h1>
    </Teleport>

    <PromoCodeForm :type-options="typeOptions" @submit="handleSubmit" :return-page="filters.page" />
</template>
