<script setup>
    import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <AdminLayout>
        <section>
            <h1 class="mb-4 text-2xl font-bold">Dashboard</h1>

            <div class="grid grid-cols-3 gap-4">
                <div class="card">Orders</div>

                <div class="card">Users</div>

                <div class="card">Revenue</div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
    .card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-3px);
    }
</style>
