<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Media } from '@/types';
    // The same interface that we described above
    import { handleImageError } from '@/utils';

    defineOptions({
        inheritAttrs: false,
    });

    type ImageType = 'thumbnails' | 'previews';

    interface Props {
        src: string | Media;
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
</script>

<template>
    <picture v-if="isMediaObject && !isError">
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
            :class="['object-cover', className]"
        />
    </picture>

    <img
        v-else
        :src="
            isError
                ? `/images/no-${context === 'general' ? 'image' : context}.jpg`
                : (src as string)
        "
        :alt="isDecorative ? '' : alt || 'Rancho Image'"
        @load="onImageLoad"
        @error="onError"
        loading="lazy"
        v-bind="$attrs"
        :class="['object-cover', className]"
    />
</template>
