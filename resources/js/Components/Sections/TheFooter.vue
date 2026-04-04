<template>
    <footer
        class="bg-rancho-forest text-rancho-paper rounded-t-[1rem] pb-8 pt-16 md:rounded-t-[2rem]"
    >
        <AppContainer>
            <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col gap-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-rancho-buttercup flex h-10 w-10 items-center justify-center rounded-full"
                        >
                            <span class="text-rancho-forest font-header text-xl">МД</span>
                        </div>
                        <span class="font-header text-2xl tracking-wide">Молочная Долина</span>
                    </div>
                    <p class="max-w-xs font-sans text-sm leading-relaxed opacity-80">
                        Мы верим, что еда должна быть честной. Наше ранчо — это место, где природа и
                        современные технологии встречаются для создания идеальных продуктов.
                    </p>
                    <div class="flex gap-4">
                        <a
                            href="https://t.me/elenikaglossa"
                            target="_blank"
                            class="hover:text-rancho-buttercup transition-colors"
                        >
                            <span class="sr-only">Telegram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.91-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-header text-rancho-buttercup mb-6 text-xl">Разделы</h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li>
                            <router-link
                                to="/catalog"
                                class="inline-block transition-transform hover:translate-x-2"
                                >Весь Каталог</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                to="/animals"
                                class="inline-block transition-transform hover:translate-x-2"
                                >Наши Коровы</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                to="/about"
                                class="inline-block transition-transform hover:translate-x-2"
                                >История Ранчо</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                to="/delivery"
                                class="inline-block transition-transform hover:translate-x-2"
                                >Оплата и Доставка</router-link
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-header text-rancho-buttercup mb-6 text-xl">Продукция</h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li><a href="#" class="hover:text-rancho-buttercup">Свежее Молоко</a></li>
                        <li><a href="#" class="hover:text-rancho-buttercup">Домашние Сыры</a></li>
                        <li><a href="#" class="hover:text-rancho-buttercup">Творог и Сливки</a></li>
                        <li><a href="#" class="hover:text-rancho-buttercup">Фермерские Яйца</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-header text-rancho-buttercup mb-6 text-xl">Связаться</h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li class="flex items-start gap-3">
                            <svg
                                class="text-rancho-buttercup h-5 w-5 shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span>Крым, Симферопольский р-н,<br />с. Долинное</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg
                                class="text-rancho-buttercup h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                />
                            </svg>
                            <span>+7 (978) 000-00-00</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="border-rancho-paper/10 flex flex-col items-center justify-between gap-4 border-t pt-8 md:flex-row"
            >
                <p class="font-sans text-xs opacity-60">
                    © 2026 Молочная Долина. Сделано с любовью к природе.
                </p>
                <div class="flex gap-6 font-sans text-xs opacity-60">
                    <a href="#" class="hover:opacity-100">Политика конфиденциальности</a>
                    <a href="#" class="hover:opacity-100">Публичная оферта</a>
                </div>
            </div>
        </AppContainer>
    </footer>
</template>

<script setup>
    // Здесь пока нет логики, но можно добавить подписку на рассылку
</script>

<style scoped>
    /* Дополнительные стили, если понадобятся */
</style>
