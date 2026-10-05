/**
 * Haptic feedback presets (duration in ms or "vibration-pause-vibration" patterns)
 */
export const VIBRATE_PRESETS = {
    click: 10, // Lightweight native click
    success: [20, 50, 20], // Fast double buzz
    error: [60, 50, 100], // A long, anxious buzzing
} as const;

export type VibratePreset = keyof typeof VIBRATE_PRESETS;

/**
 * Triggers device vibration, if supported by the browser.
 *
 * @param type A vibration preset ('click', 'success', 'error') or a custom number/array.
 */
export const triggerVibration = (type: VibratePreset | number | number[] = 'click'): void => {
    // Safe check for environments where `window` is physically absent (e.g., SSR)
    if (typeof window === 'undefined') return;

    if (navigator?.vibrate) {
        // If a preset key is provided, use it; otherwise, use the value as is.
        const pattern = typeof type === 'string' ? VIBRATE_PRESETS[type] : type;

        try {
            navigator.vibrate(pattern as VibratePattern);
        } catch (error) {
            console.warn('Vibration failed or blocked by browser policy:', error);
        }
    }
};
