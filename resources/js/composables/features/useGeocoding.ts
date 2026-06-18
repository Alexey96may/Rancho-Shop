import { ref } from 'vue';

interface Suggestion {
    place_name: string;
    center: [number, number];
}

export function useGeocoding() {
    const suggestions = ref<Suggestion[]>([]);
    const loading = ref(false);

    let timeout: number | null = null;

    const search = async (query: string) => {
        if (!query || query.length < 3) {
            suggestions.value = [];
            return;
        }

        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(async () => {
            loading.value = true;

            try {
                const token = import.meta.env.VITE_MAPBOX_TOKEN;

                const res = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json` +
                        `?access_token=${token}` +
                        `&limit=7` +
                        `&language=ru` +
                        `&proximity=34.1024,44.9521`,
                );

                const data = await res.json();

                suggestions.value = data.features.map((f: any) => ({
                    place_name: f.place_name,
                    center: f.center,
                }));
            } catch (e) {
                console.error('Geocoding error', e);
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
