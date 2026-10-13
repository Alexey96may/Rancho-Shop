<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { Volume2, VolumeX } from 'lucide-vue-next';

    import type { Animal } from '@/types/Animal';

    const props = defineProps<{ animal: Animal }>();

    const isPlaying = ref(false);
    const audioRef = ref<HTMLAudioElement | null>(null);

    /** Сколько фич максимум показываем в карточке */
    const MAX_FEATURES = 3;

    const visibleFeatures = computed<Array<[string, unknown]>>(() => {
        if (!props.animal.features) return [];
        return Object.entries(props.animal.features).slice(0, MAX_FEATURES);
    });

    const hiddenFeaturesCount = computed(() => {
        if (!props.animal.features) return 0;
        return Math.max(0, Object.keys(props.animal.features).length - MAX_FEATURES);
    });

    const toggleVoice = () => {
        if (!audioRef.value) return;
        isPlaying.value ? audioRef.value.pause() : audioRef.value.play();
        isPlaying.value = !isPlaying.value;
    };
</script>

<template>
    <article
        class="border-rancho-ink-200 hover:border-rancho-ink-300 hover:shadow-xl group relative flex flex-col overflow-hidden rounded-3xl border bg-white transition-all focus-within:ring-2 focus-within:ring-rancho-pine/40 hover:-translate-y-0.5"
        :aria-labelledby="`animal-name-${animal.id}`"
    >
        <div class="bg-rancho-ink-50 relative aspect-[4/5] max-h-48 overflow-hidden sm:max-h-64">
            <AppImage
                :src="props.animal?.avatars[0] || ''"
                :alt="`Фотография нашего жителя по имени ${animal.name}`"
                :context="'animal'"
                :className="'h-full w-full object-cover transition-transform duration-700 group-hover:scale-105'"
            />

            <div class="absolute left-4 top-4">
                <span
                    class="bg-rancho-ink-50/90 shadow-sm ring-rancho-ink-200 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-rancho-forest ring-1 ring-inset backdrop-blur-md"
                    role="status"
                >
                    {{ animal.status }}
                </span>
            </div>

            <button
                v-if="animal.voice_url"
                @click.stop="toggleVoice"
                type="button"
                :aria-pressed="isPlaying"
                :aria-label="
                    isPlaying
                        ? `Остановить голос ${animal.name}`
                        : `Включить звук животного ${animal.name}`
                "
                class="shadow-lg hover:text-rancho-ink-950 absolute bottom-4 right-4 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-rancho-forest transition-all hover:bg-rancho-buttercup focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine"
            >
                <component :is="isPlaying ? VolumeX : Volume2" :size="24" aria-hidden="true" />
                <span class="sr-only" aria-live="polite">
                    {{ isPlaying ? 'Воспроизводится звук' : 'Звук остановлен' }}
                </span>
                <audio
                    ref="audioRef"
                    :src="animal.voice_url"
                    @ended="isPlaying = false"
                    class="hidden"
                ></audio>
            </button>
        </div>

        <div class="flex flex-1 flex-col gap-3 p-4 sm:p-6">
            <h3
                :id="`animal-name-${animal.id}`"
                class="text-lg font-bold text-rancho-forest transition-colors group-hover:text-rancho-pine sm:text-xl"
            >
                <Link :href="route('animals.show', animal.slug)" class="focus:outline-none">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    {{ animal.name }}
                </Link>
            </h3>

            <p
                v-if="animal.bio"
                class="text-rancho-ink-600 line-clamp-3 text-xs leading-relaxed sm:text-sm"
            >
                {{ animal.bio }}
            </p>

            <dl
                v-if="visibleFeatures.length"
                class="border-rancho-ink-100 mt-auto flex flex-col gap-2 border-t pt-4"
            >
                <div
                    v-for="[key, value] in visibleFeatures"
                    :key="key"
                    class="bg-rancho-ink-50 text-rancho-ink-600 line-clamp-1 flex items-center gap-1 rounded-lg px-3 py-2 text-[10px] font-semibold uppercase tracking-tighter"
                >
                    <dt>{{ key }}:</dt>
                    <dd>{{ value }}</dd>
                </div>
            </dl>
        </div>
    </article>
</template>
