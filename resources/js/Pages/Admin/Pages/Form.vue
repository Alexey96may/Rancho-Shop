<script setup lang="ts">
    import { PropType, computed, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import {
        Cog6ToothIcon,
        DocumentTextIcon,
        EyeIcon,
        GlobeAltIcon,
    } from '@heroicons/vue/24/outline';

    import ContentSection from '@/Components/Admin/Sections/PageContentSection.vue';
    import GeneralSection from '@/Components/Admin/Sections/PageGeneralSection.vue';
    import SeoSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { AdminPage, ResourceSingle } from '@/types';
    import { formatDateTime } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        page: {
            type: Object as PropType<ResourceSingle<AdminPage>>,
            required: false,
            validator: (value: unknown): boolean => {
                if (value === undefined || value === null) return true;

                const val = value as Record<string, unknown>;
                const hasData = !!(val?.data && typeof val.data === 'object' && val.data !== null);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "page" prop is provided but missing a valid "data" object wrapper.',
                    );
                }
                return hasData;
            },
        },
        page_types: {
            type: Array as PropType<Array<{ id: string; name: string }>>,
            required: true,
            default: () => [],
        },
        templates: {
            type: Array as PropType<Array<{ id: string; name: string }>>,
            required: true,
            default: () => [],
        },
        backUrl: {
            type: String,
            required: true,
        },
    });

    const form = useForm({
        title: props.page?.data.title || '',
        slug: props.page?.data.slug || '',
        content: props.page?.data.content || '',
        type: props.page?.data.type || props.page_types[0]?.id || '',
        template: props.page?.data.template || props.templates[0]?.id || 'default',
        is_active: props.page?.data.is_active ?? true,
        seo: {
            title: props.page?.data.seo?.title || '',
            description: props.page?.data.seo?.description || '',
            keywords: props.page?.data.seo?.keywords || '',
            canonical: props.page?.data.seo?.canonical || '',
            is_noindex: props.page?.data.seo?.is_noindex || false,
        },
        backUrl: props.backUrl,
    });

    const tabs = [
        { id: 'general', name: 'Общее', icon: Cog6ToothIcon },
        { id: 'content', name: 'Контент', icon: DocumentTextIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];

    const isEdit = computed(() => !!props.page?.data?.id);
    const activeTab = ref('general');

    const { submitForm } = useAdminForm();
    const { deleteEntity, isDeleting } = useAdminCrud();

    const submit = () => {
        const id = isEdit.value && props.page ? props.page.data.id : null;
        submitForm(form, 'admin.pages', id);
    };

    const deletePage = async () => {
        if (!isEdit.value || !props.page?.data.can_delete) return;

        deleteEntity(
            'admin.pages',
            props.page.data.id,
            `Удаление страницы «${props.page.data.title}»`,
        );
    };

    const computedUpdatedData = computed(() =>
        formatDateTime(props.page?.data.updated_at || null, 'dd.MM.yyyy HH:mm'),
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            :title="isEdit ? 'Редактирование страницы' : 'Создание страницы'"
            :subtitle="isEdit ? page?.data.title : 'Новая запись'"
        />
    </Teleport>

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <BaseCancelButton :href="backUrl" label="Назад" />

                <div class="space-y-1">
                    <div v-if="isEdit" class="flex items-center gap-2">
                        <span
                            class="rounded-full px-2 py-0.5 text-[8px] font-bold uppercase"
                            :style="{
                                backgroundColor: page?.data.type_color + '33',
                                color: page?.data.type_color,
                            }"
                        >
                            {{ page?.data.type_label }}
                        </span>
                        <span class="text-[10px] font-medium text-slate-600"
                            >ID: {{ page?.data.id }}</span
                        >
                    </div>

                    <span v-else class="rounded-full px-2 py-0.5 text-[8px] font-bold uppercase">
                        Новая страница
                    </span>
                </div>
            </div>

            <a
                v-if="isEdit"
                :href="page?.data.url"
                target="_blank"
                class="group flex items-center gap-2 text-[10px] font-black uppercase text-slate-500 transition-colors hover:text-orange-500"
            >
                <EyeIcon class="h-4 w-4 transition-transform group-hover:scale-110" /> Просмотр
            </a>
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

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
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
                            :errors="form.errors"
                            :page_types="page_types"
                            :disabled="form.processing"
                            :templates="templates"
                        />
                    </div>

                    <div v-else-if="activeTab === 'content'" key="content">
                        <ContentSection
                            v-model="form.content"
                            :page-id="page?.data.id"
                            :errors="form.errors"
                        />
                    </div>

                    <div v-else-if="activeTab === 'seo'" key="seo">
                        <SeoSection
                            v-model="form.seo"
                            :disabled="form.processing"
                            :errors="form.errors"
                        />
                    </div>
                </TransitionGroup>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="sticky top-6 space-y-6">
                    <div
                        class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <BaseSwitch
                            v-model="form.is_active"
                            label="Статус страницы"
                            :active-text="isEdit ? 'Опубликовано' : 'Опубликовать сразу'"
                            :inactive-text="isEdit ? 'В архиве' : 'В черновик'"
                            :disabled="form.processing"
                        />

                        <BaseSubmitButton
                            :processing="form.processing"
                            :is-edit="isEdit"
                            :with-icon="false"
                            :label="isEdit ? 'Применить изменения' : 'Создать страницу'"
                        />
                    </div>

                    <div
                        v-if="isEdit"
                        class="space-y-4 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div class="flex justify-between text-[10px] font-black uppercase">
                            <span class="text-slate-500">Отзывы</span>
                            <span class="text-white">{{ page?.data.reviews_count || 0 }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] font-black uppercase">
                            <span class="text-slate-500">Обновлено</span>
                            <span class="text-slate-400">{{ computedUpdatedData }}</span>
                        </div>

                        <BaseDeleteButton
                            v-if="page?.data.can_delete"
                            :disabled="isDeleting(page?.data.id)"
                            @confirm="deletePage"
                        >
                            <span>{{
                                isDeleting(page?.data.id) ? 'Удаление...' : 'Удалить страницу'
                            }}</span>
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
        </form>
    </div>
</template>
