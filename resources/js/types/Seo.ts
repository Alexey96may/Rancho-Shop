export interface OpenGraphData {
    title?: string | null;
    description?: string | null;
    image?: string | null;
    type?: 'website' | 'article' | 'product';
    url?: string | null;
}

export interface SeoData {
    id: number;
    title: string | null;
    description: string | null;
    keywords: string | null;
    robots?: string | null; //add
    image?: string | null; //add
    canonical: string | null;
    og_data: OpenGraphData | null;
    is_noindex: boolean;
}

// Тип для моделей, у которых есть SEO (например, Product или Animal)
export interface HasSeo {
    seo?: SeoData | null;
}
