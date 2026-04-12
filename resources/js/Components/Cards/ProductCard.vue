<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import BuyButton from '@/Components/UI/BuyButton.vue';
    import type { ProductVariantDTO, ProductWithCategory } from '@/types';

    const props = defineProps<{
        product: ProductWithCategory;
    }>();

    const defaultVariant = computed(() => {
        return (
            props.product.variants?.find((v) => v.is_default) ?? props.product.variants?.[0] ?? null
        );
    });

    const displayPrice = computed(() =>
        defaultVariant.value ? (defaultVariant.value.price / 100).toFixed(2) : '0.00',
    );

    const displayOldPrice = computed(() =>
        defaultVariant.value?.old_price_rub
            ? (defaultVariant.value.old_price_rub / 100).toFixed(2)
            : null,
    );

    const availabilityLabels: Record<string, string> = {
        stock: 'В наличии',
        daily: 'Ежедневно',
        preorder: 'Предзаказ',
    };

    const getDaysNames = (days: number[] | undefined | null) => {
        if (!Array.isArray(days)) return 'Не указано';

        const names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return days.map((d) => names[d]).join(', ');
    };

    const discountBadge = computed(() => {
        if (
            !defaultVariant.value?.old_price_rub ||
            defaultVariant.value.old_price_rub <= defaultVariant.value.price
        ) {
            return null;
        }

        return Math.round(
            100 - (defaultVariant.value.price / defaultVariant.value.old_price_rub) * 100,
        );
    });
</script>

<template>
    <div
        class="shadow-sm hover:shadow-xl group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white transition-all duration-300"
    >
        <Link
            :href="route('catalog.show', product.slug)"
            class="relative aspect-square overflow-hidden bg-slate-100"
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
                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-orange-600">
                    {{ product.category?.name }}
                </div>

                <h3
                    class="text-lg font-bold leading-tight text-slate-900 transition-colors hover:text-orange-600"
                >
                    <Link :href="route('catalog.show', product.slug)">
                        {{ product.name }}
                    </Link>
                </h3>
            </div>

            <div class="mb-4 flex items-center gap-3">
                <span class="text-2xl font-black text-slate-900">{{ displayPrice }}₽</span>
                <div v-if="displayOldPrice" class="flex flex-col">
                    <span class="text-xs leading-none text-slate-400 line-through"
                        >{{ displayOldPrice }}₽</span
                    >
                    <span class="text-[10px] font-bold text-red-500">-{{ discountBadge }}%</span>
                </div>
                <span class="ml-auto text-sm text-slate-400"
                    >/ {{ defaultVariant?.unit?.slug ?? 'pcs' }}</span
                >
            </div>

            <div class="mt-auto space-y-2 border-t border-slate-50 pt-4">
                <div v-if="product.schedule" class="flex items-start gap-2">
                    <div class="rounded bg-blue-100 p-1">📅</div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400"
                            >График сбора:</span
                        >
                        <span class="text-xs font-semibold text-slate-700">{{
                            getDaysNames(product.schedule?.days)
                        }}</span>
                    </div>
                </div>
            </div>

            <BuyButton
                v-if="defaultVariant"
                :availability_type="product.availability_type"
                :variant="defaultVariant"
            />
        </div>
    </div>
</template>
