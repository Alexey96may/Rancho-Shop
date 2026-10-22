<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { router, usePage } from '@inertiajs/vue3';

    import { BellIcon, BellSlashIcon } from '@heroicons/vue/24/outline';

    import { useNotificationsStore } from '@/stores/notifications';
    import type { SharedData } from '@/types';

    interface Props {
        productId: number;
        isSubscribed?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        isSubscribed: false,
    });

    const notify = useNotificationsStore();
    const page = usePage<SharedData>();

    const isAuthenticated = computed(() => !!page.props.auth?.user);

    const subscribed = ref<boolean>(props.isSubscribed);
    const isSubscribing = ref(false);
    const bellState = ref<'idle' | 'ringing' | 'subscribed'>('idle');

    function toggleSubscription() {
        if (typeof window === 'undefined') return;

        if (!isAuthenticated.value) {
            router.visit(route('login'), {
                onFinish: () => {
                    notify.info('Войдите, чтобы подписаться на уведомления о товаре');
                },
            });
            return;
        }

        if (isSubscribing.value) return;

        isSubscribing.value = true;
        bellState.value = 'ringing';

        try {
            const url = subscribed.value
                ? route('products.unsubscribe', props.productId)
                : route('products.subscribe', props.productId);

            const method = subscribed.value ? 'delete' : 'post';

            router[method](
                url,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        subscribed.value = !subscribed.value;
                        bellState.value = 'subscribed';

                        if (subscribed.value) {
                            playBellSound();
                        }

                        setTimeout(() => {
                            bellState.value = 'idle';
                        }, 700);
                    },
                    onFinish: () => {
                        isSubscribing.value = false;
                    },
                    onError: () => {
                        bellState.value = 'idle';
                        notify.error('Ошибка подписки, попробуйте позже!');
                    },
                },
            );
        } catch (e) {
            console.error('Subscription toggle failed', e);
            isSubscribing.value = false;
            bellState.value = 'idle';
        }
    }

    watch(
        () => props.isSubscribed,
        (v) => {
            subscribed.value = v;
        },
    );

    function playBellSound() {
        if (typeof window === 'undefined') return;

        try {
            const audio = new Audio('/audio/effects/bell.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        } catch {}
    }
</script>

<template>
    <div title="Пришлём письмо, когда товар в наличии">
        <button
            type="button"
            @click="toggleSubscription"
            :disabled="isSubscribing"
            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border-2 px-2 text-xs font-black uppercase tracking-widest outline-none transition-all duration-300 focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            :class="
                subscribed
                    ? 'border-rancho-pine bg-rancho-pine/10 text-rancho-pine hover:bg-rancho-pine/20 hover:fill-rancho-forest'
                    : 'border-rancho-ink-300 bg-white text-rancho-forest hover:border-rancho-forest hover:bg-rancho-ink-50 hover:fill-rancho-forest'
            "
        >
            <BellIcon
                v-if="!subscribed"
                class="h-4 w-4 transition-transform"
                :class="{
                    'bell-wiggle': bellState === 'ringing',
                    'bell-ring': bellState === 'subscribed',
                }"
                aria-hidden="true"
            />
            <BellSlashIcon
                v-else
                class="h-4 w-4 transition-transform"
                :class="{
                    'bell-wiggle': bellState === 'ringing',
                    'bell-ring': bellState === 'subscribed',
                }"
                aria-hidden="true"
            />

            <div class="hidden sm:block">
                <Transition name="fade" mode="out-in">
                    <span v-if="isSubscribing">Минуточку...</span>
                    <span v-else-if="subscribed">Вы подписаны — отписаться</span>
                    <span v-else>Подписаться</span>
                </Transition>
            </div>
        </button>
    </div>
</template>

<style scoped>
    @keyframes bell-wiggle {
        0%,
        100% {
            transform: rotate(0);
        }
        15% {
            transform: rotate(-12deg);
        }
        30% {
            transform: rotate(10deg);
        }
        45% {
            transform: rotate(-8deg);
        }
        60% {
            transform: rotate(6deg);
        }
        75% {
            transform: rotate(-4deg);
        }
    }

    @keyframes bell-ring {
        0% {
            transform: scale(1) rotate(0);
        }
        25% {
            transform: scale(1.15) rotate(-10deg);
        }
        50% {
            transform: scale(1.15) rotate(10deg);
        }
        75% {
            transform: scale(1.1) rotate(-5deg);
        }
        100% {
            transform: scale(1) rotate(0);
        }
    }

    .bell-wiggle {
        animation: bell-wiggle 0.6s ease-in-out;
        transform-origin: top center;
    }

    .bell-ring {
        animation: bell-ring 0.7s ease-in-out;
        transform-origin: top center;
    }

    @media (prefers-reduced-motion: reduce) {
        .bell-wiggle,
        .bell-ring {
            animation: none;
        }
    }
</style>
