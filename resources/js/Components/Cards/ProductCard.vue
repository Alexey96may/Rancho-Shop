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
        class="group flex h-full flex-col overflow-hidden rounded-2xl border transition-all duration-300"
        :class="[
            isAvailable
                ? 'border-rancho-ink-200 hover:border-rancho-ink-300 hover:shadow-xl bg-white hover:-translate-y-0.5'
                : 'border-rancho-ink-200 bg-rancho-ink-50 hover:shadow-sm opacity-80',
        ]"
    >
        <!-- Ссылка на товар / Фото -->
        <Link
            :href="route('catalog.show', product.slug)"
            class="bg-rancho-ink-50 relative aspect-square max-h-48 overflow-hidden sm:max-h-64"
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
                            ? 'bg-rancho-pine text-white'
                            : 'bg-rancho-forest text-white',
                    ]"
                >
                    {{ product.availability?.label }}
                </span>

                <!-- Плашка при отсутствии варианта -->
                <span
                    v-else
                    class="bg-rancho-ink-800 text-rancho-ink-100 shadow-sm rounded-lg px-2 py-1 text-[10px] font-black uppercase tracking-wider"
                >
                    Нет в наличии
                </span>
            </div>
        </Link>

        <!-- Контент карточки -->
        <div class="flex flex-grow flex-col gap-6 p-5">
            <div class="">
                <div class="text-rancho-ember mb-1 text-[10px] font-bold uppercase tracking-widest">
                    {{ product.category?.name }}
                </div>

                <h3
                    class="text-lg font-bold leading-tight text-rancho-forest transition-colors group-hover:text-rancho-pine sm:text-xl"
                >
                    <Link :href="route('catalog.show', product.slug)">
                        {{ product.name }}
                    </Link>
                </h3>
            </div>

            <!-- Блок с ценой (если вариант есть) -->
            <div v-if="isAvailable" class="flex items-center gap-3">
                <span class="text-xl font-black text-rancho-forest">
                    {{ formatMoney(product.default_variant?.price) }}
                </span>

                <div v-if="discountBadge" class="flex flex-col">
                    <span class="text-rancho-ink-400 text-xs leading-none line-through">
                        {{ formatMoney(product.default_variant?.old_price) }}
                    </span>
                    <span class="text-rancho-ember text-[10px] font-bold">
                        -{{ discountBadge }}%
                    </span>
                </div>

                <span class="text-rancho-ink-500 ml-auto text-sm">
                    / {{ product.default_variant?.unit?.short ?? 'шт' }}
                </span>
            </div>

            <!-- Заглушка цены (если варианта нет) -->
            <div v-else class="flex min-h-[36px] items-center justify-between">
                <span class="text-rancho-ink-500 w-full text-center text-sm font-semibold">
                    Товар недоступен
                </span>
            </div>

            <!-- Кнопка действия -->
            <div class="mt-auto">
                <BuyButton v-if="isAvailable" :product="product" />

                <button
                    v-else
                    disabled
                    class="bg-rancho-ink-200 text-rancho-ink-500 w-full cursor-not-allowed rounded-xl py-3 text-center text-xs font-bold transition-colors"
                >
                    Нельзя заказать
                </button>
            </div>
        </div>
    </div>
</template>
