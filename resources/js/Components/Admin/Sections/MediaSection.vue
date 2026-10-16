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

    const { handleFile, clearNewVoice, removeGalleryItem } = useMediaUpload(props.modelValue);

    const selectedImage = ref<string | null>(null);

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const openImage = (url: string) => {
        if (url) {
            selectedImage.value = url;
        }
    };
</script>

<template>
    <div class="space-y-8 sm:space-y-12">
        <!-- АУДИО -->
        <AudioUpload
            :model-value="modelValue"
            :existing-audio-url="existingVoiceUrl"
            label="Голос животного:"
            id="animal-voice"
            @change="handleFile($event, 'voice')"
            @clear="clearNewVoice"
        />

        <!-- ГАЛЕРЕЯ -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Заголовок галереи -->
            <div class="flex items-center justify-between gap-3 px-2">
                <label
                    class="text-ranko-ink-400 text-[10px] font-black uppercase tracking-[0.15em]"
                >
                    Галерея изображений
                </label>

                <span class="text-ranko-ink-500 text-[10px] font-black uppercase tracking-wider">
                    Всего: {{ modelValue.gallery?.length || 0 }}
                </span>
            </div>

            <!-- Сетка галереи + кнопка добавления -->
            <MediaGallery
                v-model="modelValue.gallery"
                @remove="removeGalleryItem"
                @preview="openImage"
                variant="edit"
            >
                <label
                    class="border-ranko-ink-700 bg-ranko-ink-950/50 hover:border-ranko-mint/50 hover:bg-ranko-ink-900 focus-within:border-ranko-mint focus-within:ring-ranko-mint/20 group relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed transition-all focus-within:ring-4 sm:rounded-3xl"
                >
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleFile($event, 'gallery')"
                        class="sr-only"
                    />

                    <PhotoIcon
                        class="text-ranko-ink-500 group-hover:text-ranko-mint mb-2 h-7 w-7 transition-colors sm:h-8 sm:w-8"
                        aria-hidden="true"
                    />

                    <span
                        class="text-ranko-ink-500 group-hover:text-ranko-mint text-[10px] font-black uppercase tracking-widest transition-colors"
                    >
                        Добавить
                    </span>
                </label>
            </MediaGallery>

            <!-- Ошибка галереи -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-1 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
            >
                <p
                    v-if="modelValue.errors.gallery"
                    class="text-center text-[10px] font-bold uppercase tracking-wider text-rose-500"
                    role="alert"
                >
                    {{ modelValue.errors.gallery }}
                </p>
            </Transition>
        </div>

        <!-- МОДАЛКА ПРОСМОТРА -->
        <BaseModal :show="isModalOpen" variant="ui" @close="selectedImage = null">
            <AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </div>
</template>
