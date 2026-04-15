<script setup>
    import { usePage } from '@inertiajs/vue3';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const { props } = usePage();
    const users = props.users.data;
</script>

<template>
    <AdminLayout>
        <section>
            <h1 class="mb-6 text-2xl font-bold">Users</h1>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="user in users"
                    :key="user.id"
                    class="card"
                    tabindex="0"
                    role="article"
                    :aria-label="`User ${user.name}`"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ user.name }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                {{ user.email }}
                            </p>
                        </div>

                        <span class="badge">
                            {{ user.role }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
    .card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .card:focus {
        outline: 2px solid var(--buttercup);
    }

    .badge {
        background: var(--olive);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
    }
</style>
