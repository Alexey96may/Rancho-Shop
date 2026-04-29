<script setup lang="ts">
    import { InertiaForm } from '@inertiajs/vue3';

    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
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
        })).post(route('admin.promocodes.store'), {
            onSuccess: () =>
                form.reset('code', 'value', 'expires_at', 'max_discount', 'usage_limit'),
        });
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Новый промокод</h1>
    </Teleport>

    <BaseCancelButton
        :route-name="'admin.promocodes.index'"
        :route-params="{ page: filters.page }"
        label="Назад"
    />

    <PromoCodeForm :type-options="typeOptions" @submit="handleSubmit" :return-page="filters.page" />
</template>
