<script setup>
    import { Link, usePage } from '@inertiajs/vue3';

    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toast from '@/Components/Shared/Toast.vue';

    const { props } = usePage();
    const user = props.auth.user;
    const can = props.can;
</script>

<template>
    <SeoMeta />
    <Toast />

    <div class="flex min-h-screen bg-[var(--paper)] text-[var(--forest)]">
        <!-- Sidebar -->
        <aside
            class="flex w-64 flex-col bg-[var(--forest)] text-white"
            aria-label="Admin navigation"
        >
            <div class="p-4 text-lg font-bold">Rancho Admin</div>

            <nav class="flex flex-col gap-2 px-3">
                <Link href="/admin" class="nav-link" aria-label="Dashboard"> Dashboard </Link>

                <Link
                    v-if="can.manageUsers"
                    href="/admin/users"
                    class="nav-link"
                    aria-label="Users"
                >
                    Users
                </Link>
            </nav>
        </aside>

        <!-- Content -->
        <main class="flex-1 p-6" tabindex="-1">
            <slot />
        </main>
    </div>
</template>

<style scoped>
    .nav-link {
        padding: 10px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        background: var(--pine);
    }

    .nav-link:focus {
        outline: 2px solid var(--buttercup);
        outline-offset: 2px;
    }
</style>
