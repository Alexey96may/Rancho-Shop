<script setup lang="ts">
    import { type PropType, computed, onMounted, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { Volume2, VolumeX } from 'lucide-vue-next';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import AnimalGallery from '@/Components/Shared/UIGallery.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import type { GalleryItem } from '@/composables/features/useMediaUpload';
    import type { Animal, Comment, Paginated, ResourceSingle, SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        animal: {
            type: Object as PropType<ResourceSingle<Animal>>,
            required: true,
        },
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
        },
    });

    const animal = computed(() => props.animal.data);

    // voice
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

    // gallery
    const selectedImage = ref<string | null>(null);

    const openImage = (item: string) => {
        if (item) selectedImage.value = item;
    };

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const { submitComment } = useComments('animal', animal.value.id);

    const page = usePage<SharedData>();
    const isAuthenticated = computed(() => !!page.props.auth?.user);

    /** Проверка, есть ли хоть что-то в блоке «Семья» */
    const hasFamily = computed(() => {
        const f = animal.value.family;
        return !!(f?.parent || (f?.children && f.children.length > 0));
    });
</script>

<template>
    <AppContainer
        class="min-h-screen bg-rancho-paper"
        :aria-label="`Страница животного ${animal.name}`"
    >
        <div class="py-12 sm:py-20">
            <!-- TOP: GALLERY + INFO -->
            <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
                <!-- GALLERY -->
                <div class="relative">
                    <AnimalGallery
                        v-scroll-reveal
                        :main-photo="animal.avatars?.[0]"
                        :gallery="animal.gallery"
                        :alt="animal.name"
                        @preview="openImage"
                    />

                    <span
                        v-if="animal.status"
                        class="shadow-md absolute right-5 top-5 line-clamp-1 inline-flex max-w-[50%] items-center rounded-full bg-rancho-pine/90 px-4 py-1 text-[10px] font-black uppercase tracking-widest text-rancho-ink-100 ring-1 ring-inset ring-rancho-pine/20 backdrop-blur-md sm:right-7 sm:top-7 sm:text-xs"
                    >
                        {{ animal.status }}
                    </span>
                </div>

                <!-- INFO -->
                <section v-scroll-reveal class="flex flex-col gap-4">
                    <!-- Статус + Категория -->
                    <div class="flex items-center gap-2 sm:flex-col sm:items-start">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Разделитель -->
                            <span
                                v-if="animal.status && animal.category?.name"
                                class="h-3 w-px bg-rancho-ink-200"
                                aria-hidden="true"
                            ></span>

                            <span
                                v-if="animal.category?.name"
                                class="inline-flex items-center rounded-full bg-rancho-ember/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rancho-ember ring-1 ring-inset ring-rancho-ember/20"
                            >
                                {{ animal.category.name }}
                            </span>
                        </div>

                        <!-- Имя + голос -->
                        <div class="mt-4 flex items-start gap-3">
                            <h1 class="text-3xl font-black text-rancho-forest sm:text-4xl">
                                {{ animal.name }}
                            </h1>

                            <button
                                v-if="animal.voice_url"
                                type="button"
                                :aria-pressed="isPlaying"
                                :aria-label="isPlaying ? 'Остановить звук' : 'Воспроизвести звук'"
                                class="group mt-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rancho-pine/10 text-rancho-pine ring-1 ring-inset ring-rancho-pine/20 transition-all hover:bg-rancho-pine hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 sm:h-11 sm:w-11"
                                @click="toggleVoice"
                            >
                                <component
                                    :is="isPlaying ? VolumeX : Volume2"
                                    :size="18"
                                    aria-hidden="true"
                                    class="transition-transform group-hover:scale-110"
                                />
                            </button>

                            <audio
                                v-if="animal.voice_url"
                                ref="audioRef"
                                :src="animal.voice_url"
                                @ended="isPlaying = false"
                            />
                        </div>
                    </div>

                    <!-- Био -->
                    <p
                        v-if="animal.bio"
                        class="mt-4 text-sm leading-relaxed text-rancho-ink-600 sm:text-base"
                    >
                        {{ animal.bio }}
                    </p>

                    <!-- Характеристики -->
                    <div v-if="animal.features?.length" class="mt-8">
                        <h2
                            class="text-xs font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Характеристики
                        </h2>

                        <dl class="mt-3 divide-y divide-rancho-ink-100 text-sm">
                            <div
                                v-for="(feature, index) in animal.features"
                                :key="index"
                                class="flex items-start justify-between gap-4 py-2.5"
                            >
                                <dt class="text-rancho-ink-500">{{ feature.label }}</dt>
                                <dd class="text-right font-medium text-rancho-forest">
                                    {{ feature.value }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Семья -->
                    <div v-if="hasFamily" class="mt-8 space-y-5">
                        <!-- Родитель -->
                        <div v-if="animal.family?.parent">
                            <h2
                                class="text-xs font-black uppercase tracking-widest text-rancho-ink-500"
                            >
                                Родитель
                            </h2>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <Link
                                    :href="route('animals.show', animal.family.parent.slug)"
                                    class="inline-flex items-center rounded-full border border-rancho-ink-200 bg-white px-3 py-1.5 text-xs font-bold text-rancho-forest transition-colors hover:border-rancho-pine hover:bg-rancho-ink-50"
                                >
                                    {{ animal.family.parent.name }}
                                </Link>
                            </div>
                        </div>

                        <!-- Дети -->
                        <div v-if="animal.family?.children?.length">
                            <h2
                                class="text-xs font-black uppercase tracking-widest text-rancho-ink-500"
                            >
                                Дети
                            </h2>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <Link
                                    v-for="child in animal.family.children"
                                    :key="child.slug"
                                    :href="route('animals.show', child.slug)"
                                    class="inline-flex items-center rounded-full border border-rancho-ink-200 bg-white px-3 py-1.5 text-xs font-bold text-rancho-forest transition-colors hover:border-rancho-pine hover:bg-rancho-ink-50"
                                >
                                    {{ child.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- PRODUCTS -->
            <section
                v-if="animal.products?.length"
                v-scroll-reveal
                class="mt-8 border-t border-rancho-ink-200 pt-8 sm:mt-20"
                aria-label="Продукты животного"
            >
                <header class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-rancho-forest sm:text-3xl">Продукты</h2>
                        <p class="mt-2 text-xs text-rancho-ink-500 sm:text-sm">
                            Что производит {{ animal.name }}
                        </p>
                    </div>

                    <Link
                        :href="route('catalog.index', { animal: animal.slug })"
                        class="hidden text-xs font-bold text-rancho-pine transition-colors hover:text-rancho-forest sm:inline-flex"
                    >
                        Все продукты →
                    </Link>
                </header>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 lg:grid-cols-4">
                    <Link
                        v-for="product in animal.products"
                        :key="product.id"
                        :href="route('catalog.show', product.slug)"
                        class="hover:shadow-md group flex flex-col overflow-hidden rounded-2xl border border-rancho-ink-200 bg-white transition-all hover:-translate-y-0.5 hover:border-rancho-pine"
                    >
                        <div class="aspect-square overflow-hidden bg-rancho-ink-50">
                            <AppImage
                                v-if="product.main_photo?.[0]"
                                :src="product.main_photo[0]"
                                :alt="product.name"
                                class-name="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-xs text-rancho-ink-400"
                            >
                                Нет фото
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col gap-2 p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 text-sm font-bold text-rancho-forest group-hover:text-rancho-pine"
                            >
                                {{ product.name }}
                            </h3>

                            <div class="mt-auto">
                                <span
                                    v-if="product.default_variant?.price"
                                    class="text-base font-black text-rancho-forest sm:text-lg"
                                >
                                    {{ formatMoney(product.default_variant.price) }}
                                </span>
                                <span v-else class="text-xs italic text-rancho-ink-400">
                                    Цена не указана
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div class="mt-6 sm:hidden">
                    <Link
                        :href="route('catalog.index', { animal: animal.slug })"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-rancho-ink-200 bg-white px-4 py-3 text-xs font-bold text-rancho-forest transition-colors hover:border-rancho-pine hover:bg-rancho-ink-50"
                    >
                        Все продукты →
                    </Link>
                </div>
            </section>

            <!-- COMMENTS -->
            <section v-if="animal.is_commentable" v-scroll-reveal class="mt-16 sm:mt-20">
                <CommentsSection
                    :comments="comments"
                    :is-authenticated="isAuthenticated"
                    @submit="submitComment"
                />
            </section>
        </div>

        <BaseModal :show="isModalOpen" variant="ui" @close="selectedImage = null">
            <AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </AppContainer>
</template>
