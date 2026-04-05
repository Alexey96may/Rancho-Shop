<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { useCartStore } from '@/stores/cart';

    const menuItems = [
        { name: 'Каталог', path: '/catalog' },
        { name: 'Наши Животные', path: '/animals' },
        { name: 'О ферме', path: '/about' },
        { name: 'Доставка', path: '/delivery' },
    ];

    const cartStore = useCartStore();

    const toggleCart = () => {
        console.log('Открываем боковую панель корзины');
    };
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-rancho-olive/10 bg-rancho-paper/80 backdrop-blur-md"
    >
        <AppContainer class="flex h-20 items-center justify-between">
            <router-link to="/" class="group flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-rancho-pine transition-transform group-hover:rotate-12"
                >
                    <span class="text-white font-header text-xl">МД</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-header text-2xl leading-none text-rancho-forest"
                        >Молочная Долина</span
                    >
                    <span
                        class="font-sans text-[10px] uppercase tracking-[0.2em] text-rancho-olive opacity-70"
                        >Семейное Ранчо</span
                    >
                </div>
            </router-link>

            <nav class="hidden items-center gap-8 md:flex">
                <router-link
                    v-for="item in menuItems"
                    :key="item.path"
                    :to="item.path"
                    class="group relative font-sans font-semibold text-rancho-forest transition-colors hover:text-rancho-pine"
                >
                    {{ item.name }}
                    <span
                        class="absolute -bottom-1 left-0 h-0.5 w-0 bg-rancho-buttercup transition-all group-hover:w-full"
                    ></span>
                </router-link>
            </nav>

            <div class="flex items-center gap-5">
                <button class="p-2 text-rancho-forest transition-colors hover:text-rancho-pine">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </button>

                <button
                    @click="toggleCart"
                    class="text-white shadow-md relative flex items-center gap-2 rounded-xl bg-rancho-pine px-4 py-2 transition-all hover:bg-rancho-forest active:scale-95"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                        />
                    </svg>

                    <span class="hidden font-sans text-sm font-bold sm:inline">Лукошко</span>

                    <div
                        v-if="cartStore.totalItems > 0"
                        class="animate-bounce-short absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full border-2 border-rancho-paper bg-rancho-buttercup text-[10px] font-black text-rancho-forest"
                    >
                        {{ cartStore.totalItems }}
                    </div>
                </button>
            </div>
        </AppContainer>
    </header>
</template>

<style scoped>
    .animate-bounce-short {
        animation: bounce-short 0.5s ease-in-out;
    }

    @keyframes bounce-short {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-4px);
        }
    }
</style>
