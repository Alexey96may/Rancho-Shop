<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useYandexGeocoder } from '@/composables/useYandexGeocoder';
    import type { Comment, DeliveryDraft, DeliveryInfo, Page, Paginated } from '@/types';

    interface Props {
        page: Page;
        comments: Paginated<Comment>;
        delivery: DeliveryInfo;
    }

    defineOptions({ layout: MainLayout });

    const props = defineProps<Props>();
    const isDeliveryAllowed = ref<boolean>(true);

    const farmCoords = computed(() => {
        if (!props.delivery.farm_coords) return null;
        const [lat, lng] = props.delivery.farm_coords.split(',').map(Number);
        return { lat, lng };
    });

    const address = ref('');
    const selectedPoint = ref<{ lat: number; lng: number } | null>(null);
    const isFromMap = ref(false);

    const deliveryDraft = ref<DeliveryDraft>({
        address: '',
        point: null as { lat: number; lng: number } | null,
        isValid: false,
    });

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

    const isLocating = ref(false);
    const isConfirmed = ref(false);

    async function useMyLocation() {
        isLocating.value = true;

        try {
            const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                });
            });

            selectedPoint.value = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            };
        } catch (e: unknown) {
            const message =
                e && typeof e === 'object' && 'message' in e
                    ? String((e as any).message)
                    : 'Ошибка';

            console.error('Geolocation failed', e);
            alert('Ошибка геолокации: ' + message);
        } finally {
            isLocating.value = false;
        }
    }

    async function saveDeliveryDraftToServer() {
        if (!deliveryDraft.value) return;

        await fetch('/api/delivery/draft', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                address: deliveryDraft.value.address,
                lat: deliveryDraft.value.point?.lat,
                lng: deliveryDraft.value.point?.lng,
                is_valid: deliveryDraft.value.isValid,
            }),
        });
    }

    async function confirmDeliveryPoint() {
        if (!selectedPoint.value || !isDeliveryAllowed.value) return;

        deliveryDraft.value = {
            address: address.value,
            point: selectedPoint.value,
            isValid: isDeliveryAllowed.value,
        };

        isConfirmed.value = true;

        try {
            await saveDeliveryDraftToServer();
            console.log('Draft saved to server');
        } catch (e) {
            console.error('Failed to save draft', e);
        }
    }

    watch(
        deliveryDraft,
        (val) => {
            if (!val) return;

            localStorage.setItem('delivery_draft', JSON.stringify(val));
        },
        { deep: true },
    );

    onMounted(() => {
        const saved = localStorage.getItem('delivery_draft');

        if (saved) {
            try {
                deliveryDraft.value = JSON.parse(saved);
                isConfirmed.value = true;
            } catch (e) {}
        }
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
            <div class="space-y-4 rounded-xl border bg-white p-6">
                <!-- ADDRESS INPUT -->
                <div>
                    <label class="block text-sm font-medium" for="delivAdress">
                        Адрес доставки
                    </label>

                    <input
                        v-model="address"
                        @input="search(address)"
                        type="text"
                        id="delivAdress"
                        placeholder="Введите адрес..."
                        class="mt-2 w-full rounded-lg border px-3 py-2 outline-none focus:ring-2 focus:ring-[#3B7558]"
                        autocomplete="street-address"
                        aria-label="Поле ввода адреса доставки"
                    />

                    <!-- SUGGESTIONS -->
                    <ul
                        v-if="suggestions.length"
                        class="shadow absolute z-10 mt-1 w-[calc(100%-3rem)] rounded-lg border bg-white"
                        role="listbox"
                        aria-label="Список подсказок адреса"
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
                </div>

                <!-- DELIVERY INFO -->
                <div class="space-y-1 text-sm">
                    <p>
                        Доставка: <strong>{{ delivery.delivery_cost }} ₽</strong>
                    </p>
                    <p>
                        Бесплатно от: <strong>{{ delivery.free_delivery_from }} ₽</strong>
                    </p>
                </div>

                <!-- SELF PICKUP -->
                <div class="text-sm text-[#597D5B]">
                    <p>Самовывоз: {{ delivery.address_farm }}</p>
                </div>

                <!-- LOCATION BUTTON -->
                <button
                    type="button"
                    @click="useMyLocation"
                    :disabled="isLocating"
                    class="w-full rounded-lg border border-[#3B7558] px-3 py-2 text-sm font-medium text-[#3B7558] transition hover:bg-[#3B7558] hover:text-white disabled:opacity-50"
                    aria-label="Использовать моё текущее местоположение"
                >
                    <span v-if="!isLocating">📍 Моё местоположение</span>
                    <span v-else>Определяем...</span>
                </button>

                <!-- CONFIRM BUTTON -->
                <button
                    type="button"
                    @click="confirmDeliveryPoint"
                    :disabled="!selectedPoint || !isDeliveryAllowed || isConfirmed"
                    class="w-full rounded-lg bg-[#3B7558] px-3 py-3 font-semibold text-white transition hover:bg-[#2f5d45] disabled:opacity-40"
                    aria-label="Подтвердить адрес доставки"
                >
                    <span v-if="!isConfirmed">Подтвердить доставку</span>
                    <span v-else>✔ Адрес подтверждён</span>
                </button>

                <!-- ERROR -->
                <div v-if="!isDeliveryAllowed" class="text-sm text-orange-700">
                    Доставка недоступна в этом районе. Выберите точку в зоне маршрута.
                </div>
            </div>

            <!-- RIGHT (MAP PLACEHOLDER) -->
            <div
                class="flex h-[400px] items-center justify-center rounded-xl border bg-white"
                role="img"
                aria-label="Карта доставки"
            >
                <MapboxPicker
                    v-model="selectedPoint"
                    :farm-coords="farmCoords"
                    @delivery-valid="(v: boolean) => (isDeliveryAllowed = v)"
                />
            </div>
        </section>

        <!-- 💬 COMMENTS -->
        <div class="mx-auto mt-12 max-w-3xl px-6 pb-16">
            <CommentsSection :comments="comments" :title="'Отзывы о доставке'" />
        </div>
    </main>
</template>
