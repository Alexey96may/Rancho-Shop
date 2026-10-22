<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import BuyButton from '@/Components/UI/BuyButton.vue';
    import type { Product } from '@/types';
    import { formatMoney } from '@/utils/format';

    interface Props {
        product: Product;
        theme?: 'light' | 'dark';
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const isDark = computed(() => props.theme === 'dark');

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

    const cardClass = computed(() =>
        isDark.value
            ? [
                  isAvailable.value
                      ? 'border-rancho-ink-800 bg-rancho-ink-950/60 hover:border-rancho-ink-700 hover:bg-rancho-ink-900/60'
                      : 'border-rancho-ink-800 bg-rancho-ink-950/40 opacity-80',
              ]
            : [
                  isAvailable.value
                      ? 'border-rancho-ink-200 bg-white hover:border-rancho-ink-300 hover:-translate-y-0.5 hover:shadow-xl'
                      : 'border-rancho-ink-200 bg-rancho-ink-50 opacity-80 hover:shadow-sm',
              ],
    );

    const titleClass = computed(() =>
        isDark.value
            ? 'text-white group-hover:text-rancho-pine'
            : 'text-rancho-forest group-hover:text-rancho-pine',
    );

    const priceClass = computed(() => (isDark.value ? 'text-white' : 'text-rancho-forest'));

    const oldPriceClass = computed(() =>
        isDark.value ? 'text-rancho-ink-500' : 'text-rancho-ink-400',
    );

    const unitClass = computed(() =>
        isDark.value ? 'text-rancho-ink-400' : 'text-rancho-ink-500',
    );

    const unavailableTextClass = computed(() =>
        isDark.value ? 'text-rancho-ink-400' : 'text-rancho-ink-500',
    );

    const unavailableButtonClass = computed(() =>
        isDark.value
            ? 'bg-rancho-ink-800 text-rancho-ink-400'
            : 'bg-rancho-ink-200 text-rancho-ink-500',
    );
</script>

<template>
    <div
        class="group flex h-full flex-col overflow-hidden rounded-2xl border transition-all duration-300"
        :class="cardClass"
    >
        <!-- Ссылка на товар / Фото -->
        <Link
            :href="route('catalog.show', product.slug)"
            class="relative aspect-square max-h-48 overflow-hidden sm:max-h-64"
            :class="isDark ? 'bg-rancho-ink-900' : 'bg-rancho-ink-50'"
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

                <span
                    v-else
                    class="shadow-sm rounded-lg bg-rancho-ink-800 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-rancho-ink-100"
                >
                    Нет в наличии
                </span>
            </div>
        </Link>

        <!-- Контент карточки -->
        <div class="flex flex-grow flex-col gap-6 p-5">
            <div>
                <div class="mb-1 text-[10px] font-bold uppercase tracking-widest text-rancho-ember">
                    {{ product.category?.name }}
                </div>

                <Link :href="route('catalog.show', product.slug)">
                    <h3
                        class="text-lg font-bold leading-tight transition-colors sm:text-xl"
                        :class="titleClass"
                    >
                        {{ product.name }}
                    </h3>
                </Link>
            </div>

            <!-- Блок с ценой (если вариант есть) -->
            <div v-if="isAvailable" class="flex items-center gap-3">
                <span class="text-xl font-black" :class="priceClass">
                    {{ formatMoney(product?.default_variant?.price) }}
                </span>

                <div v-if="discountBadge" class="flex flex-col">
                    <span class="text-xs leading-none line-through" :class="oldPriceClass">
                        {{ formatMoney(product?.default_variant?.old_price) }}
                    </span>
                    <span class="text-[10px] font-bold text-rancho-ember">
                        -{{ discountBadge }}%
                    </span>
                </div>

                <span class="ml-auto text-sm" :class="unitClass">
                    / {{ product.default_variant?.unit?.short ?? 'шт' }}
                </span>
            </div>

            <!-- Заглушка цены (если варианта нет) -->
            <div v-else class="flex min-h-[36px] items-center justify-between">
                <span
                    class="w-full text-center text-sm font-semibold"
                    :class="unavailableTextClass"
                >
                    Товар недоступен
                </span>
            </div>

            <!-- Кнопка действия -->
            <div class="mt-auto">
                <BuyButton v-if="isAvailable" :has-undo="false" :product="product" :theme="theme" />

                <button
                    v-else
                    type="button"
                    disabled
                    class="flex h-12 w-full cursor-not-allowed items-center justify-center rounded-xl px-3 text-xs font-bold transition-colors sm:text-lg"
                    :class="unavailableButtonClass"
                >
                    Нельзя заказать
                </button>
            </div>
        </div>
    </div>
</template>
