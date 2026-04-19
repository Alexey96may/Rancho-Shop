import type { Media } from './Media';
import type { SeoData } from './Seo';

export type PageType = 'static' | 'blog' | 'news';

export interface Page {
    id: number;

    title: string;
    slug: string;
    content: string | null;

    type: PageType;
    template: string;
    is_active: boolean;

    media?: Media[];

    seo?: SeoData | null;

    reviews_count?: number;
}
