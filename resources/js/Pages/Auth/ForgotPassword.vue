<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, useForm } from '@inertiajs/vue3';

    import { CheckCircleIcon } from '@heroicons/vue/24/outline';

    import AppCaptcha from '@/Components/UI/AppCaptcha.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    defineProps<{
        status?: string;
    }>();

    const form = useForm({
        email: '',
        captcha: '',
    });

    const isCaptchaValid = ref(false);
    const captchaRef = ref<InstanceType<typeof AppCaptcha> | null>(null);

    const canSubmit = computed(() => {
        return form.email.trim().length > 0 && isCaptchaValid.value && !form.processing;
    });

    const submit = () => {
        if (!canSubmit.value) return;

        form.post(route('password.email'), {
            onSuccess: () => {
                form.reset('captcha');
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
    <Head title="Восстановление пароля" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Восстановление пароля</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
            Забыли пароль? Ничего страшного. Укажите email — мы отправим ссылку для сброса пароля.
        </p>
    </header>

    <!-- Статус -->
    <Transition name="fade-up">
        <div
            v-if="status"
            v-scroll-reveal
            class="mb-5 flex items-start gap-3 rounded-xl border border-rancho-pine/30 bg-rancho-pine/10 px-4 py-3"
            role="status"
            aria-live="polite"
        >
            <CheckCircleIcon class="mt-0.5 h-4 w-4 shrink-0 text-rancho-pine" aria-hidden="true" />
            <p class="text-sm font-medium text-rancho-forest">
                {{ status }}
            </p>
        </div>
    </Transition>

    <form v-scroll-reveal class="space-y-5" @submit.prevent="submit">
        <BaseInput
            id="email"
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            :error="form.errors.email"
            required
            autofocus
            autocomplete="username"
            theme="light"
            @input-start="form.clearErrors('email')"
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

        <div class="flex items-center justify-end pt-2">
            <BaseSubmitButton
                :processing="form.processing"
                :disabled="!canSubmit"
                label="Отправить ссылку"
            />
        </div>
    </form>
</template>
