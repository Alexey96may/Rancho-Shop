<script setup lang="ts">
    import { computed, ref } from 'vue';

    interface Props {
        settings: Record<string, any>;
        delivery: {
            farm_coords: string;
            delivery_cost: number;
            free_delivery_from: number;
            address_farm: string;
        };
    }

    const props = defineProps<Props>();

    // 📍 user input state
    const address = ref<string>('');
    const isInsideDeliveryArea = ref<boolean | null>(null);

    // 🧠 parsed farm coords (temporary string -> later Mapbox/geo object)
    const farmCoords = computed(() => {
        if (!props.delivery.farm_coords) return null;
        const [lat, lng] = props.delivery.farm_coords.split(',').map(Number);

        return { lat, lng };
    });

    // 💰 simple delivery info
    const deliveryCost = computed(() => props.delivery.delivery_cost);
    const freeFrom = computed(() => props.delivery.free_delivery_from);
</script>

<template>
    <main
        class="min-h-screen bg-[#FCFAF5] p-6 text-[#1C3F34]"
        role="main"
        aria-labelledby="delivery-title"
    >
        <!-- HEADER -->
        <header class="mx-auto mb-8 max-w-3xl">
            <h1 id="delivery-title" class="text-3xl font-bold text-[#1C3F34]">Доставка</h1>

            <p class="mt-2 text-[#597D5B]" aria-describedby="delivery-title">
                Укажите адрес доставки или выберите точку на карте
            </p>
        </header>

        <!-- MAIN GRID -->
        <section
            class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-2"
            aria-label="Delivery information section"
        >
            <!-- LEFT: FORM -->
            <div
                class="shadow-sm rounded-xl border border-[#EDE7DB] bg-white p-6"
                role="form"
                aria-label="Delivery address form"
            >
                <label for="address" class="block text-sm font-medium text-[#1C3F34]">
                    Адрес доставки
                </label>

                <input
                    id="address"
                    v-model="address"
                    type="text"
                    autocomplete="street-address"
                    placeholder="Введите адрес..."
                    class="mt-2 w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E3B44B]"
                    aria-label="Delivery address input"
                />

                <!-- DELIVERY STATUS -->
                <div class="mt-6" role="status" aria-live="polite">
                    <p v-if="isInsideDeliveryArea === true" class="font-medium text-green-700">
                        ✔ Доставка доступна
                    </p>

                    <p v-else-if="isInsideDeliveryArea === false" class="font-medium text-red-600">
                        ✖ Только самовывоз
                    </p>

                    <p v-else class="text-[#597D5B]">Введите адрес для проверки доставки</p>
                </div>

                <!-- DELIVERY INFO -->
                <div class="mt-6 text-sm text-[#597D5B]" aria-label="Delivery pricing info">
                    <p>
                        Стоимость доставки: <strong>{{ deliveryCost }} ₽</strong>
                    </p>

                    <p>
                        Бесплатно от: <strong>{{ freeFrom }} ₽</strong>
                    </p>
                </div>
            </div>

            <!-- RIGHT: MAP PLACEHOLDER -->
            <div
                class="shadow-sm rounded-xl border border-[#EDE7DB] bg-white p-6"
                aria-label="Map section"
            >
                <div
                    class="flex h-[400px] items-center justify-center text-[#597D5B]"
                    role="img"
                    aria-label="Delivery map placeholder"
                >
                    🗺️ Mapbox map будет здесь
                </div>

                <p class="mt-3 text-xs text-[#597D5B]">
                    Вы можете выбрать точку на карте внутри зоны доставки
                </p>
            </div>
        </section>

        <!-- PICKUP INFO -->
        <section
            class="mx-auto mt-10 max-w-3xl rounded-lg bg-[#F7F3EA] p-5"
            aria-label="Pickup information"
        >
            <h2 class="text-lg font-semibold text-[#1C3F34]">Самовывоз</h2>

            <p class="mt-2 text-sm text-[#597D5B]">Адрес фермы: {{ delivery.address_farm }}</p>

            <p v-if="farmCoords" class="mt-1 text-xs text-[#597D5B]">
                Координаты: {{ farmCoords.lat }}, {{ farmCoords.lng }}
            </p>
        </section>
    </main>
</template>
