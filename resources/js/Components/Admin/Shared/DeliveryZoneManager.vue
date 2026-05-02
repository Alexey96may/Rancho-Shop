<script setup lang="ts">
    import { ref, watch } from 'vue';

    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import type { DeliveryZone } from '@/types';

    const props = defineProps<{
        modelValue: DeliveryZone[]; // Это массив зон из настроек
    }>();

    const emit = defineEmits(['update:modelValue']);

    // Берем первую зону (так как в сидере она одна, но закладываем массив)
    const zones = ref<DeliveryZone[]>(
        Array.isArray(props.modelValue) ? JSON.parse(JSON.stringify(props.modelValue)) : [],
    );

    // Если зон нет, создаем пустой скелет по структуре сидера
    if (zones.value.length === 0) {
        zones.value.push({
            name: 'Новый маршрут',
            path: [],
            radius: 700,
            delivery_price: 20000,
            free_from: 150000,
            enabled: true,
            priority: 1,
            max_distance: 20000,
        });
    }

    const getPathText = (path: [number, number][]) => {
        return path.map((point) => point.join(', ')).join('\n');
    };

    const updatePath = (index: number, text: string) => {
        const points = text
            .split('\n')
            .map((line): [number, number] => {
                const coords = line.split(',').map((coord) => parseFloat(coord.trim()));

                return [coords[0] || 0, coords[1] || 0] as [number, number];
            })
            .filter((point) => !isNaN(point[0]) && !isNaN(point[1]));

        zones.value[index].path = points;
    };

    watch(
        zones,
        (newVal) => {
            emit('update:modelValue', newVal);
        },
        { deep: true },
    );
</script>

<template>
    <div
        v-for="(zone, zIndex) in zones"
        :key="zIndex"
        class="space-y-6 rounded-2xl border border-slate-800 bg-slate-950 p-6"
    >
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-4">
                <BaseInput
                    label="Название маршрута"
                    :model-value="String(zone.name)"
                    @update:model-value="zone.name = String($event)"
                />

                <div class="grid grid-cols-2 gap-4">
                    <AdminNumberInput v-model="zone.radius" :min="0" label="Радиус (м)" />

                    <AdminNumberInput v-model="zone.priority" :min="0" label="Приоритет" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <AdminNumberInput
                        v-model="zone.delivery_price"
                        :min="0"
                        label="Цена"
                        :is-money="true"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <BaseStatusToggle v-model="zone.enabled" label="Зона активна" />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <AdminBaseTextarea
                    :label="zone.path.length + ' точек'"
                    :model-value="getPathText(zone.path)"
                    @update:model-value="(e) => updatePath(zIndex, e)"
                    rows="10"
                />
            </div>
        </div>
    </div>
</template>
