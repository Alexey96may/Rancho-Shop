<script setup lang="ts">
    import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import { Media } from '@/types';

    const props = defineProps<{
        modelValue: Media[]; // Массив из File или объектов Media
    }>();

    const emit = defineEmits(['remove', 'preview']);

    // Универсальная функция получения ссылки
    const getImageUrl = (item: any) => {
        if (!item) return '';
        if (item instanceof File) return URL.createObjectURL(item);
        return item.url || ''; // Твой случай: берем из item.url
    };

    const remove = (index: number) => {
        emit('remove', index);
    };
</script>

<template>
    <div
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
        role="list"
        aria-label="Галерея изображений"
    >
        <div
            v-for="(file, index) in modelValue"
            :key="file.id || index"
            class="shadow-lg group relative aspect-square overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"
            role="listitem"
        >
            <AppImage :src="file" type="previews" :alt="file.name" :class-name="'h-full w-full '" />

            <div
                class="absolute inset-0 flex items-center justify-center gap-2 bg-slate-950/60 opacity-0 transition-opacity group-hover:opacity-100"
            >
                <button
                    type="button"
                    @click="$emit('preview', getImageUrl(file))"
                    class="rounded-full bg-white/10 p-2 text-white backdrop-blur-md hover:bg-white/20"
                    aria-label="Увеличить изображение"
                >
                    <PhotoIcon class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    @click="remove(index)"
                    class="rounded-full bg-red-500/20 p-2 text-red-400 backdrop-blur-md hover:bg-red-500/40"
                    aria-label="Удалить изображение"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>
        </div>

        <slot />
    </div>
</template>
