<script setup lang="ts">
    import { computed } from 'vue';

    interface Props {
        modelValue: boolean;
        error?: string;
        /** Текст перед ссылкой */
        label?: string;
        /** Текст ссылки */
        linkText?: string;
        /** URL политики */
        href?: string;
        /** id для связки label/input (если нужно) */
        id?: string;
        /** Доп. классы для обёртки */
        class?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: 'Я согласен с',
        linkText: 'Политикой конфиденциальности',
        href: '/privacy',
        error: '',
        id: 'agreement',
    });

    const emit = defineEmits<{
        (e: 'update:modelValue', value: boolean): void;
    }>();

    const checked = computed({
        get: () => props.modelValue,
        set: (v: boolean) => emit('update:modelValue', v),
    });
</script>

<template>
    <div :class="props.class">
        <label
            :for="id"
            class="flex cursor-pointer items-start gap-2 text-xs text-rancho-ink-600 sm:text-sm"
        >
            <input
                :id="id"
                v-model="checked"
                type="checkbox"
                required
                class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-rancho-ink-300 text-rancho-pine focus:ring-rancho-pine"
            />
            <span>
                {{ label }}
                <a
                    :href="href"
                    target="_blank"
                    rel="noopener"
                    class="font-semibold text-rancho-pine underline decoration-rancho-pine/30 underline-offset-2 hover:text-rancho-forest"
                >
                    {{ linkText }}
                </a>
                и даю согласие на обработку моих персональных данных.
            </span>
        </label>

        <p v-if="error" class="mt-1 text-xs font-medium text-rancho-ember">
            {{ error }}
        </p>
    </div>
</template>
