<script setup lang="ts">
    import { computed, useId, watch } from 'vue';

    type DateMode = 'date' | 'datetime-local' | 'time';
    type OutputFormat = 'string' | 'date';

    interface Props {
        label?: string;
        placeholder?: string;
        disabled?: boolean;
        min?: string | Date;
        max?: string | Date;
        /** Что показывать: только дата, дата+время, только время */
        mode?: DateMode;
        /** Что отдавать наружу: строку или Date-объект */
        output?: OutputFormat;
    }

    const props = withDefaults(defineProps<Props>(), {
        placeholder: 'дд.мм.гггг',
        disabled: false,
        mode: 'date',
        output: 'string',
    });

    const model = defineModel<string | Date | null | undefined>({ default: null });
    const error = defineModel<string | undefined>('error');

    const inputId = useId();
    const errorId = useId();

    watch(model, () => {
        if (error.value) error.value = undefined;
    });

    /**
     * Приводим входное значение к строке нужного формата для <input>:
     * - mode="date"            → YYYY-MM-DD
     * - mode="datetime-local"  → YYYY-MM-DDTHH:mm
     * - mode="time"            → HH:mm
     */
    function toInputString(value: string | Date | null | undefined): string {
        if (!value) return '';

        let date: Date;

        if (value instanceof Date) {
            date = value;
        } else {
            // Строка: пробуем как есть, иначе — как дату
            const parsed = new Date(value);
            if (isNaN(parsed.getTime())) return '';
            date = parsed;
        }

        const pad = (n: number) => String(n).padStart(2, '0');
        const y = date.getFullYear();
        const m = pad(date.getMonth() + 1);
        const d = pad(date.getDate());
        const hh = pad(date.getHours());
        const mm = pad(date.getMinutes());

        if (props.mode === 'date') return `${y}-${m}-${d}`;
        if (props.mode === 'time') return `${hh}:${mm}`;
        return `${y}-${m}-${d}T${hh}:${mm}`;
    }

    /**
     * Приводим строку из <input> к нужному output:
     * - output="string" → строка в формате mode
     * - output="date"   → Date-объект
     */
    function fromInputString(value: string): string | Date | null {
        if (!value) return null;

        if (props.output === 'date') {
            const parsed = new Date(value);
            return isNaN(parsed.getTime()) ? null : parsed;
        }

        return value; // строка уже в нужном формате
    }

    const inputValue = computed(() => toInputString(model.value));

    const minValue = computed(() => (props.min ? toInputString(props.min) : undefined));
    const maxValue = computed(() => (props.max ? toInputString(props.max) : undefined));

    const onInput = (event: Event) => {
        const raw = (event.target as HTMLInputElement).value;
        model.value = fromInputString(raw);
    };

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

        <div class="group relative">
            <input
                :id="inputId"
                :type="mode"
                :value="inputValue"
                :min="minValue"
                :max="maxValue"
                :disabled="disabled"
                :aria-invalid="!!error"
                :aria-describedby="error ? errorId : undefined"
                class="w-full cursor-pointer overflow-hidden rounded-2xl border bg-rancho-ink-950 px-4 py-4 font-mono text-sm font-bold text-white [color-scheme:dark] placeholder:text-xs placeholder:text-rancho-ink-500 focus:outline-none focus:ring-0"
                :class="
                    error
                        ? 'border-rose-500/50 focus:border-rose-500'
                        : 'border-rancho-ink-800 focus:border-rancho-mint'
                "
                @input="onInput"
            />
        </div>

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
