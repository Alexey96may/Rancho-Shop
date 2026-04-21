<script setup lang="ts">
    import { PencilSquareIcon, SpeakerWaveIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import AppImage from '@/Components/UI/AppImage.vue';
    import { AdminAnimal } from '@/types';

    defineProps<{
        animal: AdminAnimal;
    }>();

    defineEmits(['edit', 'delete']);

    const playVoice = (url: string) => {
        const audio = new Audio(url);
        audio.play().catch((error) => {
            console.error('Ошибка воспроизведения:', error);
        });
    };
</script>

<template>
    <div
        role="listitem"
        class="group relative flex flex-col overflow-hidden rounded-[2.5rem] border border-slate-900 bg-slate-900/30 p-5 transition-all hover:border-orange-500/40 hover:bg-slate-900/60"
    >
        <div class="relative h-48 w-full overflow-hidden rounded-[2rem] bg-slate-800">
            <AppImage
                :src="animal?.avatars?.[0] || ''"
                :type="'thumbnails'"
                :alt="animal.name"
                :context="'animal'"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />

            <div class="absolute right-3 top-3 flex gap-2">
                <span
                    class="rounded-full bg-slate-950/60 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white backdrop-blur-md"
                >
                    {{ animal.status }}
                </span>
            </div>
        </div>
        <div class="mt-5 flex-1 px-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500">
                    {{ animal.category?.name || 'Без категории' }}
                </span>
                <div
                    class="h-2 w-2 rounded-full"
                    :class="
                        animal.is_active ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-red-500'
                    "
                    :aria-label="animal.is_active ? 'Активен' : 'В архиве'"
                ></div>
            </div>

            <h4 class="mt-2 line-clamp-1 text-xl font-black uppercase tracking-tight text-white">
                {{ animal.name }}
            </h4>

            <div
                v-if="animal.family?.parent"
                class="mt-2 flex items-center gap-1.5 text-xs text-slate-500"
            >
                <span class="font-bold uppercase tracking-tighter text-slate-600">Родитель:</span>
                <span class="truncate">{{ animal.family?.parent?.name }}</span>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-slate-800/50 px-2 pt-4">
            <div class="flex gap-2">
                <button
                    v-if="animal.voice_url"
                    @click="playVoice(animal.voice_url)"
                    class="rounded-xl bg-slate-800 p-2 text-slate-400 hover:text-orange-500"
                    title="Прослушать голос"
                >
                    <SpeakerWaveIcon class="h-5 w-5" />
                </button>
            </div>
            <div
                class="flex gap-1 transition-opacity duration-300 lg:opacity-0 lg:group-hover:opacity-100"
            >
                <button
                    @click="$emit('edit', animal)"
                    class="rounded-xl p-2 text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
                    :aria-label="'Редактировать ' + animal.name"
                >
                    <PencilSquareIcon class="h-5 w-5" />
                </button>
                <button
                    @click="$emit('delete', animal.id)"
                    class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-500/10 hover:text-red-500"
                    :aria-label="'Удалить ' + animal.name"
                >
                    <TrashIcon class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</template>
