<script setup lang="ts">
    import { computed, useId } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import {
        ArrowPathIcon,
        ChatBubbleLeftIcon,
        PencilSquareIcon,
        PhotoIcon,
        StarIcon,
    } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import AppImage from '@/Components/UI/AppImage.vue';
    import type { AdminProduct, SharedData } from '@/types';

    const props = defineProps<{
        product: AdminProduct;
        disabled: boolean;
    }>();

    defineEmits(['edit', 'delete', 'restore']);

    const titleId = useId();

    const statusInfo = computed(() => {
        if (props.product.is_trashed) {
            return {
                label: 'Удалён',
                class: 'bg-rose-500/20 text-rose-300 border border-rose-500/40',
                aria: 'Товар удалён',
            };
        }

        if (!props.product.is_active) {
            return {
                label: 'Скрыт',
                class: 'bg-rancho-ink-800 text-rancho-ink-400 border border-rancho-ink-700',
                aria: 'Товар скрыт',
            };
        }

        const types = {
            stock: {
                label: 'В наличии',
                class: 'bg-rancho-pine/30 text-rancho-mint border border-rancho-pine/50',
                aria: 'Статус: в наличии',
            },
            daily: {
                label: 'Ежедневно',
                class: 'bg-rancho-sky/20 text-rancho-sky border border-rancho-sky/40',
                aria: 'Статус: по графику',
            },
            weekly: {
                label: 'Еженедельно',
                class: 'bg-rancho-sky/20 text-rancho-sky border border-rancho-sky/40',
                aria: 'Статус: по графику',
            },
            preorder: {
                label: 'Предзаказ',
                class: 'bg-rancho-buttercup/20 text-rancho-buttercup border border-rancho-buttercup/40',
                aria: 'Статус: предзаказ',
            },
        };

        return (
            types[props.product.availability.value] || {
                label: 'Нет данных',
                class: 'bg-rose-500/20 text-rose-300 border border-rose-500/40',
                aria: 'Нет данных',
            }
        );
    });

    const page = usePage<SharedData>();
    const can = computed(() => page.props.can ?? {});
</script>

<template>
    <article
        :aria-labelledby="titleId"
        :class="[
            product.is_trashed || !product.is_active || disabled ? 'opacity-60' : 'opacity-100',
            disabled ? 'pointer-events-none scale-[0.96]' : 'scale-100',
            product.is_trashed ? 'border-rose-900/30 grayscale-[0.4]' : 'grayscale-0',
        ]"
        class="group relative flex flex-col overflow-hidden rounded-3xl border border-rancho-ink-800 bg-rancho-ink-900 transition-all duration-300 hover:border-rancho-mint/60"
    >
        <!-- BADGES (rating + comments) -->
        <div class="absolute right-4 top-4 z-10 flex flex-col gap-2">
            <div
                v-if="product.rating_avg"
                class="flex items-center gap-1 rounded-lg bg-rancho-ink-950/80 px-2 py-1 backdrop-blur-md"
            >
                <StarIcon class="h-3 w-3 text-rancho-buttercup" aria-hidden="true" />
                <span class="text-[10px] font-black text-white">{{ product.rating_avg }}</span>
            </div>

            <div
                v-if="product.comments_count"
                class="flex items-center gap-1 rounded-lg bg-rancho-ink-950/80 px-2 py-1 backdrop-blur-md"
            >
                <ChatBubbleLeftIcon class="h-3 w-3 text-rancho-sky" aria-hidden="true" />
                <span class="text-[10px] font-black text-white">{{ product.comments_count }}</span>
            </div>
        </div>

        <!-- IMAGE -->
        <div class="relative aspect-square w-full overflow-hidden bg-rancho-ink-950">
            <AppImage
                v-if="product.main_photo?.[0]"
                :src="product.main_photo[0].url"
                :type="'thumbnails'"
                :alt="product.name"
                :context="'animal'"
                :class="!disabled && !product.is_trashed ? 'group-hover:scale-110' : ''"
                class="h-full w-full object-cover transition-transform duration-500"
            />

            <div v-else class="flex h-full w-full items-center justify-center text-rancho-ink-700">
                <PhotoIcon class="h-12 w-12" aria-hidden="true" />
            </div>

            <!-- STATUS BADGE -->
            <div
                class="absolute left-4 top-4 rounded-xl px-3 py-1 text-[9px] font-black uppercase tracking-widest backdrop-blur-md"
                :class="statusInfo.class"
            >
                {{ statusInfo.label }}
            </div>
        </div>

        <!-- CONTENT -->
        <div class="flex flex-1 flex-col gap-2 p-6">
            <h3
                :id="titleId"
                class="line-clamp-1 text-sm font-bold"
                :class="product.is_trashed ? 'text-rancho-ink-500' : 'text-white'"
            >
                {{ product.name }}
            </h3>

            <p class="mb-4 text-[10px] font-bold uppercase tracking-widest text-rancho-ink-500">
                {{ product.category?.name || 'Без категории' }}
            </p>

            <!-- FOOTER: actions -->
            <div
                class="mt-auto flex items-center justify-end gap-2 border-t border-rancho-ink-800/60 pt-4"
            >
                <AdminEditButton
                    v-if="product.is_trashed && can.restore"
                    @click="$emit('restore', product.id, product.name)"
                    :title="`Восстановить ${product.name}`"
                    :disabled="disabled"
                    :icon="ArrowPathIcon"
                />

                <AdminEditButton
                    v-else-if="!product.is_trashed"
                    @click="$emit('edit', product)"
                    :title="`Изменить ${product.name}`"
                    :disabled="disabled"
                    :icon="PencilSquareIcon"
                />

                <AdminDeleteButton
                    v-if="!product.is_trashed || can.forceDelete"
                    @click="$emit('delete', product.id)"
                    :title="product.is_trashed ? 'Удалить окончательно' : 'Удалить в архив'"
                    :disabled="disabled"
                />
            </div>
        </div>
    </article>
</template>
