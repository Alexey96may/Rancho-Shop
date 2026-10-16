<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import { useForm, usePage } from '@inertiajs/vue3';

    import { debounce } from 'lodash-es';
    import { Check, MapPin, MapPinned } from 'lucide-vue-next';

    import MapboxPicker from '@/Components/Map/MapboxPicker.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import DeliveryMapLegend from '@/Components/Shared/DeliveryMapLegend.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import { useYandexGeocoder } from '@/composables/features/useYandexGeocoder';
    import { useNotificationsStore } from '@/stores/notifications';
    import type {
        Comment,
        DeliveryDraft,
        DeliveryInfo,
        Page,
        Paginated,
        ResourceSingle,
        SharedData,
    } from '@/types';
    import { formatDistance, formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const notify = useNotificationsStore();

    interface Props {
        page: ResourceSingle<Page>;
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

        debouncedCalculateDelivert();
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
            notify.error('Откройте доступ к вашей геолокации');
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
            onError: () => {},
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

    const debouncedCalculateDelivert = debounce(calculateDelivery, 500);

    async function calculateDelivery() {
        if (!selectedPoint.value) return;

        try {
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
                throw new Error('HTTP ' + res.status);
            }

            const data = await res.json();

            isDeliveryAllowed.value = data.is_valid;

            distance.value = data.distance_to_farm;
            price.value = data.delivery_price;
        } catch (e) {
            console.error(e);
            notify.error('Ошибка выбора адресса.');
            isDeliveryAllowed.value = false;
        }
    }

    const { submitComment } = useComments('page', props.page.data.id);

    const usedPage = usePage<SharedData>();
    const isAuthenticated = computed(() => !!usedPage.props.auth?.user);
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <!-- HEADER -->
        <header v-scroll-reveal class="pt-10 sm:pt-14">
            <h1 class="mb-5 text-xl font-black text-rancho-forest sm:mb-6 sm:text-2xl">
                {{ page.data.title }}
            </h1>
        </header>

        <!-- MAIN -->
        <section v-scroll-reveal class="mx-auto mt-10 grid max-w-5xl gap-6 md:grid-cols-2">
            <!-- LEFT -->
            <div
                class="shadow-sm space-y-5 rounded-2xl border border-rancho-ink-200 bg-white p-5 sm:p-6"
            >
                <!-- ADDRESS INPUT -->
                <div>
                    <label
                        class="block text-xs font-black uppercase tracking-widest text-rancho-ink-600"
                    >
                        Адрес доставки
                    </label>

                    <div class="relative mt-2">
                        <MapPin
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-rancho-ink-500"
                            aria-hidden="true"
                        />

                        <input
                            v-model="address"
                            @input="onAddressInput"
                            class="w-full rounded-xl border border-rancho-ink-300 bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-rancho-forest transition-all placeholder:text-rancho-ink-500 focus:border-rancho-pine focus:outline-none focus:ring-4 focus:ring-rancho-pine/10"
                            placeholder="Введите адрес..."
                        />
                    </div>

                    <!-- SUGGESTIONS -->
                    <ul
                        v-if="suggestions.length"
                        class="shadow-lg mt-2 overflow-hidden rounded-xl border border-rancho-ink-200 bg-white"
                    >
                        <li
                            v-for="(item, i) in suggestions"
                            :key="i"
                            @click="select(item)"
                            class="cursor-pointer border-b border-rancho-ink-100 px-4 py-2.5 text-sm text-rancho-ink-700 transition-colors last:border-b-0 hover:bg-rancho-ink-50 hover:text-rancho-forest"
                        >
                            {{ item.name }}
                        </li>
                    </ul>
                </div>

                <!-- LOCATE ME -->
                <button
                    type="button"
                    @click="useMyLocation"
                    :disabled="isLocating"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-rancho-ink-300 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-widest text-rancho-forest transition-all hover:border-rancho-forest hover:bg-rancho-ink-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <MapPinned class="h-4 w-4" aria-hidden="true" />
                    {{ isLocating ? 'Определяем...' : 'Моё местоположение' }}
                </button>

                <!-- CONFIRM -->
                <button
                    type="button"
                    @click="confirmDeliveryPoint"
                    :disabled="!selectedPoint || isConfirmed || !isDeliveryAllowed"
                    class="shadow-md hover:shadow-lg disabled:shadow-none flex w-full items-center justify-center gap-2 rounded-xl bg-rancho-pine px-4 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 disabled:cursor-not-allowed disabled:bg-rancho-ink-200 disabled:text-rancho-ink-500 disabled:hover:bg-rancho-ink-200"
                >
                    <Check v-if="isConfirmed" class="h-4 w-4" aria-hidden="true" />
                    {{ isConfirmed ? 'Сохранено' : 'Подтвердить' }}
                </button>

                <!-- DELIVERY INFO -->
                <div
                    class="space-y-2 border-t border-rancho-ink-100 pt-4 text-sm text-rancho-ink-600"
                >
                    <p v-if="delivery.delivery_info">{{ delivery.delivery_info }}</p>
                    <p v-if="delivery.delivery_schedule">🕒 {{ delivery.delivery_schedule }}</p>
                </div>

                <!-- STATUS MESSAGES -->
                <div
                    v-if="!isDeliveryAllowed"
                    class="rounded-xl bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-600 ring-1 ring-inset ring-rose-500/20"
                    role="alert"
                >
                    Вы вне зоны доставки
                </div>

                <!-- SUMMARY -->
                <dl class="space-y-2 text-sm">
                    <div v-if="delivery?.address_farm" class="flex justify-between gap-3">
                        <dt class="text-rancho-ink-500">Самовывоз:</dt>
                        <dd class="text-right font-bold text-rancho-forest">
                            {{ delivery.address_farm }}
                        </dd>
                    </div>

                    <div v-if="serverDraft?.address" class="flex justify-between gap-3">
                        <dt class="text-rancho-ink-500">Вы выбрали:</dt>
                        <dd class="text-right font-bold text-rancho-forest">
                            {{ serverDraft.address }}
                        </dd>
                    </div>

                    <div v-if="price" class="flex justify-between gap-3">
                        <dt class="text-rancho-ink-500">Цена доставки:</dt>
                        <dd class="text-right font-black text-rancho-forest">
                            {{ formatMoney(price) }}
                        </dd>
                    </div>

                    <div v-if="distance" class="flex justify-between gap-3">
                        <dt class="text-rancho-ink-500">Расстояние до нас:</dt>
                        <dd class="text-right font-bold text-rancho-forest">
                            ~{{ formatDistance(distance) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- MAP -->
            <div
                class="shadow-sm relative h-full min-h-[400px] overflow-hidden rounded-2xl border border-rancho-ink-200"
            >
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

                <!-- Легенда -->
                <DeliveryMapLegend position="bottom-left" />
            </div>
        </section>

        <!-- MAIN CONTENT -->
        <section
            v-if="page.data.content"
            v-scroll-reveal
            class="prose prose-stone max-w-none prose-headings:font-black prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-ink-700 prose-a:text-rancho-pine prose-a:no-underline hover:prose-a:underline prose-blockquote:border-l-4 prose-blockquote:border-rancho-buttercup prose-blockquote:bg-rancho-ink-50 prose-blockquote:py-1 prose-blockquote:pl-4 prose-blockquote:not-italic prose-blockquote:text-rancho-ink-700 prose-strong:text-rancho-forest prose-code:rounded prose-code:bg-rancho-ink-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-rancho-forest prose-code:before:content-none prose-code:after:content-none"
            aria-label="Описание фермы"
        >
            <div v-html="page.data.content"></div>
        </section>

        <!-- COMMENTS -->
        <div v-if="page.data.is_commentable" v-scroll-reveal class="mx-auto max-w-5xl">
            <CommentsSection
                :comments="comments"
                title="Отзывы о доставке"
                @submit="submitComment"
                :is-authenticated="isAuthenticated"
            />
        </div>
    </AppContainer>
</template>
