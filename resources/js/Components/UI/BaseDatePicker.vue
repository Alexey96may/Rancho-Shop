<script setup lang="ts">
    import { useId, watch } from 'vue';

    import { CalendarIcon, XMarkIcon } from '@heroicons/vue/24/outline';
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';

    const model = defineModel<any>();
    const error = defineModel<string | undefined>('error');

    interface Props {
        label?: string;
        placeholder?: string;
        disabled?: boolean;
        mode?: 'date' | 'dateTime' | 'time';
    }

    const props = withDefaults(defineProps<Props>(), {
        placeholder: 'Выберите дату и время',
        disabled: false,
        mode: 'dateTime',
    });

    const inputId = useId();
    const errorId = useId();

    watch(model, () => {
        if (error.value) {
            error.value = undefined;
        }
    });

    const clearDate = () => {
        model.value = null;
    };
</script>

<template>
    <div class="flex flex-col space-y-2" :class="{ 'pointer-events-none opacity-60': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="ml-4 text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="error ? 'text-red-500' : 'text-slate-500'"
        >
            {{ label }}
        </label>

        <DatePicker
            v-model="model"
            :mode="mode"
            is-dark
            is24hr
            hide-time-header
            :popover="{ visibility: 'click', placement: 'bottom' }"
        >
            <template #default="{ inputValue, inputEvents }">
                <div class="group relative">
                    <input
                        :id="inputId"
                        class="w-full cursor-pointer rounded-2xl border bg-slate-950 p-4 font-bold text-white transition-all focus:ring-0"
                        :class="
                            error
                                ? 'border-red-500/50 focus:border-red-500'
                                : 'border-slate-800 focus:border-orange-500'
                        "
                        :value="inputValue"
                        v-on="inputEvents"
                        readonly
                        :placeholder="placeholder"
                        :aria-invalid="!!error"
                        :aria-describedby="error ? errorId : undefined"
                    />

                    <div class="absolute right-4 top-4 flex items-center gap-2">
                        <button
                            v-if="model"
                            type="button"
                            @click.stop="clearDate"
                            class="text-slate-600 transition-colors hover:text-white"
                        >
                            <XMarkIcon class="h-4 w-4" />
                        </button>

                        <CalendarIcon
                            class="h-5 w-5 transition-colors"
                            :class="
                                error
                                    ? 'text-red-500/50'
                                    : 'text-slate-500 group-hover:text-slate-300'
                            "
                        />
                    </div>
                </div>
            </template>
        </DatePicker>

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
