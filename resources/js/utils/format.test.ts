import { describe, expect, it } from 'vitest';

import { formatDistance, formatMoney } from './format';

describe('formatMoney', () => {
    it('returns 0 ₽ for null/undefined/0', () => {
        expect(formatMoney(null)).toBe('0 ₽');
        expect(formatMoney(undefined)).toBe('0 ₽');
        expect(formatMoney(0)).toBe('0 ₽');
    });

    it('formats whole rubles correctly', () => {
        expect(formatMoney(100)).toBe('1 ₽');
        expect(formatMoney(1000)).toBe('10 ₽');
    });

    it('formats kopeks correctly', () => {
        expect(formatMoney(150)).toBe('1,5 ₽');
        expect(formatMoney(199)).toBe('1,99 ₽');
    });

    it('rounds to max 2 decimal places', () => {
        expect(formatMoney(105)).toBe('1,05 ₽');
        expect(formatMoney(101)).toBe('1,01 ₽');
    });

    it('formats large numbers with grouping', () => {
        expect(formatMoney(1000000)).toBe('10 000 ₽'); // NBSP between thousands
    });
});

describe('formatDistance', () => {
    it('returns 0 км for null/undefined/0', () => {
        expect(formatDistance(null)).toBe('0 км');
        expect(formatDistance(undefined)).toBe('0 км');
        expect(formatDistance(0)).toBe('0 км');
    });

    it('formats meters to kilometers', () => {
        expect(formatDistance(1000)).toBe('1,0 км');
        expect(formatDistance(1500)).toBe('1,5 км');
    });

    it('keeps 1-2 decimal places', () => {
        expect(formatDistance(1234)).toBe('1,23 км');
        expect(formatDistance(1200)).toBe('1,2 км');
    });

    it('formats large distances', () => {
        expect(formatDistance(100000)).toBe('100,0 км');
    });
});
