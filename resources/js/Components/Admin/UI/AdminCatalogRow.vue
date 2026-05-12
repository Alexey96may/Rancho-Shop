<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { MinusIcon, PackageIcon, PlusIcon, SquarePenIcon } from 'lucide-vue-next';

    import type { AdminProductVariantDTO } from '@/types';

    const props = defineProps<{
        variant: AdminProductVariantDTO;
        disabled?: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'quick-update', id: number, data: { price?: number; stock?: number }): void;
    }>();

    // Статус наличия
    const stockStatus = computed(() => ({
        label: props.variant.stock > 0 ? 'В наличии' : 'Нет на складе',
        class:
            props.variant.stock > 0
                ? 'bg-emerald-500/10 text-emerald-500'
                : 'bg-rose-500/10 text-rose-500',
    }));

    // Обработка ручного ввода цены (переводим в копейки для сервера)
    const handlePriceChange = (e: Event) => {
        const val = parseFloat((e.target as HTMLInputElement).value);
        if (!isNaN(val)) {
            emit('quick-update', props.variant.id, { price: Math.round(val * 100) });
        }
    };
</script>

<template>
    <div
        role="listitem"
        class="group relative grid grid-cols-1 items-center gap-4 rounded-[2rem] border border-slate-800 bg-slate-900/40 p-4 transition-all hover:border-orange-500/30 hover:bg-slate-900/60 lg:grid-cols-12 lg:px-8 lg:py-3"
        :class="{ 'pointer-events-none scale-[0.98] opacity-40 grayscale-[0.5]': disabled }"
    >
        <div class="col-span-5 flex items-center gap-4">
            <div
                class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-800"
            >
                <img
                    v-if="variant.media?.[0]"
                    :src="variant.media[0].url"
                    :alt="variant.name"
                    class="h-full w-full object-cover"
                />
                <div v-else class="flex h-full w-full items-center justify-center text-slate-600">
                    <PackageIcon class="h-6 w-6" />
                </div>
            </div>
            <div class="min-w-0">
                <h3
                    class="truncate text-sm font-black text-white transition-colors group-hover:text-orange-500"
                >
                    {{ variant.product?.name || 'Без названия' }}
                </h3>
                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                    <span class="truncate">{{ variant.name }}</span>
                    <span
                        v-if="variant.unit"
                        class="rounded bg-slate-800 px-1.5 py-0.5 text-[9px] uppercase tracking-tighter opacity-50"
                    >
                        {{ variant.unit.short }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-span-2 flex flex-col items-center justify-center">
            <div class="group/input relative">
                <label :for="'price-' + variant.id" class="sr-only"
                    >Цена для {{ variant.name }}</label
                >
                <input
                    :id="'price-' + variant.id"
                    type="number"
                    step="0.01"
                    :value="variant.price / 100"
                    @change="handlePriceChange"
                    :disabled="disabled"
                    class="w-24 rounded-xl border-transparent bg-slate-950/50 p-2 text-center text-sm font-black text-orange-500 transition-all focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10"
                />
                <span
                    class="absolute -right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600"
                    >₽</span
                >
            </div>
        </div>

        <div class="col-span-2 flex items-center justify-center">
            <div
                class="flex items-center gap-1 rounded-xl border border-slate-800/50 bg-slate-950/50 p-1"
            >
                <button
                    @click="emit('quick-update', variant.id, { stock: variant.stock - 1 })"
                    :disabled="disabled || variant.stock <= 0"
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white transition-all hover:bg-red-500/20 hover:text-red-500 disabled:opacity-20"
                    aria-label="Уменьшить остаток"
                >
                    <MinusIcon class="h-3 w-3" />
                </button>

                <span class="min-w-[2.5rem] text-center text-xs font-black text-white">
                    {{ variant.stock }}
                </span>

                <button
                    @click="emit('quick-update', variant.id, { stock: variant.stock + 1 })"
                    :disabled="disabled"
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white transition-all hover:bg-emerald-500/20 hover:text-emerald-500"
                    aria-label="Увеличить остаток"
                >
                    <PlusIcon class="h-3 w-3" />
                </button>
            </div>
        </div>

        <div class="col-span-2 flex justify-center">
            <div
                class="border-current/10 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-widest"
                :class="stockStatus.class"
                role="status"
            >
                <span class="h-1 w-1 animate-pulse rounded-full bg-current"></span>
                {{ stockStatus.label }}
            </div>
        </div>

        <div class="col-span-1 flex justify-end">
            <Link
                :href="route('admin.catalog.edit', variant.id)"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-slate-400 transition-all hover:bg-orange-600 hover:text-white focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                title="Полное редактирование"
            >
                <SquarePenIcon class="h-5 w-5" />
                <span class="sr-only">Редактировать {{ variant.name }}</span>
            </Link>
        </div>
    </div>
</template>
