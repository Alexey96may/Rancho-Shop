import { describe, expect, it } from 'vitest';

import { clamp, getStarStats } from './math';

describe('Math Utilities', () => {
    describe('getStarStats', () => {
        it('should return 4 full, 1 half, and 0 empty for a 4.5 rating', () => {
            const result = getStarStats(4.5);
            expect(result).toEqual({ full: 4, half: true, empty: 0 });
        });

        it('should handle perfect scores correctly (5.0)', () => {
            const result = getStarStats(5);
            expect(result).toEqual({ full: 5, half: false, empty: 0 });
        });

        it('should trigger half star only if remainder is >= 0.1', () => {
            expect(getStarStats(3.05).half).toBe(false);
            expect(getStarStats(3.1).half).toBe(true);
        });

        it('should not allow empty stars to be negative on overflow', () => {
            // Testing 6 out of 5 stars
            const result = getStarStats(6, 5);

            expect(result.full).toBe(5);
            expect(result.half).toBe(false);
            expect(result.empty).toBe(0);
        });

        it('should return all empty stars for null, undefined, or invalid strings', () => {
            const fallback = { full: 0, half: false, empty: 5 };

            expect(getStarStats(null)).toEqual(fallback);
            expect(getStarStats(undefined)).toEqual(fallback);
            expect(getStarStats('not-a-number')).toEqual(fallback);
        });

        it('should clamp negative ratings to 0', () => {
            const result = getStarStats(-5);

            expect(result.full).toBe(0);
            expect(result.empty).toBe(5);
        });
    });

    describe('clamp', () => {
        it('should restrain a value to the maximum bound', () => {
            expect(clamp(10, 0, 5)).toBe(5);
        });

        it('should restrain a value to the minimum bound', () => {
            expect(clamp(-10, 0, 5)).toBe(0);
        });

        it('should return the value itself if within bounds', () => {
            expect(clamp(3, 0, 5)).toBe(3);
        });
    });
});
