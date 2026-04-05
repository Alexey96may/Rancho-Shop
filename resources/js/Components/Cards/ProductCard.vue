<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { CalendarCheck, Clock, ShoppingBasket } from 'lucide-vue-next';

    import { useCartStore } from '@/stores/cart';
    import type { Product } from '@/types/Product';

    const props = defineProps<{
        product: Product;
    }>();

    const cartStore = useCartStore();

    // Форматируем цену из копеек в рубли
    const formattedPrice = computed(() => (props.product.price_rub / 100).toLocaleString('ru-RU'));
    const formattedOldPrice = computed(() =>
        props.product.old_price ? (props.product.old_price / 100).toLocaleString('ru-RU') : null,
    );

    // Берем первое изображение из media или ставим заглушку
    const mainImage = computed(() =>
        props.product.media?.[0]?.url
            ? props.product.media?.[0]?.url
            : '/images/placeholder-product.jpg',
    );

    // Логика бейджа доступности
    const availabilityLabel = computed(() => {
        if (props.product.availability_type === 'daily') return 'Свежий удой сегодня';
        if (props.product.availability_type === 'preorder') return 'Предзаказ';
        return props.product.stock > 0 ? `В наличии: ${props.product.stock}` : 'Под заказ';
    });
</script>

<template>
    <article
        class="bg-white hover:shadow-xl group relative flex flex-col overflow-hidden rounded-2xl border border-rancho-paper transition-all focus-within:ring-2 focus-within:ring-rancho-buttercup"
        role="listitem"
    >
        <div class="relative aspect-square overflow-hidden bg-rancho-paper/30">
            <AppImage
                :src="props.product?.media?.[0] || ''"
                :alt="`Продукт: ${product.name}`"
                :context="'product'"
                :className="'h-full w-full object-cover transition-transform duration-700 group-hover:scale-105'"
            />

            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                <span
                    class="bg-white/90 shadow-sm inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rancho-forest backdrop-blur-sm"
                >
                    <Clock v-if="product.availability_type === 'daily'" :size="12" />
                    <CalendarCheck v-if="product.availability_type === 'preorder'" :size="12" />
                    {{ availabilityLabel }}
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col p-5">
            <h3 class="mb-2 text-lg font-bold text-rancho-forest">
                <Link :href="route('home')" class="focus:outline-none">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    {{ product.name }}
                </Link>
            </h3>

            <p v-if="product.description" class="mb-4 line-clamp-2 text-sm text-rancho-olive/70">
                {{ product.description }}
            </p>

            <div class="mt-auto flex items-end justify-between">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black text-rancho-forest"
                            >{{ formattedPrice }} ₽</span
                        >
                        <span
                            v-if="formattedOldPrice"
                            class="text-sm text-rancho-olive/40 line-through"
                        >
                            {{ formattedOldPrice }} ₽
                        </span>
                    </div>
                    <span
                        class="text-[11px] font-medium uppercase tracking-tight text-rancho-olive/50"
                    >
                        за {{ product.unit }}
                    </span>
                </div>

                <button
                    @click.stop="cartStore.add(product)"
                    class="text-white relative z-20 flex h-12 w-12 items-center justify-center rounded-full bg-rancho-forest transition-all hover:bg-rancho-buttercup hover:text-rancho-forest active:scale-90"
                    :aria-label="`Добавить ${product.name} в корзину`"
                >
                    <ShoppingBasket :size="20" />
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
