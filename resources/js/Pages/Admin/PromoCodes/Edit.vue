<script setup lang="ts">
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminPromoCode, ResourceSingle } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    defineProps<{
        promo: ResourceSingle<AdminPromoCode>;
        filters: { page: number | string };
        typeOptions: Array<{ value: string; label: string }>;
    }>();

    defineOptions({ layout: AdminLayout });

    const handleSubmit = () => {
        // Добавляем return_page в данные формы перед отправкой
        form.transform((data) => ({
            ...data,
            return_page: props.returnPage,
        })).put(route('admin.promocodes.update', props.promo.id));
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
        @submit="(form) => form.put(route('admin.promocodes.update', promo.data.id))"
    />
</template>
