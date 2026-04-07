<script setup lang="ts">
    import { computed } from 'vue';

    import type { ProductWithCategory, ResourceSingle } from '@/types';

    const props = defineProps<{
        product: ResourceSingle<ProductWithCategory>;
    }>();

    const productData = computed(() => props.product.data);

    // Форматируем цену (из копеек в рубли)
    const displayPrice = computed(() => (productData.value.price_rub / 100).toFixed(2));
    const displayOldPrice = computed(() =>
        productData.value.old_price ? (productData.value.old_price / 100).toFixed(2) : null,
    );

    // Текст для типа доступности
    const availabilityLabels: Record<string, string> = {
        stock: 'В наличии',
        daily: 'Ежедневно',
        preorder: 'Предзаказ',
    };

    // Функция для получения дней недели (если есть schedule)
    const getDaysNames = (days: number[]) => {
        const names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return days.map((d) => names[d]).join(', ');
    };
</script>

<template>
    <div
        class="bg-white shadow-sm border-slate-100 hover:shadow-md flex h-full flex-col overflow-hidden rounded-2xl border transition-shadow"
    >
        <div class="bg-slate-100 relative aspect-video">
            <img
                v-if="productData.media.length"
                :src="productData.media[0].url"
                class="h-full w-full object-cover"
            />

            <div class="absolute left-2 top-2 flex gap-1">
                <span
                    :class="[
                        'rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wider',
                        productData.availability_type === 'daily'
                            ? 'bg-green-500 text-white'
                            : 'bg-slate-800 text-white',
                    ]"
                >
                    {{ availabilityLabels[productData.availability_type] }}
                </span>
            </div>
        </div>

        <div class="flex flex-grow flex-col p-4">
            <div class="mb-2">
                <h3 class="text-slate-900 text-lg font-bold leading-tight">
                    {{ productData.name }}
                </h3>
                <span v-if="productData.attributes?.breed" class="text-slate-500 text-xs">
                    Порода: {{ productData.attributes.breed }}
                </span>
            </div>

            <div class="mb-4 flex items-baseline gap-2">
                <span class="text-slate-900 text-2xl font-black">{{ displayPrice }}₽</span>
                <span class="text-slate-400 text-sm">/ {{ productData.unit }}</span>
                <span v-if="displayOldPrice" class="text-slate-400 text-sm line-through">
                    {{ displayOldPrice }}₽
                </span>
            </div>

            <div class="mt-auto space-y-2">
                <div
                    v-if="productData.schedule"
                    class="bg-blue-50 border-blue-100 rounded-lg border p-2"
                >
                    <span class="text-blue-600 block text-[10px] font-bold uppercase"
                        >Дни сбора/доставки:</span
                    >
                    <span class="text-blue-800 text-xs font-medium">{{
                        getDaysNames(productData.schedule.days)
                    }}</span>
                </div>

                <div
                    v-if="productData.next_delivery_date"
                    class="text-orange-600 text-xs font-medium"
                >
                    Ближайшая дата: {{ productData.next_delivery_date }}
                </div>
            </div>

            <button
                class="bg-slate-900 text-white hover:bg-orange-600 mt-4 w-full rounded-xl py-2 font-bold transition-colors active:scale-95"
                :disabled="productData.stock === 0 && productData.availability_type === 'stock'"
            >
                {{ productData.availability_type === 'preorder' ? 'Заказать' : 'В корзину' }}
            </button>
        </div>
    </div>
</template>
