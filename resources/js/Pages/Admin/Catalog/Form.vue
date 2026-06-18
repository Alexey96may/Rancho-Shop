<script setup lang="ts">
    import { PropType } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import type { AdminProductVariantDTO, ResourceSingle, UnitAdmin } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        variant: {
            type: Object as PropType<ResourceSingle<AdminProductVariantDTO>>,
            required: false,
            validator: (value: unknown): boolean => {
                if (value === undefined || value === null) return true;

                const val = value as Record<string, unknown>;
                const hasData = 'data' in val && typeof val.data === 'object' && val.data !== null;

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "variant" prop must contain a "data" wrapper object.',
                    );
                }
                return hasData;
            },
        },
        products: {
            type: Array as PropType<{ id: number; name: string }[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;
                return value.every((item: unknown) => {
                    const product = item as Record<string, unknown>;
                    return typeof product?.id === 'number' && typeof product?.name === 'string';
                });
            },
        },
        units: {
            type: Array as PropType<UnitAdmin[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;
                return value.every((item: unknown) => {
                    const unit = item as Record<string, unknown>;
                    return 'id' in unit && 'name' in unit;
                });
            },
        },
        isEdit: {
            type: Boolean as PropType<boolean>,
            required: true,
        },
        backUrl: {
            type: String as PropType<string>,
            required: true,
            validator: (value: unknown): boolean => {
                return typeof value === 'string' && value.length > 0;
            },
        },
    });

    const form = useForm({
        product_id: props.variant?.data.product_id ?? null,
        unit_id: props.variant?.data.unit_id ?? null,
        name: props.variant?.data.name ?? '',
        price: props.variant?.data.price ?? 0,
        old_price: props.variant?.data.old_price ?? null,
        stock: props.variant?.data.stock ?? 0,
        position: props.variant?.data.position ?? 999,
        is_default: props.variant?.data.is_default ?? false,
        attributes: props.variant?.data.attributes ?? {},
        backUrl: props.backUrl,
        create_another: false,
    });

    const { submitForm } = useAdminForm();

    const submit = () => {
        const id = props.isEdit && props.variant ? props.variant.data.id : null;

        submitForm(form, 'admin.catalog', id);
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            :title="isEdit ? 'Редактировать вариант' : 'Новый вариант товара'"
            :subtitle="
                isEdit
                    ? 'для «' + variant?.data.product?.name + '»'
                    : 'Заполните данные для нового торгового предложения'
            "
        />
    </Teleport>

    <div class="mx-auto max-w-5xl space-y-8">
        <BaseCancelButton :href="backUrl" label="Назад" />

        <form @submit.prevent="submit" class="space-y-6">
            <div
                class="grid grid-cols-1 gap-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8 md:grid-cols-12"
            >
                <BaseSelect
                    v-model="form.product_id"
                    v-model:error="form.errors.product_id"
                    :options="products"
                    label="Основной товар"
                    labelKey="name"
                    valueKey="id"
                    placeholder="Выберите товар"
                    class="md:col-span-8"
                    :disabled="form.processing"
                    variant="admin"
                />

                <BaseSelect
                    v-model="form.unit_id"
                    v-model:error="form.errors.unit_id"
                    :options="units"
                    label="Ед. измерения"
                    placeholder="Выберите единицу"
                    labelKey="name"
                    valueKey="id"
                    class="md:col-span-4"
                    variant="admin"
                />

                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="Название варианта"
                    placeholder="Например: 500 мл, XL, Красный"
                    class="md:col-span-12"
                    :disabled="form.processing"
                />

                <AdminNumberInput
                    v-model="form.price"
                    v-model:error="form.errors.price"
                    label="Цена продажи"
                    :is-money="true"
                    class="md:col-span-4"
                    :min="0"
                />

                <AdminNumberInput
                    v-model="form.old_price"
                    v-model:error="form.errors.old_price"
                    label="Старая цена"
                    :is-money="true"
                    class="md:col-span-4"
                    :min="0"
                />

                <AdminNumberInput
                    v-model="form.stock"
                    v-model:error="form.errors.stock"
                    label="Текущий остаток"
                    class="md:col-span-4"
                    :min="0"
                />

                <AdminNumberInput
                    v-model="form.position"
                    v-model:error="form.errors.position"
                    label="Позиция в списке"
                    class="md:col-span-4"
                />

                <div class="flex items-end pb-2 md:col-span-8">
                    <BaseSwitch
                        v-model="form.is_default"
                        label="Вариант по умолчанию"
                        active-text="Главный вариант"
                        inactive-text="Обычный вариант"
                    />
                </div>
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-800/50 pt-8"
            >
                <BaseStatusToggle
                    v-if="!isEdit"
                    v-model="form.create_another"
                    label="После создания не выходить из формы"
                />

                <BaseSubmitButton
                    :processing="form.processing"
                    :is-edit="isEdit"
                    :label="isEdit ? 'Сохранить изменения' : 'Создать вариант'"
                />
            </div>
        </form>
    </div>
</template>
