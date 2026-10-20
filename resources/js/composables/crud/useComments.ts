import { ref } from 'vue';

import { router } from '@inertiajs/vue3';

import { useNotificationsStore } from '@/stores/notifications';

export type CommentableType = 'animal' | 'product' | 'page' | null;

export interface CommentSubmitPayload {
    content: string;
    guest_name?: string;
    rating?: number;
    agreement: boolean;
}

export function useComments(
    commentableType: CommentableType = null,
    commentableId: number | null = null,
) {
    const notify = useNotificationsStore();
    const errors = ref<Record<string, string>>({});

    const submitComment = (payload: CommentSubmitPayload) => {
        router.post(
            route('comments.store'),
            {
                content: payload.content,
                guest_name: payload.guest_name,
                rating: payload.rating,
                agreement: payload.agreement,
                commentable_type: commentableType,
                commentable_id: commentableId,
            },
            {
                preserveScroll: true,
                onError: (e: Record<string, string>) => {
                    errors.value = e;
                    notify.error('Ошибка валидации!');
                },
            },
        );
    };

    return { submitComment, errors };
}
