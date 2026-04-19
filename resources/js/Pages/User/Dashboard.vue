<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';

    import { CreditCardIcon, ShieldCheckIcon, ShoppingBagIcon, UserIcon } from 'lucide-vue-next';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order, User } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        auth: { user: User };
        latestOrder: Order | null;
        stats: { total_orders: number; total_spent: number };
    }>();

    const roleLabels = {
        admin: { label: 'Администратор', class: 'bg-rose-500/10 text-rose-600' },
        user: { label: 'Клиент', class: 'bg-emerald-500/10 text-emerald-600' },
    };
</script>

<template>
    <Head title="Личный кабинет" />
    <main class="mx-auto max-w-6xl space-y-10 px-4 py-10" role="main">
        <section
            class="shadow-sm flex flex-col items-center gap-8 rounded-[3rem] border border-slate-200 bg-white p-8 md:flex-row"
            aria-labelledby="profile-heading"
        >
            <div class="relative" aria-hidden="true">
                <img
                    :src="auth.user.avatar"
                    :alt="auth.user.name"
                    class="h-24 w-24 rounded-3xl object-cover ring-4 ring-slate-50"
                />
                <div
                    v-if="auth.user.is_admin"
                    class="absolute -right-2 -top-2 rounded-xl bg-rose-500 p-1.5 text-white"
                >
                    <ShieldCheckIcon class="h-4 w-4" />
                </div>
            </div>

            <div class="flex-1 text-center md:text-left">
                <div class="flex flex-wrap items-center justify-center gap-3 md:justify-start">
                    <h1
                        id="profile-heading"
                        class="text-3xl font-black uppercase tracking-tight text-slate-900"
                    >
                        {{ auth.user.name }}
                    </h1>
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest',
                            roleLabels[auth.user.role]?.class,
                        ]"
                    >
                        {{ roleLabels[auth.user.role]?.label }}
                    </span>
                </div>
                <p class="font-medium text-slate-500">{{ auth.user.email }}</p>
            </div>

            <Link
                :href="route('profile.edit')"
                class="rounded-2xl bg-slate-100 px-6 py-3 text-xs font-black uppercase outline-none transition-all hover:bg-slate-200 focus:ring-4 focus:ring-slate-200"
                aria-label="Редактировать профиль"
            >
                Настройки
            </Link>
        </section>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                class="group relative overflow-hidden rounded-[2.5rem] bg-orange-500 p-8 text-white"
                role="status"
            >
                <p class="text-[10px] font-black uppercase tracking-widest opacity-80">
                    Заказов сделано
                </p>
                <p class="mt-2 text-4xl font-black">{{ stats.total_orders }}</p>
                <ShoppingBagIcon
                    class="absolute -bottom-4 -right-4 h-24 w-24 opacity-20"
                    aria-hidden="true"
                />
            </div>

            <div
                class="group relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-8 text-white"
                role="status"
            >
                <p class="text-[10px] font-black uppercase tracking-widest opacity-80">
                    Сумма покупок
                </p>
                <p class="mt-2 text-4xl font-black">{{ stats.total_spent.toLocaleString() }} ₽</p>
                <CreditCardIcon
                    class="absolute -bottom-4 -right-4 h-24 w-24 opacity-20"
                    aria-hidden="true"
                />
            </div>
        </div>
    </main>
</template>
