<script setup lang="ts">
    import { computed, useId } from 'vue';

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
    import type { AdminProduct } from '@/types';

    const props = defineProps<{
        product: AdminProduct;
        disabled: boolean;
    }>();

    defineEmits(['edit', 'delete', 'restore']);

    const titleId = useId();

    const statusInfo = computed(() => {
        if (props.product.is_trashed) {
            return {
                label: 'Удален',
                class: 'bg-red-500/20 text-red-400 border border-red-500/30',
                aria: 'Товар удален',
            };
        }

        if (!props.product.is_active) {
            return { label: 'Скрыт', class: 'bg-slate-800 text-slate-500', aria: 'Товар скрыт' };
        }

        const types = {
            stock: {
                label: 'В наличии',
                class: 'bg-green-500/80 text-green-100 border border-green-500/20',
                aria: 'Статус: в наличии',
            },
            daily: {
                label: 'Ежедневно',
                class: 'bg-blue-500/80 text-blue-100 border border-blue-500/20',
                aria: 'Статус: по графику',
            },
            weekly: {
                label: 'Еженедельно',
                class: 'bg-blue-500/80 text-blue-1200 border border-blue-500/20',
                aria: 'Статус: по графику',
            },
            preorder: {
                label: 'Предзаказ',
                class: 'bg-orange-500/80 text-orange-100 border border-orange-500/20',
                aria: 'Статус: предзаказ',
            },
        };

        return (
            types[props.product.availability.value] || {
                label: 'Нет данных',
                class: 'bg-red-500/80 text-red-100',
                aria: 'Нет данных',
            }
        );
    });
</script>

<template>
    <article
        :aria-labelledby="titleId"
        :class="[
            product.is_trashed || !product.is_active || disabled ? 'opacity-60' : 'opacity-100',
            disabled ? 'pointer-events-none scale-[0.96]' : 'scale-100',
            product.is_trashed ? 'border-red-900/30 grayscale-[0.4]' : 'grayscale-0',
        ]"
        class="group relative flex flex-col overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 transition-all duration-300 hover:border-orange-500/80"
    >
        <div class="absolute right-4 top-4 z-10 flex flex-col gap-2">
            <div
                v-if="product.rating_avg"
                class="flex items-center gap-1 rounded-lg bg-slate-950/80 px-2 py-1 backdrop-blur-md"
            >
                <StarIcon class="h-3 w-3 text-yellow-400" aria-hidden="true" />
                <span class="text-[10px] font-black text-white">{{ product.rating_avg }}</span>
            </div>
            <div
                v-if="product.comments_count"
                class="flex items-center gap-1 rounded-lg bg-slate-950/80 px-2 py-1 backdrop-blur-md"
            >
                <ChatBubbleLeftIcon class="h-3 w-3 text-blue-400" aria-hidden="true" />
                <span class="text-[10px] font-black text-white">{{ product.comments_count }}</span>
            </div>
        </div>

        <div class="relative aspect-square w-full overflow-hidden bg-slate-950">
            <AppImage
                v-if="product.main_photo?.[0]"
                :src="product.main_photo[0].url"
                :type="'thumbnails'"
                :alt="product.name"
                :context="'animal'"
                :class="!disabled && !product.is_trashed ? 'group-hover:scale-110' : ''"
                class="h-full w-full object-cover transition-transform duration-500"
            />

            <div v-else class="flex h-full w-full items-center justify-center text-slate-800">
                <PhotoIcon class="h-12 w-12" />
            </div>

            <div
                class="absolute left-4 top-4 rounded-xl px-3 py-1 text-[9px] font-black uppercase tracking-widest backdrop-blur-md"
                :class="statusInfo.class"
            >
                {{ statusInfo.label }}
            </div>
        </div>

        <div class="flex flex-1 flex-col p-6">
            <h3
                :id="titleId"
                class="line-clamp-1 text-sm font-bold"
                :class="product.is_trashed ? 'text-slate-500' : 'text-white'"
            >
                {{ product.name }}
            </h3>

            <p class="mb-4 text-[10px] font-bold uppercase tracking-widest text-slate-600">
                {{ product.category?.name || 'Без категории' }}
            </p>

            <div
                class="mt-auto flex items-center justify-between border-t border-slate-800/50 pt-4"
            >
                <AdminEditButton
                    v-if="product.is_trashed"
                    @click="$emit('restore', product.id)"
                    :title="`Восстановить ${product.name}`"
                    :disabled="disabled"
                    :icon="ArrowPathIcon"
                />

                <AdminEditButton
                    v-else
                    @click="$emit('edit', product)"
                    :title="`Изменить ${product.name}`"
                    :disabled="disabled"
                    :icon="PencilSquareIcon"
                />

                <AdminDeleteButton
                    @click="$emit('delete', product.id)"
                    :title="product.is_trashed ? 'Удалить окончательно' : 'Удалить в архив'"
                    :disabled="disabled"
                />
            </div>
        </div>
    </article>
</template>
