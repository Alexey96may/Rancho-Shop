<script setup lang="ts">
    import { computed } from 'vue';

    import { SpeakerWaveIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    interface MediaFormFields {
        voice: File | string | null;
        errors: Record<string, string | undefined>;
        [key: string]: unknown;
    }

    const props = withDefaults(
        defineProps<{
            modelValue: MediaFormFields;
            existingAudioUrl?: string | null;
            label?: string;
            id?: string;
        }>(),
        {
            existingAudioUrl: null,
            label: 'Аудиозапись',
            id: 'audio-upload',
        },
    );

    const emit = defineEmits<{
        (e: 'change', event: Event): void;
        (e: 'clear'): void;
    }>();

    const isNewAudioSelected = computed(() => {
        return props.modelValue.voice instanceof File;
    });

    const errorId = computed(() => `${props.id}-error`);

    const fileName = computed(() => {
        if (typeof window !== 'object') return '';

        let pathname = '';
        if (isNewAudioSelected.value && props.modelValue.voice instanceof File) {
            pathname = props.modelValue.voice.name;
        } else {
            if (!props.existingAudioUrl) return '';
            pathname = new URL(props.existingAudioUrl).pathname;
        }

        return pathname.substring(pathname.lastIndexOf('/') + 1);
    });

    const audioSrc = computed(() => {
        if (typeof window !== 'object') return '';

        if (props.modelValue.voice instanceof File) {
            return URL.createObjectURL(props.modelValue.voice);
        }

        return props.existingAudioUrl || undefined;
    });
</script>

<template>
    <div class="space-y-4">
        <label
            :for="id"
            class="text-rancho-ink-400 ml-2 text-[10px] font-black uppercase tracking-[0.15em]"
        >
            {{ label }}
        </label>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <div
                    class="border-rancho-ink-800 bg-rancho-ink-950/50 focus-within:border-rancho-mint/40 flex items-center gap-4 rounded-3xl border p-6 transition-colors"
                >
                    <div
                        class="bg-rancho-mint/70 flex h-12 w-12 items-center justify-center rounded-2xl text-rancho-paper"
                        aria-hidden="true"
                    >
                        <SpeakerWaveIcon class="h-6 w-6" />
                    </div>

                    <div class="flex-1">
                        <input
                            :id="id"
                            type="file"
                            accept="audio/*"
                            @change="emit('change', $event)"
                            class="sr-only"
                        />

                        <label
                            :for="id"
                            class="bg-rancho-ink-800 text-rancho-mint hover:bg-rancho-ink-700 inline-flex cursor-pointer items-center rounded-xl px-4 py-2 text-[10px] font-black uppercase transition"
                        >
                            <span>Загрузить аудио</span>
                        </label>

                        <Transition name="fade-slide" mode="out-in">
                            <span
                                v-if="isNewAudioSelected && fileName"
                                :key="fileName"
                                class="text-rancho-ink-300 ml-2 truncate text-xs"
                            >
                                {{ fileName }}
                            </span>

                            <span
                                v-else
                                :key="'placeholder'"
                                class="text-rancho-ink-500 ml-2 text-xs"
                            >
                                Выберите файл
                            </span>
                        </Transition>
                    </div>

                    <Transition name="fade">
                        <button
                            v-if="isNewAudioSelected"
                            type="button"
                            class="text-rancho-ink-500 transition-colors hover:text-rose-500 focus:text-rose-500 focus:outline-none"
                            aria-label="Удалить выбранный аудиофайл"
                            title="Удалить выбранный аудиофайл"
                            @click="emit('clear')"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </Transition>
                </div>

                <Transition name="fade">
                    <p
                        v-if="modelValue.errors.voice"
                        :id="errorId"
                        role="alert"
                        class="ml-2 text-[10px] font-bold uppercase text-rose-500"
                    >
                        {{ modelValue.errors.voice }}
                    </p>
                </Transition>
            </div>

            <div class="relative">
                <Transition name="fade-layout" mode="out-in">
                    <figure
                        v-if="fileName"
                        :key="'player'"
                        class="border-rancho-mint/20 bg-rancho-mint/5 flex flex-col justify-center rounded-3xl border p-6"
                    >
                        <figcaption
                            class="text-rancho-mint/60 mb-2 text-[9px] font-black uppercase tracking-tighter"
                        >
                            Текущая запись:
                            <span v-if="fileName" class="text-rancho-mint">{{ fileName }}</span>
                        </figcaption>
                        <audio
                            v-if="audioSrc"
                            :src="audioSrc"
                            controls
                            class="accent-rancho-mint h-8 w-full"
                            aria-label="Воспроизвести текущую аудиозапись"
                        >
                            Ваш браузер не поддерживает элемент <code>audio</code>.
                        </audio>
                    </figure>
                </Transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.2s ease;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateX(6px);
    }
    .fade-slide-leave-to {
        opacity: 0;
        transform: translateX(-6px);
    }

    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }

    .fade-layout-enter-active,
    .fade-layout-leave-active {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .fade-layout-enter-from {
        opacity: 0;
        transform: scale(0.98);
    }
    .fade-layout-leave-to {
        opacity: 0;
        transform: scale(0.98);
    }
</style>
