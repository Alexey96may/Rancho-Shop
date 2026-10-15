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

    const visibleFeatures = computed(() => {
        return (props.animal.features ?? []).slice(0, MAX_FEATURES);
    });

    const toggleVoice = () => {
        if (!audioRef.value) return;
        isPlaying.value ? audioRef.value.pause() : audioRef.value.play();
        isPlaying.value = !isPlaying.value;
    };
</script>

<template>
    <article
        class="hover:shadow-xl group relative flex flex-col overflow-hidden rounded-3xl border border-rancho-ink-200 bg-white transition-all focus-within:ring-2 focus-within:ring-rancho-pine/40 hover:-translate-y-0.5 hover:border-rancho-ink-300"
        :aria-labelledby="`animal-name-${animal.id}`"
    >
        <div class="relative aspect-[4/5] max-h-48 overflow-hidden bg-rancho-ink-50 sm:max-h-64">
            <AppImage
                :src="props.animal?.avatars[0] || ''"
                :alt="`Фотография нашего жителя по имени ${animal.name}`"
                :context="'animal'"
                :className="'h-full w-full object-cover transition-transform duration-700 group-hover:scale-105'"
            />

            <div class="absolute left-4 top-4">
                <span
                    class="shadow-sm inline-flex items-center gap-1.5 rounded-full bg-rancho-ink-50/90 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-rancho-forest ring-1 ring-inset ring-rancho-ink-200 backdrop-blur-md"
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
                class="shadow-lg absolute bottom-4 right-4 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-rancho-forest transition-all hover:bg-rancho-buttercup hover:text-rancho-ink-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine"
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
                class="line-clamp-3 text-xs leading-relaxed text-rancho-ink-600 sm:text-sm"
            >
                {{ animal.bio }}
            </p>

            <dl
                v-if="visibleFeatures.length"
                class="mt-auto flex flex-col gap-2 border-t border-rancho-ink-100 pt-4"
            >
                <div
                    v-for="(feature, i) in visibleFeatures"
                    :key="i"
                    class="line-clamp-1 flex items-center gap-1 rounded-lg bg-rancho-ink-50 px-3 py-2 text-[10px] font-semibold uppercase tracking-tighter text-rancho-ink-600"
                >
                    <dt>{{ feature.label }}:</dt>
                    <dd>{{ feature.value }}</dd>
                </div>
            </dl>
        </div>
    </article>
</template>
