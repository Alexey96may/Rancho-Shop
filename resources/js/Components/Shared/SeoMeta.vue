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

        if (path.startsWith('/admin')) {
            return '/images/favicon-admin.svg';
        }

        const cabinetPrefixes = ['/profile', '/dashboard', '/my-orders', '/account'];
        const isCabinet = cabinetPrefixes.some((prefix) => path.startsWith(prefix));

        if (isCabinet) {
            return '/images/favicon-cabinet.svg';
        }

        return '/images/favicon-main.svg';
    });

    const getSeoData = () => {
        const fromController = page.props.seo as SeoData;
        const settings = page.props.settings as SiteSettings;

        return {
            title:
                props.seo?.title || fromController?.title || settings?.site_name || 'Planto Shop',
            description:
                props.seo?.description ||
                fromController?.description ||
                'Welcome to the future of plants.',
            robots:
                props.forceRobots || props.seo?.robots || fromController?.robots || 'index, follow',
            image: props.seo?.image || fromController?.image || '/images/og-default.png',
            canonical: props.seo?.canonical || fromController?.canonical || '',
            keywords: props.seo?.keywords || fromController?.keywords || '',
        };
    };

    const displayTitle = ref(getSeoData().title);
    const currentSeo = computed(() => getSeoData());

    watch(
        () => currentSeo.value.title,
        (newTitle) => {
            setTimeout(() => {
                displayTitle.value = newTitle;
            }, 50);
        },
    );
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

        <meta property="og:title" :content="currentSeo.title" head-key="og:title" />
        <meta
            property="og:description"
            :content="currentSeo.description"
            head-key="og:description"
        />
        <meta property="og:image" :content="currentSeo.image" head-key="og:image" />
        <meta property="og:type" content="website" head-key="og:type" />
        <meta property="og:url" :content="currentSeo.canonical || ''" head-key="og:url" />

        <meta name="twitter:card" content="summary_large_image" head-key="twitter:card" />
        <meta name="twitter:title" :content="currentSeo.title" head-key="twitter:title" />
        <meta
            name="twitter:description"
            :content="currentSeo.description"
            head-key="twitter:description"
        />
        <meta name="twitter:image" :content="currentSeo.image" head-key="twitter:image" />
    </Head>
</template>
