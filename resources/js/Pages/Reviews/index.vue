<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { Head, router, useForm, usePage } from '@inertiajs/vue3';

    import CommentCard from '@/Components/Cards/ReviewsPageCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
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
    });

    const submitReview = () => {
        form.post(route('comments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('content', 'rating');
                isFormVisible.value = false;
            },
        });
    };

    const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <main class="min-h-screen bg-[#fcfaf5]">
        <Head title="Отзывы о нашем сервисе" />

        <div class="mx-auto max-w-5xl px-6 py-10">
            <!-- HEADER -->
            <header class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-[#1c3f34]">Отзывы о сайте</h1>
                    <p class="mt-2 text-sm text-[#597d5b]">
                        Впечатления и оценки наших посетителей
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-[#1c3f34] px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-[#285949] focus:outline-none focus:ring-2 focus:ring-[#1c3f34]"
                    @click="isFormVisible = !isFormVisible"
                >
                    {{ isFormVisible ? 'Скрыть форму' : 'Оставить отзыв' }}
                </button>
            </header>

            <!-- FORM SECTION -->
            <Transition name="fade">
                <section
                    v-if="isFormVisible"
                    class="shadow-sm mb-12 rounded-2xl border border-slate-200/80 bg-white p-6"
                >
                    <h2 class="mb-4 text-xl font-bold text-[#1c3f34]">Ваш отзыв</h2>

                    <form class="space-y-4" @submit.prevent="submitReview">
                        <div v-if="!isAuthenticated">
                            <BaseInput
                                v-model="form.guest_name"
                                label="Ваше имя"
                                placeholder="Как к вам обращаться?"
                                required
                                :error="form.errors.guest_name"
                            />
                        </div>

                        <AppRating v-model:rating="form.rating" :readonly="false" :max="5" />

                        <BaseTextarea
                            v-model="form.content"
                            :required="true"
                            :error="form.errors.content"
                            placeholder="Расскажите о вашем опыте работы с сайтом..."
                        />

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl bg-[#1c3f34] px-6 py-2.5 text-sm font-bold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Отправка...' : 'Опубликовать' }}
                            </button>
                        </div>
                    </form>
                </section>
            </Transition>

            <!-- SORTING BAR -->
            <div class="mb-6 flex items-center justify-between border-b border-slate-200/60 pb-4">
                <span class="text-sm font-medium text-slate-500">
                    Всего отзывов: {{ comments.meta?.total ?? comments.data.length }}
                </span>

                <BaseSelect
                    label="Сортировка:"
                    v-model="selectedSort"
                    :options="sortOptions"
                    label-key="label"
                    value-key="value"
                    width-class="w-64"
                />
            </div>

            <!-- REVIEWS LIST / EMPTY STATE -->
            <EmptyState
                v-if="!comments.data.length"
                title="Отзывов пока нет"
                description="Станьте первым, кто поделится мнением о нашей работе!"
                action-label="Написать отзыв"
                @action="isFormVisible = true"
            />

            <section v-else aria-label="Список отзывов" class="space-y-4">
                <CommentCard
                    v-for="comment in comments.data"
                    :key="comment.id"
                    :comment="comment"
                />
            </section>

            <!-- PAGINATION -->
            <div class="mt-8">
                <MainPagination :links="comments.meta.links" />
            </div>
        </div>
    </main>
</template>
