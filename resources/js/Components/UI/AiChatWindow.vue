<script setup lang="ts">
    import { computed, nextTick, onMounted, ref, watch } from 'vue';

    import { PaperAirplaneIcon } from '@heroicons/vue/24/outline';

    import AppImage from '@/Components/UI/AppImage.vue';
    import { useAiChatStore } from '@/stores/aiChat';

    type Theme = 'dark' | 'light';

    const props = withDefaults(
        defineProps<{
            quickQuestions?: string[];
            maxLength?: number;
            avatar?: string;
            theme?: Theme;
            honeypot?: {
                enabled: boolean;
                nameFieldName: string;
                validFromFieldName: string;
                encryptedValidFrom: string;
            };
        }>(),
        {
            quickQuestions: () => ['Как оформить заказ?', 'Условия доставки', 'Способы оплаты'],
            maxLength: 1000,
            avatar: '/images/ai/bot-avatar.png',
            theme: 'dark',
            honeypot: undefined,
        },
    );

    const store = useAiChatStore();

    const input = ref('');
    const formHoneypot = ref('');
    const scrollRef = ref<HTMLElement | null>(null);
    const textareaRef = ref<HTMLTextAreaElement | null>(null);
    const mounted = ref(false);

    const isDark = computed(() => props.theme === 'dark');

    const messages = computed(() => store.messages);
    const isSending = computed(() => store.isSending);
    const error = computed(() => store.error);
    const canSend = computed(() => input.value.trim().length > 0 && !store.isSending);

    onMounted(() => {
        mounted.value = true;
        store.ensureSession();
        scrollToBottom(true);
    });

    /* ---------- Автоскролл ---------- */
    const isNearBottom = (): boolean => {
        const el = scrollRef.value;
        if (!el) return true;
        return el.scrollHeight - el.scrollTop - el.clientHeight < 80;
    };

    const scrollToBottom = async (force = false) => {
        await nextTick();
        if (!force && !isNearBottom()) return;
        scrollRef.value?.scrollTo({
            top: scrollRef.value.scrollHeight,
            behavior: 'smooth',
        });
    };

    /* ---------- Автовысота textarea ---------- */
    const resizeTextarea = () => {
        const el = textareaRef.value;
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = `${Math.min(el.scrollHeight, 128)}px`;
    };

    watch(input, resizeTextarea);

    /* ---------- Отправка ---------- */
    const send = async (forcedQuestion?: string) => {
        const question = (forcedQuestion ?? input.value).trim();
        if (!question || store.isSending) return;

        input.value = '';
        await scrollToBottom(true);

        const honeypotPayload = props.honeypot?.enabled
            ? {
                  name: props.honeypot.nameFieldName,
                  value: formHoneypot.value,
                  validFromName: props.honeypot.validFromFieldName,
                  validFromValue: props.honeypot.encryptedValidFrom,
              }
            : undefined;

        await store.send(question, honeypotPayload);
        await scrollToBottom();
    };

    const onKeydown = (e: KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (canSend.value) send();
        }
    };

    /* ---------- Стили по теме ---------- */
    const emptyTextClass = computed(() =>
        isDark.value ? 'text-rancho-ink-400' : 'text-rancho-ink-500',
    );

    const quickBtnClass = computed(() =>
        isDark.value
            ? 'border-rancho-ink-700 bg-rancho-ink-800/60 text-rancho-ink-200 hover:border-rancho-mint/50 hover:bg-rancho-ink-800 hover:text-rancho-ink-100 focus-visible:ring-rancho-mint/40'
            : 'border-rancho-ink-200 bg-rancho-paper text-rancho-ink-600 hover:border-rancho-pine/50 hover:bg-rancho-ink-100 hover:text-rancho-ink-900 focus-visible:ring-rancho-pine/40',
    );

    const userBubbleClass = computed(() =>
        isDark.value ? 'bg-rancho-mint text-rancho-ink-950' : 'bg-rancho-pine text-rancho-paper',
    );

    const assistantBubbleClass = computed(() =>
        isDark.value
            ? 'bg-rancho-ink-800 text-rancho-ink-100'
            : 'bg-rancho-ink-100 text-rancho-ink-900 border border-rancho-ink-200',
    );

    const typingBubbleClass = computed(() =>
        isDark.value ? 'bg-rancho-ink-800' : 'bg-rancho-ink-100 border border-rancho-ink-200',
    );

    const dotClass = computed(() => (isDark.value ? 'bg-rancho-ink-500' : 'bg-rancho-ink-400'));

    const inputBorderClass = computed(() =>
        isDark.value ? 'border-rancho-ink-800' : 'border-rancho-ink-200',
    );

    const textareaClass = computed(() =>
        isDark.value
            ? 'border-rancho-ink-700 bg-rancho-ink-950 text-rancho-ink-100 placeholder-rancho-ink-500 focus:border-rancho-mint focus:ring-rancho-mint/30'
            : 'border-rancho-ink-200 bg-rancho-paper text-rancho-ink-900 placeholder-rancho-ink-400 focus:border-rancho-pine focus:ring-rancho-pine/30',
    );

    const sendBtnClass = computed(() =>
        isDark.value
            ? 'bg-rancho-mint text-rancho-ink-950 hover:bg-rancho-mint/90 focus-visible:ring-rancho-mint/40'
            : 'bg-rancho-pine text-rancho-paper hover:bg-rancho-forest focus-visible:ring-rancho-pine/40',
    );

    const clearBtnClass = computed(() =>
        isDark.value
            ? 'text-rancho-ink-500 hover:text-rancho-ink-200 focus-visible:ring-rancho-mint/40'
            : 'text-rancho-ink-500 hover:text-rancho-ink-900 focus-visible:ring-rancho-pine/40',
    );

    const scrollbarClass = computed(() =>
        isDark.value ? 'custom-scrollbar' : 'custom-scrollbar-light',
    );
</script>

<template>
    <div v-if="mounted" class="flex h-[60vh] max-h-[500px] flex-col">
        <!-- HONEYPOT (скрытые поля для защиты от ботов) -->
        <div v-if="honeypot?.enabled" style="display: none" aria-hidden="true">
            <input
                type="text"
                v-model="formHoneypot"
                :name="honeypot.nameFieldName"
                :id="honeypot.nameFieldName"
                tabindex="-1"
                autocomplete="off"
            />
            <input
                type="hidden"
                :name="honeypot.validFromFieldName"
                :value="honeypot.encryptedValidFrom"
            />
        </div>

        <!-- MESSAGES -->
        <div
            ref="scrollRef"
            class="flex-1 space-y-3 overflow-y-auto pr-1"
            :class="scrollbarClass"
            role="log"
            aria-live="polite"
        >
            <!-- Пустой экран -->
            <div
                v-if="!messages.length && !isSending"
                class="flex h-full flex-col items-center justify-center gap-4 py-6"
            >
                <p class="text-center text-sm" :class="emptyTextClass">
                    Задайте вопрос — я подскажу по доставке, оплате и заказам.
                </p>

                <div v-if="quickQuestions.length" class="flex flex-wrap justify-center gap-2">
                    <button
                        v-for="q in quickQuestions"
                        :key="q"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-xs transition-colors focus:outline-none focus-visible:ring-2"
                        :class="quickBtnClass"
                        @click="send(q)"
                    >
                        {{ q }}
                    </button>
                </div>
            </div>

            <!-- Сообщения -->
            <div
                v-for="(msg, i) in messages"
                :key="i"
                class="flex items-end gap-2"
                :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
            >
                <div
                    v-if="msg.role === 'assistant' && avatar"
                    class="mb-1 h-7 w-7 shrink-0 overflow-hidden rounded-full sm:h-12 sm:w-12"
                >
                    <AppImage
                        :src="avatar"
                        alt=""
                        context="general"
                        is-decorative
                        class="h-full w-full"
                    />
                </div>

                <div
                    class="max-w-[80%] whitespace-pre-line rounded-2xl px-4 py-2.5 text-sm leading-relaxed"
                    :class="[
                        msg.role === 'user' ? 'rounded-br-sm' : 'rounded-bl-sm',
                        msg.role === 'user' ? userBubbleClass : assistantBubbleClass,
                    ]"
                >
                    {{ msg.content }}
                </div>
            </div>

            <!-- Индикатор печати -->
            <div v-if="isSending" class="flex items-end gap-2">
                <div v-if="avatar" class="mb-1 h-7 w-7 shrink-0 overflow-hidden rounded-full">
                    <AppImage
                        :src="avatar"
                        alt=""
                        context="general"
                        is-decorative
                        class="h-full w-full"
                    />
                </div>
                <div
                    class="flex items-center gap-1.5 rounded-2xl px-4 py-3"
                    :class="typingBubbleClass"
                >
                    <span
                        class="h-1.5 w-1.5 animate-bounce rounded-full [animation-delay:-0.3s]"
                        :class="dotClass"
                    ></span>
                    <span
                        class="h-1.5 w-1.5 animate-bounce rounded-full [animation-delay:-0.15s]"
                        :class="dotClass"
                    ></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full" :class="dotClass"></span>
                </div>
            </div>

            <p v-if="error" class="text-center text-xs text-rancho-ember" role="alert">
                {{ error }}
            </p>
        </div>

        <!-- INPUT -->
        <div class="mt-4 flex items-end gap-2 border-t pt-4" :class="inputBorderClass">
            <textarea
                ref="textareaRef"
                v-model="input"
                rows="1"
                :maxlength="maxLength"
                placeholder="Ваш вопрос..."
                aria-label="Ваш вопрос ИИ-помощнику"
                class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2"
                :class="[textareaClass, scrollbarClass]"
                @keydown="onKeydown"
            ></textarea>

            <button
                type="button"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors focus:outline-none focus-visible:ring-2 disabled:opacity-50"
                :class="sendBtnClass"
                :disabled="!canSend"
                aria-label="Отправить"
                @click="send()"
            >
                <PaperAirplaneIcon class="h-5 w-5" />
            </button>
        </div>

        <!-- Сброс -->
        <div v-if="messages.length" class="mt-2 flex justify-end">
            <button
                type="button"
                class="text-[10px] font-black uppercase tracking-widest transition-colors focus:outline-none focus-visible:ring-2"
                :class="clearBtnClass"
                @click="store.clearHistory()"
            >
                Очистить историю
            </button>
        </div>
    </div>
</template>

<style scoped>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #403a2d;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #756d57;
    }

    .custom-scrollbar-light::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar-light::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar-light::-webkit-scrollbar-thumb {
        background: #d2cbb9;
        border-radius: 10px;
    }
    .custom-scrollbar-light::-webkit-scrollbar-thumb:hover {
        background: #b5ac96;
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-bounce {
            animation: none;
        }
    }
</style>
