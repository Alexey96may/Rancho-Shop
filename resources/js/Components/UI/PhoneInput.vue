<script setup lang="ts">
    import { computed } from 'vue';

    import BaseInput from '@/Components/UI/BaseInput.vue';

    type Theme = 'light' | 'dark';

    const model = defineModel<string | null | undefined>({ default: '' });

    const props = withDefaults(
        defineProps<{
            label?: string;
            placeholder?: string;
            disabled?: boolean;
            required?: boolean;
            error?: string;
            theme?: Theme;
        }>(),
        {
            placeholder: '+7 (___) ___-__-__',
            theme: 'light',
        },
    );

    const emit = defineEmits<{
        (e: 'input-start'): void;
    }>();

    /** Блокируем всё, кроме цифр и служебных клавиш */
    function onKeydown(e: KeyboardEvent) {
        // Разрешаем служебные
        const allowed = [
            'Backspace',
            'Delete',
            'Tab',
            'Enter',
            'Escape',
            'ArrowLeft',
            'ArrowRight',
            'Home',
            'End',
        ];
        if (allowed.includes(e.key)) return;
        if (e.ctrlKey || e.metaKey) return;

        // Только цифры
        if (!/^\d$/.test(e.key)) {
            e.preventDefault();
            return;
        }

        // Проверяем лимит
        const current = (model.value ?? '').replace(/\D/g, '');
        if (current.length >= 11) {
            e.preventDefault();
        }
    }

    /** Вставка из буфера — фильтруем */
    function onPaste(e: ClipboardEvent) {
        e.preventDefault();
        const text = e.clipboardData?.getData('text') ?? '';
        const digits = text.replace(/\D/g, '');

        if (!digits) return;

        // Добавляем к текущему значению
        const current = model.value ?? '';
        const next = formatPhone(current.replace(/\D/g, '') + digits);
        model.value = next;
        emit('input-start');
    }

    function formatPhone(value: string): string {
        const digits = value.replace(/\D/g, '').slice(0, 11);

        if (digits.length === 0) return '';

        let normalized = digits;
        if (digits.length >= 10 && digits.startsWith('8')) {
            normalized = '7' + digits.slice(1);
        }

        const withSeven = normalized.startsWith('7') ? normalized : '7' + normalized;
        const d = withSeven.slice(1);

        let result = '+7';
        if (d.length > 0) result += ` (${d.slice(0, 3)}`;
        if (d.length >= 3) result += ')';
        if (d.length > 3) result += ` ${d.slice(3, 6)}`;
        if (d.length > 6) result += `-${d.slice(6, 8)}`;
        if (d.length > 8) result += `-${d.slice(8, 10)}`;

        return result;
    }

    const formatted = computed(() => formatPhone(model.value ?? ''));

    function onInput(v: string | number | null | undefined) {
        const raw = typeof v === 'string' ? v : String(v ?? '');
        model.value = formatPhone(raw);
        emit('input-start');
    }
</script>

<template>
    <BaseInput
        :model-value="formatted"
        :label="label"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :error="error"
        :theme="theme"
        type="tel"
        inputmode="tel"
        autocomplete="tel"
        @keydown="onKeydown"
        @paste="onPaste"
        @update:model-value="onInput"
        @input-start="emit('input-start')"
    />
</template>
