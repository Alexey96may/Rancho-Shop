<script setup lang="ts">
    import { onMounted, ref, watch } from 'vue';

    import * as turf from '@turf/turf';
    import type { Feature, LineString, Polygon } from 'geojson';
    import mapboxgl from 'mapbox-gl';

    interface Props {
        modelValue?: { lat: number; lng: number } | null;
        farmCoords?: { lat: number; lng: number } | null;
    }

    const props = defineProps<Props>();
    const emit = defineEmits(['update:modelValue']);

    const mapContainer = ref<HTMLDivElement | null>(null);

    let map: mapboxgl.Map;
    let marker: mapboxgl.Marker;

    let isInternalUpdate = false;

    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN;

    // 🧭 START
    const farm = props.farmCoords || {
        lat: 44.8445,
        lng: 34.3381,
    };

    let routeTimeout: any = null;

    function scheduleRouteUpdate() {
        if (routeTimeout) clearTimeout(routeTimeout);

        routeTimeout = setTimeout(() => {
            loadRoute();
        }, 300);
    }

    // 🚚 WAYPOINTS
    const waypoints: [number, number][] = [
        [34.32541, 44.83384],
        [34.12673, 44.94398],
        [34.11062, 44.95797],
        [34.09723, 44.97342],
        [34.07753, 44.9583],
        [34.07865, 44.95596],
        [34.09397, 44.94407],
        [34.10249, 44.95186],
        [34.11062, 44.95797],
        [34.12673, 44.94398],
        [34.32541, 44.83384],
    ];

    //zone 500m
    function buildDeliveryBuffer(line: Feature<LineString>) {
        return turf.buffer(line, 0.5, {
            units: 'kilometers', // 0.5 km = 500m
        }) as Feature<Polygon>;
    }

    // ===============================
    // 🚚 ROUTE
    // ===============================
    async function loadRoute() {
        if (!map) return;

        const source = map.getSource('delivery-route') as mapboxgl.GeoJSONSource;
        const zoneSource = map.getSource('delivery-zone') as mapboxgl.GeoJSONSource;
        if (!source) return;

        const url =
            `https://api.mapbox.com/directions/v5/mapbox/driving/` +
            waypoints.map((p) => `${p[0]},${p[1]}`).join(';') +
            `?geometries=geojson&overview=full&access_token=${mapboxgl.accessToken}`;

        const res = await fetch(url);
        const data = await res.json();

        const route = data?.routes?.[0]?.geometry;
        if (!route) return;

        const line: Feature<LineString> = {
            type: 'Feature',
            geometry: route,
            properties: {},
        };

        source?.setData(line);

        const buffer = buildDeliveryBuffer(line);

        zoneSource?.setData(buffer);
    }

    function getUserLocation(): Promise<{ lat: number; lng: number } | null> {
        return new Promise((resolve) => {
            if (!navigator.geolocation) {
                resolve(null);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    resolve({
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                    });
                },
                () => resolve(null),
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                },
            );
        });
    }

    // ===============================
    // INIT
    // ===============================
    onMounted(async () => {
        let center = props.modelValue || farm;

        if (!props.modelValue) {
            const userLoc = await getUserLocation();

            if (userLoc) {
                center = userLoc;

                waypoints[0] = [userLoc.lng, userLoc.lat];
            }
        }

        emit('update:modelValue', center);

        map = new mapboxgl.Map({
            container: mapContainer.value!,
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [center.lng, center.lat],
            zoom: 11,
        });

        marker = new mapboxgl.Marker({ draggable: true })
            .setLngLat([center.lng, center.lat])
            .addTo(map);

        // ===============================
        // INIT SOURCES
        // ===============================
        map.on('load', async () => {
            map.addSource('delivery-route', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: [],
                    },
                    properties: {},
                },
            });

            map.addLayer({
                id: 'delivery-route-line',
                type: 'line',
                source: 'delivery-route',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round',
                },
                paint: {
                    'line-color': '#994000',
                    'line-width': 4,
                },
            });

            map.addSource('delivery-zone', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'Polygon',
                        coordinates: [],
                    },
                    properties: {},
                },
            });

            map.addLayer({
                id: 'delivery-zone-fill',
                type: 'fill',
                source: 'delivery-zone',
                paint: {
                    'fill-color': '#3B7558',
                    'fill-opacity': 0.25,
                },
            });

            map.addLayer({
                id: 'delivery-zone-border',
                type: 'line',
                source: 'delivery-zone',
                paint: {
                    'line-color': '#1C3F34',
                    'line-width': 2,
                },
            });

            await new Promise((r) => setTimeout(r, 50));

            loadRoute();
        });

        // ===============================
        // USER INTERACTION
        // ===============================
        marker.on('dragend', () => {
            const lngLat = marker.getLngLat();

            isInternalUpdate = true;

            const val = {
                lat: lngLat.lat,
                lng: lngLat.lng,
            };

            emit('update:modelValue', val);

            waypoints[0] = [lngLat.lng, lngLat.lat];
            scheduleRouteUpdate();
            loadRoute();

            map.setCenter([lngLat.lng, lngLat.lat]);

            setTimeout(() => (isInternalUpdate = false), 0);
        });

        map.on('click', (e) => {
            isInternalUpdate = true;

            const val = {
                lat: e.lngLat.lat,
                lng: e.lngLat.lng,
            };

            marker.setLngLat(e.lngLat);

            emit('update:modelValue', val);

            waypoints[0] = [e.lngLat.lng, e.lngLat.lat];
            loadRoute();

            map.setCenter([e.lngLat.lng, e.lngLat.lat]);

            setTimeout(() => (isInternalUpdate = false), 0);
        });
    });

    // ===============================
    // 🔁 WATCH (FIX MARKER JUMP)
    // ===============================
    watch(
        () => props.modelValue,
        (val) => {
            if (!val || !marker || !map) return;
            if (isInternalUpdate) return;

            marker.setLngLat([val.lng, val.lat]);
            map.setCenter([val.lng, val.lat]);
        },
    );

    function isInsideDeliveryZone(point: [number, number], zone: Feature<Polygon>) {
        return turf.booleanPointInPolygon(turf.point(point), zone);
    }
</script>

<template>
    <div
        ref="mapContainer"
        class="h-full w-full rounded-xl"
        aria-label="Маршрут доставки по Симферополю"
    />
</template>
