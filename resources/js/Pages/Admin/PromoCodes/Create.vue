<script setup lang="ts">
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    import PromoCodeForm from './Partials/PromoCodeForm.vue';

    defineOptions({ layout: AdminLayout });

    defineProps<{
        typeOptions: Array<{ value: string; label: string }>;
    }>();
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Новый промокод</h1>
    </Teleport>

    <PromoCodeForm
        :type-options="typeOptions"
        @submit="(form) => form.post(route('admin.promocodes.store'))"
    />
</template>
