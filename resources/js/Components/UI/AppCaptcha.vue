<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { VueClientRecaptcha } from 'vue-client-recaptcha';

    import BaseInput from '@/Components/UI/BaseInput.vue';

    const props = withDefaults(
        defineProps<{
            modelValue: string;
            valid?: boolean;
            count?: number;
            charsPreset?: 'numeric' | 'letters' | 'alphanumeric';
            distortion?: 'none' | 'lines' | 'dots';
            theme?: 'light' | 'dark';
        }>(),
        {
            valid: false,
            count: 5,
            charsPreset: 'alphanumeric',
            distortion: 'lines',
            theme: 'light',
        },
    );

    const emit = defineEmits<{
        (e: 'update:modelValue', value: string): void;
        (e: 'update:valid', value: boolean): void;
    }>();

    // Локальный ключ для принудительного пересоздания капчи
    const captchaKey = ref(0);

    // Двусторонняя привязка значения ввода
    const input = ref(props.modelValue);

    watch(
        () => props.modelValue,
        (v) => {
            input.value = v;
        },
    );

    watch(input, (v) => {
        emit('update:modelValue', v);
    });

    // Двусторонняя привязка валидности
    const isValid = ref(props.valid);

    watch(
        () => props.valid,
        (v) => {
            isValid.value = v;
        },
    );

    watch(isValid, (v) => {
        emit('update:valid', v);
    });

    // Метод для сброса капчи (можно вызывать через ref)
    const reset = () => {
        input.value = '';
        isValid.value = false;
        captchaKey.value++;
    };

    defineExpose({ reset });
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-4 pt-3 sm:flex-row">
        <VueClientRecaptcha
            :key="captchaKey"
            v-model="input"
            v-model:valid="isValid"
            :count="count"
            :chars-preset="charsPreset"
            :distortion="distortion"
            :theme="theme"
            class="styled-captcha"
            canvas-label="Капча"
            refresh-label="Обновить капчу"
        />

        <BaseInput
            v-model="input"
            placeholder="Введите капчу"
            type="text"
            class="max-w-60"
            :theme="theme"
            required
        />
    </div>
</template>

<style scoped>
    :deep(.vue_client_recaptcha_sr_only) {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
        pointer-events: none !important;
    }

    .styled-captcha {
        border-radius: 12px;
        flex-shrink: 0;
        width: auto !important;
        position: relative;
    }

    .styled-captcha :deep(.vue_client_recaptcha_icon_svg) {
        color: #597d5b;
        position: absolute;
        top: 0;
        left: 0;
        transform: translateY(-75%);
        transition:
            color 0.3s ease,
            transform 0.3s ease;
    }

    .styled-captcha :deep(.vue_client_recaptcha_icon_svg):hover {
        color: #1c3f34;
    }

    .styled-captcha :deep(canvas) {
        border-radius: 8px;
    }
</style>
