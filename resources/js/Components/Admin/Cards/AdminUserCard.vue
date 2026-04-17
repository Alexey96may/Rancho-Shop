<script setup lang="ts">
    import {
        EnvelopeIcon,
        PencilSquareIcon,
        PhoneIcon,
        ShieldCheckIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import { User } from '@/types';

    defineProps<{
        user: User;
    }>();

    const getRoleStyles = (role: string) => {
        const roles: Record<string, string> = {
            admin: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
            worker: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            customer: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        };
        return roles[role.toLowerCase()] || 'bg-slate-800 text-slate-400 border-slate-700';
    };
</script>

<template>
    <div
        class="group relative flex flex-col rounded-3xl border border-slate-800 bg-slate-900/40 p-5 transition-all duration-300 focus-within:ring-2 focus-within:ring-orange-500/50 hover:border-orange-500/30 hover:bg-slate-900"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-slate-950 ring-1 ring-slate-800"
                >
                    <img
                        v-if="user.avatar"
                        :src="user.avatar"
                        class="h-full w-full object-cover"
                        :alt="user.name"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-slate-600"
                    >
                        <UserIcon class="h-7 w-7" />
                    </div>
                </div>

                <div class="min-w-0">
                    <h2
                        class="truncate text-base font-black uppercase tracking-tight text-white transition-colors group-hover:text-orange-500"
                    >
                        {{ user.name }}
                    </h2>
                    <span
                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-widest"
                        :class="getRoleStyles(user.role)"
                    >
                        <ShieldCheckIcon v-if="user.is_admin" class="h-2.5 w-2.5" />
                        {{ user.role }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-5 space-y-2">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <EnvelopeIcon class="h-4 w-4 text-slate-600" />
                <span class="truncate">{{ user.email }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <PhoneIcon class="h-4 w-4 text-slate-600" />
                <span>{{ user.phone || 'Телефон не указан' }}</span>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-slate-800/50 pt-4">
            <div class="flex items-center gap-1.5" title="Количество заказов">
                <ShoppingBagIcon class="h-4 w-4 text-orange-600" />
                <span class="text-xs font-black text-white">{{ user.orders_count || 0 }}</span>
                <span class="text-[10px] font-bold uppercase tracking-tighter text-slate-600"
                    >заказов</span
                >
            </div>

            <slot name="actions" />
        </div>
    </div>
</template>
