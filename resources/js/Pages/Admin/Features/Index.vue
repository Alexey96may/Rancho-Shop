<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, Link, router } from '@inertiajs/vue3';

    import {
        ChevronRightIcon,
        EyeIcon,
        EyeOffIcon,
        FilterIcon,
        LayersIcon,
        Settings2Icon,
    } from 'lucide-vue-next';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        blocks: { data: any[] };
        filters: any;
    }>();

    const activeFilter = ref('all');

    // Фильтрация
    const filterKeys = computed(() => {
        const keys = props.blocks.data.map((b) => b.key);
        return ['all', ...new Set(keys)];
    });

    const filteredBlocks = computed(() => {
        if (activeFilter.value === 'all') return props.blocks.data;
        return props.blocks.data.filter((b) => b.key === activeFilter.value);
    });

    const toggleVisibility = (id: number) => {
        router.patch(
            route('admin.features.toggle', id),
            {},
            {
                preserveScroll: true,
            },
        );
    };
</script>

<template>
    <Head title="Конструктор Лендинга" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500"
                >
                    <LayersIcon class="h-6 w-6" />
                </div>
                <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                    Секции Лендинга
                </h1>
            </div>
        </template>

        <div class="animate-in fade-in slide-in-from-bottom-4 space-y-8 duration-700">
            <div
                class="flex w-fit flex-wrap items-center gap-3 rounded-[1.5rem] border border-slate-800 bg-slate-900/40 p-2"
            >
                <div
                    class="mr-1 flex items-center gap-2 border-r border-slate-800 px-3 text-slate-500"
                >
                    <FilterIcon class="h-4 w-4" />
                    <span class="text-[10px] font-black uppercase tracking-widest">Ключи:</span>
                </div>
                <button
                    v-for="key in filterKeys"
                    :key="key"
                    @click="activeFilter = key"
                    class="rounded-xl border px-5 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="
                        activeFilter === key
                            ? 'shadow-lg border-orange-500 bg-orange-600 text-white shadow-orange-900/20'
                            : 'border-transparent bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
                    "
                >
                    {{ key === 'all' ? 'Все блоки' : key }}
                </button>
            </div>

            <TransitionGroup name="list" tag="div" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div
                    v-for="block in filteredBlocks"
                    :key="block.id"
                    class="shadow-xl group relative flex flex-col rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8 transition-all hover:border-orange-500/30 hover:bg-slate-900/60"
                >
                    <div class="mb-6 flex items-start justify-between">
                        <div class="space-y-2">
                            <span
                                class="rounded-lg border border-slate-800 bg-slate-950 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-orange-500"
                            >
                                {{ block.key }}
                            </span>
                            <h3
                                class="text-xl font-bold leading-tight text-slate-100"
                                v-html="block.title"
                            ></h3>
                        </div>

                        <button
                            @click="toggleVisibility(block.id)"
                            type="button"
                            class="rounded-2xl border p-3 transition-all active:scale-95"
                            :class="
                                block.is_visible
                                    ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-500'
                                    : 'border-slate-700 bg-slate-800 text-slate-600'
                            "
                        >
                            <EyeIcon v-if="block.is_visible" class="h-5 w-5" />
                            <EyeOffIcon v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mb-8 grid grid-cols-2 gap-3">
                        <div
                            v-for="(item, idx) in block.content.slice(0, 4)"
                            :key="idx"
                            class="flex items-center gap-2 rounded-2xl border border-slate-800/50 bg-slate-950/60 p-3"
                        >
                            <div class="h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                            <span class="truncate text-[10px] font-bold text-slate-400">
                                {{ item.title || 'Элемент' }}
                            </span>
                        </div>
                    </div>

                    <Link
                        :href="route('admin.features.edit', block.id)"
                        class="mt-auto flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-800 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-600 group-hover:bg-slate-700"
                    >
                        <Settings2Icon class="h-4 w-4" /> Настроить контент
                        <ChevronRightIcon
                            class="ml-1 h-4 w-4 -translate-x-2 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100"
                        />
                    </Link>
                </div>
            </TransitionGroup>
        </div>
    </AdminLayout>
</template>

<style scoped>
    /* Анимация перемещения (когда меняется порядок) */
    .list-move,
    .list-enter-active,
    .list-leave-active {
        transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
    }

    /* Анимация появления и исчезновения (для фильтров) */
    .list-enter-from,
    .list-leave-to {
        opacity: 0;
        transform: scale(0.9) translateY(30px);
    }

    /* Гарантирует, что элементы плавно покидают свои места */
    .list-leave-active {
        position: absolute;
        width: 100%; /* Или фиксированная ширина, если сетка ломается */
    }

    /* Твоя старая анимация (можно убрать или оставить для первого появления) */
    .animate-in {
        animation: fadeInScale 0.7s ease;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
