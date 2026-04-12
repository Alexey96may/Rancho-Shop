<script setup lang="ts">
    import { computed, ref } from 'vue';

    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, DeliveryInfo, Page, Paginated } from '@/types';

    interface Props {
        page: Page;
        comments: Paginated<Comment>;
        delivery: DeliveryInfo;
    }

    defineOptions({ layout: MainLayout });

    const props = defineProps<Props>();

    const selectedPoint = ref<{ lat: number; lng: number } | null>(null);

    const farmCoords = computed(() => {
        if (!props.delivery.farm_coords) return null;
        const [lat, lng] = props.delivery.farm_coords.split(',').map(Number);
        return { lat, lng };
    });
</script>

<template>
    <main class="min-h-screen bg-[#FCFAF5] text-[#1C3F34]">
        <!-- 🧾 PAGE HEADER -->
        <header class="mx-auto max-w-3xl px-6 pt-10">
            <h1 class="text-3xl font-bold">
                {{ page.title }}
            </h1>
        </header>

        <!-- 📄 CMS CONTENT -->
        <section
            v-if="page.content"
            class="prose prose-neutral mx-auto mt-6 max-w-3xl px-6"
            aria-label="Delivery information content"
        >
            <!-- ⚠️ HTML from CMS -->
            <div v-html="page.content" />
        </section>

        <!-- 🗺️ DELIVERY BLOCK -->
        <section
            class="mx-auto mt-10 grid max-w-5xl gap-6 px-6 md:grid-cols-2"
            aria-label="Delivery map and form"
        >
            <!-- LEFT -->
            <div class="rounded-xl border bg-white p-6">
                <label class="block text-sm font-medium"> Адрес доставки </label>

                <input
                    type="text"
                    placeholder="Введите адрес..."
                    class="mt-2 w-full rounded-lg border px-3 py-2"
                    autocomplete="street-address"
                    aria-label="Введите адрес доставки"
                />

                <div class="mt-6 text-sm">
                    <p>
                        Доставка: <strong>{{ delivery.delivery_cost }} ₽</strong>
                    </p>
                    <p>
                        Бесплатно от: <strong>{{ delivery.free_delivery_from }} ₽</strong>
                    </p>
                </div>

                <div class="mt-6 text-sm text-[#597D5B]">
                    <p>Самовывоз: {{ delivery.address_farm }}</p>
                </div>
            </div>

            <!-- RIGHT (MAP PLACEHOLDER) -->
            <div
                class="flex h-[400px] items-center justify-center rounded-xl border bg-white"
                role="img"
                aria-label="Карта доставки"
            >
                <MapboxPicker v-model="selectedPoint" :farm-coords="farmCoords" />
            </div>
        </section>

        <!-- 💬 COMMENTS -->
        <section class="mx-auto mt-12 max-w-3xl px-6 pb-16" aria-label="Отзывы о доставке">
            <h2 class="mb-4 text-xl font-semibold">Отзывы о доставке</h2>

            <CommentsSection :comments="comments" />
        </section>
    </main>
</template>
