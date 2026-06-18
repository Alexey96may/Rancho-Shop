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
        <label
            v-if="label"
            class="mb-3 ml-2 block text-[10px] font-black uppercase tracking-widest text-slate-500"
        >
            {{ label }}
        </label>

        <div
            @click="triggerFileInput"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop"
            class="relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed transition-all duration-500"
            :class="[
                isDragging
                    ? 'scale-[0.98] border-orange-500 bg-orange-500/5'
                    : 'border-slate-800 bg-slate-950 hover:border-slate-700',
                error ? 'border-red-500/50 bg-red-500/5' : '',
            ]"
        >
            <input
                type="file"
                ref="fileInput"
                class="hidden"
                accept="image/*"
                @change="handleFileChange"
            />

            <div v-if="displayPreview" class="relative h-full w-full p-4">
                <img
                    :src="displayPreview"
                    class="shadow-2xl h-full w-full rounded-[1.5rem] object-cover"
                />

                <button
                    @click.stop="removeImage"
                    type="button"
                    class="shadow-lg absolute -right-2 -top-2 z-10 flex h-8 w-8 items-center justify-center rounded-xl bg-red-500 text-white transition-transform hover:rotate-90 hover:bg-red-600"
                >
                    <X class="h-4 w-4" />
                </button>

                <div
                    class="absolute inset-0 flex items-center justify-center rounded-[1.5rem] bg-black/40 opacity-0 backdrop-blur-sm transition-opacity hover:opacity-100"
                >
                    <ImagePlus class="h-8 w-8 text-white" />
                </div>
            </div>

            <div v-else class="flex flex-col items-center gap-4 p-8 text-center">
                <div class="rounded-2xl bg-slate-900 p-4 text-slate-500">
                    <ImagePlus class="h-8 w-8" />
                </div>
                <div class="space-y-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-white">
                        Нажмите или перетащите
                    </p>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600">
                        JPG, PNG до 25MB
                    </p>
                </div>
            </div>

            <div
                v-if="isCompressing"
                class="absolute inset-0 z-10 flex flex-col items-center justify-center rounded-[2rem] bg-slate-950/80 backdrop-blur-sm"
            >
                <Loader2 class="mb-4 h-10 w-10 animate-spin text-orange-500" />
                <span class="text-[10px] font-black uppercase tracking-widest text-orange-500"
                    >Оптимизация...</span
                >
            </div>
        </div>

        <p
            v-if="error"
            class="ml-2 mt-3 text-[10px] font-bold uppercase tracking-widest text-red-500"
        >
            {{ error }}
        </p>
    </div>
</template>
