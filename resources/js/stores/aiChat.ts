import { computed, ref } from 'vue';

import axios from 'axios';
import { defineStore } from 'pinia';

export interface AiChatMessage {
    role: 'user' | 'assistant';
    content: string;
}

export interface HoneypotPayload {
    name: string;
    value: string;
    validFromName: string;
    validFromValue: string;
}

export const useAiChatStore = defineStore(
    'aiChat',
    () => {
        const sessionId = ref<string>('');
        const messages = ref<AiChatMessage[]>([]);
        const teaserDismissed = ref(false);
        const isSending = ref(false);
        const error = ref<string | null>(null);

        const canSend = computed(() => !isSending.value);

        const generateSessionId = (): string => {
            if (typeof window === 'undefined') return '';
            if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
                return crypto.randomUUID();
            }
            return `s_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
        };

        const ensureSession = () => {
            if (!sessionId.value) {
                sessionId.value = generateSessionId();
            }
        };

        const send = async (question: string, honeypot?: HoneypotPayload): Promise<void> => {
            const trimmed = question.trim();
            if (!trimmed || isSending.value) return;

            ensureSession();

            messages.value.push({ role: 'user', content: trimmed });
            error.value = null;
            isSending.value = true;

            try {
                const payload: Record<string, string> = {
                    question: trimmed,
                    session_id: sessionId.value,
                };

                if (honeypot) {
                    payload[honeypot.name] = honeypot.value;
                    payload[honeypot.validFromName] = honeypot.validFromValue;
                }

                const { data } = await axios.post('/api/ai/consult', payload, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                messages.value.push({
                    role: 'assistant',
                    content: data.answer ?? 'Извините, не могу ответить.',
                });
            } catch (e: any) {
                const status = e?.response?.status;
                if (status === 429) {
                    error.value = 'Слишком много вопросов. Подождите минуту.';
                } else if (status === 422) {
                    error.value = e?.response?.data?.message ?? 'Проверьте формулировку вопроса.';
                } else if (status >= 500) {
                    error.value = 'Сервис временно недоступен. Попробуйте позже.';
                } else {
                    error.value = e?.response?.data?.message ?? 'Не удалось получить ответ.';
                }
            } finally {
                isSending.value = false;
            }
        };

        const dismissTeaser = () => {
            teaserDismissed.value = true;
        };

        const clearHistory = () => {
            messages.value = [];
            error.value = null;
            teaserDismissed.value = false;
            sessionId.value = generateSessionId();
        };

        return {
            sessionId,
            messages,
            teaserDismissed,
            isSending,
            error,
            canSend,
            ensureSession,
            send,
            dismissTeaser,
            clearHistory,
        };
    },
    {
        persist: {
            key: 'ai_chat',
            pick: ['sessionId', 'messages', 'teaserDismissed'],
        },
    },
);
