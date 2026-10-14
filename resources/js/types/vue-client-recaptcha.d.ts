declare module 'vue-client-recaptcha' {
    import type { DefineComponent } from 'vue';

    /**
     * Vue 3 обёртка для клиентской капчи.
     * Рисует код на canvas, проверяет ввод в браузере.
     *
     * @example
     * <VueClientRecaptcha
     *     v-model="userInput"
     *     v-model:valid="isValid"
     * />
     */
    export const VueClientRecaptcha: DefineComponent<
        // ---------- props ----------
        {
            /** Введённый пользователем код (v-model) */
            modelValue?: string;

            /** Флаг: код введён правильно (v-model:valid) */
            valid?: boolean;

            /** Количество символов в капче (по умолчанию 5) */
            count?: number;

            /** Пресет символов: alphanumeric | numeric | letters | custom */
            charsPreset?: 'alphanumeric' | 'numeric' | 'letters' | 'custom';

            /** Кастомный набор символов (когда charsPreset="custom") */
            chars?: string;

            /** Тип искажения: none | lines | dots | both */
            distortion?: 'none' | 'lines' | 'dots' | 'both';

            /** Тема оформления */
            theme?: 'auto' | 'light' | 'dark';

            /** Ширина canvas */
            width?: number;

            /** Высота canvas */
            height?: number;

            /** Скрыть линии-искажения */
            hideLines?: boolean;

            /** Скрыть иконку обновления */
            hideRefreshIcon?: boolean;

            /** Упрощённый режим (без лишних элементов) */
            simpleMode?: boolean;
        },
        // ---------- emits ----------
        {
            'update:modelValue': (value: string) => void;
            'update:valid': (value: boolean) => void;
            getCode: (code: string) => void;
            isValid: (valid: boolean) => void;
            refresh: (code: string) => void;
            ready: () => void;
            error: (error: unknown) => void;
        },
        // ---------- slots ----------
        Record<string, never>
    >;
}
