<script setup lang="ts">
    import { computed } from 'vue';

    import { useAppearanceStore } from '@/stores/useAppearanceStore';

    const store = useAppearanceStore();

    // Вычисляем положение небесных тел
    const sunTransform = computed(() => `translateY(${store.nightProgress * 150}%)`);
    const moonTransform = computed(() => `translateY(${(1 - store.nightProgress) * 150}%)`);
</script>

<template>
    <div class="sky-container">
        <div class="sky day-sky"></div>

        <div class="sky night-sky" :style="{ opacity: store.nightProgress }"></div>

        <div
            class="celestial sun"
            :style="{ transform: sunTransform, opacity: 1 - store.nightProgress }"
        ></div>

        <div
            class="celestial moon"
            :style="{ transform: moonTransform, opacity: store.nightProgress }"
        ></div>

        <div
            class="stars"
            :style="{ opacity: store.nightProgress > 0.5 ? (store.nightProgress - 0.5) * 2 : 0 }"
        >
            <div
                v-for="n in 30"
                :key="n"
                class="star"
                :style="{
                    top: `${Math.random() * 50}%`,
                    left: `${Math.random() * 100}%`,
                    animationDelay: `${Math.random() * 3}s`,
                }"
            ></div>
        </div>
    </div>
</template>

<style scoped>
    .sky-container {
        position: fixed;
        inset: 0;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }

    .sky {
        position: absolute;
        inset: 0;
        transition: opacity 0.1s linear;
    }

    .day-sky {
        background: linear-gradient(to bottom, #73c8ef, #b3e0f2);
    }

    .night-sky {
        background: linear-gradient(to bottom, #131836, #293462);
    }

    .celestial {
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        top: 10%;
        left: 15%;
        will-change: transform, opacity;
    }

    .sun {
        background: #fdfc97;
        box-shadow: 0 0 50px #fdfc97;
    }

    .moon {
        background: #d9d9d9;
        box-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
    }

    .star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: white;
        border-radius: 50%;
        animation: twinkle 2s infinite ease-in-out;
    }

    @keyframes twinkle {
        0%,
        100% {
            opacity: 0.3;
            transform: scale(0.8);
        }
        50% {
            opacity: 1;
            transform: scale(1.2);
        }
    }
</style>
