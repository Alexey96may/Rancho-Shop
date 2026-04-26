<script setup lang="ts">
    import { ref } from 'vue';

    import { Link, router, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        EyeIcon,
        GlobeAltIcon,
    } from '@heroicons/vue/24/outline';

    import ContentSection from '@/Components/Admin/Sections/PageContentSection.vue';
    import GeneralSection from '@/Components/Admin/Sections/PageGeneralSection.vue';
    import SeoSection from '@/Components/Admin/Sections/SEOSection.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
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
            canonical: props.page.data.seo?.canonical || '',
            is_noindex: props.page.data.seo?.is_noindex || false,
        },
    });

    const submit = () => form.put(route('admin.pages.update', props.page.data.id));
    const { notifyWithUndo } = useFlash();

    const isDeleting = ref(false);

    const deletePage = async () => {
        if (!props.page.data.can_delete) return;

        if (isDeleting.value) return;
        isDeleting.value = true;

        const isDeleted = await notifyWithUndo(`Удаление страницы "${props.page.data.title}"`);

        if (isDeleted) {
            router.delete(route('admin.pages.destroy', props.page.data.id), {
                onFinish: () => {
                    isDeleting.value = false;
                },
            });
        } else {
            isDeleting.value = false;
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Редактирование страницы "{{ page.data.title }}"
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
                :href="page.data.url"
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

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8">
            <div class="space-y-6 lg:col-span-8">
                <TransitionGroup
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-4 opacity-0"
                    mode="out-in"
                >
                    <div
                        v-if="activeTab === 'general'"
                        key="general"
                        role="tabpanel"
                        class="space-y-6"
                    >
                        <GeneralSection
                            v-model:title="form.title"
                            v-model:slug="form.slug"
                            v-model:type="form.type"
                            v-model:template="form.template"
                            :page_types="page_types"
                            :templates="templates"
                        />
                    </div>

                    <div v-else-if="activeTab === 'content'" key="content" role="tabpanel">
                        <ContentSection v-model="form.content" :page-id="page.data.id" />
                    </div>

                    <div v-else-if="activeTab === 'seo'" key="seo" role="tabpanel">
                        <SeoSection v-model="form.seo" />
                    </div>
                </TransitionGroup>

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
                                <Link
                                    :href="
                                        route('admin.comments.index', {
                                            status: 'approved',
                                            type: 'page',
                                        })
                                    "
                                    ><span class="text-white">{{
                                        page.data.reviews_count || 0
                                    }}</span></Link
                                >
                            </div>
                            <div class="flex justify-between text-[10px] font-black uppercase">
                                <span class="text-slate-500">Обновлено</span>
                                <span class="text-slate-400">{{
                                    formatDateTime(page.data.updated_at, 'dd.MM.yyyy HH:mm')
                                }}</span>
                            </div>

                            <BaseDeleteButton
                                v-if="page.data.can_delete"
                                :disabled="isDeleting"
                                @confirm="deletePage"
                                ><span v-if="isDeleting">Удаление...</span>
                                <span v-else>Удалить страницу</span>
                            </BaseDeleteButton>

                            <div
                                v-else
                                class="rounded-xl bg-orange-500/10 p-3 text-center text-[9px] font-bold uppercase text-orange-500"
                            >
                                Системная страница: удаление запрещено
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped></style>
