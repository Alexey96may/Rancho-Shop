<script setup lang="ts">
    import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

    interface Props {
        /** Уровень воды в % (0–100) */
        percent: number;
        /** Цвет поверхности (hex) */
        color?: string;
        /** Цвет глубины (hex, для градиента) */
        deepColor?: string;
        /** Скорость анимации (1 = норма, 0.5 = медленнее, 2 = быстрее) */
        speed?: number;
        /** Амплитуда волны в px */
        amplitude?: number;
    }

    const props = withDefaults(defineProps<Props>(), {
        color: '#7AB89A', // rancho-mint
        deepColor: '#3B7558', // rancho-pine
        speed: 1,
        amplitude: 6,
    });

    const canvas = ref<HTMLCanvasElement | null>(null);
    const container = ref<HTMLDivElement | null>(null);

    let animationFrame: number | null = null;
    let resizeObserver: ResizeObserver | null = null;
    let startTime = 0;

    /** Отрисовка одного кадра */
    function draw(timestamp: number) {
        const cv = canvas.value;
        const ctn = container.value;
        if (!cv || !ctn) return;

        const ctx = cv.getContext('2d');
        if (!ctx) return;

        // DPR для retina
        const dpr = window.devicePixelRatio || 1;
        const w = ctn.clientWidth;
        const h = ctn.clientHeight;

        if (cv.width !== w * dpr || cv.height !== h * dpr) {
            cv.width = w * dpr;
            cv.height = h * dpr;
            cv.style.width = `${w}px`;
            cv.style.height = `${h}px`;
            ctx.scale(dpr, dpr);
        } else {
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(dpr, dpr);
        }

        // Время
        if (!startTime) startTime = timestamp;
        const elapsed = ((timestamp - startTime) / 1000) * props.speed;

        // Уровень воды: percent% от высоты (снизу)
        const waterLevel = h * (1 - props.percent / 100);

        // Очистка
        ctx.clearRect(0, 0, w, h);

        // === ЗАЛИВКА С ГРАДИЕНТОМ ===
        const gradient = ctx.createLinearGradient(0, h, 0, waterLevel);
        gradient.addColorStop(0, props.deepColor + 'CC'); // 80% у дна
        gradient.addColorStop(0.7, props.color + '99'); // 60% в середине
        gradient.addColorStop(1, props.color + '33'); // 20% у поверхности ← приглушено

        // === ВОЛНА (два слоя) ===
        // 1. Задняя волна (медленнее, темнее)
        drawWave(ctx, w, h, waterLevel, elapsed, props.color + '4D', 0.6, props.amplitude * 0.7);

        // 2. Передняя волна (быстрее, светлее)
        drawWave(ctx, w, h, waterLevel, elapsed, props.color + '80', 1.2, props.amplitude);

        // === ЗАПОЛНЕНИЕ ПОД ВОЛНОЙ ===
        ctx.beginPath();
        drawWavePath(ctx, w, waterLevel, elapsed, 1.2, props.amplitude);
        ctx.lineTo(w, h);
        ctx.lineTo(0, h);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        animationFrame = requestAnimationFrame(draw);
    }

    /** Построение пути волны (для заливки) */
    function drawWavePath(
        ctx: CanvasRenderingContext2D,
        w: number,
        y: number,
        t: number,
        freq: number,
        amp: number,
    ) {
        ctx.beginPath();
        ctx.moveTo(0, y);

        const waves = 3;
        const step = 4;
        for (let x = 0; x <= w; x += step) {
            const phase = (x / w) * Math.PI * 2 * waves;
            const yOffset = Math.sin(phase - t * freq) * amp;
            ctx.lineTo(x, y + yOffset);
        }
    }

    /** Рисование полупрозрачной волны (для слоёв) */
    function drawWave(
        ctx: CanvasRenderingContext2D,
        w: number,
        h: number,
        y: number,
        t: number,
        color: string,
        freq: number,
        amp: number,
    ) {
        ctx.beginPath();
        ctx.moveTo(0, y);

        const waves = 3;
        const step = 4;
        for (let x = 0; x <= w; x += step) {
            const phase = (x / w) * Math.PI * 2 * waves;
            const yOffset = Math.sin(phase - t * freq) * amp;
            ctx.lineTo(x, y + yOffset);
        }

        ctx.lineTo(w, h);
        ctx.lineTo(0, h);
        ctx.closePath();
        ctx.fillStyle = color;
        ctx.fill();
    }

    /** Запуск */
    function start() {
        if (animationFrame !== null) return;
        animationFrame = requestAnimationFrame(draw);
    }

    /** Остановка */
    function stop() {
        if (animationFrame !== null) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }
    }

    onMounted(() => {
        start();

        // Следим за размером контейнера
        if (typeof ResizeObserver !== 'undefined' && container.value) {
            resizeObserver = new ResizeObserver(() => {
                // просто перерисуется на след. кадре
            });
            resizeObserver.observe(container.value);
        }
    });

    onBeforeUnmount(() => {
        stop();
        resizeObserver?.disconnect();
        resizeObserver = null;
    });

    /** Если percent меняется — просто перерисуем (анимация сама подхватит) */
    watch(
        () => props.percent,
        () => {
            // ничего делать не надо, draw() использует props.percent каждый кадр
        },
    );
</script>

<template>
    <div ref="container" class="absolute inset-0 overflow-hidden" aria-hidden="true">
        <canvas ref="canvas" class="block h-full w-full"></canvas>
    </div>
</template>
