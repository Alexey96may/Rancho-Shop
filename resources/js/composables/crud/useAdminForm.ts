import { ref } from 'vue';

import { router } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

import { useFlash } from '@/composables/ui/useFlash';

const { notify } = useFlash();

type DeepNullablePartial<T> = {
    [P in keyof T]?: T[P] | null;
};

interface SubmitOptions {
    onSuccess?: () => void;
    preserveScroll?: boolean;
    hasFiles?: boolean;
}

type RouterPostOptions = Parameters<typeof router.post>[2];

export const useAdminForm = () => {
    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentId = ref<number | string | null>(null);

    /**
     * Universal Form Submission (Create or Update)
     *
     * @param form              The Inertia form object (useForm)
     * @param baseRouteName     The base part of the route (e.g., 'admin.products')
     * @param id                The ID of the entity (null for create mode, string/number for edit mode)
     * @param options       Optional configuration (onSuccess callback, hasFiles flag)
     */
    const submitForm = async <T extends object>(
        form: InertiaForm<T>,
        baseRouteName: string,
        id: number | string | null = null,
        options: SubmitOptions = {},
    ) => {
        const isEditMode = id !== null;
        const { onSuccess, preserveScroll, hasFiles = false } = options;

        const inertiaOptions = {
            ...(hasFiles && { forceFormData: true }),

            preserveScroll: preserveScroll,
            onSuccess: () => {
                if (onSuccess) onSuccess();
            },
            onError: (errors: Record<string, string>) => {
                console.error(errors);
                notify('Ошибка валидации', 'error');
            },
        };

        if (isEditMode) {
            if (hasFiles) {
                const routerOptions: RouterPostOptions = {
                    forceFormData: true,
                    onBefore: () => {
                        form.clearErrors();
                    },
                    onStart: () => {
                        form.processing = true;
                    },
                    onFinish: () => {
                        form.processing = false;
                    },
                    onSuccess: () => {
                        form.reset();
                        if (onSuccess) onSuccess();
                    },
                    onError: (errors) => {
                        form.setError(errors as unknown as Parameters<typeof form.setError>[0]);
                        console.error(errors);
                        notify('Ошибка валидации', 'error');
                    },
                };

                router.post(
                    route(`${baseRouteName}.update`, id),
                    {
                        ...form.data(),
                        _method: 'PUT',
                    },
                    routerOptions,
                );
            } else {
                form.put(route(`${baseRouteName}.update`, id), inertiaOptions);
            }
        } else {
            form.post(route(`${baseRouteName}.store`), inertiaOptions);
        }
    };

    /**
     * Generic modal opener (Create or Edit)
     *
     * @param form   Inertia form object (useForm)
     * @param entity The entity to edit (or null for creation)
     */
    const openModal = <T extends object>(
        form: InertiaForm<T>,
        entity: (DeepNullablePartial<T> & { id?: number | string }) | null = null,
    ) => {
        form.clearErrors();
        editMode.value = !!entity;

        if (entity) {
            currentId.value = entity.id ?? null;

            const formKeys = Object.keys(form.data()) as Array<keyof T>;
            const updatedData = {} as Partial<T>;

            formKeys.forEach((key) => {
                if (key in entity) {
                    updatedData[key] = entity[key] as T[keyof T];
                }
            });

            if (typeof (form as any).setData === 'function') {
                (form as any).setData(updatedData);
            } else {
                Object.assign(form, updatedData);
            }
        } else {
            currentId.value = null;
            form.reset();
        }

        isModalOpen.value = true;
    };

    const closeModal = <T extends object>(form: InertiaForm<T>) => {
        isModalOpen.value = false;
        editMode.value = false;
        currentId.value = null;
        form.reset();
    };

    return {
        isModalOpen,
        editMode,
        currentId,
        openModal,
        closeModal,
        submitForm,
    };
};
