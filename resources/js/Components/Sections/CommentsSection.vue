<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import type { Comment, Paginated } from '@/types';
    import { getInitials } from '@/utils/user';

    const { deleteEntity } = useAdminCrud();

    const props = withDefaults(
        defineProps<{
            comments: Paginated<Comment>;
            title?: string;
            isAuthenticated?: boolean;
            onlyShow?: boolean;
        }>(),
        {
            title: 'Отзывы',
            isAuthenticated: false,
            onlyShow: false,
        },
    );

    const emit = defineEmits<{
        (e: 'submit', payload: { content: string; guest_name?: string; rating?: number }): void;
    }>();

    // Реактивный массив для подгружаемых комментариев
    const items = ref<Comment[]>([...props.comments.data]);
    const page = ref(props.comments.meta.current_page);
    const lastPage = ref(props.comments.meta.last_page);
    const loading = ref(false);

    // Синхронизация при первом рендере или смене пропсов извне
    watch(
        () => props.comments,
        (newVal) => {
            if (newVal.meta.current_page === 1) {
                items.value = [...newVal.data];
            }
            page.value = newVal.meta.current_page;
            lastPage.value = newVal.meta.last_page;
        },
    );

    const content = ref('');
    const guestName = ref('');
    const rating = ref(5);

    // Валидация: если не авторизован — имя гостя обязательно
    const canSubmit = computed(() => {
        const hasContent = content.value.trim().length > 0;
        if (props.isAuthenticated) {
            return hasContent;
        }
        return hasContent && guestName.value.trim().length > 0;
    });

    const isEmpty = computed(() => items.value.length === 0);

    const submit = () => {
        if (!canSubmit.value || props.onlyShow) return;

        emit('submit', {
            content: content.value.trim(),
            guest_name: props.isAuthenticated ? undefined : guestName.value.trim(),
            rating: rating.value,
        });

        content.value = '';
        guestName.value = '';
        rating.value = 5;
    };

    const loadMore = () => {
        if (typeof window !== 'object') return;
        if (loading.value || page.value >= lastPage.value) return;

        loading.value = true;

        router.get(
            window.location.pathname,
            { page: page.value + 1 },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['comments'],
                onSuccess: (res) => {
                    const newComments = (res.props as any).comments.data;
                    items.value.push(...newComments);
                    page.value++;
                    loading.value = false;
                },
                onError: () => {
                    loading.value = false;
                },
            },
        );
    };

    let observer: IntersectionObserver;
    const sentinel = ref<HTMLElement | null>(null);

    onMounted(() => {
        observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadMore();
            }
        });

        if (sentinel.value) observer.observe(sentinel.value);
    });

    onBeforeUnmount(() => {
        observer?.disconnect();
    });

    const handleDelete = (comment: Comment) => {
        const orderDate = new Date(comment.created_at).toLocaleString();
        const message = 'Удалить отзыв от ' + orderDate + '?';
        deleteEntity('profile.comments', comment.id, message);
    };
</script>

<template>
    <section
        class="border-rancho-ink-200 mt-16 border-t pt-12 sm:mt-24 sm:pt-16"
        aria-labelledby="comments-title"
    >
        <!-- HEADER -->
        <header class="mb-10 flex items-center justify-between gap-4">
            <h2 id="comments-title" class="text-2xl font-black text-rancho-forest sm:text-4xl">
                {{ title }}
            </h2>

            <span
                class="bg-rancho-ink-100 text-rancho-ink-700 ring-rancho-ink-200 rounded-full px-3 py-1 text-xs font-black ring-1 ring-inset"
                role="status"
                aria-live="polite"
            >
                {{ comments.meta.total ?? items.length }}
            </span>
        </header>

        <!-- FORM FOR ALL USERS (AUTH & GUEST) -->
        <form
            v-if="!onlyShow"
            @submit.prevent="submit"
            class="border-rancho-ink-200 shadow-sm mb-10 space-y-6 rounded-2xl border bg-white p-6"
            aria-label="Форма добавления комментария"
        >
            <!-- GUEST NAME FIELD -->
            <BaseInput
                v-if="!isAuthenticated"
                v-model="guestName"
                placeholder="Как к вам обращаться?"
                type="text"
                label="Ваше имя"
                theme="light"
                required
            />

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-rancho-ink-600 text-xs font-medium md:text-sm">
                    Ваша оценка:
                </span>
                <AppRating v-model:rating="rating" :readonly="false" :max="5" />
            </div>

            <!-- COMMENT CONTENT FIELD -->
            <BaseTextarea
                v-model="content"
                label="Комментарий"
                placeholder="Ваш комментарий"
                :disabled="false"
                :rows="7"
                theme="light"
                required
                :max-height="150"
            />

            <div
                class="flex flex-col-reverse items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-rancho-ink-500 text-xs">
                    Нажмите Enter в форме или кнопку отправки для публикации
                </p>

                <button
                    type="submit"
                    class="shadow-md hover:shadow-lg disabled:hover:shadow-md rounded-xl bg-rancho-pine px-6 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-rancho-pine"
                    :disabled="!canSubmit"
                    :aria-disabled="!canSubmit"
                >
                    Отправить
                </button>
            </div>
        </form>

        <!-- EMPTY STATE -->
        <div
            v-if="isEmpty"
            class="border-rancho-ink-300 bg-rancho-ink-50/50 rounded-2xl border border-dashed py-12 text-center"
            role="status"
            aria-live="polite"
        >
            <p class="text-rancho-ink-600 text-sm italic">Пока нет отзывов — станьте первым!</p>
        </div>

        <!-- LIST -->
        <div
            v-else
            class="grid grid-cols-1 gap-6 md:grid-cols-2"
            role="list"
            aria-label="Список комментариев"
        >
            <article
                v-for="comment in items"
                :key="comment.id"
                class="border-rancho-ink-200 shadow-sm hover:border-rancho-ink-300 hover:shadow-md group rounded-2xl border bg-white p-6 transition-all"
                role="listitem"
                :aria-label="`Комментарий от ${comment.author_name}`"
            >
                <header class="mb-4 flex items-start gap-3">
                    <div
                        class="ring-rancho-ink-100 h-12 w-12 shrink-0 overflow-hidden rounded-2xl ring-2"
                        aria-hidden="true"
                    >
                        <AppImage
                            v-if="comment.avatar"
                            :src="comment.avatar"
                            alt="Аватар"
                            class-name="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="to-rancho-ink-800 flex h-full w-full items-center justify-center bg-gradient-to-br from-rancho-forest text-lg font-black uppercase text-rancho-paper"
                        >
                            {{ getInitials(comment.author_name) }}
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col gap-2">
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <p class="truncate font-bold text-rancho-forest">
                                {{ comment.author_name }}
                            </p>

                            <AppRating :rating="comment.rating" />
                        </div>

                        <BaseSmartTime :date="comment.created_at" />
                    </div>
                </header>

                <p class="text-rancho-ink-700 text-sm leading-relaxed">
                    {{ comment.content }}
                </p>
            </article>
        </div>

        <!-- LOADING TRIGGER -->
        <div
            v-if="page < lastPage"
            ref="sentinel"
            class="mt-6 flex h-10 items-center justify-center"
            aria-hidden="true"
        >
            <span v-if="loading" class="text-rancho-ink-500 text-sm">Загрузка...</span>
        </div>
    </section>
</template>
