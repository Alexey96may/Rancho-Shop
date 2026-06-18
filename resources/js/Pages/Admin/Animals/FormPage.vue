<script setup lang="ts">
    import { PropType } from 'vue';

    import AnimalForm from '@/Components/Admin/Sections/AnimalForm.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminAnimal, Category, ResourceSingle } from '@/types';

    defineOptions({ layout: AdminLayout });

    defineProps({
        animal: {
            type: Object as PropType<ResourceSingle<AdminAnimal> | null>,
            required: true,
            validator: (value: unknown): boolean => {
                if (value === null) return true;

                const val = value as Record<string, unknown>;
                const hasData = 'data' in val && typeof val.data === 'object' && val.data !== null;

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "animal" prop is expected to be null or an object containing a "data" wrapper.',
                    );
                }

                return hasData;
            },
        },
        categories: {
            type: Array as PropType<Category[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;

                return value.every((item: unknown) => {
                    const category = item as Record<string, unknown>;
                    const isValid = 'id' in category && 'name' in category;

                    if (!isValid) {
                        console.warn(
                            'Runtime Error: Element in "categories" array is missing an "id" or "name".',
                            category,
                        );
                    }
                    return isValid;
                });
            },
        },
        backUrl: {
            type: String as PropType<string>,
            required: true,
            validator: (value: unknown): boolean => {
                return typeof value === 'string' && value.length > 0;
            },
        },
    });
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
