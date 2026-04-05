export interface LandingContentItem {
    title: string;
    desc: string;
    icon?: string;
    step?: number;
}

export interface LandingBlock {
    id: number;
    key: 'values' | 'how_it_works' | 'about';
    title: string;
    subtitle: string | null;
    content: LandingContentItem[];
}
