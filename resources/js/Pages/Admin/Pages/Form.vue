<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

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
    import type { AdminPage, ResourceSingle } from '@/types';
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
        is_commentable: props.page?.data.is_commentable ?? true,
        show_in_menu: props.page?.data.show_in_menu ?? false,
        menu_order: props.page?.data.menu_order ?? 0,
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
    <div>
        <Teleport to="#admin-header-content">
            <AdminPageHeader
                :title="isEdit ? 'Редактирование страницы' : 'Создание страницы'"
                :subtitle="isEdit ? page?.data.title : 'Новая запись'"
            />
        </Teleport>

        <div class="mx-auto space-y-10">
            <!-- TOP BAR -->
            <div class="flex flex-row flex-wrap justify-between gap-4 sm:items-center">
                <div>
                    <BaseCancelButton :href="backUrl" label="Назад" theme="dark" />
                </div>

                <div class="flex flex-col items-center gap-2">
                    <div class="space-y-1">
                        <div v-if="isEdit" class="flex flex-wrap items-baseline gap-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-[8px] font-bold uppercase"
                                :style="{
                                    backgroundColor: page?.data.type_color + '33',
                                    color: page?.data.type_color,
                                }"
                            >
                                {{ page?.data.type_label }}
                            </span>

                            <span class="text-[10px] font-medium text-rancho-ink-500">
                                ID: {{ page?.data.id }}
                            </span>
                        </div>

                        <span
                            v-else
                            class="rounded-full px-2 py-0.5 text-[8px] font-bold uppercase"
                        >
                            Новая страница
                        </span>
                    </div>

                    <a
                        v-if="isEdit"
                        :href="page?.data.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex items-center gap-2 text-[10px] font-black uppercase text-rancho-ink-500 transition-colors hover:text-rancho-mint sm:self-auto"
                    >
                        <EyeIcon
                            class="h-4 w-4 transition-transform group-hover:scale-110"
                            aria-hidden="true"
                        />
                        Просмотр
                    </a>
                </div>
            </div>

            <!-- TABS -->
            <nav
                class="flex gap-1 overflow-x-auto rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-1.5 sm:gap-2 sm:rounded-3xl sm:p-2"
                role="tablist"
                aria-label="Разделы формы"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    @click="activeTab = tab.id"
                    :aria-selected="activeTab === tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                            : 'text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-rancho-ink-100',
                        'flex shrink-0 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-[10px] font-black uppercase tracking-widest outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-mint/40 sm:flex-1 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-3.5',
                    ]"
                >
                    <component :is="tab.icon" class="h-4 w-4" aria-hidden="true" />
                    {{ tab.name }}
                </button>
            </nav>

            <!-- FORM -->
            <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
                <!-- CONTENT AREA -->
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
                                v-model:show-in-menu="form.show_in_menu"
                                v-model:menu-order="form.menu_order"
                                v-model:template="form.template"
                                v-model:is-commentable="form.is_commentable"
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
                                :page-name="form.title"
                                :disabled="form.processing"
                                :errors="form.errors"
                            />
                        </div>
                    </TransitionGroup>
                </div>

                <!-- SIDEBAR -->
                <div class="space-y-6 lg:col-span-4">
                    <div class="space-y-6 lg:sticky lg:top-6">
                        <!-- ACTIONS -->
                        <div
                            class="flex flex-col gap-4 space-y-6 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/30 p-4 sm:rounded-3xl sm:p-6 md:p-8"
                        >
                            <BaseSwitch
                                v-model="form.is_active"
                                label="Статус страницы"
                                @input-start="form.clearErrors('is_active')"
                                :active-text="isEdit ? 'Опубликовано' : 'Опубликовать сразу'"
                                :inactive-text="isEdit ? 'В архиве' : 'В черновик'"
                                :disabled="form.processing"
                                theme="dark"
                            />

                            <BaseSubmitButton
                                :processing="form.processing"
                                :is-edit="isEdit"
                                :label="isEdit ? 'Применить изменения' : 'Создать страницу'"
                            />
                        </div>

                        <!-- META + DELETE -->
                        <div
                            v-if="isEdit"
                            class="space-y-4 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/30 p-4 sm:rounded-3xl sm:p-6 md:p-8"
                        >
                            <div class="flex justify-between text-[10px] font-black uppercase">
                                <span class="text-rancho-ink-500">Отзывы</span>
                                <span class="text-white">
                                    {{ page?.data.reviews_count || 0 }}
                                </span>
                            </div>

                            <div class="flex justify-between text-[10px] font-black uppercase">
                                <span class="text-rancho-ink-500">Обновлено</span>
                                <span class="text-rancho-ink-300">
                                    {{ computedUpdatedData }}
                                </span>
                            </div>

                            <BaseDeleteButton
                                v-if="page?.data.can_delete"
                                :disabled="isDeleting(page?.data.id)"
                                @confirm="deletePage"
                            >
                                <span>
                                    {{
                                        isDeleting(page?.data.id)
                                            ? 'Удаление...'
                                            : 'Удалить страницу'
                                    }}
                                </span>
                            </BaseDeleteButton>

                            <div
                                v-else
                                class="rounded-xl bg-rancho-ember/10 p-3 text-center text-[9px] font-bold uppercase text-rancho-ember ring-1 ring-inset ring-rancho-ember/20"
                            >
                                Системная страница: удаление запрещено
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
