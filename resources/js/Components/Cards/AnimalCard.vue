<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { Heart, Home, Moon, Trees, Volume2, VolumeX } from 'lucide-vue-next';

    import type { FarmAnimal } from '@/types/Animal';

    const props = defineProps<{ animal: FarmAnimal }>();

    const isPlaying = ref(false);
    const audioRef = ref<HTMLAudioElement | null>(null);

    const statusConfig = computed(() => {
        const configs: Record<string, { label: string; icon: any; color: string }> = {
            pasture: { label: 'На пастбище', icon: Trees, color: 'text-emerald-700 bg-emerald-50' },
            home: { label: 'В домике', icon: Home, color: 'text-amber-700 bg-amber-50' },
            sleeping: { label: 'Спит', icon: Moon, color: 'text-indigo-700 bg-indigo-50' },
            hunting: { label: 'Охотится', icon: Heart, color: 'text-rose-700 bg-rose-50' },
            default: {
                label: props.animal.status,
                icon: Heart,
                color: 'text-rancho-olive bg-rancho-paper/50',
            },
        };
        return configs[props.animal.status] || configs.default;
    });

    const toggleVoice = () => {
        if (!audioRef.value) return;
        isPlaying.value ? audioRef.value.pause() : audioRef.value.play();
        isPlaying.value = !isPlaying.value;
    };
</script>

<template>
    <article
        class="border-rancho-paper focus-within:ring-rancho-buttercup group relative flex flex-col overflow-hidden rounded-3xl border bg-white transition-all focus-within:ring-2 hover:shadow-2xl"
        :aria-labelledby="`animal-name-${animal.id}`"
    >
        <div class="bg-rancho-paper/20 relative aspect-[4/5] overflow-hidden">
            <img
                :src="animal.avatar_url || '/images/placeholder-animal.jpg'"
                :alt="`Фотография нашего жителя по имени ${animal.name}`"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />

            <div class="absolute left-4 top-4">
                <span
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold shadow-sm backdrop-blur-sm',
                        statusConfig.color,
                    ]"
                    role="status"
                >
                    <component :is="statusConfig.icon" :size="14" aria-hidden="true" />
                    {{ statusConfig.label }}
                </span>
            </div>

            <button
                v-if="animal.voice_url"
                @click.stop="toggleVoice"
                type="button"
                :aria-pressed="isPlaying"
                :aria-label="
                    isPlaying ? `Остановить голос ${animal.name}` : `Послушать голос ${animal.name}`
                "
                class="text-rancho-forest hover:bg-rancho-buttercup absolute bottom-4 right-4 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-white/95 shadow-lg transition-all focus:outline-none"
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

        <div class="flex flex-1 flex-col p-6">
            <h3 :id="`animal-name-${animal.id}`" class="text-rancho-forest text-2xl font-bold">
                <Link :href="route('home')" class="focus:outline-none">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    {{ animal.name }}
                </Link>
            </h3>

            <p
                v-if="animal.bio"
                class="text-rancho-olive/80 mt-3 line-clamp-3 text-sm leading-relaxed"
            >
                {{ animal.bio }}
            </p>

            <dl
                v-if="animal.features"
                class="border-rancho-paper/50 mt-auto flex flex-wrap gap-2 border-t pt-4"
            >
                <div
                    v-for="(value, key) in animal.features"
                    :key="key"
                    class="bg-rancho-paper/40 text-rancho-olive flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-semibold uppercase tracking-tighter"
                >
                    <dt>{{ key }}:</dt>
                    <dd>{{ value }}</dd>
                </div>
            </dl>
        </div>
    </article>
</template>
