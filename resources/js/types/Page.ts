import type { Comment } from './Comment';
import type { SeoData } from './Seo';

interface BasePage {
    title: string;
    slug: string;
    content: string | null;
    type: 'static' | 'blog' | 'news';
}

export interface PublicPage extends BasePage {
    seo?: SeoData;
    reviews?: Comment[];
    published_at: string;
}

export interface AdminPage extends BasePage {
    id: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;

    reviews_count: number;

    seo?: SeoData;
}
