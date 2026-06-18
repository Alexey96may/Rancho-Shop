import { OrderStatus } from '@/types';

export interface StatusDetail {
    label: string;
    class: string;
}

export const ORDER_STATUSES: Record<OrderStatus, StatusDetail> = {
    new: {
        label: 'Новый',
        class: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    },
    confirmed: {
        label: 'Подтвержден',
        class: 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
    },
    delivering: {
        label: 'В доставке',
        class: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
    },
    completed: {
        label: 'Завершен',
        class: 'bg-green-500/10 text-green-500 border-green-500/20',
    },
    cancelled: {
        label: 'Отменен',
        class: 'bg-red-500/10 text-red-500 border-red-500/20',
    },
};
