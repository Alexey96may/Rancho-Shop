<script setup lang="ts">
    import { computed, onBeforeUnmount } from 'vue';

    import { MagnifyingGlassPlusIcon, PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import type { Media } from '@/types';

    type GalleryVariant = 'preview' | 'edit';

    interface Props {
        modelValue: Array<Media | File>;
        /** preview — компактная линия для клиента, edit — сетка с удалением */
        variant?: GalleryVariant;
    }

    const props = withDefaults(defineProps<Props>(), {
        variant: 'edit',
        maxPreview: 4,
    });

    const emit = defineEmits<{
        (e: 'remove', index: number): void;
        (e: 'preview', url: string): void;
    }>();

    const objectUrls = new Set<string>();

    const getImageUrl = (item: Media | File): string => {
        if (item instanceof File) {
            const url = URL.createObjectURL(item);
            objectUrls.add(url);
            return url;
        }
        return item.url;
    };

    onBeforeUnmount(() => {
        objectUrls.forEach((url) => URL.revokeObjectURL(url));
    });

    const remove = (index: number) => {
        emit('remove', index);
    };

    const preview = (item: Media | File) => {
        emit('preview', getImageUrl(item));
    };
</script>

<template>
    <!-- РЕЖИМ EDIT: сетка с удалением -->
    <div
        v-if="variant === 'edit'"
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
        role="list"
        aria-label="Галерея изображений товара"
    >
        <div
            v-for="(file, index) in modelValue"
            :key="'id' in file ? file.id : index"
            class="shadow-lg group relative aspect-square overflow-hidden rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900"
            role="listitem"
        >
            <AppImage
                :src="file"
                type="previews"
                :alt="file.name"
                class-name="h-full w-full  object-cover"
            />

            <div
                class="absolute inset-0 flex items-center justify-center gap-2 bg-rancho-ink-950/70 opacity-0 backdrop-blur-sm transition-opacity focus-within:opacity-100 group-hover:opacity-100"
            >
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white outline-none backdrop-blur-md transition-all hover:bg-white/25 focus-visible:ring-2 focus-visible:ring-white/60 active:scale-95"
                    title="Увеличить"
                >
                    <PhotoIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">Просмотреть фото</span>
                </button>

                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-500/30 text-rose-100 outline-none backdrop-blur-md transition-all hover:bg-rose-500/60 hover:text-white focus-visible:ring-2 focus-visible:ring-rose-300 active:scale-95"
                    title="Удалить"
                    @click="remove(index)"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">Удалить это изображение</span>
                </button>
            </div>
        </div>

        <slot />
    </div>

    <!-- РЕЖИМ PREVIEW: компактная линия миниатюр -->
    <div
        v-else
        class="flex w-full items-center gap-3 px-1 pb-4 sm:gap-4 sm:py-4 sm:pt-2"
        role="list"
        aria-label="Миниатюры изображений товара"
    >
        <button
            v-for="(file, index) in modelValue"
            :key="'id' in file ? file.id : index"
            type="button"
            role="listitem"
            class="group relative h-24 w-24 shrink-0 cursor-zoom-in overflow-hidden rounded-xl border border-rancho-ink-200 bg-rancho-ink-50 outline-none transition-all hover:border-rancho-pine focus-visible:ring-2 focus-visible:ring-rancho-pine/40 sm:h-36 sm:w-36"
            :aria-label="`Открыть фото ${index + 1}`"
            @click="preview(file)"
        >
            <AppImage
                :src="file"
                type="previews"
                :alt="file.name"
                class-name="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />

            <span
                class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-rancho-forest opacity-0 backdrop-blur-md transition-opacity group-hover:opacity-100"
                aria-hidden="true"
            >
                <MagnifyingGlassPlusIcon class="h-3 w-3" />
            </span>
        </button>
    </div>
</template>
