<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

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

    type GeoPoint = {
        lat: number;
        lng: number;
    };

    type DeliveryForm = {
        address: string;
        lat: number | null;
        lng: number | null;
        is_valid: boolean;
    };

    const farmCoords = computed<GeoPoint | null>(() => {
        if (!props.delivery.farm_coords) return null;
        const [lat, lng] = props.delivery.farm_coords.split(',').map(Number);
        return { lat, lng };
    });

    const { suggestions, search, reverse } = useYandexGeocoder();

    const address = ref('');
    const selectedPoint = ref<GeoPoint | null>(null);
    const isDeliveryAllowed = ref(true);
    const isLocating = ref(false);
    const isConfirmed = ref(false);

    const deliveryDraft = ref<DeliveryDraft>({
        address: '',
        point: null,
        isValid: false,
    });

    const form = useForm<DeliveryForm>({
        address: '',
        lat: null,
        lng: null,
        is_valid: false,
    });

    let confirmedKey: string | null = null;
    let reverseTimeout: ReturnType<typeof setTimeout> | null = null;
    let lastReverseKey: string | null = null;

    function pointKey(point: GeoPoint | null): string | null {
        if (!point) return null;
        return `${point.lat.toFixed(6)}:${point.lng.toFixed(6)}`;
    }

    function syncPoint(point: GeoPoint | null) {
        selectedPoint.value = point;
        deliveryDraft.value.point = point;
    }

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

    watch(address, (val) => {
        deliveryDraft.value.address = val;
    });

    watch(selectedPoint, (val) => {
        if (!val) return;

        deliveryDraft.value.point = val;

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
                    deliveryDraft.value.address = addr;
                }
            } catch (e) {
                console.error('Reverse geocoding failed', e);
            }
        }, 350);
    });

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

    async function confirmDeliveryPoint() {
        if (!selectedPoint.value || !isDeliveryAllowed.value) return;

        form.address = address.value;
        form.lat = selectedPoint.value.lat;
        form.lng = selectedPoint.value.lng;
        form.is_valid = isDeliveryAllowed.value;

        form.post(route('delivery.draft.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('saved via inertia');
            },
        });
    }

    onMounted(() => {
        const saved = localStorage.getItem('delivery_draft');

        if (!saved) return;

        try {
            const parsed = JSON.parse(saved) as DeliveryDraft;

            deliveryDraft.value = parsed;
            address.value = parsed.address ?? '';
            selectedPoint.value = parsed.point ?? null;
            isDeliveryAllowed.value = parsed.isValid ?? true;
            isConfirmed.value = !!parsed.isValid;
            confirmedKey = pointKey(parsed.point);
        } catch (e) {
            console.error('Failed to restore delivery draft', e);
        }
    });

    watch(
        deliveryDraft,
        (val) => {
            if (!val) return;

            localStorage.setItem('delivery_draft', JSON.stringify(val));
        },
        { deep: true },
    );
</script>

<template>
    <main class="min-h-screen bg-[#FCFAF5] text-[#1C3F34]">
        <header class="mx-auto max-w-3xl px-6 pt-10">
            <h1 class="text-3xl font-bold">
                {{ page.title }}
            </h1>
        </header>

        <section
            v-if="page.content"
            class="prose prose-neutral mx-auto mt-6 max-w-3xl px-6"
            aria-label="Delivery information content"
        >
            <div v-html="page.content" />
        </section>

        <section
            class="mx-auto mt-10 grid max-w-5xl gap-6 px-6 md:grid-cols-2"
            aria-label="Delivery map and form"
        >
            <div class="space-y-4 rounded-xl border bg-white p-6">
                <div class="relative">
                    <label class="block text-sm font-medium" for="delivAdress">
                        Адрес доставки
                    </label>

                    <input
                        v-model="address"
                        @input="onAddressInput"
                        type="text"
                        id="delivAdress"
                        placeholder="Введите адрес..."
                        class="mt-2 w-full rounded-lg border px-3 py-2 outline-none focus:ring-2 focus:ring-[#3B7558]"
                        autocomplete="street-address"
                        aria-label="Поле ввода адреса доставки"
                    />

                    <ul
                        v-if="suggestions.length"
                        class="shadow absolute z-10 mt-1 w-full rounded-lg border bg-white"
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

                <div class="space-y-1 text-sm">
                    <p>
                        Доставка: <strong>{{ delivery.delivery_cost }} ₽</strong>
                    </p>
                    <p>
                        Бесплатно от: <strong>{{ delivery.free_delivery_from }} ₽</strong>
                    </p>
                </div>

                <div class="text-sm text-[#597D5B]">
                    <p>Самовывоз: {{ delivery.address_farm }}</p>
                </div>

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

                <button
                    type="button"
                    @click="confirmDeliveryPoint"
                    :disabled="!selectedPoint || !isDeliveryAllowed || isLocating || isConfirmed"
                    class="w-full rounded-lg bg-[#3B7558] px-3 py-3 font-semibold text-white transition hover:bg-[#2f5d45] disabled:opacity-40"
                    aria-label="Подтвердить адрес доставки"
                >
                    <span v-if="!isConfirmed">Подтвердить доставку</span>
                    <span v-else>✔ Адрес подтверждён</span>
                </button>

                <div v-if="isConfirmed" class="text-sm text-green-700">
                    Адрес сохранён. Можно изменить точку на карте или адрес в поле.
                </div>

                <div v-if="!isDeliveryAllowed" class="text-sm text-orange-700">
                    Доставка недоступна в этом районе. Выберите точку в зоне маршрута.
                </div>
            </div>

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

        <div class="mx-auto mt-12 max-w-3xl px-6 pb-16">
            <CommentsSection :comments="comments" :title="'Отзывы о доставке'" />
        </div>
    </main>
</template>
