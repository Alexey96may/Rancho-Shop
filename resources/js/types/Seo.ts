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
    robots: string;
    canonical: string | null;
    og_data: OpenGraphData;
    image?: string | null;
    is_noindex: boolean;

    json_ld: Record<string, any>;
}

// Type for models that have SEO (eg Product or Animal)
export interface HasSeo {
    seo?: SeoData | null;
}

// <Head>
//     <title>{{ seo.title }}</title>
//     <component
//         is="script"
//         type="application/ld+json"
//         v-if="seo.json_ld"
//     >
//         {{ JSON.stringify(seo.json_ld) }}
//     </component>
// </Head>
