<script setup lang="ts">
    import { onMounted, ref } from 'vue';

    import mapboxgl from 'mapbox-gl';

    interface Props {
        modelValue?: {
            lat: number;
            lng: number;
        } | null;

        farmCoords?: {
            lat: number;
            lng: number;
        } | null;
    }

    const props = defineProps<Props>();
    const emit = defineEmits(['update:modelValue']);

    const mapContainer = ref<HTMLDivElement | null>(null);
    let map: mapboxgl.Map;
    let marker: mapboxgl.Marker;

    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN;

    onMounted(() => {
        const center = props.modelValue ||
            props.farmCoords || {
                lat: 55.75,
                lng: 37.61,
            };

        map = new mapboxgl.Map({
            container: mapContainer.value!,
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [center.lng, center.lat],
            zoom: 10,
        });

        marker = new mapboxgl.Marker({ draggable: true })
            .setLngLat([center.lng, center.lat])
            .addTo(map);

        // 📍 drag event
        marker.on('dragend', () => {
            const lngLat = marker.getLngLat();

            emit('update:modelValue', {
                lat: lngLat.lat,
                lng: lngLat.lng,
            });
        });

        // 🖱 click to move marker
        map.on('click', (e) => {
            marker.setLngLat(e.lngLat);

            emit('update:modelValue', {
                lat: e.lngLat.lat,
                lng: e.lngLat.lng,
            });
        });
    });
</script>

<template>
    <div
        ref="mapContainer"
        class="h-full w-full rounded-xl"
        aria-label="Интерактивная карта выбора точки доставки"
    />
</template>
