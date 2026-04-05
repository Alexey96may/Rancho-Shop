<script setup lang="ts">
    import { ref } from 'vue';

    import { Howl } from 'howler';
    import { ShoppingBasket, Volume2 } from 'lucide-vue-next';

    import AboutSection from '@/Components/Sections/AboutSection.vue';
    import BestAnimalsSection from '@/Components/Sections/BestAnimalsSection.vue';
    import BestProductSection from '@/Components/Sections/BestProductSection.vue';
    import FaqSection from '@/Components/Sections/FaqSection.vue';
    import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import ReviewsSection from '@/Components/Sections/ReviewsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Cow, Faq, Product } from '@/types';

    defineOptions({ layout: MainLayout });

    interface Props {
        cows: Cow[];
        products: Product[];
        comments: Comment[];
        faq: Faq[];
    }

    const { cows = [], products = [] } = defineProps<Props>();

    const addToCart = (product: Product): void => {
        console.log('В лукошке:', product.name);
    };

    const faqs: Faq[] = [
        {
            id: 1,
            question: 'Почему молоко может менять вкус и жирность?',
            answer: 'Это главный признак натуральности! Летом коровы едят сочную траву, и молоко становится более ароматным. Зимой мы переходим на сено, и жирность естественным образом растет.',
        },
        {
            id: 2,
            question: 'Как работает доставка свежих продуктов?',
            answer: 'Мы собираем заказы до 9:00 утра. После дойки и охлаждения продукты упаковываются в термо-боксы и доставляются курьером прямо к вашей двери в тот же день.',
        },
        {
            id: 3,
            question: 'Нужно ли кипятить ваше молоко?',
            answer: 'Наше молоко проходит бережную пастеризацию (нагрев до 72-75°C), что убивает вредные бактерии, но сохраняет все витамины и ферменты. Оно полностью готово к употреблению без кипячения.',
        },
        {
            id: 4,
            question: 'Принимаете ли вы обратно стеклянную тару?',
            answer: 'Да, мы стремимся к Zero Waste! Вы можете вернуть чистые стеклянные бутылки нашему курьеру при следующем заказе. За каждую возвращенную бутылку мы начислим вам бонусные баллы в корзине.',
        },
        {
            id: 5,
            question: 'Какой срок годности у ваших продуктов?',
            answer: 'Так как мы не используем консерванты, сроки минимальны: молоко хранится до 5 суток, творог и мягкие сыры — до 72 часов, крафтовые твердые сыры — до нескольких месяцев при правильной температуре.',
        },
        {
            id: 6,
            question: 'Почему желток у яиц такой ярко-оранжевый?',
            answer: 'Это результат свободного выгула. Наши птицы гуляют на солнце и едят натуральные корма, богатые каротином (траву, кукурузу), а не синтетические красители, которые часто добавляют на птицефабриках.',
        },
        {
            id: 7,
            question: 'Как узнать, когда мой любимый продукт появится в наличии?',
            answer: 'Вы можете подписаться на уведомления на странице товара или следить за статусами наших животных. Например, если корова Зорька в «отпуске», молоко от неё временно недоступно.',
        },
        {
            id: 8,
            question: 'Что делать, если продукт приехал поврежденным?',
            answer: 'Мы гарантируем свежесть! Если упаковка нарушена или качество вас не устроило — просто сделайте фото и напишите нам в Telegram в течение 2 часов после доставки. Мы вернем деньги или заменим товар бесплатно.',
        },
        {
            id: 9,
            question: 'Ваши животные проходят ветеринарный контроль?',
            answer: 'Безусловно. Всё поголовье находится под строгим надзором ветеринара, регулярно сдает анализы и проходит вакцинацию. Безопасность наших гостей и здоровье животных — наш главный приоритет.',
        },
        {
            id: 10,
            question: 'Можно ли приехать на ферму на экскурсию?',
            answer: 'Мы всегда рады гостям! Раз в месяц мы проводим «День открытых ворот», где можно познакомиться с нашими подопечными и увидеть процесс производства своими глазами. Следите за анонсами в блоге.',
        },
        {
            id: 11,
            question: 'Есть ли в ваших продуктах добавленный сахар?',
            answer: 'Принципиально нет. Мы исключили рафинированный сахар из всех линеек — от йогуртов до десертов. Сладость в наших продуктах только естественная (из фруктов, ягод или самого молока).',
        },
        {
            id: 12,
            question: 'Почему у сыра иногда бывает неровная корочка?',
            answer: 'Наши сыры созревают в натуральной корке. Небольшие неровности или пятнышки — это работа благородной плесени и результат ручного переворачивания каждой головки в камере созревания.',
        },
    ];
</script>

<template>
    <HeroSection></HeroSection>
    <BestProductSection :products="products"></BestProductSection>
    <BestAnimalsSection :animals="cows"></BestAnimalsSection>
    <FeaturesSection />
    <ReviewsSection :comments="comments" />
    <AboutSection />
    <FaqSection :faqs="faqs" />
</template>
