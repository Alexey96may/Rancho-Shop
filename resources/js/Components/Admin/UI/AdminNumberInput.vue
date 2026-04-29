<script setup lang="ts">
    import { useId, watch } from 'vue';

    import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';

    // Основная модель для числа
    const model = defineModel<number | null | undefined>({ default: 0 });

    // Модель для ошибки (позволяет компоненту "занулять" её в родителе)
    const error = defineModel<string | undefined>('error');

    const props = defineProps<{
        label?: string;
        min?: number;
        max?: number;
        step?: number;
        disabled?: boolean;
    }>();

    const inputId = useId();

    watch(model, () => {
        if (error.value) {
            error.value = undefined;
        }
    });

    const increment = () => {
        if (props.disabled) return;
        const currentValue = model.value ?? 0;
        if (props.max !== undefined && currentValue >= props.max) return;
        model.value = currentValue + (props.step ?? 1);
    };

    const decrement = () => {
        if (props.disabled) return;
        const currentValue = model.value ?? 0;
        if (props.min !== undefined && currentValue <= props.min) return;
        model.value = currentValue - (props.step ?? 1);
    };

    const handleInput = (event: Event) => {
        const target = event.target as HTMLInputElement;
        let val = parseInt(target.value);

        if (isNaN(val)) val = props.min ?? 0;
        if (props.max !== undefined && val > props.max) val = props.max;
        if (props.min !== undefined && val < props.min) val = props.min;

        model.value = val;
    };
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
                @click="decrement"
                tabindex="-1"
                class="flex w-14 items-center justify-center border-r border-slate-800 py-3 text-slate-400 transition-colors hover:bg-slate-900 hover:text-white"
            >
                <MinusIcon class="h-5 w-5" />
            </button>

            <input
                :id="inputId"
                type="text"
                :value="model ?? 0"
                @input="handleInput"
                class="w-full border-none bg-transparent px-3 py-2.5 text-center font-mono font-bold text-white focus:ring-0"
            />

            <button
                type="button"
                @click="increment"
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
