<script setup lang="ts">
    /**
     * AppImage Component
     *
     * A robust image handler that supports multiple input formats:
     * - Remote URLs (strings)
     * - Media Resource Objects (from Laravel Spatie Media Library)
     * - Raw Browser File Objects (for instant upload previews)
     *
     * Key Features:
     * - Smart Fallbacks: switches to context-specific placeholders on error.
     * - Modern Formats: renders <picture> with AVIF/WebP support for Media objects.
     * - Blur-up: shows a low-quality blurred thumbnail while the full image loads.
     * - Smooth reveal: fades in the image via <Transition> (no abrupt swap).
     * - Zero Latency: uses URL.createObjectURL for local File previews (revoked on unmount).
     * - Accessible: supports decorative images and ARIA-compliant alt text.
     *
     * @props {string | Media | File} src — image source (URL, Media object, or File).
     * @props {'thumbnails' | 'previews'} [type='previews'] — Media collection used for full-size sources.
     * @props {'product' | 'animal' | 'general'} [context='general'] — error placeholder kind.
     * @props {string} [alt=''] — alt text for the image.
     * @props {string} [className] — extra CSS classes for the <img> tag.
     * @props {boolean} [isDecorative=false] — if true, alt is empty for screen readers.
     *
     * @emits {Event} load — fired when the image successfully loads.
     */
    import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

    import type { Media } from '@/types';
    import { handleImageError } from '@/utils';

    defineOptions({
        inheritAttrs: false,
    });

    type ImageType = 'thumbnails' | 'previews';

    interface Props {
        src: string | Media | File;
        type?: ImageType;
        context?: 'product' | 'animal' | 'general';
        alt?: string;
        className?: string;
        isDecorative?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        alt: '',
        type: 'previews',
        context: 'general',
        isDecorative: false,
    });

    const emit = defineEmits<{ (e: 'load', event: Event): void }>();

    // ─────────────── Состояния (объявить до watch!) ───────────────
    const isLoading = ref(true);
    const isError = ref(false);

    // ─────────────── Определение типа src ───────────────
    const isMediaObject = computed(
        () => typeof props.src === 'object' && props.src !== null && 'url' in props.src,
    );
    const isFileObject = computed(() => props.src instanceof File);

    // ─────────────── Object URL для File ───────────────
    const fileUrl = ref<string | null>(null);

    onBeforeUnmount(() => {
        if (fileUrl.value) URL.revokeObjectURL(fileUrl.value);
    });

    // ─────────────── Производные ───────────────
    const fallbackSrc = computed((): string => {
        if (typeof props.src === 'string') return props.src;
        if (isFileObject.value) return fileUrl.value ?? '';
        if (isMediaObject.value) {
            const media = props.src as Media;
            return media.previews?.original ?? media.url;
        }
        return '';
    });

    const sources = computed(() => {
        if (!isMediaObject.value) return [];
        const media = props.src as Media;
        const data = media[props.type];
        if (!data) return [];
        return [
            { url: data.avif, type: 'image/avif' },
            { url: data.webp, type: 'image/webp' },
        ].filter((s): s is { url: string; type: string } => {
            return typeof s.url === 'string' && s.url.length > 0;
        });
    });

    const blurSrc = computed((): string | null => {
        if (!isMediaObject.value) return null;
        const media = props.src as Media;
        return (
            media.thumbnails?.avif ??
            media.thumbnails?.webp ??
            media.thumbnails?.original ??
            media.previews?.original ??
            null
        );
    });

    const showBlur = computed(() => !!blurSrc.value && !isError.value);
    const showSkeleton = computed(() => !showBlur.value);
    const shouldRenderPicture = computed(
        () => isMediaObject.value && !isFileObject.value && !isError.value,
    );
    const errorSrc = computed(
        () => `/images/no-${props.context === 'general' ? 'image' : props.context}.jpg`,
    );
    const imgAlt = computed(() => (props.isDecorative ? '' : props.alt || 'Молочная долина'));

    // ─────────────── Обработчики ───────────────
    const onImageLoad = (e: Event) => {
        // два rAF — гарантия, что opacity-0 отрисован до смены на opacity-100
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                isLoading.value = false;
            });
        });
        emit('load', e);
    };

    const onError = (e: Event) => {
        isLoading.value = false;
        if (isError.value) return;
        isError.value = true;
        handleImageError(e, props.context);
    };

    const imgRef = ref<HTMLImageElement | null>(null);

    watch(
        () => fallbackSrc.value,
        (url, oldUrl) => {
            if (fileUrl.value) {
                URL.revokeObjectURL(fileUrl.value);
                fileUrl.value = null;
            }
            if (props.src instanceof File && typeof window !== 'undefined') {
                fileUrl.value = URL.createObjectURL(props.src);
            }
            // если URL не изменился — не сбрасываем isLoading
            if (url !== oldUrl) {
                isLoading.value = true;
                isError.value = false;
            }
        },
        { immediate: true },
    );

    onMounted(() => {
        if (imgRef.value?.complete) {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="relative h-full w-full overflow-hidden">
        <!-- ─────────── Основная картинка: всегда в DOM, плавно проявляется ─────────── -->
        <picture v-if="shouldRenderPicture">
            <source
                v-for="source in sources"
                :key="source.url + source.type"
                :srcset="source.url"
                :type="source.type"
            />
            <img
                :src="fallbackSrc"
                :alt="imgAlt"
                ref="imgRef"
                @error="onError"
                @load="onImageLoad"
                loading="lazy"
                decoding="async"
                v-bind="$attrs"
                :class="[
                    'h-full w-full object-cover transition-opacity duration-700 ease-out',
                    isLoading ? 'opacity-0' : 'opacity-100',
                    className,
                ]"
            />
        </picture>

        <img
            v-else
            :src="isError ? errorSrc : fallbackSrc"
            :alt="imgAlt"
            ref="imgRef"
            @load="onImageLoad"
            @error="onError"
            loading="lazy"
            decoding="async"
            v-bind="$attrs"
            :class="[
                'h-full w-full object-cover transition-opacity duration-700 ease-out',
                isLoading ? 'opacity-0' : 'opacity-100',
                className,
            ]"
        />

        <!-- ─────────── Плейсхолдер: blur или скелетон, поверх картинки ─────────── -->
        <Transition
            appear
            enter-active-class="transition-opacity duration-500 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="absolute inset-0 transition-opacity duration-500 ease-out"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isLoading && !isError"
                class="pointer-events-none absolute inset-0"
                aria-hidden="true"
            >
                <img
                    v-if="showBlur"
                    :src="blurSrc!"
                    class="h-full w-full scale-110 object-cover blur-xl"
                    loading="eager"
                    decoding="async"
                />
                <div v-else-if="showSkeleton" class="h-full w-full animate-pulse bg-rancho-paper" />
            </div>
        </Transition>
    </div>
</template>
