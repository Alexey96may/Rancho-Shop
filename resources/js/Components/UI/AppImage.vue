<script setup lang="ts">
    /**
     * AppImage Component
     * * A robust image handler that supports multiple input formats:
     * - Remote URLs (strings)
     * - Media Resource Objects (from Laravel Spatie Media Library)
     * - Raw Browser File Objects (for instant upload previews)
     * * Key Features:
     * - Smart Fallbacks: Automatically switches to context-specific placeholders on error.
     * - Modern Formats: Renders <picture> with AVIF/WebP support for Media objects.
     * - Zero Latency: Handles URL.createObjectURL for immediate local file previews.
     * - Accessible: Supports decorative images and ARIA-compliant alt text.
     * @props {string | Media | File} src - The image source (URL, Media object, or File).
     * @props {'thumbnails' | 'previews'} [type='previews'] - The Media object collection type.
     * @props {'product' | 'animal' | 'general'} [context='general'] - Used to determine the error placeholder.
     * @props {string} [alt=''] - Alt text for the image.
     * @props {string} [className] - Additional CSS classes for the <img> tag.
     * @props {boolean} [isDecorative=false] - If true, alt will be empty for screen readers.
     * * @emits {Event} load - Fired when the image successfully loads.
     */
    import { computed, ref } from 'vue';

    import { Media } from '@/types';
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

    // Check if src is our MediaResource object
    const isMediaObject = computed(() => {
        return typeof props.src === 'object' && props.src !== null && 'url' in props.src;
    });

    const isFileObject = computed(() => props.src instanceof File);

    // Extract formats for <source>
    const sources = computed(() => {
        if (!isMediaObject.value) return [];

        const media = props.src as Media;
        const typeKey = props.type as 'thumbnails' | 'previews';
        const data = media[typeKey];

        if (!data) return [];

        // The order is important: the browser takes the first matching one. AVIF -> WebP -> Original
        return [
            { url: data.avif, type: 'image/avif' },
            { url: data.webp, type: 'image/webp' },
            { url: data.original, type: `image/${media.mime_type.split('/')[1] || 'jpeg'}` },
        ].filter((s): s is { url: string; type: string } => {
            return typeof s.url === 'string' && s.url.length > 0;
        });
    });

    const fallbackSrc = computed((): string => {
        if (typeof props.src === 'string') return props.src;

        if (isFileObject.value) {
            return URL.createObjectURL(props.src as File);
        }

        if (isMediaObject.value) return (props.src as Media).url;

        return '';
    });

    const emit = defineEmits(['load']);
    const isError = ref(false);

    const onImageLoad = (e: Event) => emit('load', e);

    const onError = (e: Event) => {
        if (isError.value) return;
        isError.value = true;
        handleImageError(e, props.context);
    };

    const shouldRenderPicture = computed(() => {
        return isMediaObject.value && !isFileObject.value && !isError.value;
    });
</script>

<template>
    <picture v-if="shouldRenderPicture">
        <source
            v-for="source in sources"
            :key="source.url + source.type"
            :srcset="source.url"
            :type="source.type"
        />
        <img
            :src="fallbackSrc"
            :alt="isDecorative ? '' : alt || 'Молочная долина'"
            @error="onError"
            @load="onImageLoad"
            loading="lazy"
            v-bind="$attrs"
            :class="['h-full w-full object-cover', className]"
        />
    </picture>

    <img
        v-else
        :src="isError ? `/images/no-${context === 'general' ? 'image' : context}.jpg` : fallbackSrc"
        :alt="isDecorative ? '' : alt || 'Молочная долина'"
        @load="onImageLoad"
        @error="onError"
        loading="lazy"
        v-bind="$attrs"
        :class="['h-full w-full object-cover', className]"
    />
</template>
