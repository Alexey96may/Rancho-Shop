<script setup lang="ts">
    import { useId } from 'vue';

    import {
        Listbox,
        ListboxButton,
        ListboxLabel,
        ListboxOption,
        ListboxOptions,
    } from '@headlessui/vue';
    import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

    const model = defineModel<any>();

    defineProps<{
        label?: string;
        options: Array<{ id: number | string; name: string; slug: string | number }>;
        placeholder?: string;
        description?: string;
        widthClass?: string;
    }>();

    const labelId = useId();
    const descriptionId = useId();
</script>

<template>
    <div :class="[widthClass || 'w-full', 'min-w-[200px]']">
        <Listbox v-model="model">
            <div class="relative mt-1">
                <ListboxLabel
                    v-if="label"
                    :id="labelId"
                    class="mb-1.5 ml-1 block text-xs font-bold uppercase tracking-wider text-slate-500"
                >
                    {{ label }}
                </ListboxLabel>

                <ListboxButton
                    :aria-labelledby="label ? labelId : undefined"
                    :aria-describedby="description ? descriptionId : undefined"
                    class="relative w-full cursor-pointer rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-left transition-all hover:border-orange-300 focus:outline-none focus-visible:border-orange-500 focus-visible:ring-4 focus-visible:ring-orange-500/10 sm:text-sm"
                >
                    <span class="block truncate font-medium text-slate-700">
                        {{ options.find((o) => o.slug === model)?.name || placeholder }}
                    </span>
                    <span
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                        aria-hidden="true"
                    >
                        <ChevronUpDownIcon class="h-5 w-5 text-slate-400" />
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
                    <ListboxOptions
                        class="shadow-xl absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-1 text-base ring-1 ring-black/5 focus:outline-none sm:text-sm"
                    >
                        <ListboxOption
                            v-if="placeholder"
                            v-slot="{ active, selected }"
                            value=""
                            as="template"
                        >
                            <li
                                :class="[
                                    active ? 'bg-orange-50 text-orange-900' : 'text-slate-500',
                                    'relative cursor-pointer select-none py-2.5 pl-10 pr-4 transition-colors',
                                ]"
                                :aria-selected="selected"
                            >
                                <span
                                    :class="[
                                        selected ? 'font-bold text-orange-600' : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ placeholder }}
                                </span>
                                <span
                                    v-if="selected"
                                    aria-hidden="true"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-orange-600"
                                >
                                    <CheckIcon class="h-5 w-5" />
                                </span>
                            </li>
                        </ListboxOption>

                        <ListboxOption
                            v-slot="{ active, selected }"
                            v-for="opt in options"
                            :key="opt.id"
                            :value="opt.slug"
                            as="template"
                        >
                            <li
                                :class="[
                                    active ? 'bg-orange-50 text-orange-900' : 'text-slate-900',
                                    'relative cursor-pointer select-none py-2.5 pl-10 pr-4 transition-colors',
                                ]"
                                :aria-selected="selected"
                            >
                                <span
                                    :class="[
                                        selected ? 'font-bold text-orange-600' : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ opt.name }}
                                </span>
                                <span
                                    v-if="selected"
                                    aria-hidden="true"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-orange-600"
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
