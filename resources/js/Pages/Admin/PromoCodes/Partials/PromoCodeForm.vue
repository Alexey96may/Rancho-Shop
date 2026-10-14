<script setup lang="ts">
    import { type PropType } from 'vue';

    import { type InertiaForm, useForm } from '@inertiajs/vue3';

    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseDatePicker from '@/Components/UI/BaseDatePicker.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import type { AdminPromoCode, PromoCodeFormState } from '@/types';

    const props = defineProps({
        promo: {
            type: Object as PropType<AdminPromoCode>,
            required: false,
            default: null,
        },
        isEdit: {
            type: Boolean,
            required: false,
            default: false,
        },
        returnPage: {
            type: String,
            required: false,
            default: '',
        },
        typeOptions: {
            type: Array as PropType<Array<{ value: string; label: string }>>,
            required: true,
            default: () => [],
        },
    });

    const emit = defineEmits<{
        (e: 'submit', form: InertiaForm<PromoCodeFormState>): void;
    }>();

    const form = useForm<PromoCodeFormState>({
        code: props.promo?.code ?? '',
        description: props.promo?.description ?? '',
        type: props.promo?.type ?? 'fixed',
        value: props.promo?.value ?? 0,
        min_order_amount: props.promo?.min_order_amount ?? 0,
        max_discount: props.promo?.max_discount ?? null,
        usage_limit: props.promo?.usage_limit ?? null,
        expires_at: props.promo?.expires_at ?? null,
        is_active: props.promo?.is_active ?? true,

        create_another: false,
        return_page: props.returnPage,
    });

    const handleSubmit = () => {
        emit('submit', form);
    };
</script>

<template>
    <form
        @submit.prevent="handleSubmit"
        class="shadow-2xl space-y-6 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/30 p-4 sm:space-y-8 sm:rounded-3xl sm:p-6 md:p-8"
        aria-labelledby="form-title"
    >
        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 md:gap-8">
            <BaseInput
                v-model="form.code"
                v-model:error="form.errors.code"
                label="Код купона"
                placeholder="SUMMER2026"
                uppercase
                :disabled="form.processing"
                theme="dark"
            />

            <BaseInput
                v-model="form.description"
                v-model:error="form.errors.description"
                label="Описание (необязательно)"
                placeholder="Скидка для постоянных клиентов"
                theme="dark"
            />

            <BaseSelect
                v-model="form.type"
                v-model:error="form.errors.type"
                :options="typeOptions"
                variant="admin"
                label="Тип скидки"
                label-key="label"
                value-key="value"
                class="w-full"
            />

            <AdminNumberInput
                v-model="form.value"
                v-model:error="form.errors.value"
                :min="0"
                :label="'Значение (' + (form.type === 'percent' ? '%' : 'в рублях') + ')'"
                :is-money="form.type === 'fixed'"
                :disabled="form.processing"
            />

            <AdminNumberInput
                v-model="form.min_order_amount"
                v-model:error="form.errors.min_order_amount"
                :is-money="true"
                zero-value="—"
                :min="0"
                label="Мин. сумма заказа (в копейках)"
            />

            <AdminNumberInput
                v-model="form.max_discount"
                v-model:error="form.errors.max_discount"
                :is-money="true"
                zero-value="—"
                :min="0"
                label="Макс. сумма скидки (в копейках)"
            />

            <BaseDatePicker
                v-model="form.expires_at"
                v-model:error="form.errors.expires_at"
                label="Срок действия"
                placeholder="Выберите дату окончания"
                :disabled="form.processing"
            />

            <AdminNumberInput
                v-model="form.usage_limit"
                v-model:error="form.errors.usage_limit"
                :min="0"
                zero-value="—"
                label="Лимит использований"
            />

            <div class="col-span-full pt-2 sm:pt-4">
                <BaseSwitch
                    v-model="form.is_active"
                    label="Статус промокода"
                    active-text="Промокод активен"
                    inactive-text="В черновик"
                    :disabled="form.processing"
                    theme="dark"
                />
            </div>
        </div>

        <div class="flex flex-col gap-8 border-t border-rancho-ink-800/60 pt-6 sm:pt-8">
            <BaseStatusToggle
                v-if="!isEdit"
                v-model="form.create_another"
                label="Создать потом ещё один"
                :disabled="form.processing"
            />

            <div class="flex flex-wrap items-center justify-between gap-2">
                <BaseCancelButton :href="returnPage" label="Назад" theme="dark" />

                <BaseSubmitButton
                    :processing="form.processing"
                    :is-edit="isEdit"
                    :label="isEdit ? 'Сохранить изменения' : 'Создать промокод'"
                />
            </div>
        </div>
    </form>
</template>
