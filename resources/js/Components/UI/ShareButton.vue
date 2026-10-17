<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

    import { Check, Link2, MessageCircle, Send, Share2 } from 'lucide-vue-next';

    import type { SeoData } from '@/types';

    const props = withDefaults(
        defineProps<{
            seo?: SeoData | null;
            title?: string;
            text?: string;
            url?: string;
            image?: string | null;
            variant?: 'button' | 'icon';
            label?: string;
        }>(),
        {
            variant: 'button',
            label: 'Поделиться',
        },
    );

    const open = ref(false);
    const copied = ref(false);
    const rootRef = ref<HTMLElement | null>(null);

    const shareUrl = computed(() => {
        if (props.url) return props.url;
        if (props.seo?.canonical) return props.seo.canonical;
        if (typeof window !== 'undefined') return window.location.href;
        return '';
    });

    const shareTitle = computed(() => {
        return props.title || props.seo?.title || props.seo?.og_data?.title || 'Молочная Долина';
    });

    const shareText = computed(() => {
        return props.text || props.seo?.description || props.seo?.og_data?.description || '';
    });

    const shareImage = computed(() => {
        return props.image || props.seo?.image || props.seo?.og_data?.image || null;
    });

    const encodedUrl = computed(() => encodeURIComponent(shareUrl.value));
    const encodedText = computed(() =>
        encodeURIComponent(`${shareTitle.value}\n${shareUrl.value}`),
    );

    const links = computed(() => [
        {
            name: 'Telegram',
            icon: Send,
            href: `https://t.me/share/url?url=${encodedUrl.value}&text=${encodeURIComponent(shareTitle.value)}`,
            color: 'text-sky-500',
        },
        {
            name: 'VK',
            icon: MessageCircle,
            href: `https://vk.com/share.php?url=${encodedUrl.value}&title=${encodeURIComponent(shareTitle.value)}${shareImage.value ? `&image=${encodeURIComponent(shareImage.value)}` : ''}`,
            color: 'text-blue-600',
        },
        {
            name: 'WhatsApp',
            icon: MessageCircle,
            href: `https://wa.me/?text=${encodedText.value}`,
            color: 'text-emerald-500',
        },
    ]);

    async function nativeShare() {
        if (typeof navigator !== 'undefined' && navigator.share) {
            try {
                await navigator.share({
                    title: shareTitle.value,
                    text: shareText.value,
                    url: shareUrl.value,
                });
                open.value = false;
                return;
            } catch {
                // пользователь отменил — покажем fallback
            }
        }
        open.value = !open.value;
    }

    async function copyLink() {
        if (typeof window === 'undefined' || typeof navigator === 'undefined') return;
        const text = shareUrl.value;
        if (!text) return;

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(text);
                }
                document.body.removeChild(ta);
            }
            copied.value = true;
            setTimeout(() => (copied.value = false), 2000);
        } catch {}
    }

    function onClickOutside(e: MouseEvent) {
        if (rootRef.value && !rootRef.value.contains(e.target as Node)) {
            open.value = false;
        }
    }

    onMounted(() => {
        if (typeof document !== 'undefined') {
            document.addEventListener('click', onClickOutside);
        }
    });
    onBeforeUnmount(() => {
        if (typeof document !== 'undefined') {
            document.removeEventListener('click', onClickOutside);
        }
    });
</script>

<template>
    <div ref="rootRef" class="relative inline-block">
        <!-- Триггер -->
        <button
            v-if="variant === 'button'"
            type="button"
            class="group inline-flex items-center gap-2 rounded-xl border border-rancho-olive/40 bg-white px-4 py-2.5 text-xs font-bold text-rancho-forest outline-none transition-all hover:border-rancho-pine/40 hover:bg-rancho-pine/80 hover:text-rancho-paper hover:shadow-rancho-sm focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 active:scale-95"
            :aria-expanded="open"
            aria-haspopup="menu"
            @click.stop="nativeShare"
        >
            <Share2
                class="text-pine h-3 w-3 transition-transform group-hover:scale-110"
                aria-hidden="true"
            />
            <span class="hidden sm:inline">{{ label }}</span>
        </button>

        <button
            v-else
            type="button"
            class="shadow-sm flex h-6 w-6 items-center justify-center rounded-xl text-rancho-paper outline-none transition-all hover:text-rancho-buttercup focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 active:scale-95"
            :aria-expanded="open"
            aria-haspopup="menu"
            :aria-label="label"
            @click.stop="nativeShare"
        >
            <Share2 class="h-4 w-4" aria-hidden="true" />
        </button>

        <!-- Меню -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="open"
                role="menu"
                class="bg-rancho-white shadow-xl absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-rancho-olive/15 p-1.5"
            >
                <div class="px-3 py-2">
                    <p
                        class="text-[10px] font-black uppercase tracking-[0.2em] text-rancho-ink-500"
                    >
                        Поделиться
                    </p>
                </div>
                <a
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    role="menuitem"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-rancho-forest transition-colors hover:bg-rancho-pine/50 hover:text-rancho-pine"
                    @click="open = false"
                >
                    <component
                        :is="link.icon"
                        class="h-4 w-4"
                        :class="link.color"
                        aria-hidden="true"
                    />
                    {{ link.name }}
                </a>

                <button
                    type="button"
                    role="menuitem"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-rancho-forest transition-colors hover:bg-rancho-pine/50 hover:text-rancho-pine"
                    @click.stop="copyLink"
                >
                    <component
                        :is="copied ? Check : Link2"
                        class="h-4 w-4"
                        :class="copied ? 'text-emerald-500' : 'text-olive'"
                        aria-hidden="true"
                    />
                    {{ copied ? 'Скопировано' : 'Скопировать ссылку' }}
                </button>
            </div>
        </Transition>
    </div>
</template>
