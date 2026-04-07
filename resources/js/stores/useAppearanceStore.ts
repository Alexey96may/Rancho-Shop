import { defineStore } from 'pinia';

interface AppearanceState {
    nightProgress: number; // от 0.0 до 1.0
}

export const useAppearanceStore = defineStore('appearance', {
    state: (): AppearanceState => ({
        nightProgress: 0,
    }),
    actions: {
        setNightProgress(value: number): void {
            this.nightProgress = Math.min(Math.max(value, 0), 1);
        },
    },
});
