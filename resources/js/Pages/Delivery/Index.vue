<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useYandexGeocoder } from '@/composables/useYandexGeocoder';
    import type { Comment, DeliveryInfo, Page, Paginated } from '@/types';

    interface Props {
        page: Page;
        comments: Paginated<Comment>;
        delivery: DeliveryInfo;
    }

    defineOptions({ layout: MainLayout });

    const props = defineProps<Props>();

    const farmCoords = computed(() => {
        if (!props.delivery.farm_coords) return null;
        const [lat, lng] = props.delivery.farm_coords.split(',').map(Number);
        return { lat, lng };
    });

    const address = ref('');
    const selectedPoint = ref<{ lat: number; lng: number } | null>(null);
    const isFromMap = ref(false);

    const { suggestions, search, reverse } = useYandexGeocoder();

    const select = (item: any) => {
        isFromMap.value = false;

        address.value = item.name;

        selectedPoint.value = {
            lng: item.point[0],
            lat: item.point[1],
        };

        suggestions.value = [];
    };

    let isFirstResolved = false;
    let reverseTimeout: any = null;

    watch(
        selectedPoint,
        async (val) => {
            if (!val) return;

            // 🟢 ПЕРВЫЙ РАЗ
            if (!isFirstResolved) {
                isFirstResolved = true;

                const addr = await reverse(val.lat, val.lng);

                if (addr) {
                    address.value = addr;
                }

                return;
            }

            // 🟢 debounce
            if (reverseTimeout) clearTimeout(reverseTimeout);

            reverseTimeout = setTimeout(async () => {
                const addr = await reverse(val.lat, val.lng);

                console.log('UPDATE REVERSE', addr);

                if (addr) {
                    address.value = addr;
                }
            }, 400);
        },
        { immediate: true },
    );
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
                <label class="block text-sm font-medium" for="delivAdress"> Адрес доставки </label>
                <input
                    v-model="address"
                    @input="search(address)"
                    type="text"
                    id="delivAdress"
                    placeholder="Введите адрес..."
                    class="mt-2 w-full rounded-lg border px-3 py-2"
                    autocomplete="street-address"
                    aria-label="Адрес доставки"
                />
                <ul
                    v-if="suggestions.length"
                    class="shadow absolute z-10 mt-1 w-full rounded-lg border bg-white"
                    role="listbox"
                >
                    <li
                        v-for="(item, i) in suggestions"
                        :key="i"
                        @click="select(item)"
                        class="cursor-pointer px-3 py-2 hover:bg-[#FCFAF5]"
                        role="option"
                    >
                        <div class="font-medium">{{ item.name }}</div>
                        <div class="text-xs text-gray-500">{{ item.description }}</div>
                    </li>
                </ul>

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
        <div class="mx-auto mt-12 max-w-3xl px-6 pb-16">
            <CommentsSection :comments="comments" :title="'Отзывы о доставке'" />
        </div>
    </main>
</template>
