<script setup lang="ts">
    import { computed, useId } from 'vue';

    import {
        Listbox,
        ListboxButton,
        ListboxLabel,
        ListboxOption,
        ListboxOptions,
    } from '@headlessui/vue';
    import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

    const model = defineModel<any>();

    const props = defineProps<{
        options: Array<{ id: number | string; name: string; slug: string | number }>;
        label?: string;
        placeholder?: string;
        description?: string;
        widthClass?: string;
        variant?: 'admin' | 'classic';
    }>();

    const labelId = useId();
    const descriptionId = useId();

    const styles = computed(() => {
        const isDark = props.variant === 'admin';

        return {
            label: isDark
                ? 'mb-1.5 ml-2 block text-[10px] font-black uppercase tracking-[0.15em] text-slate-500'
                : 'mb-1.5 ml-1 block text-xs font-bold uppercase tracking-wider text-slate-500',

            button: isDark
                ? 'relative w-full cursor-pointer rounded-2xl border border-slate-800 bg-slate-950/50 py-3 pl-4 pr-10 text-left text-slate-300 transition-all hover:border-orange-500/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/20'
                : 'relative w-full cursor-pointer rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-left transition-all hover:border-orange-300 focus:outline-none focus-visible:border-orange-500 focus-visible:ring-4 focus-visible:ring-orange-500/10 sm:text-sm',

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
    <div :class="[widthClass || 'w-full', 'min-w-[200px]']">
        <Listbox v-model="model">
            <div class="relative mt-1">
                <ListboxLabel v-if="label" :id="labelId" :class="styles.label">
                    {{ label }}
                </ListboxLabel>

                <ListboxButton
                    :aria-labelledby="label ? labelId : undefined"
                    :aria-describedby="description ? descriptionId : undefined"
                    :class="styles.button"
                >
                    <span class="block truncate font-medium">
                        {{ options.find((o) => o.id === model)?.name || placeholder }}
                    </span>
                    <span
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                        aria-hidden="true"
                    >
                        <ChevronUpDownIcon class="h-5 w-5 text-slate-500" />
                    </span>
                </ListboxButton>

                <span v-if="description" :id="descriptionId" class="sr-only">
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
                            v-slot="{ active, selected }"
                            v-for="opt in options"
                            :key="opt.id"
                            :value="opt.id"
                            as="template"
                        >
                            <li :class="styles.option(active, selected)">
                                <span
                                    :class="[
                                        selected ? 'font-bold text-orange-500' : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ opt.name }}
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
    </div>
</template>
