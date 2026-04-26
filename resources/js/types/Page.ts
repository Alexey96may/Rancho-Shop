import type { Media } from './Media';
import type { SeoData } from './Seo';

export type PageType = 'default' | 'system' | 'contacts' | 'home';

export interface Page {
    id: number;
    title: string;
    slug: string;
    content?: string;
    template: string;
    url: string;
    media: Media[];
    seo?: SeoData;
    reviews_count?: number;
}

export interface AdminPage extends Page {
    type: PageType;
    type_label: string;
    type_color: string;
    is_active: boolean;
    can_delete: boolean;
    created_at: string; // ISO 8601
    updated_at: string; // ISO 8601
}
