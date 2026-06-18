import { describe, expect, it, vi } from 'vitest';

import { handleImageError } from '../utils';

describe('handleImageError', () => {
    it('should replace src with a placeholder and reset onerror', () => {
        const img = document.createElement('img');
        img.src = 'broken-image.jpg';

        img.onerror = () => {};

        const mockEvent = {
            target: img,
        } as unknown as Event;

        handleImageError(mockEvent);

        expect(img.src).toContain('/images/no-image.png');

        expect(img.onerror).toBeNull();
    });

    it('should not fall if the target is missing', () => {
        const mockEvent = { target: null } as unknown as Event;

        expect(() => handleImageError(mockEvent)).not.toThrow();
    });
});
