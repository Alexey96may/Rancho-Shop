<script setup lang="ts">
    import { useId, watch } from 'vue';

    const model = defineModel<string | number | null | undefined>({ default: '' });
    const error = defineModel<string | undefined>('error');

    interface Props {
        label?: string;
        type?: string;
        placeholder?: string;
        disabled?: boolean;
        autofocus?: boolean;
        uppercase?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        type: 'text',
        disabled: false,
        uppercase: false,
    });

    const inputId = useId();
    const errorId = useId();

    watch(model, () => {
        if (error.value) {
            error.value = undefined;
        }
    });
</script>

<template>
    <div class="col-span-full space-y-2" :class="{ 'pointer-events-none opacity-60': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="ml-4 text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="error ? 'text-red-500' : 'text-slate-500'"
        >
            {{ label }}
        </label>

        <input
            :id="inputId"
            v-model="model"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :autofocus="autofocus"
            class="w-full rounded-2xl border bg-slate-950 p-4 font-black text-white transition-all focus:ring-0"
            :class="[
                error
                    ? 'border-red-500/50 focus:border-red-500'
                    : 'border-slate-800 focus:border-orange-500',
                uppercase ? 'uppercase' : '',
            ]"
            :aria-invalid="!!error"
            :aria-describedby="error ? errorId : undefined"
        />

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p
                v-if="error"
                :id="errorId"
                class="ml-4 text-[10px] font-bold uppercase italic text-red-500"
            >
                {{ error }}
            </p>
        </transition>
    </div>
</template>
