import { readonly, ref } from 'vue';

const STORAGE_KEY = 'cookie_consent';

type Consent = boolean | null;

// Инициализация: на сервере — null, на клиенте — из localStorage
const readConsent = (): Consent => {
    if (typeof window === 'undefined') return null;
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'accepted') return true;
    if (stored === 'rejected') return false;
    return null;
};

// Синглтон: создаётся один раз при импорте модуля
const consent = ref<Consent>(readConsent());

// Синхронизация между вкладками (storage event)
if (typeof window !== 'undefined') {
    window.addEventListener('storage', (e) => {
        if (e.key === STORAGE_KEY) {
            consent.value = readConsent();
        }
    });
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

    return { consent: readonly(consent), accept, reject, reset };
}
