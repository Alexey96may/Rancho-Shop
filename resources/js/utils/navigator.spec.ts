import { beforeEach, describe, expect, it, vi } from 'vitest';

import { VIBRATE_PRESETS, triggerVibration } from './navigator';

describe('navigator utils: triggerVibration', () => {
    let vibrateSpy: any;

    beforeEach(() => {
        vi.clearAllMocks();
        vi.restoreAllMocks();

        vibrateSpy = vi.fn(() => true);
        vi.stubGlobal('navigator', {
            vibrate: vibrateSpy,
        });
    });

    it('should trigger vibration with default "click" preset when no arguments provided', () => {
        triggerVibration();

        expect(vibrateSpy).toHaveBeenCalledTimes(1);
        expect(vibrateSpy).toHaveBeenCalledWith(VIBRATE_PRESETS.click);
    });

    it('should trigger vibration with predefined presets', () => {
        triggerVibration('success');
        expect(vibrateSpy).toHaveBeenCalledWith(VIBRATE_PRESETS.success);

        triggerVibration('error');
        expect(vibrateSpy).toHaveBeenCalledWith(VIBRATE_PRESETS.error);

        expect(vibrateSpy).toHaveBeenCalledTimes(2);
    });

    it('should accept custom numbers or vibration arrays', () => {
        const customValue = 100;
        const customPattern = [100, 50, 100];

        triggerVibration(customValue);
        expect(vibrateSpy).toHaveBeenCalledWith(customValue);

        triggerVibration(customPattern);
        expect(vibrateSpy).toHaveBeenCalledWith(customPattern);
    });

    it('should not crash and do nothing if navigator.vibrate is undefined', () => {
        vi.stubGlobal('navigator', {});

        expect(() => triggerVibration('click')).not.toThrow();
    });

    it('should catch browser execution errors and log a console warning', () => {
        const warnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});

        const mockError = new Error('Not allowed by security policy');
        vibrateSpy.mockImplementationOnce(() => {
            throw mockError;
        });

        triggerVibration('click');

        expect(warnSpy).toHaveBeenCalledTimes(1);
        expect(warnSpy).toHaveBeenCalledWith(
            'Vibration failed or blocked by browser policy:',
            mockError,
        );
    });
});
