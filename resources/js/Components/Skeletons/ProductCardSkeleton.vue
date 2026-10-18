<template>
    <div
        class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-rancho-ink-200 bg-white"
        aria-hidden="true"
    >
        <!-- Фото -->
        <div class="relative aspect-square max-h-48 animate-pulse bg-rancho-ink-100 sm:max-h-64">
            <!-- Бейдж статуса -->
            <div class="absolute left-3 top-3">
                <div class="h-6 w-24 rounded-lg bg-rancho-ink-200/70" />
            </div>
        </div>

        <!-- Контент -->
        <div class="flex flex-grow flex-col gap-6 p-5">
            <!-- Категория + название -->
            <div>
                <!-- Категория -->
                <div class="mb-1 h-3 w-20 animate-pulse rounded-md bg-rancho-ink-100" />

                <!-- Название (две строки для реалистичности) -->
                <div class="space-y-2">
                    <div class="h-5 w-full animate-pulse rounded-md bg-rancho-ink-200" />
                    <div class="h-5 w-2/3 animate-pulse rounded-md bg-rancho-ink-200" />
                </div>
            </div>

            <!-- Цена -->
            <div class="flex items-center gap-3">
                <!-- Текущая цена -->
                <div class="h-7 w-24 animate-pulse rounded-md bg-rancho-ink-200" />

                <!-- Старая цена + скидка -->
                <div class="flex flex-col gap-1">
                    <div class="h-3 w-16 animate-pulse rounded-md bg-rancho-ink-100" />
                    <div class="h-3 w-8 animate-pulse rounded-md bg-rancho-ink-100" />
                </div>

                <!-- Единица измерения (справа) -->
                <div class="ml-auto h-4 w-10 animate-pulse rounded-md bg-rancho-ink-100" />
            </div>

            <!-- Кнопка -->
            <div class="mt-auto">
                <div class="h-12 w-full animate-pulse rounded-xl bg-rancho-ink-200" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts"></script>
>
