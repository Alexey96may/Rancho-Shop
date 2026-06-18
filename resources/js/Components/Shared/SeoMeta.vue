<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { Head, usePage } from '@inertiajs/vue3';

    import { SeoData, SharedData, SiteSettings } from '@/types';

    interface SeoProps {
        seo?: SeoData;
        forceRobots?: string;
    }

    const props = defineProps<SeoProps>();
    const page = usePage<SharedData>();

    const faviconPath = computed(() => {
        const path = page.url.toLowerCase();

        if (path.startsWith('/admin')) return '/images/logo/favicon-admin.svg';

        const cabinetPrefixes = ['/profile', '/dashboard', '/my-orders', '/account'];
        if (cabinetPrefixes.some((prefix) => path.startsWith(prefix))) {
            return '/images/logo/favicon-cabinet.svg';
        }

        return '/images/logo/favicon-main.svg';
    });

    const getSeoData = () => {
        const fromController = page.props.seo as SeoData;
        const settings = page.props.settings as SiteSettings;

        return {
            title:
                props.seo?.title ||
                fromController?.title ||
                settings?.site_name ||
                'Молочна Долина',
            description:
                props.seo?.description ||
                fromController?.description ||
                'Добро пожаловать в крымский магазин молочной продукции.',
            keywords: props.seo?.keywords || fromController?.keywords || '',
            robots:
                props.forceRobots || props.seo?.robots || fromController?.robots || 'index, follow',
            canonical: props.seo?.canonical || fromController?.canonical || '',
            image: props.seo?.image || fromController?.image || '/images/og-default-rancho.jpg',

            og: props.seo?.og_data || fromController?.og_data || null,
            jsonLd: props.seo?.json_ld || fromController?.json_ld || null,
        };
    };

    const currentSeo = computed(() => getSeoData());
</script>

<template>
    <Head>
        <title>{{ currentSeo.title }}</title>

        <link rel="icon" type="image/svg+xml" :href="faviconPath" head-key="favicon" />

        <link
            v-if="currentSeo.canonical"
            rel="canonical"
            :href="currentSeo.canonical"
            head-key="canonical"
        />

        <meta name="description" :content="currentSeo.description" head-key="description" />
        <meta
            v-if="currentSeo.keywords"
            name="keywords"
            :content="currentSeo.keywords"
            head-key="keywords"
        />

        <meta name="robots" :content="currentSeo.robots" head-key="robots" />

        <meta
            property="og:title"
            :content="currentSeo.og?.title || currentSeo.title"
            head-key="og:title"
        />
        <meta
            property="og:description"
            :content="currentSeo.og?.description || currentSeo.description"
            head-key="og:description"
        />
        <meta
            property="og:image"
            :content="currentSeo.og?.image || currentSeo.image"
            head-key="og:image"
        />
        <meta property="og:type" :content="currentSeo.og?.type || 'website'" head-key="og:type" />
        <meta
            property="og:url"
            :content="currentSeo.og?.url || currentSeo.canonical || ''"
            head-key="og:url"
        />

        <meta name="twitter:card" content="summary_large_image" head-key="twitter:card" />
        <meta
            name="twitter:title"
            :content="currentSeo.og?.title || currentSeo.title"
            head-key="twitter:title"
        />
        <meta
            name="twitter:description"
            :content="currentSeo.og?.description || currentSeo.description"
            head-key="twitter:description"
        />
        <meta
            name="twitter:image"
            :content="currentSeo.og?.image || currentSeo.image"
            head-key="twitter:image"
        />

        <component is="script" v-if="currentSeo.jsonLd" type="application/ld+json">
            {{ JSON.stringify(currentSeo.jsonLd) }}
        </component>
    </Head>
</template>
