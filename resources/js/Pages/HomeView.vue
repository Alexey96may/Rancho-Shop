<script setup lang="ts">
    import AboutSection from '@/Components/Sections/AboutSection.vue';
    import BestAnimalsSection from '@/Components/Sections/BestAnimalsSection.vue';
    import BestProductSection from '@/Components/Sections/BestProductSection.vue';
    import FaqSection from '@/Components/Sections/FaqSection.vue';
    import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import HowItWorksSection from '@/Components/Sections/HowItWorksSection.vue';
    import ReviewsSection from '@/Components/Sections/ReviewsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type {
        Cow,
        Faq,
        LandingBlock,
        Product,
        ResourceCollection,
        ResourceSingle,
    } from '@/types';

    defineOptions({ layout: MainLayout });

    interface Props {
        cows: ResourceCollection<Cow>;
        products: ResourceCollection<Product>;
        faqs: ResourceCollection<Faq>;
        about: ResourceSingle<LandingBlock>;
        values: ResourceSingle<LandingBlock>;
        how_it_works: ResourceSingle<LandingBlock>;
    }

    defineProps<Props>();
</script>

<template>
    <HeroSection></HeroSection>
    <BestProductSection :products="products.data"></BestProductSection>
    <BestAnimalsSection :animals="cows.data"></BestAnimalsSection>
    <FeaturesSection :values="values.data" />
    <HowItWorksSection :block="how_it_works.data" />
    <ReviewsSection :comments="[]" />
    <AboutSection :about="about.data" />
    <FaqSection :faqs="faqs.data" />
</template>
