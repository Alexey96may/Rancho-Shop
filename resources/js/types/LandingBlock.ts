export enum LandingBlockKey {
    VALUES = 'values',
    ABOUT = 'about',
    HOW_IT_WORKS = 'how_it_works',
}

export interface LandingContentItem {
    title: string;
    desc: string;
    icon?: string | null;
    step?: number | null;
}

export interface LandingBlock {
    key: LandingBlockKey;
    title: string;
    subtitle: string;
    content: LandingContentItem[];
}

export interface AdminLandingBlock extends LandingBlock {
    id: number;
    is_visible: boolean;
    label: string;
    updated_at: string;
}
