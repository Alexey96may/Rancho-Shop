import type { Media } from './Media';
import type { SeoData } from './Seo';

export type AnimalType = 'cow' | 'chicken' | 'goose' | 'turkey' | 'cat';

export type CowStatus = 'pasture' | 'rest' | 'home' | 'vacation' | 'retired' | 'memorial';
export type CatStatus = 'hunting' | 'sleeping' | 'home' | 'memorial';
export type BirdStatus = 'nest' | 'pasture' | 'home';

export interface BaseAnimal {
    id: number;
    parent_id: number | null;
    name: string;
    type: AnimalType;
    status: string;
    slug: string;
    bio: string | null;
    media: Media[]; //через Спати
    features: Record<string, string> | null;

    // Хелперы для удобства (можно формировать в Resource)
    main_image?: string;
    voice_url?: string | null; // Прямая ссылка на mp3

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
    // Тут могут быть поля, уникальные только для коров (например, жирность молока в features)
}

export interface Cat extends BaseAnimal {
    type: 'cat';
    status: CatStatus;
    // Коты не дают молоко, но у них может быть поле "ловит мышей: да/нет"
}

export type FarmAnimal = Cow | Cat | BaseAnimal;

export type AdminFarmAnimal = (Cow | Cat | BaseAnimal) & {
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};
