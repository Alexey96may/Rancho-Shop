<script setup lang="ts">
    import { computed } from 'vue';

    import { MagnifyingGlassPlusIcon } from '@heroicons/vue/24/outline';

    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import type { Media } from '@/types';

    interface Props {
        mainPhoto?: Media | string | null;
        gallery?: Array<Media | File> | null;
        alt?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        mainPhoto: null,
        gallery: () => [],
        alt: '',
        maxPreview: 5,
    });

    const emit = defineEmits<{
        (e: 'preview', url: string): void;
        (e: 'update:gallery', value: Array<Media | File>): void;
    }>();

    /** Универсальное извлечение URL из Media | string */
    const resolveUrl = (item: Media | string | null | undefined): string => {
        if (!item) return '';
        if (typeof item === 'string') return item;
        return item.url;
    };

    const mainPhotoUrl = computed(() => resolveUrl(props.mainPhoto));

    const openMain = () => {
        if (mainPhotoUrl.value) {
            emit('preview', mainPhotoUrl.value);
        }
    };

    const onGalleryPreview = (url: string) => {
        emit('preview', url);
    };

    const onGalleryUpdate = (value: Array<Media | File>) => {
        emit('update:gallery', value);
    };
</script>

<template>
    <div class="shadow-sm rounded-3xl border border-rancho-ink-200 bg-white p-3 sm:p-4">
        <!-- Главное фото -->
        <button
            v-if="mainPhotoUrl"
            type="button"
            class="group relative block aspect-square w-full cursor-zoom-in overflow-hidden rounded-2xl border border-rancho-ink-100 bg-rancho-ink-50 outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30"
            :aria-label="`Открыть фото: ${alt}`"
            @click="openMain"
        >
            <AppImage
                :src="mainPhotoUrl"
                :alt="alt"
                class-name="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <span
                class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/80 text-rancho-forest opacity-0 backdrop-blur-md transition-opacity group-hover:opacity-100"
                aria-hidden="true"
            >
                <MagnifyingGlassPlusIcon class="h-4 w-4" />
            </span>
        </button>

        <div
            v-else
            class="flex aspect-square w-full items-center justify-center rounded-2xl border border-dashed border-rancho-ink-200 bg-rancho-ink-50 text-sm text-rancho-ink-400"
        >
            Нет фото
        </div>

        <!-- Галерея -->
        <div v-if="gallery && gallery.length" class="mt-3 sm:mt-4">
            <MediaGallery
                :model-value="gallery"
                variant="preview"
                @preview="onGalleryPreview"
                @update:model-value="onGalleryUpdate"
            />
        </div>
    </div>
</template>
