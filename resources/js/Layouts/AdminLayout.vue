<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toast from '@/Components/Shared/Toast.vue';
    import type { SharedData } from '@/types';

    interface MenuItem {
        key: string;
        label: string;
        href: string;
        icon: string;
    }

    const page = usePage<SharedData>();

    const user = page.props.auth.user;
    const permissions = (page.props.permissions ?? []) as any[];

    const menuConfig: Record<string, { label: string; href: string; icon: string }> = {
        'manage-products': { label: 'Продукты', href: '/admin/products', icon: '📦' },
        'manage-orders': { label: 'Заказы', href: '/admin/orders', icon: '🧾' },
        'manage-comments': { label: 'Комментарии', href: '/admin/comments', icon: '💬' },
        'manage-delivery': { label: 'Доставка', href: '/admin/delivery', icon: '🚚' },
        'manage-animals': { label: 'Животные', href: '/admin/animals', icon: '🐄' },
        'manage-users': { label: 'Пользователи', href: '/admin/users', icon: '👤' },
        'manage-categories': { label: 'Категории', href: '/admin/categories', icon: '📂' },
        'manage-catalog': { label: 'Каталог', href: '/admin/catalog', icon: '🏷' },
        'manage-pages': { label: 'Страницы', href: '/admin/pages', icon: '📄' },
        'manage-promocodes': { label: 'Промокоды', href: '/admin/promocodes', icon: '🎟' },
        'manage-faq': { label: 'FAQ', href: '/admin/faq', icon: '❓' },
        'manage-features': { label: 'Фичи', href: '/admin/features', icon: '✨' },
        'manage-settings': { label: 'Настройки', href: '/admin/settings', icon: '⚙️' },
    };

    const menuItems = computed<MenuItem[]>(() => {
        return permissions
            .map((p: any) => {
                const conf = menuConfig[p.value];
                if (!conf) return null;

                return {
                    key: p.value,
                    ...conf,
                };
            })
            .filter(Boolean) as MenuItem[];
    });

    const isActive = (href: string) => {
        return window.location.pathname.startsWith(href);
    };
</script>

<template>
    <SeoMeta />
    <Toast />

    <div class="flex min-h-screen bg-[var(--paper)] text-[var(--forest)]">
        <!-- SIDEBAR -->
        <aside class="flex w-64 flex-col bg-[var(--forest)] text-black">
            <div class="p-4 text-lg font-bold">Rancho Admin</div>

            <nav class="flex flex-col gap-1 px-3">
                <Link href="/admin" class="nav-link"> 🏠 Dashboard </Link>

                <Link
                    v-for="item in menuItems"
                    :key="item.key"
                    :href="item.href"
                    class="nav-link"
                    :class="{ 'nav-active': isActive(item.href) }"
                >
                    <span class="flex items-center gap-2">
                        <span>{{ item.icon }}</span>
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <div class="mt-auto p-4 text-sm opacity-80">
                <div>{{ user?.name }}</div>
                <div class="text-xs opacity-60">{{ user?.email }}</div>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="flex-1 p-6">
            <slot />
        </main>
    </div>
</template>

<style scoped>
    .nav-link {
        display: flex;
        padding: 10px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-active {
        background: rgba(255, 255, 255, 0.2);
    }
</style>
