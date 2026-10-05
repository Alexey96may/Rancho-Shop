import { computed } from 'vue';

import type { InertiaForm } from '@inertiajs/vue3';

import { useFlash } from '@/composables/ui/useFlash';
import type { Media } from '@/types';

export type GalleryItem = File | Media;

export interface MediaFormFields {
    voice: File | null;
    gallery: GalleryItem[];
}

export const useSingleImagePreview = (
    imageGetter: () => GalleryItem[] | GalleryItem | null | undefined,
) => {
    // 1. Check if the object is a file to upload
    const currentFileForUpload = computed<File | null>(() => {
        const val = imageGetter();
        if (!val) return null;
        const firstItem = Array.isArray(val) ? val[0] : val;
        return firstItem && firstItem instanceof File ? firstItem : null;
    });

    // 2. Check if the object is a media resource from the server
    const currentExistingImage = computed<string | null>(() => {
        const val = imageGetter();
        if (!val) return null;
        const firstItem = Array.isArray(val) ? val[0] : val;

        if (!firstItem || firstItem instanceof File) return null;

        return firstItem.url || null;
    });

    return {
        currentFileForUpload,
        currentExistingImage,
    };
};

export const useMediaUpload = (modelValue: InertiaForm<MediaFormFields>) => {
    const MAX_POST_SIZE = 20 * 1024 * 1024; // 20 MB

    const { notify } = useFlash();

    // Check: Is the user currently uploading a new file?
    const isNewVoiceSelected = computed(() => modelValue.voice instanceof File);

    // Universal function for displaying a photo or file preview
    const getPreview = (item: GalleryItem | string | null | undefined): string => {
        if (!item) return '';

        // 1. If this is a File object (a new selection in the input)
        if (item instanceof File) {
            return URL.createObjectURL(item);
        }

        // 2. If this is a Media object from the database
        if (typeof item === 'object') {
            if ('url' in item && typeof item.url === 'string') {
                return item.url;
            }
            if ('original_url' in item && typeof item.original_url === 'string') {
                return item.original_url;
            }
        }

        // 3. If a ready-made URL string is received
        if (typeof item === 'string') {
            return item;
        }

        return '';
    };

    const handleFile = (e: Event, field: 'voice' | 'gallery') => {
        const target = e.target as HTMLInputElement;
        const files = target.files;
        if (!files || files.length === 0) return;

        let currentTotalSize = 0;

        // Calculate the current size of files in the form
        if (Array.isArray(modelValue.gallery)) {
            modelValue.gallery.forEach((f: GalleryItem) => {
                if (f instanceof File) currentTotalSize += f.size;
            });
        }

        if (modelValue.voice instanceof File) {
            currentTotalSize += modelValue.voice.size;
        }

        const incomingFiles = Array.from(files);
        const newFilesSize = incomingFiles.reduce((acc, file) => acc + file.size, 0);

        if (currentTotalSize + newFilesSize > MAX_POST_SIZE) {
            notify('Общий размер файлов слишком велик! Лимит 20 МБ.', 'error');
            target.value = '';
            return;
        }

        if (field === 'gallery') {
            // Create a new array so that Vue and Inertia correctly track reactivity.
            modelValue.gallery = [...(modelValue.gallery || []), ...incomingFiles];
        } else {
            modelValue.voice = incomingFiles[0];
        }

        target.value = ''; // Reset the input value to allow for re-selection.
    };

    const clearNewVoice = () => {
        modelValue.voice = null;
    };

    const removeGalleryItem = (index: number) => {
        if (Array.isArray(modelValue.gallery)) {
            modelValue.gallery.splice(index, 1);
        }
    };

    return {
        isNewVoiceSelected,
        getPreview,
        handleFile,
        clearNewVoice,
        removeGalleryItem,
    };
};
