<script setup lang="ts">
    import { computed } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import { ArrowPathIcon, PencilSquareIcon, SpeakerWaveIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import type { AdminAnimal, SharedData } from '@/types';

    defineProps<{
        animal: AdminAnimal;
        disabled: boolean;
    }>();

    defineEmits<{
        (e: 'edit', animal: AdminAnimal): void;
        (e: 'delete', id: number): void;
        (e: 'restore', id: number, name: string): void;
    }>();

    const playVoice = (url: string) => {
        const audio = new Audio(url);
        audio.play().catch((error) => {
            if (console) {
                console.error('Ошибка воспроизведения:', error);
            }
        });
    };

    const page = usePage<SharedData>();
    const can = computed(() => page.props.can ?? {});
</script>

<template>
    <div
        role="listitem"
        class="border-rancho-ink-800 bg-rancho-ink-900/30 hover:border-rancho-mint/40 hover:bg-rancho-ink-900/60 group relative flex flex-col overflow-hidden rounded-3xl border p-5 transition-all"
        :class="{ 'border-rose-500/20 bg-rose-950/5 opacity-75': animal.is_trashed }"
    >
        <!-- IMAGE -->
        <div class="bg-rancho-ink-800 relative h-48 w-full overflow-hidden rounded-2xl">
            <AppImage
                :src="animal?.avatars?.[0] || ''"
                :type="'thumbnails'"
                :alt="animal.name"
                :context="'animal'"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                :class="{ 'contrast-125 grayscale': animal.is_trashed }"
            />

            <!-- Бейдж статуса -->
            <div class="absolute right-3 top-3 flex gap-2">
                <span
                    class="bg-rancho-ink-950/70 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white backdrop-blur-md"
                    :class="
                        animal.is_trashed
                            ? 'border border-rose-500/40 text-rose-300'
                            : 'border-rancho-ink-700 border'
                    "
                >
                    {{ animal.is_trashed ? 'В КОРЗИНЕ' : animal.status }}
                </span>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="mt-8 flex-1 px-2">
            <div class="mb-2 flex items-center justify-between gap-3">
                <span
                    class="text-rancho-mint truncate text-[10px] font-black uppercase tracking-[0.2em]"
                >
                    {{ animal.category?.name || 'Без категории' }}
                </span>

                <!-- Индикатор статуса -->
                <div
                    class="h-2 w-2 shrink-0 rounded-full"
                    :class="
                        animal.is_trashed
                            ? 'bg-rose-700 shadow-[0_0_8px_#b91c1c]'
                            : animal.is_active
                              ? 'bg-rancho-mint shadow-[0_0_8px_#7AB89A]'
                              : 'bg-rose-500'
                    "
                    :aria-label="
                        animal.is_trashed ? 'В корзине' : animal.is_active ? 'Активен' : 'В архиве'
                    "
                    role="status"
                ></div>
            </div>

            <h4 class="mt-2 line-clamp-1 text-xl font-black tracking-tight text-white">
                {{ animal.name }}
            </h4>

            <!-- Родитель -->
            <div v-if="animal.family?.parent" class="mt-2 flex items-center gap-1.5 text-xs">
                <span class="text-rancho-ink-500 font-bold tracking-tighter"> Родитель: </span>
                <span class="text-rancho-ink-300 truncate">
                    {{ animal.family?.parent?.name }}
                </span>
            </div>
        </div>

        <!-- FOOTER -->
        <div
            class="border-rancho-ink-800/70 mt-6 flex items-center justify-between border-t px-2 pt-4"
        >
            <div class="flex gap-2">
                <button
                    v-if="animal.voice_url && !animal.is_trashed"
                    type="button"
                    @click="playVoice(animal.voice_url)"
                    class="bg-rancho-ink-800 text-rancho-ink-400 hover:bg-rancho-ink-700 hover:text-rancho-mint focus-visible:ring-rancho-mint/40 flex h-9 w-9 items-center justify-center rounded-xl transition-colors focus:outline-none focus-visible:ring-2"
                    title="Прослушать голос"
                    aria-label="Прослушать голос животного"
                >
                    <SpeakerWaveIcon class="h-4 w-4 sm:h-5 sm:w-5" aria-hidden="true" />
                </button>
            </div>

            <div
                class="flex gap-2 transition-opacity duration-300 lg:opacity-0 lg:group-hover:opacity-100"
            >
                <AdminEditButton
                    v-if="animal.is_trashed && can.restore"
                    @click="$emit('restore', animal.id, animal.name)"
                    :title="'Восстановить ' + animal.name"
                    :disabled="disabled"
                    :icon="ArrowPathIcon"
                />

                <AdminEditButton
                    v-else-if="!animal.is_trashed"
                    @click="$emit('edit', animal)"
                    :title="'Редактировать ' + animal.name"
                    :disabled="disabled"
                    :icon="PencilSquareIcon"
                />

                <AdminDeleteButton
                    v-if="!animal.is_trashed || can.forceDelete"
                    @click="$emit('delete', animal.id)"
                    :title="animal.is_trashed ? 'Удалить окончательно' : 'Удалить в архив'"
                    :disabled="disabled"
                />
            </div>
        </div>
    </div>
</template>
