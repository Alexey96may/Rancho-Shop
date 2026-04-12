import { ref } from 'vue';

interface Suggestion {
    name: string;
    description: string;
    point: [number, number]; // [lng, lat]
}

export function useYandexGeocoder() {
    const suggestions = ref<Suggestion[]>([]);
    const loading = ref(false);

    let timeout: number | null = null;

    const search = (query: string) => {
        if (!query || query.length < 3) {
            suggestions.value = [];
            return;
        }

        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(async () => {
            loading.value = true;

            try {
                const key = import.meta.env.VITE_YANDEX_GEOCODER_KEY;

                console.log('YANDEX KEY:', key);

                const res = await fetch(
                    `https://geocode-maps.yandex.ru/1.x/?format=json&apikey=${key}` +
                        `&geocode=${encodeURIComponent(query)}` +
                        `&lang=ru_RU` +
                        `&results=5` +
                        `&bbox=34.00,44.80~34.30,45.05` +
                        `&ll=34.1024,44.9521` +
                        `&spn=0.5,0.5`,
                );

                const data = await res.json();

                if (!data?.response?.GeoObjectCollection) {
                    console.error('Yandex response error:', data);
                    suggestions.value = [];
                    return;
                }

                const items = data.response.GeoObjectCollection.featureMember || [];

                suggestions.value = items.map((item: any) => {
                    const geo = item.GeoObject;

                    const coords = geo.Point.pos.split(' ').map(Number);

                    return {
                        name: geo.name,
                        description: geo.description,
                        point: coords, // [lng, lat]
                    };
                });
            } catch (e) {
                console.error('Yandex geocoder error', e);
            } finally {
                loading.value = false;
            }
        }, 300);
    };

    return {
        suggestions,
        loading,
        search,
    };
}
