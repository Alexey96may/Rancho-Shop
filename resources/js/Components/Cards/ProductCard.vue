<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import BuyButton from '@/Components/UI/BuyButton.vue';
    import type { Product } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{
        product: Product;
    }>();

    const isAvailable = computed(
        () => !!props.product.default_variant && !!props.product.default_variant.is_in_stock,
    );

    const discountBadge = computed(() => {
        if (
            !props.product.default_variant?.old_price ||
            props.product.default_variant.old_price <= props.product.default_variant.price
        ) {
            return null;
        }

        return Math.round(
            100 -
                (props.product.default_variant.price / props.product.default_variant.old_price) *
                    100,
        );
    });
</script>

<template>
    <div
        class="shadow-sm hover:shadow-xl group flex h-full flex-col overflow-hidden rounded-2xl border transition-all duration-300"
        :class="[
            isAvailable
                ? 'border-slate-100 bg-white'
                : 'hover:shadow-sm border-slate-200 bg-slate-50/60 opacity-80',
        ]"
    >
        <!-- Ссылка на товар / Фото -->
        <Link
            :href="route('catalog.show', product.slug)"
            class="relative aspect-square overflow-hidden bg-slate-100"
        >
            <AppImage
                :alt="product.name"
                :src="product.main_photo?.[0] || ''"
                :class-name="
                    'h-full w-full object-cover transition-transform duration-500 ' +
                    (isAvailable ? 'group-hover:scale-110' : 'grayscale-[30%]')
                "
            />

            <!-- Бейдж наличия/доступности -->
            <div class="absolute left-3 top-3 flex flex-col gap-2">
                <span
                    v-if="isAvailable"
                    :class="[
                        'shadow-sm rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider',
                        product.availability?.value === 'daily'
                            ? 'bg-green-500 text-white'
                            : 'bg-slate-900 text-white',
                    ]"
                >
                    {{ product.availability?.label }}
                </span>

                <!-- Плашка при отсутствии варианта -->
                <span
                    v-else
                    class="shadow-sm rounded-lg bg-slate-800 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-slate-200"
                >
                    Нет в наличии
                </span>
            </div>
        </Link>

        <!-- Контент карточки -->
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

            <!-- Блок с ценой (если вариант есть) -->
            <div v-if="isAvailable" class="mb-4 flex items-center gap-3">
                <span class="text-2xl font-black text-slate-900">
                    {{ formatMoney(product.default_variant?.price) }}
                </span>

                <div v-if="discountBadge" class="flex flex-col">
                    <span class="text-xs leading-none text-slate-400 line-through">
                        {{ formatMoney(product.default_variant?.old_price) }}
                    </span>
                    <span class="text-[10px] font-bold text-red-500">-{{ discountBadge }}%</span>
                </div>

                <span class="ml-auto text-sm text-slate-400">
                    / {{ product.default_variant?.unit?.short ?? 'шт' }}
                </span>
            </div>

            <!-- Заглушка цены (если варианта нет) -->
            <div v-else class="mb-4 flex min-h-[36px] items-center justify-between">
                <span class="text-sm font-semibold text-slate-400"> Товар недоступен </span>
            </div>

            <!-- Кнопка действия -->
            <div class="mt-auto">
                <BuyButton v-if="isAvailable" :product="product" />

                <button
                    v-else
                    disabled
                    class="w-full cursor-not-allowed rounded-xl bg-slate-200 py-3 text-center text-xs font-bold text-slate-400 transition-colors"
                >
                    Нельзя заказать
                </button>
            </div>
        </div>
    </div>
</template>
