<script setup lang="ts">
    import { onBeforeUnmount } from 'vue';

    import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import type { Media } from '@/types';

    interface Props {
        modelValue: Array<Media | File>;
        isViewMode?: boolean;
    }

    const props = defineProps<Props>();
    const emit = defineEmits(['remove', 'preview']);

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
</script>

<template>
    <div
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
        role="list"
        aria-label="Галерея изображений товара"
    >
        <div
            v-for="(file, index) in modelValue"
            :key="'id' in file ? file.id : index"
            class="border-rancho-ink-800 bg-rancho-ink-900 shadow-lg group relative aspect-square overflow-hidden rounded-2xl border"
            role="listitem"
        >
            <AppImage :src="file" type="previews" :alt="file.name" :class-name="'h-full w-full '" />

            <div
                class="bg-rancho-ink-950/70 absolute inset-0 flex items-center justify-center gap-2 opacity-0 backdrop-blur-sm transition-opacity focus-within:opacity-100 group-hover:opacity-100"
            >
                <button
                    type="button"
                    @click="$emit('preview', getImageUrl(file))"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-md transition-all hover:bg-white/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 active:scale-95"
                    title="Увеличить"
                >
                    <PhotoIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">Просмотреть фото</span>
                </button>

                <button
                    type="button"
                    v-if="!isViewMode"
                    @click="remove(index)"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-500/30 text-rose-100 backdrop-blur-md transition-all hover:bg-rose-500/60 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300 active:scale-95"
                    title="Удалить"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">Удалить это изображение</span>
                </button>
            </div>
        </div>

        <slot />
    </div>
</template>
