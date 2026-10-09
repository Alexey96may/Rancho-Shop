<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import {
        ArrowRightStartOnRectangleIcon,
        ChatBubbleLeftRightIcon,
        CpuChipIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import TheFooter from '@/Components/Sections/TheFooter.vue';
    import TheHeader from '@/Components/Sections/TheHeader.vue';
    import Toaster from '@/Components/Shared/Toaster.vue';
    import { useFlashToasts } from '@/composables/useFlashToasts';
    import { SharedData } from '@/types';

    useFlashToasts();

    defineProps<{ title: string }>();

    const page = usePage<SharedData>();
    const user = computed(() => page.props.auth?.user?.data);

    const isStuff = computed(() => user.value?.is_stuff);
</script>

<template>
    <div class="bg-slate-950 text-white">
        <TheHeader />
        <Toaster />

        <div class="mx-auto min-h-screen max-w-6xl px-4 py-12">
            <h1 class="mb-8 text-3xl font-black">Личный кабинет</h1>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <aside class="flex flex-col gap-2">
                    <template v-if="isStuff">
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center gap-3 rounded-2xl border border-orange-500/20 bg-orange-500/10 px-4 py-3 text-sm font-bold text-orange-400 transition-all hover:bg-orange-500 hover:text-black"
                        >
                            <CpuChipIcon class="h-5 w-5 animate-pulse" />
                            Панель управления
                        </Link>
                        <hr class="my-1 border-slate-900" />
                    </template>

                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition-all"
                        :class="
                            route().current('profile.edit')
                                ? 'bg-slate-800 text-white ring-1 ring-slate-700'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white'
                        "
                    >
                        <UserIcon class="h-5 w-5" />
                        Профиль
                    </Link>

                    <Link
                        :href="route('profile.comments.index')"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition-all"
                        :class="
                            route().current('profile.comments.*')
                                ? 'bg-slate-800 text-white ring-1 ring-slate-700'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white'
                        "
                    >
                        <ChatBubbleLeftRightIcon class="h-5 w-5" />
                        Мои отзывы
                    </Link>

                    <Link
                        :href="route('profile.orders.index')"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition-all"
                        :class="
                            route().current('profile.orders.*')
                                ? 'bg-slate-800 text-white ring-1 ring-slate-700'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white'
                        "
                    >
                        <ShoppingBagIcon class="h-5 w-5" />
                        История заказов
                    </Link>

                    <hr class="my-2 border-slate-900" />

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-bold text-slate-500 transition-all hover:bg-red-500/10 hover:text-red-400"
                    >
                        <ArrowRightStartOnRectangleIcon class="h-5 w-5" />
                        Выйти из профиля
                    </Link>
                </aside>

                <main
                    class="rounded-3xl border border-slate-800 bg-slate-900/50 p-6 backdrop-blur-sm md:col-span-3"
                >
                    <h2 class="mb-6 text-xl font-black">{{ title }}</h2>
                    <slot />
                </main>
            </div>
        </div>

        <TheFooter />
    </div>
</template>
