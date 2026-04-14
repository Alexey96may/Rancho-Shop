<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import { useForm, usePage } from '@inertiajs/vue3';

    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useYandexGeocoder } from '@/composables/useYandexGeocoder';
    import type { Comment, DeliveryDraft, DeliveryInfo, Page, Paginated } from '@/types';

    defineOptions({ layout: MainLayout });

    interface Props {
        page: Page;
        comments: Paginated<Comment>;
        delivery: DeliveryInfo;
    }

    const props = defineProps<Props>();

    /**
     * 🔥 SERVER DELIVERY DRAFT (Inertia share)
     */
    const pageInertia = usePage<{
        deliveryDraft?: DeliveryDraft | null;
    }>();

    const serverDraft = computed(() => pageInertia.props.deliveryDraft);

    /**
     * TYPES
     */
    type GeoPoint = {
        lat: number;
        lng: number;
    };

    /**
     * GEO
     */
    const { suggestions, search, reverse } = useYandexGeocoder();

    /**
     * STATE
     */
    const address = ref('');
    const selectedPoint = ref<GeoPoint | null>(null);
    const isDeliveryAllowed = ref(true);
    const isLocating = ref(false);
    const isConfirmed = ref(false);

    /**
     * FORM
     */
    const form = useForm({
        address: '',
        lat: null as number | null,
        lng: null as number | null,
        is_valid: false,
    });

    /**
     * HELPERS
     */
    function pointKey(point: GeoPoint | null): string | null {
        if (!point) return null;
        return `${point.lat.toFixed(6)}:${point.lng.toFixed(6)}`;
    }

    let confirmedKey: string | null = null;
    let reverseTimeout: ReturnType<typeof setTimeout> | null = null;
    let lastReverseKey: string | null = null;

    /**
     * SYNC
     */
    function syncPoint(point: GeoPoint | null) {
        selectedPoint.value = point;

        if (!point) return;

        form.lat = point.lat;
        form.lng = point.lng;
    }

    /**
     * ADDRESS INPUT
     */
    function onAddressInput() {
        isConfirmed.value = false;
        search(address.value);
    }

    function select(item: any) {
        isConfirmed.value = false;

        address.value = item.name;

        syncPoint({
            lat: item.point[1],
            lng: item.point[0],
        });

        suggestions.value = [];
    }

    /**
     * WATCHERS
     */
    watch(selectedPoint, (val) => {
        if (!val) return;

        const key = pointKey(val);

        if (key && confirmedKey !== key) {
            isConfirmed.value = false;
        }

        if (key && lastReverseKey === key) return;
        lastReverseKey = key;

        if (reverseTimeout) clearTimeout(reverseTimeout);

        reverseTimeout = setTimeout(async () => {
            try {
                const addr = await reverse(val.lat, val.lng);

                if (addr) {
                    address.value = addr;
                }
            } catch (e) {
                console.error('Reverse failed', e);
            }
        }, 300);

        calculateDelivery();
    });

    /**
     * GEOLOCATION
     */
    async function useMyLocation() {
        isLocating.value = true;
        isConfirmed.value = false;

        try {
            const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                });
            });

            syncPoint({
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            });
        } catch (e) {
            console.error(e);
            alert('Ошибка геолокации');
        } finally {
            isLocating.value = false;
        }
    }

    /**
     * SAVE
     */
    function confirmDeliveryPoint() {
        if (!selectedPoint.value) return;

        form.address = address.value;
        form.is_valid = isDeliveryAllowed.value;
        form.lng = selectedPoint.value.lng;
        form.lat = selectedPoint.value.lat;

        form.post(route('delivery.draft.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isConfirmed.value = true;
            },
            onError: () => {
                alert('Nope');
            },
        });

        confirmedKey = pointKey(selectedPoint.value);
    }

    /**
     * INIT FROM SERVER 🔥
     */
    onMounted(() => {
        if (!serverDraft.value) return;

        address.value = serverDraft.value.address;

        if (serverDraft.value.lat && serverDraft.value.lng) {
            const point = {
                lat: serverDraft.value.lat,
                lng: serverDraft.value.lng,
            };

            selectedPoint.value = point;
            confirmedKey = pointKey(point);
        }

        isConfirmed.value = serverDraft.value.is_valid;
        isDeliveryAllowed.value = serverDraft.value.is_valid;
    });

    const distance = ref<number | null>(null);
    const price = ref<number | null>(null);

    async function calculateDelivery() {
        if (!selectedPoint.value) return;

        try {
            console.log(selectedPoint.value.lat, selectedPoint.value.lng, address.value);
            const res = await fetch('/api/delivery/calculate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({
                    lat: selectedPoint.value.lat,
                    lng: selectedPoint.value.lng,
                    address: address.value,
                }),
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('API ERROR RESPONSE:', text);
                throw new Error('HTTP ' + res.status);
            }

            const data = await res.json();

            console.log('API RESULT:', data);

            isDeliveryAllowed.value = data.is_valid;

            distance.value = data.distance_to_farm;
            price.value = data.delivery_price;
        } catch (e) {
            alert('Delivery API failed');
            console.error(e);
            isDeliveryAllowed.value = false;
        }
    }
</script>

<template>
    <main class="min-h-screen bg-[#FCFAF5] text-[#1C3F34]">
        <!-- HEADER -->
        <header class="mx-auto max-w-3xl px-6 pt-10">
            <h1 class="text-3xl font-bold">
                {{ page.title }}
            </h1>
        </header>

        <!-- CONTENT -->
        <section v-if="page.content" class="prose mx-auto mt-6 max-w-3xl px-6">
            <div v-html="page.content" />
        </section>

        <!-- DELIVERY INFO -->
        <section class="mx-auto mt-6 max-w-3xl space-y-2 px-6 text-sm text-[#597D5B]">
            <p>{{ delivery.delivery_info }}</p>
            <p>🕒 {{ delivery.delivery_schedule }}</p>
        </section>

        <!-- MAIN -->
        <section class="mx-auto mt-10 grid max-w-5xl gap-6 px-6 md:grid-cols-2">
            <!-- LEFT -->
            <div class="space-y-4 rounded-xl border bg-white p-6">
                <div>
                    <label class="block text-sm font-medium"> Адрес доставки </label>

                    <input
                        v-model="address"
                        @input="onAddressInput"
                        class="mt-2 w-full rounded-lg border px-3 py-2"
                        placeholder="Введите адрес..."
                    />

                    <ul v-if="suggestions.length" class="mt-1 rounded border bg-white">
                        <li
                            v-for="(item, i) in suggestions"
                            :key="i"
                            @click="select(item)"
                            class="cursor-pointer p-2 hover:bg-gray-100"
                        >
                            {{ item.name }}
                        </li>
                    </ul>
                </div>

                <button
                    @click="useMyLocation"
                    :disabled="isLocating"
                    class="w-full rounded border p-2"
                >
                    📍 Моё местоположение
                </button>

                <button
                    @click="confirmDeliveryPoint"
                    :disabled="!selectedPoint || isConfirmed || !isDeliveryAllowed"
                    class="w-full rounded bg-green-700 p-3 text-white"
                    :class="{ 'opacity-50': isConfirmed || !isDeliveryAllowed }"
                >
                    {{ isConfirmed ? '✔ Сохранено' : 'Подтвердить' }}
                </button>

                <div v-if="!isDeliveryAllowed" class="text-sm text-red-600">
                    Вы вне зоны доставки
                </div>

                <div v-if="delivery?.address_farm">
                    Самовывоз:
                    <span class="text-sm text-emerald-800">{{ delivery?.address_farm }}</span>
                </div>
                <div v-if="serverDraft?.address">
                    Вы выбрали:
                    <span class="text-sm text-emerald-800">{{ serverDraft?.address }}</span>
                </div>
                <span>{{ distance }} </span>
                <span>{{ price }} </span>
                <div v-if="serverDraft?.distance">
                    Расстояние до нас:
                    <span class="text-sm text-emerald-800">{{ serverDraft?.distance }}</span>
                </div>
            </div>

            <!-- MAP -->
            <div class="h-[400px] overflow-hidden rounded-xl border">
                <MapboxPicker
                    v-model="selectedPoint"
                    :farm-coords="delivery.farm_coords"
                    :user-coords="{
                        lng: serverDraft?.lng || 34.30457,
                        lat: serverDraft?.lat || 44.85014,
                    }"
                    :zones="delivery.delivery_zones"
                    @delivery-valid="(v: boolean) => (isDeliveryAllowed = v)"
                />
            </div>
        </section>

        <!-- COMMENTS -->
        <div class="mx-auto mt-12 max-w-3xl px-6 pb-16">
            <CommentsSection :comments="comments" title="Отзывы о доставке" />
        </div>
    </main>
</template>
