import { ref, watch } from 'vue';

import { usePage } from '@inertiajs/vue3';

import { useNotificationsStore } from '@/stores/notifications';
import type { FlashPayload, SharedData } from '@/types';

export function useFlashToasts(): void {
    const page = usePage<SharedData>();
    const notify = useNotificationsStore();
    const lastKey = ref<string | null>(null);

    watch(
        () => page.props.flash as FlashPayload | undefined,
        (flash) => {
            if (!flash) return;

            const message = flash.success || flash.error || flash.warning || flash.message;
            if (!message) return;

            // Защита от повторной обработки того же flash
            const key = `${page.url}_${JSON.stringify(flash)}`;
            if (key === lastKey.value) return;
            lastKey.value = key;

            if (flash.success) notify.success(message);
            else if (flash.error) notify.error(message);
            else if (flash.warning) notify.warning(message);
            else notify.info(message);
        },
        { deep: true, immediate: true },
    );
}
