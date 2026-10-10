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

    const { deleteEntity, isDeleting } = useAdminCrud();

    const props = withDefaults(
        defineProps<{
            comments: Paginated<Comment>;
            title?: string;
            isAuthenticated?: boolean;
            onlyShow?: boolean; // Если true — форма создания скрыта
        }>(),
        {
            onlyShow: false,
            isAuthenticated: false,
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
    const textareaId = `comment-form-${Math.random().toString(36).slice(2)}`;
    const guestNameId = `guest-name-${Math.random().toString(36).slice(2)}`;

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
        });

        content.value = '';
        guestName.value = '';
    };

    const loadMore = () => {
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
    <section class="mt-24 border-t border-slate-100 pt-16" aria-labelledby="comments-title">
        <!-- HEADER -->
        <header class="mb-10 flex items-center justify-between">
            <h2 id="comments-title" class="text-2xl font-black text-rancho-forest">
                {{ title || 'Отзывы' }}
            </h2>

            <span
                class="rounded-lg bg-rancho-paper px-3 py-1 font-bold text-rancho-olive"
                role="status"
                aria-live="polite"
            >
                {{ comments.meta.total ?? items.length }}
            </span>
        </header>

        <!-- FORM FOR ALL USERS (AUTH & GUEST) — Показывается, если onlyShow === false -->
        <form
            v-if="!onlyShow"
            @submit.prevent="submit"
            class="mb-10 space-y-4"
            aria-label="Форма добавления комментария"
        >
            <!-- GUEST NAME FIELD -->
            <BaseInput
                v-if="isAuthenticated"
                v-model="guestName"
                placeholder="Как к вам обращаться?"
                type="text"
                label="Ваше имя"
                theme="light"
                required
            />

            <!-- COMMENT CONTENT FIELD -->
            <BaseTextarea
                v-model="content"
                label="Редактировать текст"
                placeholder="Ваш комментарий"
                :disabled="false"
                :rows="7"
                theme="light"
                required
                :max-height="150"
            />

            <div class="flex items-center justify-between">
                <p id="comment-help" class="text-xs text-slate-500">
                    Нажмите Enter в форме или кнопку отправки для публикации
                </p>

                <button
                    type="submit"
                    class="rounded-xl bg-rancho-pine px-6 py-3 font-bold text-white transition hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 disabled:cursor-not-allowed disabled:opacity-50"
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
            class="rounded-3xl bg-rancho-paper py-12 text-center"
            role="status"
            aria-live="polite"
        >
            <p class="italic text-rancho-olive">Пока нет отзывов — станьте первым!</p>
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
                class="shadow-sm rounded-2xl border border-slate-100 bg-white p-6"
                role="listitem"
                :aria-label="`Комментарий от ${comment.author_name}`"
            >
                <header class="mb-3 flex items-center gap-3">
                    <div
                        class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-slate-800 ring-2 transition-transform group-hover:scale-110"
                        aria-hidden="true"
                    >
                        <AppImage v-if="comment.avatar" :src="comment.avatar" alt="Аватар" />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800 text-lg font-black uppercase text-slate-500"
                        >
                            {{ getInitials(comment.author_name) }}
                        </div>
                    </div>

                    <div>
                        <p class="font-bold text-rancho-forest">
                            {{ comment.author_name }}
                        </p>

                        <AppRating :rating="comment.rating" />

                        <BaseSmartTime :date="comment.created_at" />
                    </div>
                </header>

                <p class="text-sm leading-relaxed text-rancho-olive">
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
            <span v-if="loading" class="text-sm text-rancho-olive">Загрузка...</span>
        </div>
    </section>
</template>
