import { router } from '@inertiajs/vue3';

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
            },
        );
    };

    return {
        submitComment,
    };
}
