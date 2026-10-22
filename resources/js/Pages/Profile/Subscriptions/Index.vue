<script setup lang="ts">
    import { ref } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import { BellIcon, ChevronDownIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import type { Paginated, Product } from '@/types';

    defineOptions({ layout: ProfileLayout });

    defineProps<{
        products: Paginated<Product>;
        counts: {
            total: number;
            in_stock: number;
        };
    }>();

    function pluralize(n: number, one: string, few: string, many: string): string {
        const mod10 = n % 10;
        const mod100 = n % 100;

        if (mod10 === 1 && mod100 !== 11) return one;
        if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
        return many;
    }

    const isInfoOpen = ref(false);
</script>

<template>
    <Head title="Мои подписки" />

    <div class="space-y-12">
        <div class="space-y-6">
            <!-- HEADER -->
            <header v-scroll-reveal class="space-y-4">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-2">
                    <h1 class="text-2xl font-black text-white sm:text-4xl">Мои подписки</h1>

                    <span
                        v-if="counts.total > 0"
                        class="text-xs font-bold text-rancho-ink-500 sm:text-sm"
                    >
                        {{ counts.total }}
                        {{ pluralize(counts.total, 'товар', 'товара', 'товаров') }}
                        <template v-if="counts.in_stock > 0">
                            · <span class="text-rancho-pine">{{ counts.in_stock }} в наличии</span>
                        </template>
                    </span>
                </div>

                <p class="max-w-2xl text-xs leading-relaxed text-rancho-ink-400 sm:text-sm">
                    Здесь собраны товары, за поступлением которых вы следите. Как только товар снова
                    появится в наличии, мы пришлём письмо на вашу почту — вам не нужно проверять
                    каталог вручную.
                </p>
            </header>

            <!-- INFO BANNER -->
            <div
                v-scroll-reveal
                class="rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950/60"
            >
                <button
                    type="button"
                    @click="isInfoOpen = !isInfoOpen"
                    class="flex w-full items-center justify-between gap-3 rounded-2xl p-4 text-left transition-colors hover:bg-rancho-ink-900/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 sm:p-5"
                    :aria-expanded="isInfoOpen"
                    aria-controls="how-it-works"
                >
                    <span
                        class="flex items-center gap-2 text-xs font-bold text-rancho-ink-50 sm:text-sm"
                    >
                        <InformationCircleIcon
                            class="h-4 w-4 text-rancho-buttercup"
                            aria-hidden="true"
                        />
                        Как это работает
                    </span>

                    <ChevronDownIcon
                        class="h-4 w-4 text-rancho-ink-400 transition-transform duration-300"
                        :class="{ 'rotate-180': isInfoOpen }"
                        aria-hidden="true"
                    />
                </button>

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[500px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[500px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-show="isInfoOpen" id="how-it-works" class="overflow-hidden">
                        <ul
                            class="list-inside list-disc space-y-1 px-4 pb-4 text-xs leading-relaxed text-rancho-ink-400 sm:px-5 sm:pb-5 sm:text-sm"
                        >
                            <li>
                                Нажмите «Подписаться» на странице товара — и мы запомним, что вы
                                ждёте его поступления.
                            </li>
                            <li>
                                Когда товар снова появится в наличии, мы отправим одно письмо на ваш
                                email.
                            </li>
                            <li>
                                Если у вас не указан email в профиле — уведомление не придёт.
                                Добавьте его в
                                <Link
                                    :href="route('profile.edit')"
                                    class="font-bold text-rancho-pine underline decoration-rancho-pine/30 underline-offset-2 hover:text-rancho-forest"
                                >
                                    настройках профиля </Link
                                >. Письмо может попасть в «Спам» — проверьте папку, если не видите
                                уведомление.
                            </li>
                            <li>
                                Подписку можно отменить в любой момент — на странице товара, нажав
                                кнопку «Отписаться».
                            </li>
                        </ul>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div
            v-if="products.data.length === 0"
            v-scroll-reveal
            class="flex flex-col items-center gap-6 rounded-2xl border border-dashed border-rancho-ink-800 bg-rancho-ink-950/60 p-8 text-center sm:p-12"
        >
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-rancho-ink-800">
                <BellIcon class="h-7 w-7 text-rancho-ink-500" aria-hidden="true" />
            </div>

            <div class="space-y-2">
                <p class="text-sm font-bold text-white">Вы пока ни на что не подписаны</p>
                <p class="text-xs text-rancho-ink-400">
                    Подпишитесь на товар — и мы сообщим, когда он появится в наличии
                </p>
            </div>

            <Link
                :href="route('catalog.index')"
                class="inline-flex items-center justify-center rounded-xl bg-rancho-pine px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition-colors hover:bg-rancho-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40"
            >
                Перейти в каталог
            </Link>
        </div>

        <!-- GRID -->
        <div
            v-else
            v-scroll-reveal
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
                theme="dark"
            />
        </div>

        <!-- PAGINATION -->
        <MainPagination v-scroll-reveal :links="products?.meta?.links" theme="dark" />
    </div>
</template>
