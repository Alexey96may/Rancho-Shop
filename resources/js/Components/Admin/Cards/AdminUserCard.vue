<script setup lang="ts">
    import { computed } from 'vue';

    import {
        ChatBubbleLeftRightIcon,
        EnvelopeIcon,
        MapPinIcon,
        PhoneIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';
    import { PencilIcon, Trash2Icon } from 'lucide-vue-next';

    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import type { AdminUser, UserColor } from '@/types';

    const props = defineProps<{
        user: AdminUser;
        disabled?: boolean;
    }>();

    defineEmits(['edit', 'delete']);

    const displayAddress = computed(() => {
        if (!props.user.addresses?.length) return null;
        return props.user.addresses.find((a) => a.is_default) || props.user.addresses[0];
    });

    /** Цвет бейджа роли — все в палитре rancho */
    const badgeClasses = computed(() => {
        const styles: Record<UserColor, string> = {
            emerald: 'text-rancho-mint ring-rancho-pine/30',
            violet: 'text-rancho-ember ring-rancho-ember/30',
            sky: 'text-rancho-sky ring-rancho-sky/30',
            zinc: 'text-rancho-ink-400 ring-rancho-ink-600/30',
        };

        const colorKey = props.user.role.color;
        return styles[colorKey] || styles.zinc;
    });
</script>

<template>
    <article
        class="group relative flex flex-col gap-6 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/40 p-5 transition-all duration-500 focus-within:ring-2 focus-within:ring-rancho-mint/50 sm:rounded-3xl"
        :class="[
            disabled
                ? 'pointer-events-none opacity-50 grayscale'
                : 'hover:shadow-2xl hover:-translate-y-1 hover:border-rancho-mint/30 hover:bg-rancho-ink-900 hover:shadow-rancho-mint/5',
        ]"
        :aria-labelledby="`user-name-${user.id}`"
    >
        <!-- BADGES (Google / VK) -->
        <div class="flex gap-2 self-end" v-if="user.google_id">
            <!-- Google -->
            <div
                title="Авторизован через Google"
                class="shadow-sm flex h-5 w-5 items-center justify-center rounded-full bg-white p-1 transition-transform hover:scale-110"
            >
                <svg viewBox="0 0 24 24" class="h-full w-full" aria-hidden="true">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4"
                    />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853"
                    />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                        fill="#FBBC05"
                    />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335"
                    />
                </svg>
            </div>

            <!-- VK -->
            <a
                v-if="user.vkontakte_id"
                :href="`https://vk.com/id${user.vkontakte_id}`"
                target="_blank"
                rel="noopener noreferrer"
                title="Открыть профиль VK"
                class="shadow-sm flex h-5 w-5 items-center justify-center rounded-full bg-[#0077FF] p-2 transition-all hover:scale-110 hover:brightness-110 active:scale-95"
            >
                <span class="select-none text-[9px] font-black uppercase leading-none text-white">
                    VK
                </span>
            </a>
        </div>

        <!-- HEADER -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <div
                    class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-rancho-ink-950 ring-1 ring-rancho-ink-800 transition-transform duration-500 group-hover:scale-105"
                >
                    <AppImage
                        v-if="user.avatar"
                        :src="user?.avatar || ''"
                        :type="'thumbnails'"
                        :alt="user.name"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />

                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-rancho-ink-700"
                    >
                        <UserIcon class="h-7 w-7" aria-hidden="true" />
                    </div>
                </div>

                <!-- Родитель имени: min-w-0 + pr-12 (место под badges) -->
                <div class="min-w-0 flex-1 sm:pr-12">
                    <h2
                        :id="`user-name-${user.id}`"
                        class="truncate text-xs font-black uppercase tracking-tight text-white transition-colors group-hover:text-rancho-mint sm:text-sm"
                    >
                        {{ user.name }}
                    </h2>

                    <span
                        class="inline-flex items-center gap-1 truncate py-0.5 text-[9px] font-black uppercase tracking-widest transition-colors"
                        :class="badgeClasses"
                    >
                        {{ user.role.label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- CONTACTS -->
        <div class="flex-1 space-y-3">
            <a
                :href="`mailto:${user.email}`"
                class="flex items-center gap-2 text-xs font-medium text-rancho-ink-400 transition-colors hover:text-white"
            >
                <EnvelopeIcon class="h-4 w-4 text-rancho-ink-600" aria-hidden="true" />
                <span class="truncate">{{ user.email }}</span>
            </a>

            <a
                :href="`tel:${user.phone}`"
                class="flex items-center gap-2 text-xs font-medium text-rancho-ink-400 transition-colors hover:text-white"
            >
                <PhoneIcon class="h-4 w-4 text-rancho-ink-600" aria-hidden="true" />
                <span>{{ user.phone }}</span>
            </a>

            <!-- ADDRESS -->
            <div
                v-if="displayAddress"
                class="flex items-start gap-2 border-t border-rancho-ink-800/40 pt-2.5 text-xs font-medium text-rancho-ink-400"
            >
                <MapPinIcon class="h-4 w-4 shrink-0 text-rancho-ink-600" aria-hidden="true" />
                <div class="min-w-0">
                    <p
                        class="mb-1 text-[9px] font-black uppercase leading-none text-rancho-ink-500"
                    >
                        {{ displayAddress.label }}
                    </p>
                    <p class="truncate italic text-rancho-ink-500">
                        {{ displayAddress.address }}
                    </p>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="mt-6 flex items-center justify-between border-t border-rancho-ink-800/60 pt-4">
            <div class="flex items-center gap-4">
                <!-- Orders -->
                <div class="flex items-center gap-1.5" title="Заказов">
                    <ShoppingBagIcon class="h-4 w-4 text-rancho-mint" aria-hidden="true" />
                    <span class="text-xs font-black text-white">
                        {{ user.orders_count || 0 }}
                    </span>
                </div>

                <!-- Comments -->
                <div class="flex items-center gap-1.5" title="Комментариев">
                    <ChatBubbleLeftRightIcon class="h-4 w-4 text-rancho-sky" aria-hidden="true" />
                    <span class="text-xs font-black text-white">
                        {{ user.comments_count || 0 }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <AdminEditButton
                    @click="$emit('edit')"
                    :title="`Изменить ${user.name}`"
                    :disabled="disabled"
                    :aria-label="`Изменить ${user.name}`"
                    :icon="PencilIcon"
                />

                <BaseDeleteButton
                    :disabled="disabled"
                    :title="`Удалить ${user.name}`"
                    @confirm="$emit('delete')"
                    :aria-label="`Удалить ${user.name}`"
                >
                    <Trash2Icon class="h-4 w-4" aria-hidden="true" />
                </BaseDeleteButton>
            </div>
        </div>
    </article>
</template>

<style scoped>
    article.grayscale {
        filter: grayscale(1) contrast(0.8);
        transition: all 0.4s ease;
    }
</style>
