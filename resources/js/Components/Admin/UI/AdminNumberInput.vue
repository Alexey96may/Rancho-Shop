<script setup lang="ts">
    import { useId } from 'vue';

    import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';

    const model = defineModel<number>({ default: 0 });

    const props = defineProps<{
        label?: string;
        min?: number;
        max?: number;
        step?: number;
    }>();

    const inputId = useId();

    const increment = () => {
        if (props.max !== undefined && model.value >= props.max) return;
        model.value += props.step ?? 1;
    };

    const decrement = () => {
        if (props.min !== undefined && model.value <= props.min) return;
        model.value -= props.step ?? 1;
    };

    /**
     * Handling keyboard arrow keys (up/down) for input
     */
    const handleKeyDown = (e: KeyboardEvent) => {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            increment();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            decrement();
        }
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
    <div>
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 ml-2 mt-1 text-[10px] font-black uppercase tracking-widest text-slate-500"
        >
            {{ label }}
        </label>

        <div
            class="group relative mt-1 flex max-h-60 w-full overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 transition-all focus-within:border-orange-500/50"
        >
            <button
                type="button"
                @click="decrement"
                tabindex="-1"
                class="flex w-14 items-center justify-center border-r border-slate-800 py-3 text-slate-400 transition-colors hover:bg-slate-900 hover:text-white active:bg-orange-500/10"
                aria-label="Уменьшить значение"
            >
                <MinusIcon class="h-5 w-5" aria-hidden="true" />
            </button>

            <input
                :id="inputId"
                type="text"
                role="spinbutton"
                :aria-valuenow="model"
                :aria-valuemin="min"
                :aria-valuemax="max"
                :value="model"
                @input="handleInput"
                @keydown="handleKeyDown"
                inputmode="numeric"
                class="w-full border-none bg-transparent px-3 py-2.5 text-center font-mono font-bold font-medium text-white focus:ring-0"
            />

            <button
                type="button"
                @click="increment"
                tabindex="-1"
                class="flex w-14 items-center justify-center border-l border-slate-800 text-slate-400 transition-colors hover:bg-slate-900 hover:text-white active:bg-orange-500/10"
                aria-label="Увеличить значение"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>
