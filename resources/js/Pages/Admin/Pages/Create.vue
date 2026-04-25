<script setup lang="ts">
    import { Link, useForm } from '@inertiajs/vue3';

    import { ChevronLeftIcon, DocumentTextIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    defineOptions({ layout: AdminLayout });

    const form = useForm({
        title: '',
        slug: '',
        content: '',
        type: 'default',
        template: 'default',
        is_active: true,
        seo: { title: '', description: '', keywords: '' },
    });

    const submit = () => form.post(route('admin.pages.store'));
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center gap-4">
            <Link
                :href="route('admin.pages.index')"
                class="rounded-full border border-slate-800 p-2 text-slate-500 transition-all hover:border-orange-500 hover:text-orange-500"
            >
                <ChevronLeftIcon class="h-5 w-5" />
            </Link>
            <h1 class="text-2xl font-black uppercase">Новая страница</h1>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
                    <div class="grid gap-2">
                        <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                            >Заголовок</label
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
                            >Slug (URL)</label
                        >
                        <input
                            v-model="form.slug"
                            type="text"
                            placeholder="автоматически из заголовка"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-orange-500 focus:border-orange-500"
                        />
                    </div>

                    <div class="grid gap-2">
                        <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                            >Контент</label
                        >
                        <textarea
                            v-model="form.content"
                            rows="12"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-slate-300 focus:border-orange-500"
                        ></textarea>
                    </div>
                </div>

                <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
                    <h2
                        class="flex items-center gap-2 text-[10px] font-black uppercase text-slate-500"
                    >
                        <GlobeAltIcon class="h-4 w-4" /> SEO Настройки
                    </h2>
                    <div class="grid gap-4">
                        <input
                            v-model="form.seo.title"
                            type="text"
                            placeholder="SEO Title"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white"
                        />
                        <textarea
                            v-model="form.seo.description"
                            placeholder="SEO Description"
                            rows="3"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div
                    class="sticky top-6 space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                >
                    <BaseSelect
                        v-model="form.type"
                        label="Тип страницы"
                        variant="admin"
                        :options="[
                            { id: 'default', name: 'Обычная' },
                            { id: 'system', name: 'Системная' },
                        ]"
                    />
                    <BaseSelect
                        v-model="form.template"
                        label="Шаблон"
                        :options="[
                            { id: 'default', name: 'Текст' },
                            { id: 'about', name: 'О нас' },
                        ]"
                    />

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="shadow-lg w-full rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase text-white shadow-orange-900/40 transition-all hover:bg-orange-500"
                    >
                        Опубликовать
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
