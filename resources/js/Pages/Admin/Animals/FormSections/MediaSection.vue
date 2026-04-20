<script setup lang="ts">
    import { computed, ref } from 'vue';

    import {
        MagnifyingGlassPlusIcon,
        PhotoIcon,
        SpeakerWaveIcon,
        XMarkIcon,
    } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        modelValue: any; // Форма useForm
        existingVoiceUrl?: string | null; // URL голоса из БД
    }>();

    const selectedImage = ref<string | null>(null);

    // Проверка: загружает ли пользователь новый файл прямо сейчас
    const isNewVoiceSelected = computed(() => props.modelValue.voice instanceof File);

    // Универсальная функция для отображения фото
    const getPreview = (item: any) => {
        if (!item) return '';

        // 1. Если это новый файл (объект File), который мы только что выбрали через <input>
        if (item instanceof File) {
            return URL.createObjectURL(item);
        }

        // 2. Если это объект типа Media, который пришел из базы
        // Проверяем наличие url или original_url (смотря как описан твой тип Media)
        if (typeof item === 'object') {
            return item.url || item.original_url || '';
        }

        // 3. На случай если пришла просто строка
        if (typeof item === 'string') {
            return item;
        }

        return '';
    };

    const openImage = (file: File | string) => {
        selectedImage.value = getPreview(file);
    };

    const clearNewVoice = () => {
        props.modelValue.voice = null;
    };

    const removeGalleryItem = (index: number) => {
        props.modelValue.gallery.splice(index, 1);
    };

    const MAX_POST_SIZE = 20 * 1024 * 1024; // 20 MB

    const handleFile = (e: Event, field: 'voice' | 'gallery') => {
        const target = e.target as HTMLInputElement;
        const files = target.files;
        if (!files || files.length === 0) return;

        let currentTotalSize = 0;

        // Считаем текущий вес файлов в форме
        props.modelValue.gallery.forEach((f: any) => {
            if (f instanceof File) currentTotalSize += f.size;
        });
        if (props.modelValue.voice instanceof File) {
            currentTotalSize += props.modelValue.voice.size;
        }

        const incomingFiles = Array.from(files);
        const newFilesSize = incomingFiles.reduce((acc, file) => acc + file.size, 0);

        if (currentTotalSize + newFilesSize > MAX_POST_SIZE) {
            alert('Общий размер файлов слишком велик! Лимит 20 МБ.');
            target.value = ''; // Сбрасываем инпут
            return;
        }

        if (field === 'gallery') {
            // Важно: создаем новый массив, чтобы Vue/Inertia увидели изменения
            props.modelValue.gallery = [...props.modelValue.gallery, ...incomingFiles];
        } else {
            props.modelValue.voice = incomingFiles[0];
        }

        target.value = ''; // Очищаем инпут, чтобы можно было выбрать тот же файл повторно
    };
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-12">
        <div class="space-y-4">
            <label
                for="voice-upload"
                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
            >
                Голос животного
            </label>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <div
                        class="flex items-center gap-4 rounded-3xl border border-slate-800 bg-slate-950/50 p-6 transition-colors focus-within:border-orange-500/50"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-500"
                        >
                            <SpeakerWaveIcon class="h-6 w-6" />
                        </div>
                        <div class="flex-1">
                            <input
                                id="voice-upload"
                                type="file"
                                accept="audio/*"
                                @change="handleFile($event, 'voice')"
                                class="block w-full text-xs text-slate-400 file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:text-orange-500 hover:file:bg-slate-700"
                            />
                        </div>
                        <button
                            v-if="isNewVoiceSelected"
                            @click="clearNewVoice"
                            type="button"
                            class="text-slate-500 hover:text-red-500"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                    <p
                        v-if="modelValue.errors.voice"
                        class="ml-2 animate-pulse text-[10px] font-bold uppercase text-red-500"
                    >
                        {{ modelValue.errors.voice }}
                    </p>
                </div>

                <div
                    v-if="existingVoiceUrl && !isNewVoiceSelected"
                    class="flex items-center gap-4 rounded-3xl border border-orange-500/20 bg-orange-500/5 p-6"
                >
                    <div class="flex-1">
                        <p
                            class="mb-2 text-[9px] font-black uppercase tracking-tighter text-orange-500/60"
                        >
                            Текущая запись:
                        </p>
                        <audio controls class="h-8 w-full accent-orange-500">
                            <source :src="existingVoiceUrl" type="audio/mpeg" />
                        </audio>
                    </div>
                </div>

                <div
                    v-if="isNewVoiceSelected"
                    class="flex items-center justify-center rounded-3xl border border-dashed border-green-500/30 bg-green-500/5 p-6 text-green-500"
                >
                    <span class="text-center text-[10px] font-black uppercase tracking-widest"
                        >Файл готов к загрузке</span
                    >
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="flex items-center justify-between px-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Галерея изображений</label
                >
                <span class="text-[10px] font-bold uppercase text-slate-600"
                    >Всего: {{ modelValue.gallery.length }}</span
                >
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <div
                    v-for="(file, index) in modelValue.gallery"
                    :key="index"
                    class="group relative aspect-square cursor-pointer overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-950 transition-all hover:-translate-y-1"
                >
                    <img
                        :src="getPreview(file)"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                    />
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-slate-950/60 opacity-0 transition-opacity group-hover:opacity-100"
                    >
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="openImage(file)"
                                class="rounded-full bg-white/10 p-2 text-white backdrop-blur-md hover:bg-white/20"
                            >
                                <MagnifyingGlassPlusIcon class="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                @click="removeGalleryItem(+index)"
                                class="rounded-full bg-red-500/20 p-2 text-red-500 backdrop-blur-md hover:bg-red-500 hover:text-white"
                            >
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <label
                    class="relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-800 bg-slate-950/20 transition-all hover:border-orange-500/50 hover:bg-slate-950/40"
                >
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleFile($event, 'gallery')"
                        class="sr-only"
                    />
                    <PhotoIcon class="mb-2 h-8 w-8 text-slate-700" />
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Добавить</span
                    >
                </label>
            </div>

            <p
                v-if="modelValue.errors.gallery"
                class="animate-pulse text-center text-[10px] font-bold uppercase text-red-500"
            >
                {{ modelValue.errors.gallery }}
            </p>
        </div>

        <Transition name="fade">
            <div
                v-if="selectedImage"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 p-4 backdrop-blur-md"
                @click="selectedImage = null"
            >
                <button
                    class="absolute right-8 top-8 text-white/50 transition-all hover:rotate-90 hover:text-white"
                >
                    <XMarkIcon class="h-10 w-10" />
                </button>
                <img
                    :src="selectedImage"
                    class="shadow-2xl max-h-[85vh] max-w-[90vw] rounded-3xl border border-white/10 object-contain"
                />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
