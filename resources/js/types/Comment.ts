type CommentType = 'animal' | 'product' | 'page'; // keys from MorphMap
type CommentStatusLabel = 'Новый' | 'Одобрен' | 'Скрыт';
type CommentStatus = 'pending' | 'approved' | 'hidden';

export interface Comment {
    id: number;
    user_name: string;
    content: string;
    rating: number | null; // From 1 to 5

    avatar: string | null;
    status: CommentStatus;
    status_label: CommentStatusLabel;

    commentable_id: number | null;
    commentable_type: CommentType;

    created_at: string;
}

export interface AdminComment extends Comment {
    updated_at: string;
    deleted_at: string | null;

    commentable?: {
        name: string;
        slug: string;
        type: CommentType;
    };
}
