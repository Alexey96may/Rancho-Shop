type CommentType = 'animal' | 'product' | 'page'; // keys from MorphMap
type CommentStatusLabel = 'Новый' | 'Одобрен' | 'Скрыт';
type CommentStatus = 'pending' | 'approved' | 'hidden';

export interface Commentable {
    id: number;
    name: string | null;
    type: string;
    slug: string | null;
    url: string | null;
    is_commentable: boolean;
}

export interface Comment {
    id: number;
    user_name?: string | null;
    guest_name?: string | null;
    author_name: string;
    content: string;
    rating: number | null; // From 1 to 5

    avatar: string | null;
    status: CommentStatus;
    status_label: CommentStatusLabel;

    is_site_review: boolean;
    is_deleted_material: boolean;
    commentable: Commentable | null;

    created_at: string;
}

export interface AdminComment extends Comment {
    is_trashed: boolean;
    updated_at: string;
    deleted_at: string | null;
}
