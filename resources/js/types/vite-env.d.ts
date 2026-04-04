/// <reference types="vite/client" />
// resources/js/types/global.d.ts

declare module '*.css' {
    const content: { [className: string]: string };
    export default content;
}

declare module 'swiper/css';
declare module 'swiper/css/navigation';
declare module 'swiper/css/pagination';
declare module 'swiper/css/effect-fade';
