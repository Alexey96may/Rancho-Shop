<script setup lang="ts" id="s1a9x2">
    import { computed, onMounted, ref } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';
    import { router } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, FarmAnimal, ResourceCollection, ResourceSingle } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        animal: ResourceSingle<FarmAnimal>;
        comments: ResourceCollection<Comment>;
    }>();

    const animal = computed(() => props.animal.data);
    const comments = computed(() => props.comments.data);

    // ----------------------
    // voice player
    // ----------------------
    const isPlaying = ref(false);
    const audioRef = ref<HTMLAudioElement | null>(null);

    const toggleVoice = () => {
        if (!audioRef.value) return;

        if (isPlaying.value) {
            audioRef.value.pause();
        } else {
            audioRef.value.play();
        }

        isPlaying.value = !isPlaying.value;
    };

    // ----------------------
    // gallery
    // ----------------------
    const activeImage = ref<string | null>(null);

    onMounted(() => {
        activeImage.value = animal.value.media?.[0]?.url ?? null;
    });

    const submitComment = (content: string) => {
        router.post(route('comments.store'), {
            content,
            commentable_type: 'animal',
            commentable_id: animal.value.id,
        });
    };
</script>

<template id="s9k2v4">
    <Head :title="animal.name" />

    <main
        class="min-h-screen"
        style="background: #fcfaf5"
        :aria-label="`Страница животного ${animal.name}`"
    >
        <div class="mx-auto max-w-6xl px-6 py-10">
            <!-- breadcrumb -->
            <nav class="mb-6 text-sm" style="color: #597d5b">
                <Link :href="route('animals.index')" class="hover:underline"> Животные </Link>
                <span class="mx-2">/</span>
                <span style="color: #1c3f34">{{ animal.name }}</span>
            </nav>

            <div class="grid gap-10 lg:grid-cols-2">
                <!-- GALLERY -->
                <section aria-label="Галерея животного">
                    <div
                        class="aspect-square overflow-hidden rounded-3xl border"
                        style="border-color: #e3b44b33"
                    >
                        <img
                            :src="activeImage || animal.media?.[0]?.url"
                            class="h-full w-full object-cover"
                            :alt="`Фото ${animal.name}`"
                        />
                    </div>

                    <!-- thumbnails -->
                    <div v-if="animal.media?.length > 1" class="mt-4 flex gap-3">
                        <button
                            v-for="img in animal.media"
                            :key="img.id"
                            @click="activeImage = img.url"
                            class="h-16 w-16 overflow-hidden rounded-xl border transition hover:scale-105"
                            :style="{ borderColor: '#E3B44B33' }"
                            :aria-label="`Выбрать изображение ${animal.name}`"
                        >
                            <img :src="img.url" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </section>

                <!-- INFO -->
                <section class="flex flex-col">
                    <!-- status -->
                    <span
                        class="mb-3 inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-bold"
                        style="background: #3b755820; color: #1c3f34"
                        role="status"
                    >
                        {{ animal.status }}
                    </span>

                    <!-- title -->
                    <h1 class="text-4xl font-black" style="color: #1c3f34">
                        {{ animal.name }}
                    </h1>

                    <!-- bio -->
                    <p v-if="animal.bio" class="mt-4 leading-relaxed" style="color: #597d5b">
                        {{ animal.bio }}
                    </p>

                    <!-- features -->
                    <dl v-if="animal.features" class="mt-6 grid grid-cols-2 gap-3">
                        <div
                            v-for="(value, key) in animal.features"
                            :key="key"
                            class="rounded-xl bg-white p-3 text-sm"
                            style="border: 1px solid #e3b44b33"
                        >
                            <dt class="text-xs" style="color: #597d5b">{{ key }}</dt>
                            <dd class="font-bold" style="color: #1c3f34">
                                {{ value }}
                            </dd>
                        </div>
                    </dl>

                    <!-- voice -->
                    <div v-if="animal.voice_url" class="mt-6">
                        <button
                            @click="toggleVoice"
                            class="rounded-full px-6 py-3 font-bold text-white transition hover:scale-105"
                            style="background: #3b7558"
                            :aria-pressed="isPlaying"
                            :aria-label="isPlaying ? 'Остановить звук' : 'Воспроизвести звук'"
                        >
                            {{ isPlaying ? 'Стоп звук' : 'Слушать голос' }}
                        </button>

                        <audio ref="audioRef" :src="animal.voice_url" @ended="isPlaying = false" />
                    </div>

                    <!-- parent -->
                    <div v-if="animal.parent" class="mt-8 text-sm">
                        <span style="color: #597d5b">Родитель:</span>
                        <Link
                            :href="route('animals.show', animal.parent.slug)"
                            class="ml-2 font-bold hover:underline"
                            style="color: #1c3f34"
                        >
                            {{ animal.parent.name }}
                        </Link>
                    </div>

                    <!-- children -->
                    <div v-if="animal.children?.length" class="mt-4 text-sm">
                        <span style="color: #597d5b">Дети:</span>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <Link
                                v-for="child in animal.children"
                                :key="child.slug"
                                :href="route('animals.show', child.slug)"
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                style="background: #a8c4cb30; color: #1c3f34"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- SEO NOTE -->
                    <div
                        v-if="animal.seo?.description"
                        class="mt-10 text-xs"
                        style="color: #597d5b"
                    >
                        {{ animal.seo.description }}
                    </div>
                </section>

                <CommentsSection :comments="comments" @submit="submitComment" />
            </div>
        </div>
    </main>
</template>
