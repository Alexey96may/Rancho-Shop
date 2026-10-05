<script setup lang="ts">
    import { computed, ref } from 'vue';

    import type { InertiaForm } from '@inertiajs/vue3';

    import { PhotoIcon } from '@heroicons/vue/24/outline';

    import AudioUpload from '@/Components/Admin/Shared/AudioUpload.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import { useMediaUpload } from '@/composables/features/useMediaUpload';
    import type { GalleryItem, MediaFormFields } from '@/composables/features/useMediaUpload';

    const props = defineProps<{
        modelValue: InertiaForm<MediaFormFields & Record<string, any>>;
        existingVoiceUrl?: string | null;
    }>();

    const { getPreview, handleFile, clearNewVoice, removeGalleryItem } = useMediaUpload(
        props.modelValue,
    );

    const selectedImage = ref<string | null>(null);

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const openImage = (item: GalleryItem) => {
        const previewUrl = getPreview(item);
        if (previewUrl) {
            selectedImage.value = previewUrl;
        }
    };
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-12">
        <AudioUpload
            :model-value="modelValue"
            :existing-audio-url="existingVoiceUrl"
            label="Голос животного:"
            id="animal-voice"
            @change="handleFile($event, 'voice')"
            @clear="clearNewVoice"
        />

        <div class="space-y-6">
            <div class="flex items-center justify-between px-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Галерея изображений</label
                >
                <span class="text-[10px] font-bold uppercase text-slate-600"
                    >Всего: {{ modelValue.gallery?.length || 0 }}</span
                >
            </div>

            <MediaGallery
                v-model="modelValue.gallery"
                @remove="removeGalleryItem"
                @preview="openImage"
            >
                <label
                    class="relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-800 bg-slate-950/20 transition-all hover:border-orange-500/50 hover:bg-slate-950/40"
                >
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleFile($event, 'gallery')"
                        class="sr-only"
                    />
                    <PhotoIcon class="mb-2 h-8 w-8 text-slate-700" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Добавить</span
                    >
                </label>
            </MediaGallery>

            <p
                v-if="modelValue.errors.gallery"
                class="animate-pulse text-center text-[10px] font-bold uppercase text-red-500"
            >
                {{ modelValue.errors.gallery }}
            </p>
        </div>

        <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null">
            <AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </div>
</template>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
