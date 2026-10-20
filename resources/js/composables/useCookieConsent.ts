import { ref } from 'vue';

const STORAGE_KEY = 'cookie_consent';

// Глобальное состояние (синглтон на всё приложение)
const consent = ref<boolean | null>(null);

// Инициализация из localStorage (один раз при загрузке)
if (typeof window !== 'undefined') {
    const stored = localStorage.getItem(STORAGE_KEY);
    consent.value = stored === 'accepted' ? true : stored === 'rejected' ? false : null;
}

export function useCookieConsent() {
    const accept = () => {
        consent.value = true;
        if (typeof window !== 'undefined') localStorage.setItem(STORAGE_KEY, 'accepted');
    };

    const reject = () => {
        consent.value = false;
        if (typeof window !== 'undefined') localStorage.setItem(STORAGE_KEY, 'rejected');
    };

    const reset = () => {
        consent.value = null;
        if (typeof window !== 'undefined') localStorage.removeItem(STORAGE_KEY);
    };

    return { consent, accept, reject, reset };
}
