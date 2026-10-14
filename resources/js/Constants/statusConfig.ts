import type { OrderStatus } from '@/types';

export interface StatusDetail {
    label: string;
    class: string;
}

export const ORDER_STATUSES: Record<OrderStatus, StatusDetail> = {
    new: {
        label: 'Новый',
        class: 'bg-rancho-sky/10 text-rancho-sky border-rancho-sky/30',
    },
    confirmed: {
        label: 'Подтвержден',
        class: 'bg-rancho-buttercup/10 text-rancho-buttercup border-rancho-buttercup/30',
    },
    delivering: {
        label: 'В доставке',
        class: 'bg-rancho-ember/10 text-rancho-ember border-rancho-ember/30',
    },
    completed: {
        label: 'Завершен',
        class: 'bg-rancho-pine/15 text-rancho-mint border-rancho-pine/40',
    },
    cancelled: {
        label: 'Отменен',
        class: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
    },
};
