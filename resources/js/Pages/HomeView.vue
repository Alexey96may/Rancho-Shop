<script setup lang="ts">
    import AboutSection from '@/Components/Sections/AboutSection.vue';
    import BestAnimalsSection from '@/Components/Sections/BestAnimalsSection.vue';
    import BestProductSection from '@/Components/Sections/BestProductSection.vue';
    import FaqSection from '@/Components/Sections/FaqSection.vue';
    import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import ReviewsSection from '@/Components/Sections/ReviewsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Cow, Faq, Product, ResourceCollection } from '@/types';

    defineOptions({ layout: MainLayout });

    interface Props {
        cows: ResourceCollection<Cow>;
        products: ResourceCollection<Product>;
        comments: ResourceCollection<Comment>;
        faqs: ResourceCollection<Faq>;
    }

    defineProps<Props>();
</script>

<template>
    <HeroSection></HeroSection>
    <BestProductSection :products="products.data"></BestProductSection>
    <BestAnimalsSection :animals="cows.data"></BestAnimalsSection>
    <FeaturesSection />
    <ReviewsSection :comments="[]" />
    <AboutSection />
    <FaqSection :faqs="faqs.data" />
</template>
