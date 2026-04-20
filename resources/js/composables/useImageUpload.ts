import { nextTick, onUnmounted, ref } from 'vue';

import imageCompression from 'browser-image-compression';

export function useImageUpload(onFileSelect?: (file: File | null) => void) {
    const isCompressing = ref(false);
    const isDragging = ref(false);
    const imagePreview = ref<string | null>(null);
    const fileInput = ref<HTMLInputElement | null>(null);

    const processFile = async (file: File | undefined) => {
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Only images can be uploaded!');
            return;
        }

        const MAX_SIZE = 25 * 1024 * 1024;
        if (file.size > MAX_SIZE) {
            alert('The file is too large (over 25MB). Please reduce it manually.');
            return;
        }

        // Compression options
        const options = {
            maxSizeMB: 1,
            maxWidthOrHeight: 1200,
            useWebWorker: true,
        };

        isCompressing.value = true;
        await nextTick();

        try {
            const compressedFileBlob = await imageCompression(file, options);

            // Convert the Blob back to a File so Laravel recognizes it
            const compressedFile = new File([compressedFileBlob], file.name, {
                type: file.type,
                lastModified: Date.now(),
            });

            if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
            imagePreview.value = URL.createObjectURL(compressedFile);

            if (onFileSelect) {
                onFileSelect(compressedFile);
            }
        } catch (error) {
            console.error('Compression error:', error);
            alert('Compression error: ' + error);
            // If compression fails, send the original
            if (onFileSelect) onFileSelect(file);
        } finally {
            isCompressing.value = false;
        }
    };

    const onDrop = (e: DragEvent) => {
        e.preventDefault();
        isDragging.value = false;
        const file = e.dataTransfer?.files[0];
        processFile(file);
    };

    const handleFileChange = async (e: Event) => {
        const target = e.target as HTMLInputElement;
        if (target.files) {
            await processFile(target.files[0]);
        }
    };

    const triggerFileInput = () => fileInput.value?.click();

    const clearImage = () => {
        if (imagePreview.value && imagePreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(imagePreview.value);
        }

        imagePreview.value = null;
        if (onFileSelect) onFileSelect(null);
    };

    // Clearing memory when destroying a component
    onUnmounted(() => {
        if (imagePreview.value && imagePreview.value.startsWith('blob:')) {
            URL.revokeObjectURL(imagePreview.value);
        }
    });

    return {
        isDragging,
        imagePreview,
        isCompressing,
        fileInput,
        onDrop,
        onDragOver: () => (isDragging.value = true),
        onDragLeave: () => (isDragging.value = false),
        triggerFileInput,
        handleFileChange,
        clearImage,
    };
}
