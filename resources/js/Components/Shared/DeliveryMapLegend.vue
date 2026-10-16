<script setup lang="ts">
    interface LegendItem {
        label: string;
        type: 'line' | 'fill' | 'pin';
        color: string;
        borderColor?: string;
    }

    interface Props {
        title?: string;
        position?: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
        items?: LegendItem[];
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Обозначения',
        position: 'bottom-left',
        items: () => [
            // 🟠 Маршрут — ember
            {
                label: 'Маршрут доставки',
                type: 'line',
                color: '#D97706',
            },
            // 🟢 Зона — pine fill + forest border
            {
                label: 'Зона доставки',
                type: 'fill',
                color: '#3B7558',
                borderColor: '#1C3F34',
            },
            // 🏠 Ферма — pine pin + forest обводка
            {
                label: 'Ферма',
                type: 'pin',
                color: '#3B7558',
                borderColor: '#1C3F34',
            },
            // 📍 Ваш адрес — ember pin + тёмно-оранжевая обводка
            {
                label: 'Ваш адрес',
                type: 'pin',
                color: '#D97706',
                borderColor: '#A85A00',
            },
        ],
    });

    const positionClasses: Record<string, string> = {
        'top-left': 'top-3 left-3',
        'top-right': 'top-3 right-3',
        'bottom-left': 'bottom-3 left-3',
        'bottom-right': 'bottom-3 right-3',
    };
</script>

<template>
    <div
        class="shadow-md pointer-events-none absolute z-10 rounded-xl border border-rancho-ink-200 bg-white/95 p-3 backdrop-blur-sm"
        :class="positionClasses[position]"
        aria-label="Легенда карты"
    >
        <h3
            v-if="title"
            class="mb-2 text-center text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
        >
            {{ title }}
        </h3>

        <ul class="space-y-2">
            <li
                v-for="item in items"
                :key="item.label"
                class="flex items-center gap-2.5 text-xs font-medium text-rancho-ink-700"
            >
                <!-- Линия (маршрут) -->
                <span
                    v-if="item.type === 'line'"
                    class="h-1 w-6 shrink-0 rounded-full"
                    :style="{ backgroundColor: item.color }"
                    aria-hidden="true"
                />

                <!-- Полигон (зона) -->
                <span
                    v-else-if="item.type === 'fill'"
                    class="h-3.5 w-6 shrink-0 rounded-sm"
                    :style="{
                        backgroundColor: item.color,
                        opacity: 0.5,
                        border: `1.5px solid ${item.borderColor || item.color}`,
                    }"
                    aria-hidden="true"
                />

                <!-- Пин (маркеры) -->
                <span
                    v-else-if="item.type === 'pin'"
                    class="flex h-4 w-6 shrink-0 items-center justify-center"
                    aria-hidden="true"
                >
                    <svg
                        width="11"
                        height="15"
                        viewBox="0 0 11 15"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M5.5 0.5C2.7 0.5 0.5 2.7 0.5 5.5c0 3.5 5 9 5 9s5-5.5 5-9C10.5 2.7 8.3 0.5 5.5 0.5z"
                            :fill="item.color"
                            :stroke="item.borderColor || item.color"
                            stroke-width="0.8"
                            stroke-linejoin="round"
                        />
                        <circle cx="5.5" cy="5.5" r="2" fill="#FCFAF5" />
                    </svg>
                </span>

                <span>{{ item.label }}</span>
            </li>
        </ul>
    </div>
</template>
