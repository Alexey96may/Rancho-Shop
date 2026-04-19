<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        DocumentTextIcon,
        EyeIcon,
        GlobeAltIcon,
        MagnifyingGlassIcon,
        PencilIcon,
        PlusIcon,
        TrashIcon,
    } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import AdminSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        pages: { data: any[] };
        filters: any;
    }>();

    const view = ref<'list' | 'form'>('list');
    const activeTab = ref('content');
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        title: '',
        content: '',
        type: 'static',
        template: 'default',
        is_active: true,
        seo: { title: '', description: '', keywords: '' },
    });

    // Фильтрация
    const search = ref(props.filters.search || '');
    const debouncedFilter = debounce(() => {
        router.get(
            route('admin.pages.index'),
            { search: search.value },
            { preserveState: true, replace: true },
        );
    }, 300);

    watch(search, debouncedFilter);

    const openForm = (page: any = null) => {
        editMode.value = !!page;
        activeTab.value = 'content';
        if (page) {
            currentId.value = page.id;
            form.title = page.title;
            form.content = page.content;
            form.type = page.type;
            form.template = page.template || 'default';
            form.is_active = page.is_active;
            form.seo = page.seo || { title: '', description: '', keywords: '' };
        } else {
            form.reset();
        }
        view.value = 'form';
    };

    const submit = () => {
        const url = editMode.value
            ? route('admin.pages.update', currentId.value!)
            : route('admin.pages.store');
        form.post(url, { onSuccess: () => (view.value = 'list') });
    };
</script>

<template>
    <Head title="Управление контентом" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <button
                    v-if="view === 'form'"
                    @click="view = 'list'"
                    class="group flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 bg-slate-900 transition-all hover:border-orange-500"
                >
                    <ChevronLeftIcon class="h-5 w-5 text-slate-500 group-hover:text-orange-500" />
                </button>
                <h1 class="text-2xl font-black uppercase tracking-tighter">
                    {{
                        view === 'list'
                            ? 'Страницы сайта'
                            : editMode
                              ? 'Правка: ' + form.title
                              : 'Новый раздел'
                    }}
                </h1>
            </div>
        </template>

        <div class="animate-in fade-in grid grid-cols-1 gap-8 duration-500">
            <div v-if="view === 'list'" class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 items-center gap-4 md:grid-cols-12">
                    <div class="relative md:col-span-8">
                        <MagnifyingGlassIcon
                            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Поиск по названию..."
                            class="w-full rounded-2xl border-slate-800 bg-slate-900/50 py-3.5 pl-12 text-white transition-all focus:border-orange-500"
                        />
                    </div>
                    <div class="flex justify-end md:col-span-4">
                        <button
                            @click="openForm()"
                            class="shadow-lg flex h-[54px] w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-xs font-black uppercase tracking-widest text-white shadow-orange-900/20 transition-all hover:bg-orange-500 md:w-auto"
                        >
                            <PlusIcon class="h-5 w-5" /> Создать страницу
                        </button>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-[2.5rem] border border-slate-800 bg-slate-900/30 backdrop-blur-md"
                >
                    <table class="w-full text-left">
                        <thead class="border-b border-slate-800 bg-slate-900/50">
                            <tr>
                                <th
                                    class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500"
                                >
                                    Заголовок
                                </th>
                                <th
                                    class="hidden px-8 py-5 text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 md:table-cell"
                                >
                                    Тип / Шаблон
                                </th>
                                <th
                                    class="px-8 py-5 text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-500"
                                >
                                    Статус
                                </th>
                                <th
                                    class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-500"
                                >
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            <tr
                                v-for="page in pages.data"
                                :key="page.id"
                                class="group transition-colors hover:bg-slate-800/30"
                            >
                                <td class="px-8 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white">{{ page.title }}</span>
                                        <span class="font-mono text-[10px] text-slate-500"
                                            >/{{ page.slug }}</span
                                        >
                                    </div>
                                </td>
                                <td class="hidden px-8 py-4 text-center md:table-cell">
                                    <div class="flex flex-col items-center gap-1">
                                        <span
                                            class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-0.5 text-[9px] font-black uppercase text-slate-400"
                                        >
                                            {{ page.type }}
                                        </span>
                                        <span
                                            v-if="page.template !== 'default'"
                                            class="text-[8px] font-black uppercase tracking-tighter text-orange-500"
                                        >
                                            {{ page.template }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="flex justify-center">
                                        <div
                                            :class="
                                                page.is_active
                                                    ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]'
                                                    : 'bg-slate-700'
                                            "
                                            class="h-2 w-2 rounded-full"
                                        ></div>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div
                                        class="flex justify-end gap-2 transition-opacity md:opacity-0 md:group-hover:opacity-100"
                                    >
                                        <button
                                            @click="openForm(page)"
                                            class="p-2.5 text-slate-500 transition-all hover:text-orange-500"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="
                                                router.delete(route('admin.pages.destroy', page.id))
                                            "
                                            class="p-2.5 text-slate-500 transition-all hover:text-red-500"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form
                v-else
                @submit.prevent="submit"
                class="grid grid-cols-1 items-start gap-8 md:grid-cols-12"
            >
                <div class="grid grid-cols-1 gap-6 md:col-span-8">
                    <div
                        class="shadow-2xl rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <nav
                            class="mb-8 grid grid-cols-2 gap-4 border-b border-slate-800"
                            role="tablist"
                        >
                            <button
                                v-for="tab in [
                                    { id: 'content', name: 'Контент', icon: DocumentTextIcon },
                                    { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
                                ]"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                type="button"
                                class="relative flex items-center justify-center gap-2 pb-4 text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="
                                    activeTab === tab.id
                                        ? 'text-orange-500'
                                        : 'text-slate-500 hover:text-slate-300'
                                "
                            >
                                <component :is="tab.icon" class="h-4 w-4" /> {{ tab.name }}
                                <div
                                    v-if="activeTab === tab.id"
                                    class="absolute bottom-0 left-0 h-0.5 w-full bg-orange-500 shadow-[0_0_8px_#f97316]"
                                ></div>
                            </button>
                        </nav>

                        <div
                            v-if="activeTab === 'content'"
                            class="animate-in fade-in zoom-in-95 grid gap-6"
                        >
                            <div class="grid gap-2">
                                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                    >Заголовок страницы</label
                                >
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-xl font-bold text-white transition-all focus:border-orange-500"
                                />
                            </div>
                            <div class="grid gap-2">
                                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                                    >Контент (HTML)</label
                                >
                                <textarea
                                    v-model="form.content"
                                    rows="18"
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-6 font-mono text-sm leading-relaxed text-slate-300 focus:border-orange-500"
                                ></textarea>
                            </div>
                        </div>

                        <div
                            v-if="activeTab === 'seo'"
                            class="animate-in fade-in zoom-in-95 grid gap-6"
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
                                    rows="5"
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky top-8 grid grid-cols-1 gap-6 md:col-span-4">
                    <div class="rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
                        <h3
                            class="mb-6 text-[10px] font-black uppercase tracking-widest text-white"
                        >
                            Параметры
                        </h3>
                        <div class="grid gap-5">
                            <AdminSelect
                                label="Тип"
                                v-model="form.type"
                                :options="[
                                    { id: 'static', name: 'Статическая', slug: 'static' },
                                    { id: 'info', name: 'Инфо', slug: 'info' },
                                ]"
                            />
                            <AdminSelect
                                label="Шаблон"
                                v-model="form.template"
                                :options="[
                                    { id: 'default', name: 'Текст', slug: 'default' },
                                    { id: 'delivery', name: 'Доставка', slug: 'delivery' },
                                    { id: 'about', name: 'О нас', slug: 'about' },
                                ]"
                            />

                            <div
                                class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4"
                            >
                                <span class="text-[10px] font-black uppercase text-slate-500">{{
                                    form.is_active ? 'Активна' : 'Черновик'
                                }}</span>
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

                            <div class="grid gap-3 pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="shadow-lg w-full rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase text-white shadow-orange-900/40 transition-all hover:bg-orange-500"
                                >
                                    {{ editMode ? 'Сохранить изменения' : 'Опубликовать' }}
                                </button>
                                <button
                                    @click="view = 'list'"
                                    type="button"
                                    class="text-center text-[10px] font-black uppercase text-slate-500 transition-colors hover:text-white"
                                >
                                    Отмена
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="editMode"
                        class="grid grid-cols-1 gap-4 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
                    >
                        <div class="flex items-center gap-3 text-orange-500">
                            <EyeIcon class="h-4 w-4" />
                            <span class="text-[10px] font-black uppercase text-white">Инфо</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[10px] font-black uppercase">
                            <span class="text-slate-500">Отзывы:</span>
                            <span class="text-right text-white">{{
                                pages.data.find((p) => p.id === currentId)?.reviews_count || 0
                            }}</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
