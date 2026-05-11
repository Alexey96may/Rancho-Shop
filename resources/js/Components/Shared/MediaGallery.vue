<script setup lang="ts">
    import { onBeforeUnmount } from 'vue';

    import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import type { Media } from '@/types';

    interface Props {
        modelValue: Array<Media | File>;
    }

    const props = defineProps<Props>();
    const emit = defineEmits(['remove', 'preview']);

    // Храним ссылки на Blob, чтобы потом их "прибрать"
    const objectUrls = new Set<string>();

    const getImageUrl = (item: Media | File): string => {
        if (item instanceof File) {
            const url = URL.createObjectURL(item);
            objectUrls.add(url);
            return url;
        }
        return item.url;
    };

    // Очистка памяти при уничтожении компонента
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
            class="shadow-lg group relative aspect-square overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"
            role="listitem"
        >
            <AppImage :src="file" type="previews" :alt="file.name" :class-name="'h-full w-full '" />

            <div
                class="absolute inset-0 flex items-center justify-center gap-2 bg-slate-950/60 opacity-0 transition-opacity focus-within:opacity-100 group-hover:opacity-100"
            >
                <button
                    type="button"
                    @click="$emit('preview', getImageUrl(file))"
                    class="rounded-full bg-white/10 p-2 text-white backdrop-blur-md hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    title="Увеличить"
                >
                    <PhotoIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">Просмотреть фото</span>
                </button>

                <button
                    type="button"
                    @click="remove(index)"
                    class="rounded-full bg-red-500/20 p-2 text-red-400 backdrop-blur-md hover:bg-red-500/40 focus:outline-none focus:ring-2 focus:ring-red-500"
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
