<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, useForm } from '@inertiajs/vue3';

    import AppCaptcha from '@/Components/UI/AppCaptcha.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const form = useForm({
        password: '',
        captcha: '',
    });

    const isCaptchaValid = ref(false);
    const captchaRef = ref<InstanceType<typeof AppCaptcha> | null>(null);

    const canSubmit = computed(() => {
        return form.password.trim().length > 0 && isCaptchaValid.value && !form.processing;
    });

    const submit = () => {
        if (!canSubmit.value) return;

        form.post(route('password.confirm'), {
            onSuccess: () => {
                form.reset();
                isCaptchaValid.value = false;
                captchaRef.value?.reset();
            },
            onError: () => {
                captchaRef.value?.reset();
                isCaptchaValid.value = false;
            },
        });
    };
</script>

<template>
    <Head title="Подтверждение пароля" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Подтверждение пароля</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
            Это защищённая зона приложения. Введите пароль, чтобы продолжить.
        </p>
    </header>

    <form v-scroll-reveal class="space-y-5" @submit.prevent="submit">
        <BaseInput
            id="password"
            v-model="form.password"
            type="password"
            label="Пароль"
            placeholder="Введите пароль"
            :error="form.errors.password"
            required
            autocomplete="current-password"
            autofocus
            theme="light"
            @input-start="form.clearErrors('password')"
        />

        <Transition name="fade-slide" appear>
            <AppCaptcha
                ref="captchaRef"
                v-model="form.captcha"
                v-model:valid="isCaptchaValid"
                :count="5"
                chars-preset="alphanumeric"
                distortion="lines"
                theme="light"
            />
        </Transition>

        <p v-if="form.errors.captcha" class="text-xs font-medium text-rose-500">
            {{ form.errors.captcha }}
        </p>

        <div class="flex justify-end pt-2">
            <BaseSubmitButton
                :processing="form.processing"
                :disabled="!canSubmit"
                label="Подтвердить"
            />
        </div>
    </form>
</template>
