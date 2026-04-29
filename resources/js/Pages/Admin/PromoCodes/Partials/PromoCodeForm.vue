<script setup lang="ts">
    import { useForm } from '@inertiajs/vue3';
    import { InertiaForm } from '@inertiajs/vue3';

    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseDatePicker from '@/Components/UI/BaseDatePicker.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import type { AdminPromoCode, PromoCodeFormState } from '@/types';

    const props = defineProps<{
        promo?: AdminPromoCode;
        isEdit?: boolean;
        returnPage?: number | string;
        typeOptions: Array<{ value: string; label: string }>;
    }>();

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
    });

    const handleSubmit = () => {
        emit('submit', form);
    };
</script>

<template>
    <form
        @submit.prevent="handleSubmit"
        class="shadow-2xl max-w-4xl space-y-8 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
        aria-labelledby="form-title"
    >
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <BaseInput
                v-model="form.code"
                v-model:error="form.errors.code"
                label="Код купона"
                placeholder="SUMMER2026"
                uppercase
                :disabled="form.processing"
            />

            <BaseInput
                v-if="form.description"
                v-model="form.description"
                v-model:error="form.errors.description"
                label="Описание (необязательно)"
                placeholder="Скидка для постоянных клиентов"
            />

            <BaseSelect
                v-model="form.type"
                v-model:error="form.errors.type"
                :options="typeOptions"
                variant="admin"
                label="Тип скидки"
                labelKey="label"
                valueKey="value"
                class="lg:w-64"
            />

            <AdminNumberInput
                v-model="form.value"
                v-model:error="form.errors.value"
                :min="0"
                :label="'Значение ' + '(' + (form.type === 'percent' ? '%' : 'в копейках') + ')'"
                :disabled="form.processing"
            />

            <AdminNumberInput
                v-model="form.min_order_amount"
                v-model:error="form.errors.min_order_amount"
                :min="0"
                label="Мин. сумма заказа (в копейках)"
            />

            <AdminNumberInput
                v-model="form.max_discount"
                v-model:error="form.errors.max_discount"
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
                label="Лимит использований"
            />

            <div class="col-span-full pt-4">
                <BaseSwitch
                    v-model="form.is_active"
                    label="Статус промокода"
                    active-text="Промокод активен"
                    inactive-text="В черновик"
                    :disabled="form.processing"
                />
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-slate-800/50 pt-8">
            <BaseCancelButton
                :route-name="'admin.promocodes.index'"
                :route-params="{ page: returnPage }"
            />

            <BaseSubmitButton
                :processing="form.processing"
                :is-edit="isEdit"
                :label="isEdit ? 'Сохранить изменения' : 'Создать промокод'"
            />

            <BaseStatusToggle
                v-if="!isEdit"
                v-model="form.create_another"
                label="Создать еще один"
                :disabled="form.processing"
            />
        </div>
    </form>
</template>
