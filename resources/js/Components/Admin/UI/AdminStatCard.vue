<script setup lang="ts">
    import { computed } from 'vue';

    interface Props {
        label: string;
        value: string | number;
        isAccent?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        isAccent: false,
    });

    const labelId = `stat-label-${Math.random().toString(36).slice(2, 9)}`;

    const cardClasses = computed(() =>
        props.isAccent ? 'border-orange-600/20 bg-orange-600/10' : 'border-slate-800 bg-slate-900',
    );

    const labelClasses = computed(() => (props.isAccent ? 'text-orange-500' : 'text-slate-500'));

    const valueClasses = computed(() => (props.isAccent ? 'text-orange-500' : 'text-white'));
</script>

<template>
    <section
        :class="['rounded-3xl border p-6 transition-all hover:scale-[1.02]', cardClasses]"
        :aria-labelledby="labelId"
    >
        <p :id="labelId" :class="['text-xs font-bold uppercase tracking-wider', labelClasses]">
            {{ label }}
        </p>
        <p :class="['mt-1 text-3xl font-black', valueClasses]">
            {{ value }}
        </p>

        <span class="sr-only"> Текущий показатель для {{ label }}: {{ value }} </span>
    </section>
</template>
