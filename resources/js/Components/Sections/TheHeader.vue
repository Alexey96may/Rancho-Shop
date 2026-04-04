<template>
    <header
        class="bg-rancho-paper/80 border-rancho-olive/10 sticky top-0 z-50 border-b backdrop-blur-md"
    >
        <AppContainer class="flex h-20 items-center justify-between">
            <router-link to="/" class="group flex items-center gap-3">
                <div
                    class="bg-rancho-pine flex h-10 w-10 items-center justify-center rounded-full transition-transform group-hover:rotate-12"
                >
                    <span class="font-header text-xl text-white">МД</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-header text-rancho-forest text-2xl leading-none"
                        >Молочная Долина</span
                    >
                    <span
                        class="text-rancho-olive font-sans text-[10px] uppercase tracking-[0.2em] opacity-70"
                        >Семейное Ранчо</span
                    >
                </div>
            </router-link>

            <nav class="hidden items-center gap-8 md:flex">
                <router-link
                    v-for="item in menuItems"
                    :key="item.path"
                    :to="item.path"
                    class="text-rancho-forest hover:text-rancho-pine group relative font-sans font-semibold transition-colors"
                >
                    {{ item.name }}
                    <span
                        class="bg-rancho-buttercup absolute -bottom-1 left-0 h-0.5 w-0 transition-all group-hover:w-full"
                    ></span>
                </router-link>
            </nav>

            <div class="flex items-center gap-5">
                <button class="text-rancho-forest hover:text-rancho-pine p-2 transition-colors">
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
                    class="bg-rancho-pine hover:bg-rancho-forest relative flex items-center gap-2 rounded-xl px-4 py-2 text-white shadow-md transition-all active:scale-95"
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
                        v-if="cartCount > 0"
                        class="bg-rancho-buttercup text-rancho-forest border-rancho-paper animate-bounce-short absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full border-2 text-[10px] font-black"
                    >
                        {{ cartCount }}
                    </div>
                </button>
            </div>
        </AppContainer>
    </header>
</template>

<script setup>
    import { computed, ref } from 'vue';

    // import { useCartStore } from '@/stores/cart'; // Когда напишем стор

    const menuItems = [
        { name: 'Каталог', path: '/catalog' },
        { name: 'Наши Животные', path: '/animals' },
        { name: 'О ферме', path: '/about' },
        { name: 'Доставка', path: '/delivery' },
    ];

    // Имитация данных из Pinia для теста верстки
    // const cartStore = useCartStore();
    const cartCount = ref(3);

    const toggleCart = () => {
        console.log('Открываем боковую панель корзины');
    };
</script>

<style scoped>
    /* Анимация для баджа лукошка */
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
