<script setup lang="ts">
    import { type PropType, computed, onMounted, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { Volume2, VolumeX } from 'lucide-vue-next';

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
        class="min-h-screen bg-rancho-paper"
        :aria-label="`Страница животного ${animal.name}`"
    >
        <div class="py-12 sm:py-20">
            <div class="grid gap-10 lg:grid-cols-2">
                <!-- GALLERY -->
                <div v-scroll-reveal class="space-y-4">
                    <div
                        class="aspect-square overflow-hidden rounded-3xl border border-rancho-ink-200 bg-rancho-ink-50"
                    >
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
                    />
                </div>

                <!-- INFO -->
                <section v-scroll-reveal class="flex flex-col">
                    <!-- status -->
                    <span
                        v-if="animal.status"
                        class="mb-3 inline-flex w-fit items-center rounded-full bg-rancho-pine/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-rancho-forest ring-1 ring-inset ring-rancho-pine/20"
                        role="status"
                    >
                        {{ animal.status }}
                    </span>

                    <!-- title -->
                    <h1 class="text-3xl font-black text-rancho-forest sm:text-4xl">
                        {{ animal.name }}
                    </h1>

                    <!-- bio -->
                    <p
                        v-if="animal.bio"
                        class="mt-4 text-sm leading-relaxed text-rancho-ink-600 sm:text-base"
                    >
                        {{ animal.bio }}
                    </p>

                    <!-- features -->
                    <dl v-if="animal.features" class="mt-6 grid grid-cols-2 gap-3">
                        <div
                            v-for="(value, key) in animal.features"
                            :key="key"
                            class="rounded-xl border border-rancho-ink-200 bg-white p-3 text-sm"
                        >
                            <dt class="text-xs font-medium text-rancho-ink-500">{{ key }}</dt>
                            <dd class="font-bold text-rancho-forest">{{ value }}</dd>
                        </div>
                    </dl>

                    <!-- voice -->
                    <div v-if="animal.voice_url" class="mt-6">
                        <button
                            @click="toggleVoice"
                            type="button"
                            :aria-pressed="isPlaying"
                            :aria-label="isPlaying ? 'Остановить звук' : 'Воспроизвести звук'"
                            class="shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-full bg-rancho-pine px-6 py-3 font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                        >
                            <component
                                :is="isPlaying ? VolumeX : Volume2"
                                :size="18"
                                aria-hidden="true"
                            />
                            {{ isPlaying ? 'Стоп звук' : 'Слушать голос' }}
                        </button>

                        <audio ref="audioRef" :src="animal.voice_url" @ended="isPlaying = false" />
                    </div>

                    <!-- parent -->
                    <div v-if="animal.family?.parent" class="mt-8 text-sm">
                        <span class="text-rancho-ink-500">Родитель:</span>
                        <Link
                            :href="route('animals.show', animal.family.parent.slug)"
                            class="ml-2 font-bold text-rancho-forest hover:text-rancho-pine hover:underline"
                        >
                            {{ animal.family.parent.name }}
                        </Link>
                    </div>

                    <!-- children -->
                    <div v-if="animal.family?.children?.length" class="mt-4 text-sm">
                        <span class="text-rancho-ink-500">Дети:</span>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <Link
                                v-for="child in animal.family.children"
                                :key="child.slug"
                                :href="route('animals.show', child.slug)"
                                class="rounded-full bg-rancho-sky/20 px-3 py-1 text-xs font-bold text-rancho-forest ring-1 ring-inset ring-rancho-sky/40 transition-colors hover:bg-rancho-sky/30"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- SEO NOTE -->
                    <p v-if="animal.seo?.description" class="mt-10 text-xs text-rancho-ink-500">
                        {{ animal.seo.description }}
                    </p>
                </section>
            </div>

            <div v-if="animal.is_commentable" v-scroll-reveal class="mt-16">
                <CommentsSection
                    :comments="comments"
                    @submit="submitComment"
                    :is-authenticated="isAuthenticated"
                />
            </div>

            <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null">
                <AppImage
                    v-if="selectedImage"
                    :src="selectedImage"
                    alt="Полноэкранный просмотр изображения"
                />
            </BaseModal>
        </div>
    </AppContainer>
</template>
