<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import type { ProductWithCategory } from '@/types';

    const props = defineProps<{
        product: ProductWithCategory;
    }>();

    const displayPrice = computed(() => (props.product.price_rub / 100).toFixed(2));
    const displayOldPrice = computed(() =>
        props.product.old_price ? (props.product.old_price / 100).toFixed(2) : null,
    );

    const availabilityLabels: Record<string, string> = {
        stock: 'В наличии',
        daily: 'Ежедневно',
        preorder: 'Предзаказ',
    };

    const getDaysNames = (days: number[] | undefined | null) => {
        // Если days не массив или пустой — выходим
        if (!Array.isArray(days)) return 'Не указано';

        const names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return days.map((d) => names[d]).join(', ');
    };

    const discountBadge = computed(() => {
        if (!props.product.old_price || props.product.old_price <= props.product.price_rub) {
            return null;
        }
        return Math.round(100 - (props.product.price_rub / props.product.old_price) * 100);
    });
</script>

<template>
    <div
        class="bg-white shadow-sm border-slate-100 hover:shadow-xl group flex h-full flex-col overflow-hidden rounded-2xl border transition-all duration-300"
    >
        <Link
            :href="route('catalog.show', product.slug)"
            class="bg-slate-100 relative aspect-square overflow-hidden"
        >
            <AppImage
                :alt="product.name"
                :src="product.media?.[0] || ''"
                :class-name="'h-full w-full object-cover transition-transform duration-500 group-hover:scale-110'"
            />

            <div class="absolute left-3 top-3 flex flex-col gap-2">
                <span
                    :class="[
                        'shadow-sm rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider',
                        product.availability_type === 'daily'
                            ? 'bg-green-500 text-white'
                            : 'bg-slate-900 text-white',
                    ]"
                >
                    {{ availabilityLabels[product.availability_type] }}
                </span>
            </div>
        </Link>

        <div class="flex flex-grow flex-col p-5">
            <div class="mb-3">
                <div class="text-orange-600 mb-1 text-[10px] font-bold uppercase tracking-widest">
                    {{ product.category?.name }}
                </div>
                <Link :href="route('catalog.show', product.slug)">
                    <h3
                        class="text-slate-900 hover:text-orange-600 text-lg font-bold leading-tight transition-colors"
                    >
                        {{ product.name }}
                    </h3>
                </Link>
            </div>

            <div class="mb-4 flex items-center gap-3">
                <span class="text-slate-900 text-2xl font-black">{{ displayPrice }}₽</span>
                <div v-if="displayOldPrice" class="flex flex-col">
                    <span class="text-slate-400 text-xs leading-none line-through"
                        >{{ displayOldPrice }}₽</span
                    >
                    <span class="text-red-500 text-[10px] font-bold">-{{ discountBadge }}%</span>
                </div>
                <span class="text-slate-400 ml-auto text-sm">/ {{ product.unit }}</span>
            </div>

            <div class="border-slate-50 mt-auto space-y-2 border-t pt-4">
                <div v-if="product.schedule" class="flex items-start gap-2">
                    <div class="bg-blue-100 rounded p-1">📅</div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase"
                            >График сбора:</span
                        >
                        <span class="text-slate-700 text-xs font-semibold">{{
                            getDaysNames(product.schedule.days)
                        }}</span>
                    </div>
                </div>
            </div>

            <button
                class="bg-slate-900 text-white hover:bg-orange-600 disabled:bg-slate-200 disabled:text-slate-400 mt-5 w-full rounded-xl py-3 font-bold transition-all active:scale-95 disabled:transform-none"
                :disabled="product.stock === 0 && product.availability_type === 'stock'"
            >
                {{ product.availability_type === 'preorder' ? 'Забронировать' : 'В корзину' }}
            </button>
        </div>
    </div>
</template>
