<script setup lang="ts">
    withDefaults(
        defineProps<{
            lines?: number;
        }>(),
        {
            lines: 2,
        },
    );
</script>

<template>
    <article
        class="shadow-sm rounded-2xl border border-slate-200/60 bg-white p-6"
        aria-hidden="true"
    >
        <header
            class="flex flex-col-reverse gap-6 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <!-- Аватар -->
                <div class="h-10 w-10 shrink-0 animate-pulse rounded-full bg-slate-200"></div>

                <div class="space-y-2">
                    <!-- Имя -->
                    <div class="h-3.5 w-28 animate-pulse rounded bg-slate-200"></div>
                    <!-- Дата -->
                    <div class="h-3 w-20 animate-pulse rounded bg-slate-200/70"></div>
                </div>
            </div>

            <!-- Рейтинг -->
            <div class="flex items-center gap-1 self-end sm:self-auto">
                <div
                    v-for="i in 5"
                    :key="i"
                    class="h-4 w-4 animate-pulse rounded-sm bg-slate-200"
                ></div>
            </div>
        </header>

        <!-- Текст отзыва -->
        <div class="mt-4 space-y-2">
            <div
                v-for="i in lines"
                :key="i"
                class="h-3.5 animate-pulse rounded bg-slate-200"
                :class="i === lines ? 'w-2/3' : 'w-full'"
            ></div>
        </div>
    </article>
</template>
