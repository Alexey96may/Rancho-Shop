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
    const emit = defineEmits(['update:modelValue', 'delivery-valid']);

    const mapContainer = ref<HTMLDivElement | null>(null);

    let map: mapboxgl.Map;
    let marker: mapboxgl.Marker;

    // Prevent circular updates between map <-> v-model
    let isInternalUpdate = false;

    // Track readiness state
    let mapReady = false;
    let sourcesReady = false;

    // Mapbox token
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN;

    // ==========================
    // INITIAL CENTER (farm fallback)
    // ==========================
    const farm = props.farmCoords || {
        lat: 44.8445,
        lng: 34.3381,
    };

    // ==========================
    // WAYPOINTS (delivery route)
    // ==========================
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

    // ==========================
    // IN-MEMORY CACHE
    // ==========================
    const routeCache = new Map<string, Feature<LineString>>();
    const zoneCache = new Map<string, Feature<Polygon>>();

    // Create cache key based on route
    function getCacheKey() {
        return waypoints.map((p) => `${p[0].toFixed(5)},${p[1].toFixed(5)}`).join(';');
    }

    // ==========================
    // BUFFER ZONE (500m)
    // ==========================
    function buildDeliveryBuffer(line: Feature<LineString>) {
        return turf.buffer(line, 0.5, {
            units: 'kilometers', // 500 meters
        }) as Feature<Polygon>;
    }

    let currentZone: Feature<Polygon> | null = null;

    // ==========================
    // GET ROUTE FROM MAPBOX
    // ==========================
    async function loadRoute() {
        if (!mapReady || !sourcesReady) return;

        const source = map.getSource('delivery-route') as mapboxgl.GeoJSONSource;
        const zoneSource = map.getSource('delivery-zone') as mapboxgl.GeoJSONSource;

        if (!source || !zoneSource) return;

        const key = getCacheKey();

        // -------- CACHE HIT --------
        const cachedRoute = routeCache.get(key);
        const cachedZone = zoneCache.get(key);

        if (cachedRoute && cachedZone) {
            source.setData(cachedRoute);
            zoneSource.setData(cachedZone);
            return;
        }

        // -------- API REQUEST --------
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

        const buffer = buildDeliveryBuffer(line);

        currentZone = buffer;

        // Save to cache
        routeCache.set(key, line);
        zoneCache.set(key, buffer);

        // Render to map
        source.setData(line);
        zoneSource.setData(buffer);
    }

    // ==========================
    // GET USER LOCATION
    // ==========================
    function getUserLocation(): Promise<{ lat: number; lng: number } | null> {
        return new Promise((resolve) => {
            if (!navigator.geolocation) return resolve(null);

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

    // ==========================
    // INIT MAP
    // ==========================
    onMounted(async () => {
        let center = props.modelValue || farm;

        // Try browser geolocation if no saved value
        if (!props.modelValue) {
            const userLoc = await getUserLocation();

            if (userLoc) {
                center = userLoc;
                waypoints[0] = [userLoc.lng, userLoc.lat];
            }
        }

        emit('update:modelValue', center);

        // Create map instance
        map = new mapboxgl.Map({
            container: mapContainer.value!,
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [center.lng, center.lat],
            zoom: 11,
        });

        // Create draggable marker
        marker = new mapboxgl.Marker({ draggable: true })
            .setLngLat([center.lng, center.lat])
            .addTo(map);

        map.on('load', async () => {
            mapReady = true;

            // --------------------------
            // ROUTE SOURCE
            // --------------------------
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

            // --------------------------
            // DELIVERY ZONE SOURCE
            // --------------------------
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

            const lngLat = marker.getLngLat();

            const allowed = checkDeliveryAvailability(lngLat.lat, lngLat.lng);
            emit('delivery-valid', allowed);

            sourcesReady = true;

            // Initial route render
            loadRoute();
        });

        // ==========================
        // MARKER EVENTS
        // ==========================
        marker.on('dragend', () => {
            const lngLat = marker.getLngLat();

            const allowed = checkDeliveryAvailability(lngLat.lat, lngLat.lng);

            isInternalUpdate = true;

            const val = {
                lat: lngLat.lat,
                lng: lngLat.lng,
            };

            emit('update:modelValue', val);
            emit('delivery-valid', allowed);

            waypoints[0] = [lngLat.lng, lngLat.lat];

            map.setCenter([lngLat.lng, lngLat.lat]);

            // loadRoute();

            setTimeout(() => (isInternalUpdate = false), 0);
        });

        map.on('click', (e) => {
            isInternalUpdate = true;

            const allowed = checkDeliveryAvailability(e.lngLat.lat, e.lngLat.lng);

            const val = {
                lat: e.lngLat.lat,
                lng: e.lngLat.lng,
            };

            marker.setLngLat(e.lngLat);

            emit('update:modelValue', val);
            emit('delivery-valid', allowed);

            waypoints[0] = [e.lngLat.lng, e.lngLat.lat];

            map.setCenter([e.lngLat.lng, e.lngLat.lat]);

            // loadRoute();

            setTimeout(() => (isInternalUpdate = false), 0);
        });
    });

    // ==========================
    // EXTERNAL MODEL SYNC
    // ==========================
    watch(
        () => props.modelValue,
        (val) => {
            if (!val || !marker || !map) return;
            if (isInternalUpdate) return;

            marker.setLngLat([val.lng, val.lat]);
            map.setCenter([val.lng, val.lat]);
        },
    );

    // ==========================
    // UTILITY (optional check)
    // ==========================

    function isInsideDeliveryZone(point: [number, number], zone: Feature<Polygon>) {
        return turf.booleanPointInPolygon(turf.point(point), zone);
    }

    function checkDeliveryAvailability(lat: number, lng: number) {
        if (!currentZone) return false;

        return isInsideDeliveryZone([lng, lat], currentZone);
    }
</script>

<template>
    <div ref="mapContainer" class="h-full w-full rounded-xl" aria-label="Delivery route map" />
</template>
