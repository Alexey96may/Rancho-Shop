import { describe, expect, it } from 'vitest';

import { getAvatarColor, getInitials } from './user';

describe('User Helpers', () => {
    describe('getInitials', () => {
        it('should return initials for a full name', () => {
            expect(getInitials('Alexey Ivanov')).toBe('AI');
        });

        it('should return only the first two initials if more than two names are provided', () => {
            expect(getInitials('Ivan Ivanovich Ivanov')).toBe('II');
        });

        it('should return a single letter if only one name is provided', () => {
            expect(getInitials('Alexey')).toBe('AL');
        });

        it('should convert initials to uppercase', () => {
            expect(getInitials('alexey petrov')).toBe('AP');
        });

        it('should handle an empty string gracefully', () => {
            expect(getInitials('')).toBe('');
        });

        it('should ignore leading, trailing, and multiple spaces', () => {
            // Note: This requires .split(/\s+/).filter(Boolean) in your function
            expect(getInitials('  Alexey   Ivanov  ')).toBe('AI');
        });
    });

    describe('getAvatarColor', () => {
        const validColors = [
            'bg-rancho-olive',
            'bg-rancho-forest',
            'bg-amber-600',
            'bg-emerald-600',
            'bg-stone-500',
        ];

        it('should return a string present in the allowed colors list', () => {
            const color = getAvatarColor(5);
            expect(validColors).toContain(color);
        });

        it('should handle large IDs correctly using modulo', () => {
            const color = getAvatarColor(12345);
            expect(validColors).toContain(color);
        });

        it('should return a valid color when no ID is provided (default = 0)', () => {
            expect(validColors).toContain(getAvatarColor());
        });

        it('should always return a string from the predefined list despite shuffling', () => {
            for (let i = 0; i < 10; i++) {
                expect(validColors).toContain(getAvatarColor(i));
            }
        });
    });
});
