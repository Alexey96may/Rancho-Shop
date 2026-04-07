type CommentType = 'animal' | 'product' | 'page'; // keys from MorphMap

export interface Comment {
    id: number;
    user_name: string;
    content: string;
    rating: number | null; // From 1 to 5

    // (from $table->nullableMorphs)
    commentable_id: number | null;
    commentable_type: CommentType;

    created_at: string;
}

export interface AdminComment extends Comment {
    is_published: boolean;
    updated_at: string;

    commentable?: {
        name: string;
        slug: string;
        type: CommentType;
    };
}
