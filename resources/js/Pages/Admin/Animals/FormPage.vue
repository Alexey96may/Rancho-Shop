<script setup lang="ts">
    import AnimalForm from '@/Components/Admin/Sections/AnimalForm.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminAnimal, Category, ResourceSingle } from '@/types';

    defineProps<{
        animal: ResourceSingle<AdminAnimal> | null;
        categories: Category[];
        backUrl: string;
    }>();

    defineOptions({ layout: AdminLayout });
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            :title="animal ? `Редактирование: ${animal.data.name}` : 'Добавление особи'"
            subtitle="Заполните карточку данных"
        />
    </Teleport>

    <div class="p-8">
        <AnimalForm :animal="animal?.data || null" :back-url="backUrl" :categories="categories" />
    </div>
</template>
