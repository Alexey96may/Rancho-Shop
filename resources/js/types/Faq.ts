export interface Faq {
    id: number;
    question: string;
    answer: string;
}

export interface AdminFaq extends Faq {
    is_published: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}
