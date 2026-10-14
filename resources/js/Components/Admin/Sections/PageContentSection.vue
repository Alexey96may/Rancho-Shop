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
    import { debounce } from 'lodash-es';
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
    import { useNotificationsStore } from '@/stores/notifications';

    const notify = useNotificationsStore();

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
            notify.error('Неподдерживаемый формат файла');
            return;
        }

        const maxSizeInBytes = 3 * 1024 * 1024;

        if (file.size > maxSizeInBytes) {
            notify.error('Ошибка! Файл слишком велик (макс. 3 МБ)');
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
                    notify.error(errorMessage);
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
                dropcursor: { color: '#7AB89A', width: 2 },
            }),
            Underline,
            TextStyle,
            Color,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: 'text-rancho-mint underline decoration-rancho-mint/30 underline-offset-4 cursor-pointer',
                },
            }),
            Image.configure({
                HTMLAttributes: {
                    class: 'rounded-2xl border border-rancho-ink-800 max-w-full h-auto my-8 max-w-[250px] max-h-[250px]',
                },
            }),
        ],
        editorProps: {
            attributes: {
                class: 'prose prose-invert max-w-none focus:outline-none min-h-[350px] p-4 sm:p-6 text-rancho-ink-300',
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

    const currentHeadingLevel = ref<number>(0);
    const isSyncingHeading = ref(false);

    watch(
        () => content.value,
        (newValue) => {
            if (editor.value?.getHTML() !== newValue && !isHtmlMode.value) {
                editor.value?.commands.setContent(newValue || '', { emitUpdate: false });
            }
        },
    );

    watch(
        () => {
            if (!editor.value) return 0;
            for (const level of [1, 2, 3, 4, 5, 6] as const) {
                if (editor.value.isActive('heading', { level })) return level;
            }
            return 0;
        },
        (level) => {
            isSyncingHeading.value = true;
            currentHeadingLevel.value = level;
            // Сбрасываем флаг после того, как v-model обновится
            requestAnimationFrame(() => {
                isSyncingHeading.value = false;
            });
        },
        { immediate: true },
    );

    // Обработчик смены стиля через select
    const onHeadingChange = (e: Event) => {
        if (isSyncingHeading.value) return;
        if (!editor.value) return;

        const level = Number((e.target as HTMLSelectElement).value);

        if (level === 0) {
            editor.value.chain().focus().setParagraph().run();
        } else {
            editor.value
                .chain()
                .focus()
                .toggleHeading({ level: level as 1 | 2 | 3 | 4 | 5 | 6 })
                .run();
        }
    };
</script>

<template>
    <div
        class="border-rancho-ink-800 bg-rancho-ink-900/30 rounded-2xl border p-3 sm:rounded-3xl sm:p-4"
    >
        <!-- TOOLBAR -->
        <div class="border-rancho-ink-800 mb-4 space-y-2 border-b pb-4">
            <!-- MODE TOGGLE -->
            <button
                type="button"
                @click="isHtmlMode = !isHtmlMode"
                class="mode-toggle-btn m-auto block"
            >
                <component :is="isHtmlMode ? Eye : Code2" class="h-4 w-4" />
                {{ isHtmlMode ? 'Визуально' : 'HTML' }}
            </button>

            <!-- ROW 1: History + Headings + Paragraph -->
            <div class="flex flex-wrap items-center justify-center gap-1">
                <template v-if="!isHtmlMode && editor">
                    <!-- UNDO / REDO -->
                    <div class="flex items-center gap-0.5">
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

                    <div class="bg-rancho-ink-700 mx-1 h-5 w-px sm:mx-2" aria-hidden="true"></div>

                    <!-- HEADING SELECT -->
                    <select
                        :value="currentHeadingLevel"
                        @change="onHeadingChange"
                        class="border-rancho-ink-800 bg-rancho-ink-950 text-rancho-ink-300 focus:border-rancho-mint focus:ring-rancho-mint/20 h-8 cursor-pointer rounded-lg border pl-2 pr-7 text-xs font-bold transition-colors focus:outline-none focus:ring-2"
                        title="Стиль текста"
                        aria-label="Стиль текста"
                    >
                        <option value="0">Абзац</option>
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                        <option value="4">H4</option>
                        <option value="5">H5</option>
                        <option value="6">H6</option>
                    </select>

                    <div class="bg-rancho-ink-700 mx-1 h-5 w-px sm:mx-2" aria-hidden="true"></div>

                    <!-- TEXT STYLES -->
                    <div class="flex items-center justify-center gap-0.5">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBold().run()"
                            :class="{ 'active-btn': editor.isActive('bold') }"
                            class="toolbar-btn"
                            title="Жирный (Ctrl+B)"
                        >
                            <Bold class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="editor.chain().focus().toggleItalic().run()"
                            :class="{ 'active-btn': editor.isActive('italic') }"
                            class="toolbar-btn"
                            title="Курсив (Ctrl+I)"
                        >
                            <Italic class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="editor.chain().focus().toggleUnderline().run()"
                            :class="{ 'active-btn': editor.isActive('underline') }"
                            class="toolbar-btn"
                            title="Подчеркнутый (Ctrl+U)"
                        >
                            <UnderlineIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="bg-rancho-ink-700 mx-1 h-5 w-px sm:mx-2" aria-hidden="true"></div>

                    <!-- COLOR -->
                    <input
                        type="color"
                        @input="
                            editor
                                .chain()
                                .focus()
                                .setColor(($event.target as HTMLInputElement).value)
                                .run()
                        "
                        :value="editor.getAttributes('textStyle').color || '#b5ac96'"
                        class="border-rancho-ink-800 h-8 w-8 cursor-pointer rounded-lg border bg-transparent p-0.5"
                        title="Цвет текста"
                    />
                </template>
            </div>

            <!-- ROW 2: Lists, Blocks, Link, Image -->
            <div class="flex flex-wrap items-center justify-center gap-1">
                <template v-if="!isHtmlMode && editor">
                    <!-- LISTS / QUOTE -->
                    <div class="flex items-center gap-0.5">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBulletList().run()"
                            :class="{ 'active-btn': editor.isActive('bulletList') }"
                            class="toolbar-btn"
                            title="Маркированный список"
                        >
                            <List class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="editor.chain().focus().toggleOrderedList().run()"
                            :class="{ 'active-btn': editor.isActive('orderedList') }"
                            class="toolbar-btn"
                            title="Нумерованный список"
                        >
                            <ListOrdered class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBlockquote().run()"
                            :class="{ 'active-btn': editor.isActive('blockquote') }"
                            class="toolbar-btn"
                            title="Цитата"
                        >
                            <Quote class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="bg-rancho-ink-700 mx-1 h-5 w-px sm:mx-2" aria-hidden="true"></div>

                    <!-- BLOCKS -->
                    <div class="flex items-center gap-0.5">
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
                            title="Горизонтальная линия"
                        >
                            <Minus class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="bg-rancho-ink-700 mx-1 h-5 w-px sm:mx-2" aria-hidden="true"></div>

                    <!-- LINK / IMAGE -->
                    <div class="flex items-center gap-0.5">
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
                            class="toolbar-btn text-rose-400"
                            title="Удалить ссылку"
                        >
                            <Unlink class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="fileInput?.click()"
                            :disabled="isUploading"
                            class="toolbar-btn"
                            title="Загрузить изображение"
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
                </template>
            </div>
        </div>

        <!-- EDITOR -->
        <div class="grid gap-2 px-1 sm:px-2">
            <div v-show="!isHtmlMode" class="editor-container">
                <EditorContent :editor="editor" />
            </div>

            <div v-if="isHtmlMode" class="editor-container bg-rancho-ink-950">
                <textarea v-model="content" rows="30" class="html-textarea border-none"></textarea>
                <p
                    v-if="errors?.content"
                    class="px-2 pb-3 text-xs font-medium text-rose-500 sm:px-4"
                >
                    {{ errors?.content }}
                </p>
            </div>
        </div>
    </div>

    <!-- LINK MODAL (без изменений) -->
    <BaseModal :show="isLinkModalShow" title="Управление ссылкой" @close="isLinkModalShow = false">
        <div class="space-y-4">
            <div>
                <label
                    for="urllink"
                    class="text-rancho-ink-500 mb-2 block text-xs font-bold uppercase tracking-wider"
                >
                    URL адрес
                </label>
                <input
                    v-model="linkUrl"
                    id="urllink"
                    type="url"
                    placeholder="https://example.com"
                    class="border-rancho-ink-800 bg-rancho-ink-950 focus:border-rancho-mint focus:ring-rancho-mint/20 w-full rounded-2xl border p-4 text-white outline-none transition-colors focus:ring-2"
                    @keydown.enter="saveLink"
                />
            </div>

            <div class="flex gap-3">
                <button
                    @click="saveLink"
                    class="bg-rancho-mint text-rancho-ink-950 hover:bg-rancho-mint/90 flex-1 rounded-2xl py-4 text-xs font-black uppercase tracking-widest transition-all active:scale-95"
                >
                    Применить
                </button>

                <button
                    v-if="editor?.isActive('link')"
                    @click="
                        linkUrl = '';
                        saveLink();
                    "
                    class="rounded-2xl border border-rose-500/30 bg-rose-500/10 px-6 text-rose-400 transition-all hover:bg-rose-500 hover:text-white"
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
        @apply text-rancho-ink-500 hover:bg-rancho-ink-800 rounded-lg p-2 transition-all hover:text-white disabled:opacity-20;
    }

    .active-btn {
        @apply bg-rancho-mint/10 text-rancho-mint;
    }

    .mode-toggle-btn {
        @apply border-rancho-ink-800 bg-rancho-ink-950 text-rancho-ink-400 hover:border-rancho-mint flex items-center gap-2 rounded-xl border px-4 py-2 text-[10px] font-black uppercase tracking-wider transition-all hover:text-white;
    }

    .editor-container {
        @apply border-rancho-ink-800 bg-rancho-ink-950 focus-within:border-rancho-mint w-full overflow-hidden rounded-2xl border transition-all sm:rounded-3xl;
    }

    .html-textarea {
        @apply text-rancho-mint/90 min-h-[350px] w-full resize-none bg-transparent p-4 font-mono text-sm leading-relaxed outline-none sm:p-8;
    }

    :deep(.tiptap) {
        outline: none;
    }

    /* Чтобы цвет текста из расширения Color работал поверх стилей prose */
    :deep(.tiptap span[style]) {
        color: inherit;
    }
</style>
