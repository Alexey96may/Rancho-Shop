<script setup lang="ts">
    import { ref } from 'vue';

    import { Link, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        GlobeAltIcon,
    } from '@heroicons/vue/24/outline';

    import ContentSection from '@/Components/Admin/Sections/PageContentSection.vue';
    import GeneralSection from '@/Components/Admin/Sections/PageGeneralSection.vue';
    import SeoSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    defineOptions({ layout: AdminLayout });

    // Убираем пропс page, оставляем только справочники
    const props = defineProps<{
        page_types: Array<{ id: string; name: string }>;
        templates: Array<{ id: string; name: string }>;
    }>();

    const activeTab = ref('general');

    const tabs = [
        { id: 'general', name: 'Общее', icon: Cog6ToothIcon },
        { id: 'content', name: 'Контент', icon: DocumentTextIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];

    // Инициализируем пустые значения
    const form = useForm({
        title: '',
        slug: '',
        content: '',
        type: props.page_types[0]?.id || '', // Предзаполняем первым типом
        template: props.templates[0]?.id || 'default',
        is_active: true,

        seo: {
            title: '',
            description: '',
            keywords: '',
            canonical: '',
            is_noindex: false,
        },
    });

    const submit = () => form.post(route('admin.pages.store'));
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Создание новой страницы
        </h1>
    </Teleport>

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
                        Новая страница
                    </h1>
                </div>
            </div>
        </div>

        <div class="flex border-b border-slate-800/50" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
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

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8">
            <div class="space-y-6">
                <TransitionGroup
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    mode="out-in"
                >
                    <div v-if="activeTab === 'general'" key="general" class="space-y-6">
                        <GeneralSection
                            v-model:title="form.title"
                            v-model:slug="form.slug"
                            v-model:type="form.type"
                            v-model:template="form.template"
                            :page_types="page_types"
                            :templates="templates"
                        />
                    </div>

                    <div v-else-if="activeTab === 'content'" key="content">
                        <ContentSection v-model="form.content" />
                    </div>

                    <div v-else-if="activeTab === 'seo'" key="seo">
                        <SeoSection v-model="form.seo" />
                    </div>
                </TransitionGroup>
            </div>

            <div class="space-y-6">
                <div class="sticky top-6 space-y-6">
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <span class="text-[10px] font-black uppercase text-slate-500">
                                {{ form.is_active ? 'Опубликовать сразу' : 'В черновик' }}
                            </span>
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
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
                            <span v-if="form.processing">Создание...</span>
                            <span v-else>Создать страницу</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
