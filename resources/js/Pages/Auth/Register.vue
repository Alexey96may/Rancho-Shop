<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, Link, useForm } from '@inertiajs/vue3';

    import { ArrowRightEndOnRectangleIcon } from '@heroicons/vue/24/outline';

    import AppCaptcha from '@/Components/UI/AppCaptcha.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import GuestLayout from '@/Layouts/GuestLayout.vue';

    defineOptions({ layout: GuestLayout });

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        captcha: '',
    });

    const isCaptchaValid = ref(false);
    const captchaRef = ref<InstanceType<typeof AppCaptcha> | null>(null);

    const canSubmit = computed(() => {
        const hasFields =
            form.name.trim().length > 0 &&
            form.email.trim().length > 0 &&
            form.password.length > 0 &&
            form.password_confirmation.length > 0;

        return hasFields && isCaptchaValid.value && !form.processing;
    });

    const submit = () => {
        if (!canSubmit.value) return;

        form.post(route('register'), {
            onSuccess: () => {
                form.reset('password', 'password_confirmation', 'captcha');
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
    <Head title="Регистрация" />

    <header v-scroll-reveal class="mb-6">
        <h1 class="text-2xl font-black text-rancho-forest sm:text-3xl">Регистрация</h1>
        <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
            Создайте аккаунт — это займёт меньше минуты.
        </p>
    </header>

    <form v-scroll-reveal class="space-y-5" @submit.prevent="submit">
        <BaseInput
            id="name"
            v-model="form.name"
            type="text"
            label="Имя"
            placeholder="Как к вам обращаться?"
            :error="form.errors.name"
            required
            autofocus
            autocomplete="name"
            theme="light"
            @input-start="form.clearErrors('name')"
        />

        <BaseInput
            id="email"
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            :error="form.errors.email"
            required
            autocomplete="username"
            theme="light"
            @input-start="form.clearErrors('email')"
        />

        <BaseInput
            id="password"
            v-model="form.password"
            type="password"
            label="Пароль"
            placeholder="Придумайте пароль"
            :error="form.errors.password"
            required
            autocomplete="new-password"
            theme="light"
            @input-start="form.clearErrors('password')"
        />

        <BaseInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            label="Подтверждение пароля"
            placeholder="Повторите пароль"
            :error="form.errors.password_confirmation"
            required
            autocomplete="new-password"
            theme="light"
            @input-start="form.clearErrors('password_confirmation')"
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

        <div class="flex flex-col items-stretch gap-4 pt-2 sm:items-center sm:justify-between">
            <BaseSubmitButton
                :processing="form.processing"
                :disabled="!canSubmit"
                label="Зарегистрироваться"
                :icon="ArrowRightEndOnRectangleIcon"
                class="sm:w-auto"
            />

            <Link
                :href="route('login')"
                class="text-center text-sm font-medium text-rancho-ink-500 underline-offset-2 transition-colors hover:text-rancho-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white sm:text-left"
            >
                Уже зарегистрированы?
            </Link>
        </div>
    </form>
</template>
