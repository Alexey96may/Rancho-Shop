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
    const isFocused = ref(false);
    const timer = ref<ReturnType<typeof setInterval> | null>(null);

    const localValue = ref<number>(props.isMoney ? (model.value ?? 0) / 100 : (model.value ?? 0));

    watch(
        () => model.value,
        (newVal) => {
            if (!isFocused.value) {
                localValue.value = props.isMoney ? (newVal ?? 0) / 100 : (newVal ?? 0);
            }
        },
        { immediate: true },
    );

    const displayValue = computed(() => {
        if (isFocused.value) return localValue.value.toString();

        const formatter = new Intl.NumberFormat('ru-RU', {
            style: props.isMoney ? 'currency' : 'decimal',
            currency: 'RUB',
            minimumFractionDigits: 0,
            maximumFractionDigits: props.isMoney ? 2 : 0,
        });

        let res = formatter.format(localValue.value);
        return props.isMoney ? res.replace(',00', '') : res;
    });

    const commit = () => {
        let val = localValue.value;

        if (props.max !== undefined && val > props.max) val = props.max;
        if (props.min !== undefined && val < props.min) val = props.min;

        localValue.value = val;

        const finalValue = props.isMoney ? Math.round(val * 100) : val;

        if (finalValue !== model.value) {
            model.value = finalValue;
        }
    };

    const handleInput = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const val = parseFloat(target.value.replace(',', '.'));
        if (!isNaN(val)) localValue.value = val;
    };

    const increment = () => {
        if (props.disabled) return;
        localValue.value += props.step ?? 1;
        commit();
    };

    const decrement = () => {
        if (props.disabled) return;
        localValue.value -= props.step ?? 1;
        commit();
    };

    const startHold = (action: () => void) => {
        if (props.disabled) return;

        action();
        timer.value = setTimeout(() => {
            timer.value = setInterval(action, 80);
        }, 300) as any;

        if (typeof window !== 'object') return;
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
            class="text-rancho-ink-500 mb-1.5 block px-1 text-[10px] font-black uppercase tracking-widest"
        >
            {{ label }}
        </label>

        <div
            class="border-rancho-ink-800 bg-rancho-ink-950 focus-within:border-rancho-mint/50 group relative flex w-full overflow-hidden rounded-2xl border"
        >
            <button
                type="button"
                @mousedown="startHold(decrement)"
                class="border-rancho-ink-800 text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-rancho-mint flex w-12 items-center justify-center border-r"
            >
                <MinusIcon class="h-4 w-4" />
            </button>

            <input
                :id="inputId"
                :value="displayValue"
                @focus="isFocused = true"
                @blur="
                    isFocused = false;
                    commit();
                "
                @input="handleInput"
                @keyup.enter="(e: any) => e.target.blur()"
                class="w-full border-none bg-transparent px-2 py-2 text-center font-mono text-sm font-bold text-white focus:ring-0"
            />

            <button
                type="button"
                @mousedown="startHold(increment)"
                class="border-rancho-ink-800 text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-rancho-mint flex w-12 items-center justify-center border-l"
            >
                <PlusIcon class="h-4 w-4" />
            </button>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <p v-if="error" class="mt-1.5 px-1 text-xs text-rose-500">
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
