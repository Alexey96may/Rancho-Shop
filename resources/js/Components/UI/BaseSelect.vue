<script lang="ts">
    /** Значения, которые принимает HeadlessUI Listbox в `value` */
    export type ListboxValue = string | number | boolean | object | null | undefined;

    /** Опция — любой объект. Конкретная форма задаётся через valueKey / labelKey */
    export type SelectOption = object;
</script>

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

    const model = defineModel<ListboxValue>();
    const error = defineModel<string | undefined>('error');

    const props = withDefaults(
        defineProps<{
            options: SelectOption[];
            label?: string;
            placeholder?: string;
            description?: string;
            widthClass?: string;
            variant?: 'admin' | 'classic';
            /** Ключ, под которым в опции лежит значение */
            valueKey?: string;
            /** Ключ, под которым в опции лежит отображаемый текст */
            labelKey?: string;
            disabled?: boolean;
        }>(),
        {
            label: undefined,
            placeholder: undefined,
            description: undefined,
            widthClass: undefined,
            variant: 'classic',
            valueKey: 'id',
            labelKey: 'name',
            disabled: false,
        },
    );

    const labelId = useId();
    const descriptionId = useId();
    const errorId = useId();

    const vKey = computed(() => props.valueKey);
    const lKey = computed(() => props.labelKey);

    /** Типизированное чтение произвольного поля из объекта */
    function readField<R = unknown>(obj: object, key: string): R | undefined {
        return (obj as Record<string, R>)[key];
    }

    /** Приводим значение опции к тому, что принимает Listbox */
    function optionValue(opt: SelectOption): ListboxValue {
        const raw = readField<unknown>(opt, vKey.value);

        if (
            raw === null ||
            raw === undefined ||
            typeof raw === 'string' ||
            typeof raw === 'number' ||
            typeof raw === 'boolean'
        ) {
            return raw;
        }

        if (typeof raw === 'object') return raw;

        return undefined;
    }

    /** Приводим label опции к строке */
    function optionLabel(opt: SelectOption): string {
        const raw = readField<unknown>(opt, lKey.value);

        if (typeof raw === 'string' || typeof raw === 'number') {
            return String(raw);
        }

        return '';
    }

    /** Значение выбранной опции */
    const selected = computed<ListboxValue>({
        get: () => model.value,
        set: (value) => {
            model.value = value;
        },
    });

    /** Находим выбранную опцию для отображения её label */
    const selectedOption = computed<SelectOption | undefined>(() =>
        props.options.find((o) => optionValue(o) === selected.value),
    );

    /** Текст, который показываем в кнопке */
    const displayLabel = computed<string>(() => {
        const opt = selectedOption.value;
        if (!opt) return props.placeholder ?? '';

        const label = optionLabel(opt);
        return label || (props.placeholder ?? '');
    });

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
                hasError ? 'text-rose-500' : isDark ? 'text-rancho-ink-500' : 'text-rancho-ink-600',
            ],

            button: [
                'relative w-full cursor-pointer text-xs sm:text-sm transition-all focus:outline-none',
                isDark
                    ? 'rounded-2xl border bg-rancho-ink-950 py-3 pl-4 pr-10 text-left text-rancho-ink-300'
                    : 'rounded-xl border bg-white py-2.5 pl-4 pr-10 text-left text-rancho-ink-800',

                hasError
                    ? isDark
                        ? 'border-rose-500/50 hover:border-rose-500'
                        : 'border-rose-300 hover:border-rose-500'
                    : isDark
                      ? 'border-rancho-ink-800 hover:border-rancho-buttercup/60'
                      : 'border-rancho-ink-300 hover:border-rancho-ink-400',

                isDark
                    ? 'focus-visible:ring-2 focus-visible:ring-rancho-buttercup/20'
                    : 'focus-visible:border-rancho-pine focus-visible:ring-4 focus-visible:ring-rancho-pine/10',
            ],

            options: [
                'text-xs sm:text-sm',
                isDark
                    ? 'absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900 py-1 text-rancho-ink-300 shadow-2xl ring-1 ring-white/5 focus:outline-none sm:text-sm'
                    : 'absolute z-50 mt-2 max-h-60 w-full overflow-auto rounded-2xl bg-white py-1 text-rancho-ink-800 shadow-xl ring-1 ring-rancho-ink-900/10 focus:outline-none sm:text-sm',
            ],

            option: (active: boolean) => {
                if (isDark) {
                    return [
                        active
                            ? 'bg-rancho-buttercup/10 text-rancho-buttercup'
                            : 'text-rancho-ink-400',
                        'relative cursor-pointer select-none py-3 pl-10 pr-4 transition-colors',
                    ];
                }
                return [
                    active ? 'bg-rancho-buttercup/15 text-rancho-forest' : 'text-rancho-ink-800',
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
        <Listbox
            :model-value="model ?? null"
            @update:model-value="(v) => (model = v)"
            :disabled="disabled"
        >
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
                        {{ displayLabel }}
                    </span>
                    <span
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                        aria-hidden="true"
                    >
                        <ChevronUpDownIcon class="h-5 w-5 text-rancho-ink-500" />
                    </span>
                </ListboxButton>

                <span v-if="description && !error" :id="descriptionId" class="sr-only">
                    {{ description }}
                </span>

                <Transition
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <ListboxOptions :class="styles.options">
                        <ListboxOption
                            v-if="placeholder"
                            v-slot="{ active, selected: isSelected }"
                            :value="null"
                            as="template"
                        >
                            <li :class="styles.option(active)">
                                <span
                                    :class="[
                                        isSelected
                                            ? 'font-bold text-rancho-buttercup'
                                            : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ placeholder }}
                                </span>
                                <span
                                    v-if="isSelected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-rancho-ink-600"
                                >
                                    <CheckIcon class="h-5 w-5" />
                                </span>
                            </li>
                        </ListboxOption>

                        <ListboxOption
                            v-for="opt in options"
                            :key="String(optionValue(opt))"
                            :value="optionValue(opt)"
                            v-slot="{ active, selected: isSelected }"
                            as="template"
                        >
                            <li :class="styles.option(active)">
                                <span
                                    :class="[
                                        isSelected
                                            ? 'font-bold text-rancho-ink-600'
                                            : 'font-normal',
                                        'block truncate',
                                    ]"
                                >
                                    {{ optionLabel(opt) }}
                                </span>
                                <span
                                    v-if="isSelected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-rancho-ink-600"
                                >
                                    <CheckIcon class="h-5 w-5" />
                                </span>
                            </li>
                        </ListboxOption>
                    </ListboxOptions>
                </Transition>
            </div>
        </Listbox>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p
                v-if="error"
                :id="errorId"
                class="ml-2 mt-1.5 text-[10px] font-bold uppercase italic text-rancho-ember"
            >
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
