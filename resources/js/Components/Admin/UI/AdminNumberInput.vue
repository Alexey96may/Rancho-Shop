<script setup lang="ts">
    import { computed, onBeforeUnmount, ref, useId, watch } from 'vue';

    import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';

    const model = defineModel<number | null | undefined>({ default: 0 });
    const error = defineModel<string | undefined>('error');

    const props = defineProps<{
        label?: string;
        min?: number;
        max?: number;
        step?: number;
        disabled?: boolean;
        isMoney?: boolean;
    }>();

    const inputId = useId();
    const isFocused = ref(false); // Состояние фокуса
    const timer = ref<ReturnType<typeof setInterval> | null>(null);

    // Внутреннее вычисляемое значение для логики (рубли <-> копейки)
    const rawValue = computed({
        get() {
            const val = model.value ?? 0;
            return props.isMoney ? val / 100 : val;
        },
        set(newValue: number) {
            model.value = props.isMoney ? Math.round(newValue * 100) : newValue;
        },
    });

    // Красивое форматирование для вывода (1 250 ₽)
    const formattedValue = computed(() => {
        if (isFocused.value) return rawValue.value.toString();

        const formatter = new Intl.NumberFormat('ru-RU', {
            style: props.isMoney ? 'currency' : 'decimal',
            currency: 'RUB',
            minimumFractionDigits: 0,
            maximumFractionDigits: props.isMoney ? 2 : 0,
        });

        let display = formatter.format(rawValue.value);

        // Убираем лишние ".00" для рублей, если число целое
        if (props.isMoney) {
            display = display.replace(',00', '');
        }

        return display;
    });

    watch(model, () => {
        if (error.value) error.value = undefined;
    });

    const increment = () => {
        if (props.disabled) return;
        const current = rawValue.value;
        if (props.max !== undefined && current >= props.max) return;
        rawValue.value = current + (props.step ?? 1);
    };

    const decrement = () => {
        if (props.disabled) return;
        const current = rawValue.value;
        if (props.min !== undefined && current <= props.min) return;
        rawValue.value = current - (props.step ?? 1);
    };

    // Навигация и ввод
    const onKeyDown = (event: KeyboardEvent) => {
        const allowedKeys = [
            'Backspace',
            'Delete',
            'Tab',
            'Escape',
            'Enter',
            'ArrowLeft',
            'ArrowRight',
            '.',
            ',',
        ];
        if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) return;
        if (!/^\d$/.test(event.key)) event.preventDefault();
    };

    const handleInput = (event: Event) => {
        const target = event.target as HTMLInputElement;
        let valStr = target.value.replace(',', '.'); // замена запятой на точку для parseFloat
        let val = props.isMoney ? parseFloat(valStr) : parseInt(valStr);

        if (isNaN(val)) val = props.min ?? 0;
        if (props.max !== undefined && val > props.max) val = props.max;
        if (props.min !== undefined && val < props.min) val = props.min;

        rawValue.value = val;
    };

    // Таймеры для зажатия кнопок
    const startHold = (action: () => void) => {
        if (props.disabled) return;
        action();
        timer.value = setTimeout(() => {
            timer.value = setInterval(action, 60);
        }, 300) as any;
        window.addEventListener('mouseup', stopHold, { once: true });
    };

    const stopHold = () => {
        if (timer.value) {
            clearTimeout(timer.value);
            clearInterval(timer.value);
            timer.value = null;
        }
    };

    onBeforeUnmount(stopHold);
</script>

<template>
    <div class="w-full" :class="{ 'pointer-events-none opacity-50': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 ml-2 mt-1 block text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="error ? 'text-red-500' : 'text-slate-500'"
        >
            {{ label }}
        </label>

        <div
            class="group relative flex w-full overflow-hidden rounded-2xl border bg-slate-950 transition-all"
            :class="[
                error
                    ? 'border-red-500/50 focus-within:border-red-500'
                    : 'border-slate-800 focus-within:border-orange-500/50',
            ]"
        >
            <button
                type="button"
                @mousedown="startHold(decrement)"
                @mouseleave="stopHold"
                tabindex="-1"
                class="flex w-14 items-center justify-center border-r border-slate-800 py-3 text-slate-400 transition-colors hover:bg-slate-900 hover:text-white"
            >
                <MinusIcon class="h-5 w-5" />
            </button>

            <input
                :id="inputId"
                type="text"
                :value="formattedValue"
                @focus="isFocused = true"
                @blur="isFocused = false"
                @input="handleInput"
                @keydown="onKeyDown"
                inputmode="decimal"
                class="w-full border-none bg-transparent px-3 py-2.5 text-center font-mono font-bold text-white focus:ring-0"
            />

            <button
                type="button"
                @mousedown="startHold(increment)"
                @mouseleave="stopHold"
                tabindex="-1"
                class="flex w-14 items-center justify-center border-l border-slate-800 text-slate-400 transition-colors hover:bg-slate-900 hover:text-white"
            >
                <PlusIcon class="h-5 w-5" />
            </button>
        </div>

        <p v-if="error" class="ml-2 mt-1.5 text-[10px] font-bold uppercase text-red-500">
            {{ error }}
        </p>
    </div>
</template>
