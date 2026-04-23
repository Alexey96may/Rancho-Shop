<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { MapPinIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        modelValue: any; // Это массив зон из настроек
    }>();

    const emit = defineEmits(['update:modelValue']);

    // Берем первую зону (так как в сидере она одна, но закладываем массив)
    const zones = ref(Array.isArray(props.modelValue) ? props.modelValue : []);

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

    // Вспомогательная переменная для текстового редактирования путей
    // Превращаем [[lng, lat], [lng, lat]] в "lng, lat\nlng, lat"
    const getPathText = (path: number[][]) => path.map((point) => point.join(', ')).join('\n');

    const updatePath = (index: number, text: string) => {
        const points = text
            .split('\n')
            .map((line) => line.split(',').map((coord) => parseFloat(coord.trim())))
            .filter((point) => point.length === 2 && !isNaN(point[0]));

        zones.value[index].path = points;
    };

    // Следим за изменениями и отправляем наверх в форму
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
                <div>
                    <label class="text-[10px] font-bold uppercase text-slate-500"
                        >Название маршрута</label
                    >
                    <input
                        v-model="zone.name"
                        type="text"
                        class="mt-1 w-full rounded-xl border-slate-800 bg-slate-900 text-sm text-white focus:ring-orange-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500"
                            >Радиус (м)</label
                        >
                        <input
                            v-model.number="zone.radius"
                            type="number"
                            class="mt-1 w-full rounded-xl border-slate-800 bg-slate-900 text-sm text-white"
                        />
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500"
                            >Приоритет</label
                        >
                        <input
                            v-model.number="zone.priority"
                            type="number"
                            class="mt-1 w-full rounded-xl border-slate-800 bg-slate-900 text-sm text-white"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500"
                            >Цена (коп)</label
                        >
                        <input
                            v-model.number="zone.delivery_price"
                            type="number"
                            class="mt-1 w-full rounded-xl border-slate-800 bg-slate-900 text-sm text-orange-400"
                        />
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500"
                            >Бесплатно от (коп)</label
                        >
                        <input
                            v-model.number="zone.free_from"
                            type="number"
                            class="mt-1 w-full rounded-xl border-slate-800 bg-slate-900 text-sm text-green-400"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        v-model="zone.enabled"
                        :id="'enabled' + zIndex"
                        class="rounded border-slate-800 bg-slate-900 text-orange-600 focus:ring-orange-500"
                    />
                    <label :for="'enabled' + zIndex" class="text-xs text-slate-400"
                        >Зона активна</label
                    >
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label
                    class="flex items-center justify-between text-[10px] font-bold uppercase text-slate-500"
                >
                    <span>Точки маршрута (lng, lat)</span>
                    <span class="text-orange-500">{{ zone.path.length }} точек</span>
                </label>
                <textarea
                    :value="getPathText(zone.path)"
                    @input="(e) => updatePath(zIndex, (e.target as HTMLTextAreaElement).value)"
                    rows="10"
                    placeholder="34.30457, 44.85014"
                    class="w-full rounded-xl border-slate-800 bg-slate-900 font-mono text-xs text-slate-300 focus:ring-orange-500"
                ></textarea>
                <p class="text-[9px] text-slate-600">
                    Каждая точка с новой строки. Формат: Долгота, Широта
                </p>
            </div>
        </div>
    </div>
</template>
