<script setup lang="ts">
    import { type PropType, computed, onMounted, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { TagIcon } from '@heroicons/vue/24/outline';
    import { Volume2, VolumeX } from 'lucide-vue-next';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import ImageLightbox from '@/Components/Shared/ImageLightbox.vue';
    import AnimalGallery from '@/Components/Shared/UIGallery.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
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

    const openImageIndex = ref<number | null>(null);

    const allImages = computed<string[]>(() => {
        const list: string[] = [];

        const main = animal.value.avatars?.[0];
        const mainUrl = typeof main === 'string' ? main : main?.url;
        if (mainUrl) list.push(mainUrl);

        animal.value.gallery?.forEach((g) => {
            const url = typeof g === 'string' ? g : g?.url;
            if (url) list.push(url);
        });

        return list;
    });

    const openImage = (url: string) => {
        const idx = allImages.value.indexOf(url);
        if (idx >= 0) openImageIndex.value = idx;
    };

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
                        class="w-full"
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
                    <div class="flex flex-col gap-2 sm:items-start">
                        <!-- Категория -->
                        <div
                            v-if="animal.category?.name"
                            class="inline-flex items-center gap-1.5 self-start rounded-full bg-rancho-ember/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rancho-ember ring-1 ring-inset ring-rancho-ember/20 transition-all hover:bg-rancho-ember/20"
                        >
                            <TagIcon class="h-3 w-3" aria-hidden="true" />
                            {{ animal.category.name }}
                        </div>

                        <!-- Имя + голос -->
                        <div class="flex items-start gap-3">
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
                    <div
                        v-if="animal.features?.length"
                        class="mt-8 overflow-hidden rounded-2xl border border-rancho-ink-100 bg-white p-4"
                    >
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
                        <h2 class="text-2xl font-black text-rancho-forest sm:text-3xl">
                            Чем радует {{ animal.name }}
                        </h2>
                        <p class="mt-2 text-xs text-rancho-ink-500 sm:text-sm">Спасибо за это</p>
                    </div>

                    <Link
                        :href="route('catalog.index', { animal: animal.slug })"
                        class="hidden text-xs font-bold text-rancho-pine transition-colors hover:text-rancho-forest sm:inline-flex"
                    >
                        Все продукты →
                    </Link>
                </header>

                <div
                    class="grid grid-cols-1 justify-items-center gap-x-4 gap-y-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in animal.products"
                        :key="product.id"
                        :product="product"
                    />
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

        <ImageLightbox
            v-model="openImageIndex"
            :images="allImages"
            :alt="`Фото животного ${animal.name}`"
        />
    </AppContainer>
</template>
