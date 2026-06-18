import {
    AdjustmentsHorizontalIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

export const useAdminTabs = () => {
    const tabs = [
        { id: 'general', name: 'Основное', icon: DocumentTextIcon },
        { id: 'media', name: 'Медиа', icon: PhotoIcon },
        { id: 'features', name: 'Параметры', icon: AdjustmentsHorizontalIcon },
        { id: 'seo', name: 'SEO данные', icon: MagnifyingGlassIcon },
    ];

    return { tabs };
};
