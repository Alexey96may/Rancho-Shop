<script setup lang="ts">
    import { computed, onBeforeUnmount, ref, watch } from 'vue';

    import { router, useForm, usePage } from '@inertiajs/vue3';

    import CommentCard from '@/Components/Cards/ReviewsPageCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import CommentCardSkeleton from '@/Components/Skeletons/ReviewsPageCardSkeleton.vue';
    import AgreementCheckbox from '@/Components/UI/AgreementCheckbox.vue';
    import AppCaptcha from '@/Components/UI/AppCaptcha.vue';
    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import EmptyState from '@/Components/UI/EmptyState.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Paginated, SharedData } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        comments: Paginated<Comment>;
        filters?: {
            sort?: string;
            direction?: string;
        };
    }>();

    const page = usePage<SharedData>();

    const sortOptions = [
        { label: 'Сначала новые', value: 'created_at_desc' },
        { label: 'Сначала старые', value: 'created_at_asc' },
        { label: 'Сначала с высоким рейтингом', value: 'rating_desc' },
        { label: 'Сначала с низким рейтингом', value: 'rating_asc' },
    ];

    const parseSortFromProps = (): string => {
        const sort = props.filters?.sort ?? 'created_at';
        const direction = props.filters?.direction ?? 'desc';
        return `${sort}_${direction}`;
    };

    const selectedSort = ref<string>(parseSortFromProps());
    const isLoading = ref(false);

    // --- Inertia-переходы: показываем скелетоны на время загрузки ---
    const offStart = router.on('start', () => {
        isLoading.value = true;
    });

    const offFinish = router.on('finish', () => {
        isLoading.value = false;
    });

    onBeforeUnmount(() => {
        offStart();
        offFinish();
    });

    watch(
        () => props.filters,
        () => {
            selectedSort.value = parseSortFromProps();
        },
        { deep: true },
    );

    watch(selectedSort, (newValue, oldValue) => {
        if (newValue === oldValue) return;

        const lastUnderscoreIndex = newValue.lastIndexOf('_');
        const sort = newValue.slice(0, lastUnderscoreIndex);
        const direction = newValue.slice(lastUnderscoreIndex + 1);

        router.get(
            route('reviews.index'),
            { sort, direction },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    });

    const isFormVisible = ref(false);

    const form = useForm({
        content: '',
        rating: 5,
        guest_name: '',
        captcha: '',
        agreement: false,
    });

    const isCaptchaValid = ref(false);
    const captchaRef = ref<InstanceType<typeof AppCaptcha> | null>(null);

    const isAuthenticated = computed(() => !!page.props.auth?.user);

    const canSubmit = computed(() => {
        if (form.processing) return false;
        if (!form.agreement) return false;
        if (isAuthenticated.value) return true;
        return isCaptchaValid.value;
    });

    const submitReview = () => {
        if (!canSubmit.value) return;

        form.post(route('comments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('content', 'rating', 'captcha', 'agreement');
                isCaptchaValid.value = false;
                captchaRef.value?.reset();
                isFormVisible.value = false;
            },
            onError: () => {
                if (!isAuthenticated.value) {
                    captchaRef.value?.reset();
                }
            },
        });
    };
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="pt-12">
            <!-- HEADER -->
            <header
                v-scroll-reveal
                class="mb-8 flex flex-col justify-between gap-4 sm:mb-12 md:flex-row md:items-center"
            >
                <div>
                    <h1 class="text-2xl font-black text-rancho-forest sm:text-4xl">Отзывы о нас</h1>
                    <p class="mt-2 text-xs text-rancho-ink-600 sm:text-sm">
                        Впечатления и оценки наших посетителей
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-xs font-black uppercase tracking-widest transition-all focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                    :class="
                        isFormVisible
                            ? 'border-2 border-rancho-ink-300 bg-white text-rancho-forest hover:border-rancho-forest hover:bg-rancho-ink-50'
                            : 'shadow-md hover:shadow-lg bg-rancho-pine text-white hover:bg-rancho-forest'
                    "
                    @click="isFormVisible = !isFormVisible"
                >
                    {{ isFormVisible ? 'Скрыть форму' : 'Оставить отзыв' }}
                </button>
            </header>

            <!-- FORM SECTION -->
            <Transition name="fade-slide">
                <section
                    v-if="isFormVisible"
                    class="shadow-sm mb-12 rounded-2xl border border-rancho-ink-200 bg-white p-6"
                >
                    <h2 class="mb-6 text-lg font-black text-rancho-forest">Ваш отзыв</h2>

                    <form class="space-y-8" @submit.prevent="submitReview">
                        <BaseInput
                            v-if="!isAuthenticated"
                            v-model="form.guest_name"
                            label="Ваше имя"
                            placeholder="Как к вам обращаться?"
                            required
                            theme="light"
                            :error="form.errors.guest_name"
                        />

                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xs font-medium text-rancho-ink-600 md:text-sm">
                                Ваша оценка:
                            </span>
                            <AppRating v-model:rating="form.rating" :readonly="false" :max="5" />
                        </div>

                        <BaseTextarea
                            v-model="form.content"
                            label="Комментарий"
                            :required="true"
                            theme="light"
                            :error="form.errors.content"
                            placeholder="Расскажите о вашем опыте работы с сайтом..."
                        />

                        <Transition name="fade-slide" appear>
                            <AppCaptcha
                                v-if="!isAuthenticated"
                                ref="captchaRef"
                                v-model="form.captcha"
                                v-model:valid="isCaptchaValid"
                                :count="5"
                                chars-preset="alphanumeric"
                                distortion="lines"
                                theme="light"
                            />
                        </Transition>

                        <p v-if="form.errors.captcha" class="text-xs font-medium text-red-600">
                            {{ form.errors.captcha }}
                        </p>

                        <AgreementCheckbox
                            v-model="form.agreement"
                            :error="form.errors.agreement"
                            class="pt-1"
                        />

                        <p v-if="form.errors.agreement" class="text-xs font-medium text-red-600">
                            {{ form.errors.agreement }}
                        </p>

                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="!canSubmit"
                                :aria-disabled="!canSubmit"
                                class="shadow-md hover:shadow-lg disabled:hover:shadow-md rounded-xl bg-rancho-pine px-6 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-rancho-pine"
                            >
                                {{ form.processing ? 'Отправка...' : 'Опубликовать' }}
                            </button>
                        </div>
                    </form>
                </section>
            </Transition>

            <!-- SORTING BAR -->
            <div
                v-scroll-reveal
                class="mb-6 flex flex-col justify-between gap-4 border-b border-rancho-ink-200 pb-6 sm:flex-row sm:items-end"
            >
                <span class="text-sm font-medium text-rancho-ink-600">
                    Всего отзывов:
                    <span class="font-black text-rancho-forest">
                        {{ comments.meta?.total ?? comments.data.length }}
                    </span>
                </span>

                <BaseSelect
                    label="Сортировка:"
                    v-model="selectedSort"
                    :options="sortOptions"
                    label-key="label"
                    value-key="value"
                    width-class="w-full sm:w-64"
                />
            </div>

            <!-- REVIEWS LIST / EMPTY STATE -->
            <Transition name="fade-slide" mode="out-in">
                <!-- EMPTY -->
                <EmptyState
                    v-if="!isLoading && !comments.data.length"
                    key="empty"
                    title="Отзывов пока нет"
                    description="Станьте первым, кто поделится мнением о нашей работе!"
                    action-label="Написать отзыв"
                    @action="isFormVisible = true"
                />

                <!-- SKELETONS -->
                <div v-else-if="isLoading" key="skeletons" class="space-y-4">
                    <CommentCardSkeleton v-for="i in 3" :key="`skeleton-${i}`" />
                </div>

                <!-- CONTENT -->
                <TransitionGroup v-else key="content" name="stagger" tag="div" class="space-y-4">
                    <CommentCard
                        v-for="(comment, index) in comments.data"
                        :key="comment.id"
                        :comment="comment"
                        :style="{ '--i': index }"
                    />
                </TransitionGroup>
            </Transition>

            <!-- PAGINATION -->
            <div v-scroll-reveal class="mt-8">
                <MainPagination :links="comments.meta.links" theme="light" />
            </div>
        </div>
    </AppContainer>
</template>
