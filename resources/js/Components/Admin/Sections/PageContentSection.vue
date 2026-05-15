<script setup lang="ts">
    import { ref, watch } from 'vue';

    import type { Errors } from '@inertiajs/core';
    import { router } from '@inertiajs/vue3';

    // Tiptap Extensions
    import { Color } from '@tiptap/extension-color';
    import Image from '@tiptap/extension-image';
    import Link from '@tiptap/extension-link';
    import { TextStyle } from '@tiptap/extension-text-style';
    import { Underline } from '@tiptap/extension-underline';
    import StarterKit from '@tiptap/starter-kit';
    import { EditorContent, useEditor } from '@tiptap/vue-3';
    import debounce from 'lodash/debounce';
    // Lucide Icons
    import {
        Bold,
        Code2,
        Eye,
        Heading1,
        Heading2,
        Heading3,
        Heading4,
        Image as ImageIcon,
        Italic,
        Link as LinkIcon,
        List,
        ListOrdered,
        Loader2,
        Minus,
        Quote,
        Redo,
        Type,
        Underline as UnderlineIcon,
        Undo,
        Unlink,
        WrapText,
    } from 'lucide-vue-next';

    import BaseModal from '@/Components/UI/BaseModal.vue';
    import { useFlash } from '@/composables/useFlash';

    const { notify } = useFlash();

    const props = defineProps<{
        modelValue?: string;
        pageId?: number;
        errors?: Errors;
    }>();

    const content = defineModel<string>();
    const isHtmlMode = ref(false);
    const isUploading = ref(false);
    const fileInput = ref<HTMLInputElement | null>(null);

    const isLinkModalShow = ref(false);
    const linkUrl = ref<string>('');

    const openLinkModal = () => {
        const previousUrl = editor.value?.getAttributes('link').href;
        linkUrl.value = previousUrl || '';
        isLinkModalShow.value = true;
    };

    const saveLink = () => {
        if (linkUrl.value === '') {
            editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
        } else {
            editor.value
                ?.chain()
                .focus()
                .extendMarkRange('link')
                .setLink({ href: linkUrl.value })
                .run();
        }
        isLinkModalShow.value = false;
        linkUrl.value = '';
    };

    const handleFileUpload = (event: Event) => {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!allowedTypes.includes(file.type)) {
            notify('Неподдерживаемый формат файла', 'error');
            return;
        }

        const maxSizeInBytes = 3 * 1024 * 1024;

        if (file.size > maxSizeInBytes) {
            notify('Ошибка! Файл слишком велик (макс. 3 МБ)', 'error');
            if (fileInput.value) fileInput.value.value = '';
            return;
        }

        const uploadRoute = props.pageId
            ? route('admin.pages.upload-media', props.pageId)
            : route('admin.media.upload-temporary');

        router.post(
            uploadRoute,
            { image: file },
            {
                forceFormData: true,
                preserveScroll: true,
                preserveState: true,
                onBefore: () => (isUploading.value = true),
                onSuccess: (pageResponse) => {
                    const url = (pageResponse.props as any).flash?.last_uploaded_url;
                    if (url) {
                        editor.value?.chain().focus().setImage({ src: url }).run();
                    }
                },
                onError: (e) => {
                    console.log(e);

                    const errorMessage = e.image || 'Произошла ошибка при загрузке файла';
                    notify(errorMessage, 'error');
                },
                onFinish: () => {
                    isUploading.value = false;
                    if (fileInput.value) fileInput.value.value = '';
                },
            },
        );
    };

    const editor = useEditor({
        content: content.value,
        extensions: [
            StarterKit.configure({
                heading: { levels: [1, 2, 3, 4, 5, 6] },
                dropcursor: { color: '#f97316', width: 2 },
            }),
            Underline,
            TextStyle,
            Color,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: 'text-orange-500 underline decoration-orange-500/30 underline-offset-4 cursor-pointer',
                },
            }),
            Image.configure({
                HTMLAttributes: {
                    class: 'rounded-2xl border border-slate-800 max-w-full h-auto my-8 max-w-[250px] max-h-[250px]',
                },
            }),
        ],
        editorProps: {
            attributes: {
                class: 'prose prose-invert max-w-none focus:outline-none min-h-[350px] p-8 text-slate-300',
            },
        },
        onUpdate: debounce(({ editor }) => {
            if (!isHtmlMode.value) {
                content.value = editor.getHTML();
            }
        }, 300),
    });

    watch(isHtmlMode, (value) => {
        if (!value) {
            editor.value?.commands.setContent(content.value || '', { emitUpdate: false });
        }
    });

    watch(
        () => content.value,
        (newValue) => {
            if (editor.value?.getHTML() !== newValue && !isHtmlMode.value) {
                editor.value?.commands.setContent(newValue || '', { emitUpdate: false });
            }
        },
    );
</script>

<template>
    <div class="rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-4">
        <div
            class="mb-4 ml-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4"
        >
            <div class="flex flex-wrap items-center gap-1">
                <template v-if="!isHtmlMode && editor">
                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="editor.chain().focus().undo().run()"
                            :disabled="!editor.can().undo()"
                            class="toolbar-btn"
                            title="Назад (Ctrl+Z)"
                        >
                            <Undo class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().redo().run()"
                            :disabled="!editor.can().redo()"
                            class="toolbar-btn"
                            title="Вперед (Ctrl+Y)"
                        >
                            <Redo class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBold().run()"
                            :class="{ 'active-btn': editor.isActive('bold') }"
                            class="toolbar-btn"
                        >
                            <Bold class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleItalic().run()"
                            :class="{ 'active-btn': editor.isActive('italic') }"
                            class="toolbar-btn"
                        >
                            <Italic class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleUnderline().run()"
                            :class="{ 'active-btn': editor.isActive('underline') }"
                            class="toolbar-btn"
                        >
                            <UnderlineIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 1 }) }"
                            class="toolbar-btn"
                            title="H2"
                        >
                            <Heading1 class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 2 }) }"
                            class="toolbar-btn"
                            title="H2"
                        >
                            <Heading2 class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 3 }) }"
                            class="toolbar-btn"
                            title="H3"
                        >
                            <Heading3 class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 4 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 4 }) }"
                            class="toolbar-btn"
                            title="H4"
                        >
                            <Heading4 class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 5 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 5 }) }"
                            class="toolbar-btn text-[10px] font-bold"
                        >
                            H5
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level: 6 }).run()"
                            :class="{ 'active-btn': editor.isActive('heading', { level: 6 }) }"
                            class="toolbar-btn text-[10px] font-bold"
                        >
                            H6
                        </button>
                    </div>

                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBulletList().run()"
                            :class="{ 'active-btn': editor.isActive('bulletList') }"
                            class="toolbar-btn"
                        >
                            <List class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleOrderedList().run()"
                            :class="{ 'active-btn': editor.isActive('orderedList') }"
                            class="toolbar-btn"
                        >
                            <ListOrdered class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBlockquote().run()"
                            :class="{ 'active-btn': editor.isActive('blockquote') }"
                            class="toolbar-btn"
                        >
                            <Quote class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="editor.chain().focus().setHardBreak().run()"
                            class="toolbar-btn"
                            title="Перенос строки (BR)"
                        >
                            <WrapText class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().setHorizontalRule().run()"
                            class="toolbar-btn"
                            title="Линия (HR)"
                        >
                            <Minus class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="editor.chain().focus().setParagraph().run()"
                            :class="{ 'active-btn': editor.isActive('paragraph') }"
                            class="toolbar-btn"
                            title="Абзац"
                        >
                            <Type class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mr-2 flex items-center gap-1 border-r border-slate-800 pr-2">
                        <button
                            type="button"
                            @click="openLinkModal"
                            :class="{ 'active-btn': editor.isActive('link') }"
                            class="toolbar-btn"
                            title="Ссылка"
                        >
                            <LinkIcon class="h-4 w-4" />
                        </button>
                        <button
                            v-if="editor.isActive('link')"
                            type="button"
                            @click="editor.chain().focus().unsetLink().run()"
                            class="toolbar-btn text-red-400"
                            title="Ссылка"
                        >
                            <Unlink class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="fileInput?.click()"
                            :disabled="isUploading"
                            class="toolbar-btn"
                            title="Фото"
                        >
                            <Loader2 v-if="isUploading" class="h-4 w-4 animate-spin" />
                            <ImageIcon v-else class="h-4 w-4" />
                        </button>
                        <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            @change="handleFileUpload"
                        />
                    </div>

                    <div class="flex items-center gap-1">
                        <input
                            type="color"
                            @input="
                                editor
                                    .chain()
                                    .focus()
                                    .setColor(($event.target as HTMLInputElement).value)
                                    .run()
                            "
                            :value="editor.getAttributes('textStyle').color || '#cbd5e1'"
                            class="h-6 w-6 cursor-pointer rounded border-none bg-transparent"
                            title="Цвет текста"
                        />
                    </div>
                </template>
            </div>

            <button type="button" @click="isHtmlMode = !isHtmlMode" class="mode-toggle-btn">
                <component :is="isHtmlMode ? Eye : Code2" class="h-4 w-4" />
                {{ isHtmlMode ? 'Визуально' : 'HTML' }}
            </button>
        </div>

        <div class="grid gap-2 px-2">
            <div v-show="!isHtmlMode" class="editor-container">
                <EditorContent :editor="editor" />
            </div>
            <div v-if="isHtmlMode" class="editor-container bg-slate-950">
                <textarea v-model="content" rows="22" class="html-textarea border-none"></textarea>
                <p v-if="errors?.content" class="text-xs font-medium text-red-500">
                    {{ errors?.content }}
                </p>
            </div>
        </div>
    </div>

    <BaseModal :show="isLinkModalShow" title="Управление ссылкой" @close="isLinkModalShow = false">
        <div class="space-y-4">
            <div>
                <label
                    for="urllink"
                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500"
                >
                    URL адрес
                </label>
                <input
                    v-model="linkUrl"
                    id="urllink"
                    type="url"
                    placeholder="https://example.com"
                    class="w-full rounded-2xl border border-slate-800 bg-slate-950 p-4 text-white outline-none transition-colors focus:border-orange-500"
                    @keydown.enter="saveLink"
                />
            </div>

            <div class="flex gap-3">
                <button
                    @click="saveLink"
                    class="flex-1 rounded-2xl bg-orange-500 py-4 font-black uppercase tracking-widest text-white transition-all hover:bg-orange-600 active:scale-95"
                >
                    Применить
                </button>
                <button
                    v-if="editor?.isActive('link')"
                    @click="
                        linkUrl = '';
                        saveLink();
                    "
                    class="rounded-2xl border border-red-500/30 bg-red-500/10 px-6 text-red-500 transition-all hover:bg-red-500 hover:text-white"
                    title="Удалить ссылку"
                >
                    <Unlink class="h-5 w-5" />
                </button>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
    .toolbar-btn {
        @apply rounded-lg p-2 text-slate-500 transition-all hover:bg-slate-800 hover:text-white disabled:opacity-20;
    }
    .active-btn {
        @apply bg-orange-500/10 text-orange-500;
    }
    .mode-toggle-btn {
        @apply flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-slate-400 transition-all hover:border-orange-500 hover:text-white;
    }
    .editor-container {
        @apply w-full overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-950 transition-all focus-within:border-orange-500;
    }
    .html-textarea {
        @apply min-h-[350px] w-full resize-none bg-transparent p-8 font-mono text-sm leading-relaxed text-orange-400/90 outline-none;
    }

    :deep(.tiptap) {
        outline: none;
    }

    /* Чтобы цвет текста из расширения Color работал поверх стилей prose */
    :deep(.tiptap span[style]) {
        color: inherit;
    }
</style>
