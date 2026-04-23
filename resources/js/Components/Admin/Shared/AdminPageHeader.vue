<script setup lang="ts">
    import { PlusIcon } from '@heroicons/vue/24/outline';

    interface Props {
        titleNormal: string;
        titleOrange: string;
        subtitle: string;
        buttonText?: string;
    }

    defineProps<Props>();

    const emit = defineEmits<{
        (e: 'action'): void;
    }>();
</script>

<template>
    <header
        class="mb-12 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between"
        role="banner"
    >
        <div>
            <h1 class="text-4xl font-black uppercase tracking-tighter text-white" aria-level="1">
                {{ titleNormal }}
                <span class="text-orange-500" aria-hidden="true">{{ titleOrange }}</span>
                <span class="sr-only">{{ titleOrange }}</span>
            </h1>

            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                {{ subtitle }}
            </p>
        </div>

        <nav
            class="flex items-center gap-3"
            :aria-label="`Инструменты управления: ${titleNormal} ${titleOrange}`"
        >
            <slot name="actions">
                <button
                    v-if="buttonText"
                    type="button"
                    @click="emit('action')"
                    class="flex h-[54px] items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-xs font-black uppercase tracking-[0.15em] text-white transition-all hover:bg-orange-500 hover:shadow-[0_0_20px_rgba(234,88,12,0.3)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-500 active:scale-95"
                    :aria-label="`${buttonText}: ${titleNormal} ${titleOrange}`"
                >
                    <PlusIcon class="h-5 w-5 stroke-[3px]" aria-hidden="true" />
                    <span>{{ buttonText }}</span>
                </button>
            </slot>
        </nav>
    </header>
</template>
