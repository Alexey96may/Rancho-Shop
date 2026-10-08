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
        const totalScrollable = document.documentElement.scrollHeight - window.innerHeight;

        if (totalScrollable <= 0) return;

        const progress = window.scrollY / totalScrollable;
        store.setNightProgress(progress);
    };

    onMounted(() => {
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', onScroll);
    });
</script>

<template>
    <div>
        <HeroSection />
        <BestProductSection v-if="products.data.length" :products="products.data" />
        <BestAnimalsSection v-if="cows.data.length" :animals="cows.data" />
        <FeaturesSection v-if="values.data" :values="values.data" />
        <HowItWorksSection v-if="how_it_works.data" :block="how_it_works.data" />
        <ReviewsSection :comments="comments.data" />
        <AboutSection v-if="about.data" :about="about.data" />
        <FaqSection v-if="faqs.data.length" :faqs="faqs.data" />
    </div>
</template>
