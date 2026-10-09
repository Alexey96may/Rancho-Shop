<script setup lang="ts">
    import { type PropType, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import UserCommentCard from '@/Components/Cards/CommentCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { Comment, Paginated } from '@/types';

    defineOptions({ layout: ProfileLayout });

    const props = defineProps({
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
            validator: (value: Paginated<Comment>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
    });

    const { deleteEntity, isDeleting } = useAdminCrud();

    const editingCommentId = ref<number | null>(null);
    const editForm = useForm({
        content: '',
        rating: 5,
    });

    const cancelEdit = () => {
        editingCommentId.value = null;
        editForm.reset();
    };

    const submitUpdateComment = (id: number) => {
        editForm.put(route('profile.comments.update', id), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        });
    };

    const handleDelete = (comment: Comment) => {
        const orderDate = new Date(comment.created_at).toLocaleString();
        const message = 'Удалить отзыв от ' + orderDate + '?';
        deleteEntity('profile.comments', comment.id, message);

        console.log(props.comments.links);
    };
</script>

<template>
    <div class="space-y-6">
        <section class="space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-slate-500">
                История ваших отзывов
            </h3>

            <div
                v-if="comments.data.length === 0"
                class="rounded-2xl border border-dashed border-slate-800 bg-slate-950/20 py-12 text-center text-sm text-slate-500"
            >
                Вы еще не оставляли отзывов на сайте.
            </div>

            <div v-else class="grid grid-cols-1 gap-4">
                <UserCommentCard
                    v-for="comment in comments.data"
                    :key="comment.id"
                    :comment="comment"
                    :is-deleting="isDeleting(comment.id)"
                    :is-saving="editForm.processing"
                    @update="submitUpdateComment(comment.id)"
                    @delete="handleDelete(comment)"
                />
            </div>
        </section>

        <MainPagination :links="comments?.meta?.links" />
    </div>
</template>

<style scoped>
    .stagger-enter-active {
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        transition-delay: calc(var(--i) * 0.05s);
    }

    .stagger-leave-active {
        transition: all 0.3s ease;
        position: absolute;
        width: 100%;
    }

    .stagger-enter-from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }

    .stagger-leave-to {
        opacity: 0;
        transform: scale(0.9);
    }

    .stagger-move {
        transition: transform 0.4s ease;
    }
</style>
