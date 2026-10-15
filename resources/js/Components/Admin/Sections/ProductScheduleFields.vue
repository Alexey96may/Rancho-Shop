<script setup lang="ts">
    import type { Errors } from '@inertiajs/core';

    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseDatePicker from '@/Components/UI/BaseDatePicker.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import type { ProductSchedule } from '@/types';

    const model = defineModel<ProductSchedule>({ required: true });

    const props = defineProps<{
        errors?: Errors;
    }>();
</script>

<template>
    <div class="space-y-8 rounded-xl border border-rancho-ink-800 bg-rancho-ink-900 px-4 py-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-rancho-ink-400">Поставка</h3>

        <div class="grid items-center gap-4 sm:grid-cols-2">
            <BaseDatePicker
                v-model="model.next_delivery"
                :error="errors ? errors['schedule.next_delivery'] : ''"
                label="Ближайшая поставка"
                placeholder="Выберите дату"
            />

            <AdminNumberInput
                v-model.number="model.interval_days"
                :error="errors ? errors['schedule.interval_days'] : ''"
                label="Интервал, дней"
                :min="0"
                :max="365"
                theme="dark"
                zero-value="—"
            />
        </div>

        <BaseTextarea
            v-model="model.note"
            :error="errors ? errors['schedule.note'] : ''"
            label="Комментарий о поставке"
            placeholder="Например: каждую вторую среду"
            :max-height="400"
            theme="dark"
        />
    </div>
</template>
