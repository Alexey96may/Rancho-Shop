<script setup lang="ts">
    import { type PropType, onMounted, onUnmounted } from 'vue';

    import AboutSection from '@/Components/Sections/AboutSection.vue';
    import BestAnimalsSection from '@/Components/Sections/BestAnimalsSection.vue';
    import BestProductSection from '@/Components/Sections/BestProductSection.vue';
    import FaqSection from '@/Components/Sections/FaqSection.vue';
    import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import HowItWorksSection from '@/Components/Sections/HowItWorksSection.vue';
    import ReviewsSection from '@/Components/Sections/ReviewsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useAppearanceStore } from '@/stores/useAppearanceStore';
    import type {
        Animal,
        Comment,
        Faq,
        LandingBlock,
        Product,
        ResourceCollection,
        ResourceSingle,
    } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        cows: {
            type: Object as PropType<ResourceCollection<Animal>>,
            required: true,
            validator: (value: ResourceCollection<Animal>) =>
                Boolean(value && Array.isArray(value.data)),
        },
        products: {
            type: Object as PropType<ResourceCollection<Product>>,
            required: true,
            validator: (value: ResourceCollection<Product>) =>
                Boolean(value && Array.isArray(value.data)),
        },
        faqs: {
            type: Object as PropType<ResourceCollection<Faq>>,
            required: true,
            validator: (value: ResourceCollection<Faq>) =>
                Boolean(value && Array.isArray(value.data)),
        },
        comments: {
            type: Object as PropType<ResourceCollection<Comment>>,
            required: true,
            validator: (value: ResourceCollection<Comment>) =>
                Boolean(value && Array.isArray(value.data)),
        },
        about: {
            type: Object as PropType<ResourceSingle<LandingBlock>>,
            required: true,
            validator: (value: ResourceSingle<LandingBlock>) =>
                Boolean(value && typeof value === 'object' && value.data),
        },
        values: {
            type: Object as PropType<ResourceSingle<LandingBlock>>,
            required: true,
            validator: (value: ResourceSingle<LandingBlock>) =>
                Boolean(value && typeof value === 'object' && value.data),
        },
        how_it_works: {
            type: Object as PropType<ResourceSingle<LandingBlock>>,
            required: true,
            validator: (value: ResourceSingle<LandingBlock>) =>
                Boolean(value && typeof value === 'object' && value.data),
        },
    });

    const store = useAppearanceStore();

    const onScroll = (): void => {
        if (typeof document === 'undefined') return;
        const totalScrollable = document.documentElement.scrollHeight - window.innerHeight;

        if (totalScrollable <= 0) return;

        const progress = window.scrollY / totalScrollable;
        store.setNightProgress(progress);
    };

    onMounted(() => {
        if (typeof window === 'undefined') return;
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });

    onUnmounted(() => {
        if (typeof window === 'undefined') return;
        window.removeEventListener('scroll', onScroll);
    });
</script>

<template>
    <div>
        <HeroSection v-scroll-reveal />
        <BestProductSection v-scroll-reveal v-if="products.data.length" :products="products.data" />
        <BestAnimalsSection v-scroll-reveal v-if="cows.data.length" :animals="cows.data" />
        <FeaturesSection v-scroll-reveal v-if="values.data" :values="values.data" />
        <HowItWorksSection v-scroll-reveal v-if="how_it_works.data" :block="how_it_works.data" />
        <ReviewsSection v-scroll-reveal :comments="comments.data" />
        <AboutSection v-scroll-reveal v-if="about.data" :about="about.data" />
        <FaqSection v-scroll-reveal v-if="faqs.data.length" :faqs="faqs.data" />
    </div>
</template>
