<script setup lang="ts" id="s1a9x2">
    import { type PropType, computed, onMounted, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import type { GalleryItem } from '@/composables/features/useMediaUpload';
    import type { Animal, Comment, Paginated, ResourceSingle, SharedData } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        animal: {
            type: Object as PropType<ResourceSingle<Animal>>,
            required: true,
            validator: (value: ResourceSingle<Animal>) => {
                return Boolean(
                    value &&
                    typeof value === 'object' &&
                    value.data &&
                    typeof value.data.id !== 'undefined',
                );
            },
        },
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
            validator: (value: Paginated<Comment>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
    });

    const animal = computed(() => props.animal.data);

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
        activeImage.value = animal.value.avatars?.[0]?.url ?? null;
    });

    const { submitComment } = useComments('animal', animal.value.id);

    const selectedImage = ref<string | null>(null);

    const openImage = (item: GalleryItem) => {
        const previewUrl = getPreview(item);

        if (previewUrl) {
            selectedImage.value = previewUrl;
        }
    };

    const getPreview = (item: GalleryItem | string | null | undefined): string => {
        if (!item) return '';

        if (typeof item === 'string') {
            return item;
        }

        return '';
    };

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const page = usePage<SharedData>();
    const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <AppContainer
        class="min-h-screen"
        style="background: #fcfaf5"
        :aria-label="`Страница животного
        ${animal.name}`"
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
                <!-- IMAGES -->
                <div class="space-y-4">
                    <div class="aspect-square overflow-hidden rounded-3xl border bg-slate-100">
                        <AppImage
                            :src="animal.avatars?.[0] || ''"
                            :alt="animal.name"
                            class-name="h-full w-full object-cover"
                        />
                    </div>

                    <MediaGallery
                        v-if="animal.gallery"
                        v-model="animal.gallery"
                        @preview="openImage"
                        :is-view-mode="true"
                    ></MediaGallery>
                </div>
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
                    <div v-if="animal.family?.parent" class="mt-8 text-sm">
                        <span style="color: #597d5b">Родитель:</span>
                        <Link
                            :href="route('animals.show', animal.family.parent.slug)"
                            class="ml-2 font-bold hover:underline"
                            style="color: #1c3f34"
                        >
                            {{ animal.family.parent.name }}
                        </Link>
                    </div>

                    <!-- children -->
                    <div v-if="animal.family?.children?.length" class="mt-4 text-sm">
                        <span style="color: #597d5b">Дети:</span>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <Link
                                v-for="child in animal.family.children"
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
            </div>

            <div class="mx-auto mt-12 max-w-4xl px-6 pb-16">
                <CommentsSection
                    :comments="comments"
                    @submit="submitComment"
                    :is-authenticated="isAuthenticated"
                />
            </div>
            <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null"
                ><AppImage
                    v-if="selectedImage"
                    :src="selectedImage"
                    alt="Полноэкранный просмотр изображения"
                />
            </BaseModal>
        </div>
    </AppContainer>
</template>
