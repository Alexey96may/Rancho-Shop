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
    <div class="flex flex-col gap-2" :class="{ 'pointer-events-none opacity-60': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="ml-2 text-[10px] font-black uppercase tracking-[0.15em] transition-colors"
            :class="error ? 'text-rose-500' : 'text-rancho-ink-500'"
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
                        class="w-full cursor-pointer rounded-2xl border bg-rancho-ink-950 px-4 py-4 text-sm font-bold text-white placeholder:text-rancho-ink-500 focus:outline-none focus:ring-0"
                        :class="
                            error
                                ? 'border-rose-500/50 focus:border-rose-500'
                                : 'border-rancho-ink-800 focus:border-rancho-mint'
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
                            class="rounded text-rancho-ink-600 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
                            aria-label="Очистить дату"
                        >
                            <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                        </button>

                        <CalendarIcon
                            class="h-5 w-5 transition-colors"
                            :class="
                                error
                                    ? 'text-rose-500/50'
                                    : 'text-rancho-ink-500 group-hover:text-rancho-ink-300'
                            "
                            aria-hidden="true"
                        />
                    </div>
                </div>
            </template>
        </DatePicker>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p
                v-if="error"
                :id="errorId"
                class="ml-4 text-[10px] font-bold uppercase italic text-rose-500"
                role="alert"
            >
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
