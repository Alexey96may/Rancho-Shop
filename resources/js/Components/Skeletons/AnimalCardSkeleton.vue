<template>
    <article
        class="relative flex flex-col overflow-hidden rounded-3xl border border-rancho-ink-200 bg-white"
        aria-hidden="true"
    >
        <!-- Фото -->
        <div class="relative aspect-[4/5] max-h-48 animate-pulse bg-rancho-ink-100 sm:max-h-64">
            <!-- Бейдж статуса -->
            <div class="absolute left-4 top-4">
                <div class="h-6 w-24 rounded-full bg-rancho-ink-200/70 backdrop-blur-md" />
            </div>

            <!-- Кнопка звука -->
            <div class="absolute bottom-4 right-4 h-12 w-12 rounded-full bg-white/95" />
        </div>

        <!-- Контент -->
        <div class="flex flex-1 flex-col gap-3 p-4 sm:p-6">
            <!-- Имя -->
            <div class="h-6 w-3/4 animate-pulse rounded-md bg-rancho-ink-200" />

            <!-- Bio (3 строки) -->
            <div class="space-y-2">
                <div class="h-3 w-full animate-pulse rounded-md bg-rancho-ink-100" />
                <div class="h-3 w-5/6 animate-pulse rounded-md bg-rancho-ink-100" />
                <div class="h-3 w-2/3 animate-pulse rounded-md bg-rancho-ink-100" />
            </div>

            <!-- Фичи (3 плашки) -->
            <div class="mt-auto flex flex-col gap-2 border-t border-rancho-ink-100 pt-4">
                <div class="h-7 w-full animate-pulse rounded-lg bg-rancho-ink-50" />
                <div class="h-7 w-full animate-pulse rounded-lg bg-rancho-ink-50" />
                <div class="h-7 w-full animate-pulse rounded-lg bg-rancho-ink-50" />
            </div>
        </div>
    </article>
</template>
