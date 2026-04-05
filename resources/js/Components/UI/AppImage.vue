<script setup lang="ts">
    import { computed } from 'vue';

    import { Media } from '@/types';
    import { handleImageError } from '@/utils';

    defineOptions({
        inheritAttrs: false,
    });

    interface ImageMetadata {
        src: string;
        format: string;
        width?: number;
        height?: number;
    }

    type ImageType = 'thumbnails' | 'previews';

    interface Props {
        src: string | ImageMetadata[] | Media;
        type?: ImageType;
        alt?: string;
        className?: string;
        isDecorative?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        alt: '',
        type: 'previews',
        isDecorative: false,
    });

    const getMetadata = (media: Media, type: ImageType): ImageMetadata[] => {
        const source = media[type];
        if (!source) return [];

        const formats: ImageMetadata[] = [];

        if (source.avif) formats.push({ src: source.avif, format: 'avif' });
        if (source.webp) formats.push({ src: source.webp, format: 'webp' });
        if (source.original) {
            const ext = media.mime_type.split('/')[1] || 'jpg';
            formats.push({ src: source.original, format: ext });
        }

        return formats;
    };

    const optimizedSources = computed((): ImageMetadata[] => {
        if (typeof props.src === 'string') return [];

        if ('thumbnails' in (props.src as object)) {
            return getMetadata(props.src as Media, props.type);
        }

        return Array.isArray(props.src) ? props.src : [];
    });

    const isOptimized = computed(() => optimizedSources.value.length > 0);

    const fallbackSrc = computed((): string => {
        if (typeof props.src === 'string') return props.src;
        if (!isOptimized.value) return '';

        const fallback = optimizedSources.value.find((i) =>
            ['jpg', 'jpeg', 'png', 'gif'].includes(i.format.toLowerCase()),
        );

        return fallback ? fallback.src : optimizedSources.value[0].src;
    });

    const emit = defineEmits(['load']);

    const onImageLoad = (event: Event) => {
        emit('load', event);
    };
</script>

<template>
    <picture v-if="isOptimized">
        <source
            v-for="source in optimizedSources"
            :key="source.src"
            :srcset="source.src"
            :type="`image/${source.format}`"
        />
        <img
            :src="fallbackSrc"
            :alt="isDecorative ? '' : alt || 'Plant image'"
            :role="isDecorative ? 'presentation' : undefined"
            :aria-hidden="isDecorative ? 'true' : undefined"
            @error="handleImageError"
            @load="onImageLoad"
            loading="lazy"
            class="object-cover"
            v-bind="$attrs"
            :class="className"
        />
    </picture>

    <img
        v-else
        :src="src as string"
        :alt="isDecorative ? '' : alt || 'Plant image'"
        @load="onImageLoad"
        @error="handleImageError"
        loading="lazy"
        v-bind="$attrs"
        class="object-cover"
        :class="className"
    />
</template>
