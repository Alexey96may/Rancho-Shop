<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

    import { ChatBubbleLeftRightIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import AppImage from '@/Components/UI/AppImage.vue';
    import { useAiChatStore } from '@/stores/aiChat';

    type Theme = 'dark' | 'light';

    const props = withDefaults(
        defineProps<{
            teaser?: string;
            botName?: string;
            theme?: Theme;
            position?: 'bottom-right' | 'bottom-left';
            teaserDelay?: number;
            avatar?: string;
        }>(),
        {
            teaser: '',
            botName: 'ИИ-помощник',
            theme: 'light',
            position: 'bottom-right',
            teaserDelay: 30000,
            avatar: '/images/ai/bot-avatar.png',
        },
    );

    const emit = defineEmits<{
        (e: 'open'): void;
    }>();

    const store = useAiChatStore();

    const isDark = computed(() => props.theme === 'dark');
    const isRight = computed(() => props.position === 'bottom-right');

    const teaserVisible = ref(false);
    const mounted = ref(false);
    let teaserTimer: ReturnType<typeof setTimeout> | null = null;

    onMounted(() => {
        mounted.value = true;
        if (store.teaserDismissed) return;
        if (props.teaser && props.teaserDelay > 0) {
            teaserTimer = setTimeout(() => {
                if (!store.teaserDismissed) teaserVisible.value = true;
            }, props.teaserDelay);
        }
    });

    onBeforeUnmount(() => {
        if (teaserTimer) clearTimeout(teaserTimer);
    });

    const dismissTeaser = () => {
        teaserVisible.value = false;
        store.dismissTeaser();
        if (teaserTimer) clearTimeout(teaserTimer);
    };

    const open = () => {
        dismissTeaser();
        emit('open');
    };

    /* ---------- Стили ---------- */
    const buttonClass = computed(() =>
        isDark.value
            ? 'bg-rancho-ember text-rancho-ink-950 hover:bg-rancho-ember/90 shadow-lg shadow-rancho-ember/30 focus-visible:ring-rancho-ember/40'
            : 'bg-rancho-pine text-rancho-paper hover:bg-rancho-forest shadow-lg shadow-rancho-pine/30 focus-visible:ring-rancho-pine/40',
    );

    const teaserClass = computed(() =>
        isDark.value
            ? 'border-rancho-ink-800 bg-rancho-ink-900 text-rancho-ink-100 shadow-xl shadow-black/40'
            : 'border-rancho-ink-200 bg-rancho-paper text-rancho-ink-900 shadow-xl shadow-rancho-forest/10',
    );

    const teaserTextClass = computed(() =>
        isDark.value ? 'text-rancho-ink-200' : 'text-rancho-ink-700',
    );

    const positionClass = computed(() =>
        isRight.value ? 'right-4 sm:right-6' : 'left-4 sm:left-6',
    );

    const teaserAlignClass = computed(() => (isRight.value ? 'items-end' : 'items-start'));

    const glowClass = computed(() => (isDark.value ? 'bg-rancho-ember/30' : 'bg-rancho-pine/60'));

    const pulseClass = computed(() => (isDark.value ? 'bg-rancho-buttercup' : 'bg-rancho-olive'));
</script>

<template>
    <div
        v-if="mounted"
        class="fixed bottom-4 z-40 flex flex-col gap-3 sm:bottom-6"
        :class="[positionClass, teaserAlignClass]"
    >
        <!-- TEASER -->
        <Transition name="teaser">
            <div
                v-if="teaserVisible && teaser"
                class="relative max-w-[260px] rounded-2xl border px-4 py-3 sm:max-w-xs"
                :class="[teaserClass, isRight ? 'rounded-br-sm' : 'rounded-bl-sm']"
                role="status"
                aria-live="polite"
            >
                <button
                    type="button"
                    class="absolute -right-1.5 -top-1.5 rounded-full p-1 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-ember/40"
                    :class="
                        isDark
                            ? 'bg-rancho-ink-800 text-rancho-ink-400 hover:text-rancho-ink-100'
                            : 'bg-rancho-ink-100 text-rancho-ink-500 hover:text-rancho-ink-900'
                    "
                    aria-label="Скрыть подсказку"
                    @click="dismissTeaser"
                >
                    <XMarkIcon class="h-3 w-3" aria-hidden="true" />
                </button>

                <p class="pr-4 text-sm leading-relaxed" :class="teaserTextClass">
                    {{ teaser }}
                </p>

                <button
                    type="button"
                    class="mt-2 text-[10px] font-black uppercase tracking-widest transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-ember/40"
                    :class="
                        isDark
                            ? 'text-rancho-ember hover:text-rancho-ember/80'
                            : 'text-rancho-pine hover:text-rancho-forest'
                    "
                    @click="open"
                >
                    Открыть чат
                </button>
            </div>
        </Transition>

        <!-- BUTTON -->
        <button
            type="button"
            class="group relative flex h-10 w-10 items-center justify-center rounded-full outline-none transition-all focus-visible:ring-2 active:scale-95 sm:h-16 sm:w-16"
            :class="buttonClass"
            :aria-label="`Открыть чат с ${botName}`"
            @click="open"
        >
            <span
                class="animate-glow pointer-events-none absolute inset-0 -z-10 rounded-full"
                :class="glowClass"
                aria-hidden="true"
            ></span>

            <span
                class="relative z-10 flex h-full w-full items-center justify-center overflow-hidden rounded-full"
            >
                <AppImage
                    v-if="avatar"
                    :src="avatar"
                    :alt="botName"
                    context="general"
                    class="h-full w-full transition-transform duration-300 group-hover:scale-105"
                />

                <ChatBubbleLeftRightIcon
                    v-else
                    class="h-4 w-4 transition-transform group-hover:scale-110 sm:h-7 sm:w-7"
                    aria-hidden="true"
                />
            </span>

            <!-- Пульс-индикатор -->
            <span
                v-if="teaser && !store.teaserDismissed"
                class="absolute -right-0.5 -top-0.5 z-20 flex h-3 w-3"
                aria-hidden="true"
            >
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                    :class="pulseClass"
                ></span>
                <span class="relative inline-flex h-3 w-3 rounded-full" :class="pulseClass"></span>
            </span>
        </button>
    </div>
</template>

<style scoped>
    @keyframes glow {
        0%,
        100% {
            opacity: 0.5;
            transform: scale(1);
            filter: blur(6px);
        }
        50% {
            opacity: 0.9;
            transform: scale(1.15);
            filter: blur(10px);
        }
    }

    .animate-glow {
        animation: glow 3s ease-in-out infinite;
    }

    .teaser-enter-active,
    .teaser-leave-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .teaser-enter-from,
    .teaser-leave-to {
        opacity: 0;
        transform: translateY(8px) scale(0.96);
    }

    @media (prefers-reduced-motion: reduce) {
        .teaser-enter-active,
        .teaser-leave-active,
        .animate-glow,
        .animate-ping {
            animation: none;
            transition: none;
        }
    }
</style>
