<script setup lang="ts">
    import { computed, useId, watch } from 'vue';

    import {
        Listbox,
        ListboxButton,
        ListboxLabel,
        ListboxOption,
        ListboxOptions,
    } from '@headlessui/vue';
    import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

    const model = defineModel<any>();
    const error = defineModel<string | undefined>('error');

    const props = defineProps<{
        options: Array<any>;
        label?: string;
        placeholder?: string;
        description?: string;
        widthClass?: string;
        variant?: 'admin' | 'classic';
        valueKey?: string;
        labelKey?: string;
        disabled?: boolean;
    }>();

    const labelId = useId();
    const descriptionId = useId();
    const errorId = useId();

    const vKey = computed(() => props.valueKey || 'id');
    const lKey = computed(() => props.labelKey || 'name');

    watch(model, () => {
        if (error.value) {
            error.value = undefined;
        }
    });

    const styles = computed(() => {
        const isDark = props.variant === 'admin';
        const hasError = !!error.value;

        return {
            label: [
                isDark
                    ? 'mb-1.5 ml-2 block text-[10px] font-black uppercase tracking-[0.15em]'
                    : 'mb-1.5 ml-1 block text-xs font-bold uppercase tracking-wider',
                hasError ? 'text-red-500' : 'text-slate-500',
            ],

            button: [
                'relative w-full cursor-pointer transition-all focus:outline-none',
                isDark
                    ? 'rounded-2xl border bg-slate-950 py-3 pl-4 pr-10 text-left text-slate-300'
                    : 'rounded-xl border bg-white py-2.5 pl-4 pr-10 text-left sm:text-sm',

                // Логика состояний рамки
                hasError
                    ? isDark
                        ? 'border-red-500/50 hover:border-red-500'
                        : 'border-red-300 hover:border-red-500'
                    : isDark
                      ? 'border-slate-800 hover:border-orange-500/50'
                      : 'border-slate-200 hover:border-orange-300',

                isDark
                    ? 'focus-visible:ring-2 focus-visible:ring-orange-500/20'
                    : 'focus-visible:border-orange-500 focus-visible:ring-4 focus-visible:ring-orange-500/10',
            ],

            options: isDark
                ? 'absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl border border-slate-800 bg-slate-900 py-1 text-base shadow-2xl ring-1 ring-white/5 focus:outline-none sm:text-sm'
                : 'absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-1 text-base shadow-xl ring-1 ring-black/5 focus:outline-none sm:text-sm',

            option: (active: boolean, selected: boolean) => {
                if (isDark) {
                    return [
                        active ? 'bg-orange-500/10 text-orange-400' : 'text-slate-400',
                        'relative cursor-pointer select-none py-3 pl-10 pr-4 transition-colors',
                    ];
                }
                return [
                    active ? 'bg-orange-50 text-orange-900' : 'text-slate-900',
                    'relative cursor-pointer select-none py-2.5 pl-10 pr-4 transition-colors',
                ];
            },
        };
    });
</script>

<template>
    <div
        :class="[
            widthClass || 'w-full',
            'min-w-[200px]',
            { 'pointer-events-none opacity-50': disabled },
        ]"
    >
        <Listbox v-model="model" :disabled="disabled">
            <div class="relative">
                <ListboxLabel v-if="label" :id="labelId" :class="styles.label">
                    {{ label }}
                </ListboxLabel>

                <ListboxButton
                    :aria-labelledby="label ? labelId : undefined"
                    :aria-describedby="error ? errorId : description ? descriptionId : undefined"
                    :aria-invalid="!!error"
                    :class="styles.button"
                >
                    <span class="block truncate font-medium">
                        {{ options.find((o) => o[vKey] === model)?.[lKey] || placeholder }}
                    </span>
                    <span
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                        aria-hidden="true"
                    >
                        <ChevronUpDownIcon class="h-5 w-5 text-slate-500" />
                    </span>
                </ListboxButton>

                <span v-if="description && !error" :id="descriptionId" class="sr-only">
                    {{ description }}
                </span>

                <transition
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <ListboxOptions :class="styles.options">
                        <ListboxOption
                            v-if="placeholder"
                            v-slot="{ active, selected }"
                            :value="null"
                            as="template"
                        >
                            <li :class="styles.option(active, selected)">
                                <span
                                    :class="[
                                        selected ? 'font-bold text-orange-500' : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ placeholder }}
                                </span>
                                <span
                                    v-if="selected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-orange-500"
                                >
                                    <CheckIcon class="h-5 w-5" />
                                </span>
                            </li>
                        </ListboxOption>

                        <ListboxOption
                            v-for="opt in options"
                            :key="opt[vKey]"
                            :value="opt[vKey]"
                            v-slot="{ active, selected }"
                            as="template"
                        >
                            <li :class="styles.option(active, selected)">
                                <span
                                    :class="[
                                        selected ? 'font-bold text-orange-500' : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ opt[lKey] }}
                                </span>
                                <span
                                    v-if="selected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-orange-500"
                                >
                                    <CheckIcon class="h-5 w-5" />
                                </span>
                            </li>
                        </ListboxOption>
                    </ListboxOptions>
                </transition>
            </div>
        </Listbox>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p
                v-if="error"
                :id="errorId"
                class="ml-2 mt-1.5 text-[10px] font-bold uppercase italic text-red-500"
            >
                {{ error }}
            </p>
        </transition>
    </div>
</template>
