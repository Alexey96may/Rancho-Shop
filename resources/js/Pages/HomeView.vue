<script setup lang="ts">
    import { onMounted, onUnmounted } from 'vue';

    import AboutSection from '@/Components/Sections/AboutSection.vue';
    import BestAnimalsSection from '@/Components/Sections/BestAnimalsSection.vue';
    import BestProductSection from '@/Components/Sections/BestProductSection.vue';
    import FaqSection from '@/Components/Sections/FaqSection.vue';
    import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import HowItWorksSection from '@/Components/Sections/HowItWorksSection.vue';
    import ReviewsSection from '@/Components/Sections/ReviewsSection.vue';
    import DayNightScene from '@/Components/UI/DayNightScene.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useAppearanceStore } from '@/stores/useAppearanceStore';
    import type {
        Comment,
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
        comments: ResourceCollection<Comment>;
        how_it_works: ResourceSingle<LandingBlock>;
    }

    defineProps<Props>();

    const store = useAppearanceStore();

    const onScroll = (): void => {
        // Вычисляем прогресс: текущий скролл / (высота всей страницы - высота экрана)
        const totalScrollable = document.documentElement.scrollHeight - window.innerHeight;

        if (totalScrollable <= 0) return;

        const progress = window.scrollY / totalScrollable;
        store.setNightProgress(progress);
    };

    onMounted(() => {
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // Инициализация при загрузке
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', onScroll);
    });
</script>

<template>
    <DayNightScene />

    <HeroSection />
    <BestProductSection :products="products.data" />
    <BestAnimalsSection :animals="cows.data" />
    <FeaturesSection :values="values.data" />
    <HowItWorksSection :block="how_it_works.data" />
    <ReviewsSection :comments="comments.data" />
    <AboutSection :about="about.data" />
    <FaqSection :faqs="faqs.data" />
</template>
