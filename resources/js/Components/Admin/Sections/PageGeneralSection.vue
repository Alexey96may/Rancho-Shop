<script setup lang="ts">
    import type { Errors } from '@inertiajs/core';

    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';

    interface SelectOption {
        id: string;
        name: string;
    }

    const props = defineProps<{
        page_types: SelectOption[];
        templates: SelectOption[];
        errors: Errors;
        disabled: boolean;
    }>();

    const title = defineModel<string>('title');
    const slug = defineModel<string>('slug');
    const type = defineModel<string>('type');
    const template = defineModel<string>('template');
    const showInMenu = defineModel<boolean>('showInMenu');
    const menuOrder = defineModel<number>('menuOrder');
    const isCommentable = defineModel<boolean>('isCommentable');
</script>

<template>
    <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
        <!-- Основное -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <BaseInput
                v-model="title"
                v-model:error="errors.title"
                label="Заголовок страницы"
                placeholder="Например: О нас"
                :disabled="disabled"
            />

            <BaseInput
                v-model="slug"
                v-model:error="errors.slug"
                label="URL Путь (Slug)"
                placeholder="Например: about_us"
                :disabled="disabled"
            />
        </div>

        <!-- Тип и шаблон -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <BaseSelect
                v-model="type"
                v-model:error="errors.type"
                :options="page_types"
                label="Тип страницы"
                :disabled="disabled"
                variant="admin"
            />

            <BaseSelect
                v-model="template"
                v-model:error="errors.template"
                :options="templates"
                label="Системный шаблон"
                :disabled="disabled"
                variant="admin"
            />
        </div>

        <BaseSwitch
            v-if="isCommentable !== undefined"
            v-model="isCommentable"
            label="Разрешить комментарии"
            active-text="Посетители могут оставлять отзывы"
            inactive-text="Комментарии отключены"
            :disabled="disabled"
        />

        <!-- Навигация в меню -->
        <div class="space-y-4">
            <BaseSwitch
                v-if="showInMenu !== undefined"
                v-model="showInMenu"
                label="Показывать в меню"
                active-text="Пункт появится в шапке сайта"
                inactive-text="Скрыто из меню"
                :disabled="disabled"
            />

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showInMenu">
                    <AdminNumberInput
                        v-model.number="menuOrder"
                        v-model:error="errors.menu_order"
                        :min="0"
                        label="Порядок в меню"
                        placeholder="0"
                        :disabled="disabled"
                    />
                    <p class="mt-1.5 px-1 text-[10px] text-slate-500">
                        Меньше число — выше пункт. Одинаковые значения сортируются по алфавиту.
                    </p>
                </div>
            </Transition>
        </div>
    </div>
</template>
