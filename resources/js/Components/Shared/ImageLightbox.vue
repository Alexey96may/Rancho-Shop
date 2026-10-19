<script setup lang="ts">
    import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

    import { XMarkIcon } from '@heroicons/vue/24/outline';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/zoom';
    import { Keyboard, Navigation, Zoom } from 'swiper/modules';
    import type { Swiper as SwiperClass } from 'swiper/types';
    import { Swiper, SwiperSlide } from 'swiper/vue';

    interface Props {
        images: string[];
        modelValue: number | null;
        alt?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        alt: 'Просмотр изображения',
    });

    const emit = defineEmits<{
        (e: 'update:modelValue', value: number | null): void;
    }>();

    const isOpen = computed(() => props.modelValue !== null);
    const hasMultiple = computed(() => props.images.length > 1);

    const currentIndex = computed({
        get: () => props.modelValue ?? 0,
        set: (v: number) => emit('update:modelValue', v),
    });

    const mainSwiper = ref<SwiperClass | null>(null);

    const onSwiper = (swiper: SwiperClass) => {
        mainSwiper.value = swiper;
    };

    const onSlideChange = (swiper: SwiperClass) => {
        currentIndex.value = swiper.activeIndex;
    };

    const goTo = (index: number) => {
        currentIndex.value = index;
        mainSwiper.value?.slideTo(index);
    };

    const close = () => emit('update:modelValue', null);

    // Блокировка скролла body
    watch(
        isOpen,
        (open) => {
            if (typeof document === 'undefined') return;
            document.body.classList.toggle('modal-open', open);
        },
        { immediate: true },
    );

    // === History-стек для кнопки «Назад» ===

    let ignoreNextPop = false;

    function pushHistoryIfNeeded() {
        if (typeof window === 'undefined') return;
        if (!window.history.state?.lightbox) {
            window.history.pushState(
                { ...window.history.state, lightbox: true },
                '',
                window.location.href,
            );
        }
    }

    function popHistoryIfNeeded() {
        if (typeof window === 'undefined') return;
        if (window.history.state?.lightbox && !ignoreNextPop) {
            ignoreNextPop = true;
            window.history.back();
        }
    }

    watch(
        () => props.modelValue,
        (val, prev) => {
            if (typeof window === 'undefined') return;

            if (val !== null && prev === null) {
                pushHistoryIfNeeded();
            } else if (val === null && prev !== null) {
                popHistoryIfNeeded();
            }
        },
    );

    function onPopState() {
        if (typeof window === 'undefined') return;

        if (ignoreNextPop) {
            ignoreNextPop = false;
            return;
        }

        if (props.modelValue !== null) {
            close();
        }
    }

    function onKeydown(e: KeyboardEvent) {
        if (!isOpen.value) return;
        if (e.key === 'Escape') close();
    }

    onMounted(() => {
        if (typeof window === 'undefined') return;
        window.addEventListener('popstate', onPopState);
        window.addEventListener('keydown', onKeydown);
    });

    onUnmounted(() => {
        if (typeof window === 'undefined') return;
        window.removeEventListener('popstate', onPopState);
        window.removeEventListener('keydown', onKeydown);
        document.body.classList.remove('modal-open');
        popHistoryIfNeeded();
    });

    const openAt = (index: number) => {
        if (index >= 0 && index < props.images.length) {
            emit('update:modelValue', index);
        }
    };

    const openByUrl = (url: string) => {
        const index = props.images.indexOf(url);
        if (index >= 0) openAt(index);
    };

    defineExpose({ openAt, openByUrl, close });
</script>

<template>
    <Teleport to="body">
        <Transition name="lightbox">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[9999] flex flex-col bg-rancho-paper/95 backdrop-blur-md"
                role="dialog"
                aria-modal="true"
                :aria-label="alt"
                @click.self="close"
            >
                <!-- HEADER -->
                <header class="flex shrink-0 items-center justify-between gap-3 p-4 sm:p-6">
                    <span
                        v-if="hasMultiple"
                        class="rounded-full bg-rancho-ink-950/10 px-3 py-1 text-xs font-bold text-rancho-forest backdrop-blur-md sm:text-sm"
                    >
                        {{ currentIndex + 1 }} / {{ images.length }}
                    </span>

                    <button
                        type="button"
                        class="ml-auto flex h-10 w-10 items-center justify-center rounded-full bg-rancho-ink-950/10 text-rancho-forest transition-all hover:rotate-90 hover:bg-rancho-ink-950/20 focus:outline-none sm:h-11 sm:w-11"
                        aria-label="Закрыть"
                        @click="close"
                    >
                        <XMarkIcon class="h-5 w-5 sm:h-6 sm:w-6" />
                    </button>
                </header>

                <!-- SWIPER -->
                <div class="flex min-h-0 flex-1 items-center justify-center px-4 sm:px-8">
                    <Swiper
                        :modules="[Navigation, Keyboard, Zoom]"
                        :navigation="hasMultiple"
                        :keyboard="{ enabled: true }"
                        :zoom="{ maxRatio: 2 }"
                        :space-between="16"
                        :slides-per-view="1"
                        :initial-slide="currentIndex"
                        class="!h-full !w-full"
                        @swiper="onSwiper"
                        @slide-change="onSlideChange"
                    >
                        <SwiperSlide
                            v-for="(url, i) in images"
                            :key="i"
                            class="!flex !items-center !justify-center"
                        >
                            <div class="swiper-zoom-container !h-full !w-full">
                                <img
                                    :src="url"
                                    :alt="`${alt} ${i + 1}`"
                                    class="max-h-[70vh] max-w-full rounded-2xl object-contain sm:max-h-[75vh]"
                                    draggable="false"
                                />
                            </div>
                        </SwiperSlide>
                    </Swiper>
                </div>

                <!-- THUMBS -->
                <div
                    v-if="hasMultiple"
                    class="mt-4 flex shrink-0 gap-2 overflow-x-auto px-4 pb-4 sm:justify-center sm:px-8 sm:pb-6"
                >
                    <button
                        v-for="(url, i) in images"
                        :key="`thumb-${i}`"
                        type="button"
                        class="h-14 w-14 shrink-0 cursor-pointer overflow-hidden rounded-lg border-2 transition-all sm:h-16 sm:w-16"
                        :class="
                            currentIndex === i
                                ? 'border-rancho-pine opacity-100'
                                : 'border-transparent opacity-50 hover:opacity-80'
                        "
                        :aria-label="`Изображение ${i + 1}`"
                        @click="goTo(i)"
                    >
                        <img
                            :src="url"
                            :alt="`Миниатюра ${i + 1}`"
                            class="h-full w-full object-cover"
                            draggable="false"
                        />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
    /* === Transition: появление / уход === */
    .lightbox-enter-active,
    .lightbox-leave-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s ease;
    }

    .lightbox-enter-from {
        opacity: 0;
        transform: scale(0.96);
    }

    .lightbox-leave-to {
        opacity: 0;
        transform: scale(0.96);
    }

    /* Стрелки Swiper */
    :deep(.swiper-button-next),
    :deep(.swiper-button-prev) {
        width: 44px;
        height: 44px;
        color: #1c3f34;
        padding: 12px;
        background-color: rgb(255 255 255 / 0.7);
        border-radius: 9999px;
        backdrop-filter: blur(4px);
    }

    :deep(.swiper-button-next::after),
    :deep(.swiper-button-prev::after) {
        font-size: 18px;
        font-weight: 900;
    }

    @media (max-width: 640px) {
        :deep(.swiper-button-next),
        :deep(.swiper-button-prev) {
            display: none;
        }
    }

    img {
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .lightbox-enter-active,
        .lightbox-leave-active {
            transition: none;
        }
    }
</style>
