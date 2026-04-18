<script setup lang="ts">
    import { computed } from 'vue';

    import { PencilSquareIcon, PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        product: any; // В идеале заменить на интерфейс Product
    }>();

    defineEmits(['edit', 'delete']);

    // Получаем первую цену из вариантов
    const displayPrice = computed(() => {
        const variant = props.product.variants?.[0];
        return variant ? `${variant.price} ₽` : 'Цена не задана';
    });

    // Статусы наличия (цвета)
    const availabilityStatus = computed(() => {
        const types = {
            in_stock: { label: 'В наличии', class: 'bg-green-500/10 text-green-500' },
            on_order: { label: 'Под заказ', class: 'bg-orange-500/10 text-orange-500' },
            out_of_stock: { label: 'Нет в наличии', class: 'bg-red-500/10 text-red-500' },
        };
        return types[props.product.availability_type] || types['out_of_stock'];
    });
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 transition-all hover:border-orange-500/50"
        aria-labelledby="product-name"
    >
        <div class="relative aspect-square w-full overflow-hidden bg-slate-950">
            <img
                v-if="product.media?.[0]"
                :src="product.media[0].url"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div v-else class="flex h-full w-full items-center justify-center text-slate-700">
                <PhotoIcon class="h-12 w-12" aria-hidden="true" />
            </div>

            <div
                class="absolute left-4 top-4 rounded-xl px-3 py-1 text-[10px] font-black uppercase tracking-widest backdrop-blur-md"
                :class="availabilityStatus.class"
            >
                {{ availabilityStatus.label }}
            </div>
        </div>

        <div class="flex flex-1 flex-col p-6">
            <div class="mb-2 flex items-start justify-between gap-2">
                <h3 id="product-name" class="line-clamp-1 font-bold text-white">
                    {{ product.name }}
                </h3>
                <span class="text-sm font-black text-orange-500">
                    {{ displayPrice }}
                </span>
            </div>

            <p class="mb-4 line-clamp-2 text-xs italic text-slate-500">
                {{ product.category?.name || 'Без категории' }}
            </p>

            <div class="mb-6 flex flex-wrap gap-2">
                <span
                    v-for="animal in product.animals"
                    :key="animal.id"
                    class="rounded-lg bg-slate-800 px-2 py-1 text-[9px] font-bold uppercase text-slate-400"
                >
                    {{ animal.name }}
                </span>
            </div>

            <div class="mt-auto flex items-center justify-between border-t border-slate-800 pt-4">
                <button
                    @click="$emit('edit', product)"
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 transition-colors hover:text-white"
                    :aria-label="'Редактировать ' + product.name"
                >
                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                    Изменить
                </button>

                <button
                    @click="$emit('delete', product.id)"
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-red-900 transition-colors hover:text-red-500"
                    :aria-label="'Удалить ' + product.name"
                >
                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </div>
    </article>
</template>
