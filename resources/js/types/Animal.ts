import type { Attribute } from './';
import type { Media } from './Media';
import type { SeoData } from './Seo';

export type AnimalType = 'cow' | 'chicken' | 'goose' | 'turkey' | 'cat';

interface Timestamps {
    created_at: string;
    updated_at: string;
}

export type AnimalFeature = Attribute;

export interface Animal {
    id: number;
    name: string;
    slug: string;
    status: string;
    bio: string | null;
    features: AnimalFeature[];
    voice_url: string | null;
    avatars: Media[];
    gallery: Media[] | null;
    is_commentable: boolean;
    category?: {
        name: string;
        slug: string;
    };
    family?: {
        parent?: { name: string; slug: string };
        children: Array<{ name: string; slug: string }>;
    };
    products?: Array<any>;
    seo?: SeoData;
}

export interface AdminAnimal extends Animal, Timestamps {
    parent_id: number | null;
    category_id: number;
    deleted_at: string;
    is_active: boolean;
    is_trashed: boolean;
    media: Media[];
}
