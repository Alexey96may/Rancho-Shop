<script setup lang="ts">
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminPromoCode, ResourceSingle } from '@/types';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    defineProps<{
        promo: ResourceSingle<AdminPromoCode>;
        typeOptions: Array<{ value: string; label: string }>;
    }>();

    defineOptions({ layout: AdminLayout });
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Редактирование: {{ promo.data.code }}</h1>
    </Teleport>
    <PromoCodeForm
        :promo="promo.data"
        :is-edit="true"
        :type-options="typeOptions"
        @submit="(form) => form.put(route('admin.promocodes.update', promo.data.id))"
    />
</template>
