<script setup lang="ts">
    import { computed } from 'vue';

    import { ImagePlus, Loader2, X } from 'lucide-vue-next';

    import { useImageUpload } from '@/composables/useImageUpload';

    const props = defineProps<{
        modelValue: File | null;
        label?: string;
        existingImage?: string | null;
        error?: string;
        className?: string;
    }>();

    const emit = defineEmits<{
        (e: 'update:modelValue', value: File | null): void;
    }>();

    const {
        isDragging,
        imagePreview,
        isCompressing,
        fileInput,
        onDrop,
        onDragOver,
        onDragLeave,
        triggerFileInput,
        handleFileChange,
        clearImage,
    } = useImageUpload((file) => {
        emit('update:modelValue', file);
    });

    const displayPreview = computed(() => {
        return imagePreview.value || props.existingImage || null;
    });

    const removeImage = () => {
        clearImage();
        emit('update:modelValue', null);
    };
</script>

<template>
    <div class="group w-full" :class="className">
        <!-- Label -->
        <label
            v-if="label"
            class="text-rancho-ink-500 mb-1.5 ml-2 block text-[10px] font-black uppercase tracking-[0.15em]"
        >
            {{ label }}
        </label>

        <!-- Drop zone -->
        <div
            @click="triggerFileInput"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop"
            class="relative flex aspect-square cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed transition-all duration-300 sm:rounded-3xl"
            :class="[
                isDragging
                    ? 'border-rancho-mint bg-rancho-mint/5 scale-[0.98]'
                    : error
                      ? 'border-rose-500/50 bg-rose-500/5'
                      : 'border-rancho-ink-700 bg-rancho-ink-950 hover:border-rancho-ink-600 hover:bg-rancho-ink-900',
            ]"
            role="button"
            tabindex="0"
            :aria-label="label || 'Загрузить изображение'"
            @keydown.enter="triggerFileInput"
            @keydown.space.prevent="triggerFileInput"
        >
            <!-- Скрытый input -->
            <input
                type="file"
                ref="fileInput"
                class="hidden"
                accept="image/*"
                @change="handleFileChange"
            />

            <!-- Превью -->
            <div v-if="displayPreview" class="relative h-full w-full p-3 sm:p-4">
                <img
                    :src="displayPreview"
                    alt="Превью"
                    class="shadow-2xl h-full w-full rounded-xl object-cover sm:rounded-2xl"
                />

                <!-- Кнопка удаления -->
                <button
                    @click.stop="removeImage"
                    type="button"
                    class="shadow-lg absolute right-1 top-1 z-10 flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500 text-white transition-all hover:rotate-90 hover:bg-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 sm:right-2 sm:top-2"
                    aria-label="Удалить изображение"
                >
                    <X class="h-4 w-4" aria-hidden="true" />
                </button>

                <!-- Hover-оверлей -->
                <div
                    class="bg-rancho-ink-950/60 pointer-events-none absolute inset-3 flex items-center justify-center rounded-xl opacity-0 backdrop-blur-sm transition-opacity duration-300 group-hover:opacity-100 sm:inset-4 sm:rounded-2xl"
                >
                    <ImagePlus class="text-rancho-mint h-8 w-8" aria-hidden="true" />
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center gap-3 p-6 text-center sm:gap-4 sm:p-8">
                <div
                    class="bg-rancho-ink-900 text-rancho-ink-500 group-hover:bg-rancho-ink-800 group-hover:text-rancho-mint rounded-2xl p-3 transition-colors sm:p-4"
                >
                    <ImagePlus class="h-7 w-7 sm:h-8 sm:w-8" aria-hidden="true" />
                </div>

                <div class="space-y-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white">
                        Нажмите или перетащите
                    </p>
                    <p class="text-rancho-ink-500 text-[9px] font-bold uppercase tracking-widest">
                        JPG, PNG до 25MB
                    </p>
                </div>
            </div>

            <!-- Compressing overlay -->
            <div
                v-if="isCompressing"
                class="bg-ranko-ink-950/80 absolute inset-0 z-10 flex flex-col items-center justify-center backdrop-blur-sm"
            >
                <Loader2
                    class="text-ranko-mint mb-3 h-9 w-9 animate-spin sm:mb-4 sm:h-10 sm:w-10"
                    aria-hidden="true"
                />
                <span class="text-ranko-mint text-[10px] font-black uppercase tracking-widest">
                    Оптимизация...
                </span>
            </div>
        </div>

        <!-- Error -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p
                v-if="error"
                class="ml-2 mt-2 text-[10px] font-bold uppercase tracking-widest text-rose-500"
                role="alert"
            >
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
