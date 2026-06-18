import { nextTick } from 'vue';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useImageUpload } from './useImageUpload';

vi.mock('browser-image-compression', () => {
    return {
        default: vi
            .fn()
            .mockImplementation(() =>
                Promise.resolve(new Blob(['compressed'], { type: 'image/jpeg' })),
            ),
    };
});

describe('useImageUpload Composable', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        vi.stubGlobal('URL', {
            createObjectURL: vi.fn(() => 'blob:http://localhost/test-url'),
            revokeObjectURL: vi.fn(),
        });

        vi.stubGlobal('alert', vi.fn());
    });

    it('must be initialized with default values', () => {
        const { imagePreview, isCompressing } = useImageUpload();
        expect(imagePreview.value).toBeNull();
        expect(isCompressing.value).toBe(false);
    });

    it('should block files larger than the limit (25MB)', async () => {
        const { handleFileChange } = useImageUpload();
        const largeFile = new File([''], 'huge.jpg', { type: 'image/jpeg' });

        Object.defineProperty(largeFile, 'size', { value: 30 * 1024 * 1024 });

        const alertMock = window.alert;

        const event = { target: { files: [largeFile] } } as unknown as Event;
        await handleFileChange(event);

        expect(alertMock).toHaveBeenCalledWith(expect.stringContaining('too large'));
    });

    it('should create a preview and call a callback after compression', async () => {
        const onFileSelect = vi.fn();
        const { handleFileChange, imagePreview } = useImageUpload(onFileSelect);

        const file = new File(['test'], 'test.jpg', { type: 'image/jpeg' });
        const event = { target: { files: [file] } } as unknown as Event;

        await handleFileChange(event);
        await nextTick();

        expect(imagePreview.value).toBe('blob:http://localhost/test-url');
        expect(onFileSelect).toHaveBeenCalled();
        expect(window.URL.createObjectURL).toHaveBeenCalled();
    });

    it('must clear the data when calling ClearImage', async () => {
        const { clearImage, imagePreview } = useImageUpload();

        const mockUrl = 'blob:http://localhost/some-uuid';
        imagePreview.value = mockUrl;

        clearImage();

        expect(imagePreview.value).toBeNull();
        expect(window.URL.revokeObjectURL).toHaveBeenCalledWith(mockUrl);
    });
});
