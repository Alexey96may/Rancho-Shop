import { router } from '@inertiajs/vue3';

import { useNotificationsStore } from '@/stores/notifications';

export type CommentableType = 'animal' | 'product' | 'page' | null;

export interface CommentSubmitPayload {
    content: string;
    guest_name?: string;
    rating?: number;
}

export function useComments(
    commentableType: CommentableType = null,
    commentableId: number | null = null,
) {
    const notify = useNotificationsStore();

    const submitComment = (payload: CommentSubmitPayload) => {
        router.post(
            route('comments.store'),
            {
                content: payload.content,
                guest_name: payload.guest_name,
                rating: payload.rating,
                commentable_type: commentableType,
                commentable_id: commentableId,
            },
            {
                preserveScroll: true,
                onError: (errors: Record<string, string>) => {
                    console.error(errors);
                    notify.error('Ошибка валидации');
                },
            },
        );
    };

    return {
        submitComment,
    };
}
