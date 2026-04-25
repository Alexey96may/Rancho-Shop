<script setup lang="ts">
    import { ref } from 'vue';

    import { Link, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        EyeIcon,
        GlobeAltIcon,
    } from '@heroicons/vue/24/outline';

    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminPage, ResourceSingle } from '@/types';
    import { formatDateTime } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        page: ResourceSingle<AdminPage>;
        page_types: Array<{ id: string; name: string }>;
        templates: Array<{ id: string; name: string }>;
    }>();

    const activeTab = ref('general');

    const tabs = [
        { id: 'general', name: 'Общее', icon: Cog6ToothIcon },
        { id: 'content', name: 'Контент', icon: DocumentTextIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];

    const form = useForm({
        title: props.page.data.title,
        slug: props.page.data.slug,
        content: props.page.data.content,
        type: props.page.data.type,
        template: props.page.data.template,
        is_active: props.page.data.is_active,
        seo: {
            title: props.page.data.seo?.title || '',
            description: props.page.data.seo?.description || '',
            keywords: props.page.data.seo?.keywords || '',
        },
    });

    const submit = () => form.put(route('admin.pages.update', props.page.data.id));
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.pages.index')"
                    class="rounded-full border border-slate-800 p-2 text-slate-500 transition-all hover:border-orange-500 hover:text-orange-500"
                    title="Назад"
                >
                    <ChevronLeftIcon class="h-5 w-5" />
                </Link>
                <div class="space-y-1">
                    <h1 class="max-w-md truncate text-2xl font-black uppercase tracking-tighter">
                        {{ page.data.title }}
                    </h1>
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded-full px-2 py-0.5 text-[8px] font-bold uppercase"
                            :style="{
                                backgroundColor: page.data.type_color + '33',
                                color: page.data.type_color,
                            }"
                        >
                            {{ page.data.type_label }}
                        </span>
                        <span class="text-[10px] font-medium text-slate-600"
                            >ID: {{ page.data.id }}</span
                        >
                    </div>
                </div>
            </div>

            <a
                :href="`/${page.data.slug}`"
                target="_blank"
                class="group flex items-center gap-2 text-[10px] font-black uppercase text-slate-500 transition-colors hover:text-orange-500"
            >
                <EyeIcon class="h-4 w-4 transition-transform group-hover:scale-110" /> Просмотр
            </a>
        </div>

        <div
            class="flex border-b border-slate-800/50"
            role="tablist"
            aria-label="Разделы редактирования"
        >
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                role="tab"
                :aria-selected="activeTab === tab.id"
                :aria-controls="`panel-${tab.id}`"
                :class="[
                    'flex items-center gap-2 border-b-2 px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-all',
                    activeTab === tab.id
                        ? 'border-orange-500 text-orange-500'
                        : 'border-transparent text-slate-500 hover:text-slate-300',
                ]"
            >
                <component :is="tab.icon" class="h-4 w-4" />
                {{ tab.name }}
            </button>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <div
                    v-show="activeTab === 'general'"
                    id="panel-general"
                    role="tabpanel"
                    aria-labelledby="tab-general"
                    class="animate-in fade-in slide-in-from-bottom-2 space-y-6 duration-300"
                >
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                    >Заголовок страницы</label
                                >
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                                />
                            </div>
                            <div class="grid gap-2">
                                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                    >URL Путь (Slug)</label
                                >
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-orange-500 focus:border-orange-500"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <BaseSelect
                                    v-model="form.type"
                                    :options="page_types"
                                    label="Тип страницы"
                                    variant="admin"
                                />
                            </div>
                            <div class="grid gap-2">
                                <BaseSelect
                                    v-model="form.template"
                                    :options="templates"
                                    variant="admin"
                                    label="Системный шаблон"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-show="activeTab === 'content'"
                    id="panel-content"
                    role="tabpanel"
                    aria-labelledby="tab-content"
                    class="animate-in fade-in slide-in-from-bottom-2 duration-300"
                >
                    <div class="rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
                        <div class="grid gap-2">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Тело страницы (HTML/Markdown)</label
                            >
                            <textarea
                                v-model="form.content"
                                rows="20"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-6 font-mono text-sm leading-relaxed text-slate-300 focus:border-orange-500 focus:ring-0"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div
                    v-show="activeTab === 'seo'"
                    id="panel-seo"
                    role="tabpanel"
                    aria-labelledby="tab-seo"
                    class="animate-in fade-in slide-in-from-bottom-2 space-y-6 duration-300"
                >
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div class="grid gap-2">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Meta Title</label
                            >
                            <input
                                v-model="form.seo.title"
                                type="text"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>
                        <div class="grid gap-2">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Meta Description</label
                            >
                            <textarea
                                v-model="form.seo.description"
                                rows="4"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            ></textarea>
                        </div>
                        <div class="grid gap-2">
                            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                >Ключевые слова</label
                            >
                            <input
                                v-model="form.seo.keywords"
                                type="text"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="sticky top-6 space-y-6">
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <span class="text-[10px] font-black uppercase text-slate-500">
                                {{ form.is_active ? 'Опубликовано' : 'В архиве' }}
                            </span>
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                :aria-checked="form.is_active"
                                role="switch"
                                :class="form.is_active ? 'bg-emerald-600' : 'bg-slate-700'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-all"
                            >
                                <span
                                    :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                                    class="h-4 w-4 transform rounded-full bg-white transition-transform"
                                />
                            </button>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative w-full overflow-hidden rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Сохранение...</span>
                            <span v-else>Применить изменения</span>
                        </button>
                    </div>

                    <div
                        class="space-y-4 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div class="flex justify-between text-[10px] font-black uppercase">
                            <span class="text-slate-500">Отзывы</span>
                            <span class="text-white">{{ page.data.reviews_count || 0 }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-black uppercase">
                            <span class="text-slate-500">Обновлено</span>
                            <span class="text-slate-400">{{
                                formatDateTime(page.data.updated_at, 'dd.MM.yyyy HH:mm')
                            }}</span>
                        </div>
                        <div
                            v-if="!page.data.can_delete"
                            class="rounded-xl bg-orange-500/10 p-3 text-center text-[9px] font-bold uppercase text-orange-500"
                        >
                            Системная страница: удаление запрещено
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
