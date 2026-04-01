export interface OpenGraphData {
    title?: string;
    description?: string;
    image?: string;
    type?: 'website' | 'article' | 'product';
    url?: string;
}

export interface SeoData {
    id?: number;
    title: string | null;
    description: string | null;
    keywords: string | null;
    og_data: OpenGraphData | null;
    is_noindex: boolean;
    // Поля для полиморфной связи (на всякий случай)
    seoable_id?: number;
    seoable_type?: string;
}

// Тип для моделей, у которых есть SEO (например, Product или Animal)
export interface HasSeo {
    seo?: SeoData | null;
}
