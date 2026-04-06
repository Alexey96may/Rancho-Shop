import type { Media } from './Media';
import type { SeoData } from './Seo';

export type CowStatus = 'pasture' | 'rest' | 'home' | 'vacation' | 'retired' | 'memorial';
export type CatStatus = 'hunting' | 'sleeping' | 'home' | 'memorial';
export type BirdStatus = 'nest' | 'pasture' | 'home';

export interface BaseAnimal {
    id: number;
    parent_id: number | null;
    name: string;
    status: CowStatus | CatStatus | BirdStatus;
    slug: string;
    bio: string | null;
    media: Media[];
    features: Record<string, string> | null;

    voice_url: string | null;

    parent?: {
        name: string;
        slug: string;
    } | null;

    children?: Array<{
        name: string;
        slug: string;
    }>;

    seo?: SeoData | null;
}

export interface Cow extends BaseAnimal {
    type: 'cow';
    status: CowStatus;
    features: {
        fat_content?: string;
        daily_yield?: string;
    } | null;
}

export interface Cat extends BaseAnimal {
    type: 'cat';
    status: CatStatus;
}

export type FarmAnimal = Cow | Cat | BaseAnimal;

export type AdminFarmAnimal = (Cow | Cat | BaseAnimal) & {
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};
