<script setup lang="ts">
    import { PackageIcon } from 'lucide-vue-next';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditLink from '@/Components/Admin/UI/AdminEditLink.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import type { AdminProductVariantDTO, QuickUpdatePayload } from '@/types';

    const props = defineProps<{
        variant: AdminProductVariantDTO;
        disabled?: boolean;
        outOfStock?: boolean;
        currentPage?: number;
    }>();

    const emit = defineEmits<{
        (e: 'quick-update', id: number, data: QuickUpdatePayload): void;
        (e: 'delete', variant: AdminProductVariantDTO): void;
    }>();

    const handlePriceChange = (newPrice: number | null | undefined) => {
        if (newPrice === undefined || newPrice === null || newPrice === props.variant.price) {
            return;
        }

        emit('quick-update', props.variant.id, { price: newPrice });
    };

    const handleStockChange = (newStock: number | null | undefined) => {
        if (newStock === undefined || newStock === null || newStock === props.variant.stock) {
            return;
        }

        emit('quick-update', props.variant.id, { stock: newStock });
    };

    const handleDefaultChange = (newStock: boolean) => {
        emit('quick-update', props.variant.id, { is_default: newStock });
    };
</script>

<template>
    <div
        role="listitem"
        class="group relative grid grid-cols-1 items-center gap-4 rounded-[1rem] border border-slate-800 bg-slate-900/40 p-4 transition-all hover:border-orange-500/30 hover:bg-slate-900/60 lg:grid-cols-12 lg:px-8 lg:py-3"
        :class="{
            'pointer-events-none scale-[0.98] opacity-40 grayscale-[0.5]': disabled,
            'border-red-800': outOfStock,
        }"
    >
        <div class="col-span-5 flex items-center gap-4">
            <div
                class="relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-slate-700/50 bg-slate-800"
            >
                <AppImage
                    v-if="variant.media?.main"
                    :src="variant.media.main.url"
                    :alt="variant.name"
                    context="product"
                    type="thumbnails"
                />

                <div v-else class="flex h-full w-full items-center justify-center text-slate-600">
                    <PackageIcon class="h-6 w-6" />
                </div>
            </div>
            <div class="min-w-0">
                <h3
                    class="truncate text-sm font-black text-white transition-colors group-hover:text-orange-500"
                    :class="{
                        'text-red-800': outOfStock,
                    }"
                >
                    {{ variant.product?.name || 'Без названия' }}
                </h3>
                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                    <span
                        class="truncate"
                        :class="{
                            'text-red-800': outOfStock,
                        }"
                        >{{ variant.name }}</span
                    >
                    <span
                        v-if="variant.unit"
                        class="absolute left-0 top-0 -translate-x-[25%] -translate-y-[25%] rounded bg-slate-400 px-1.5 py-0.5 text-[9px] uppercase tracking-tighter text-slate-800 opacity-90 transition-colors group-hover:bg-slate-300 group-hover:text-orange-800"
                        :class="{
                            'text-red-800': outOfStock,
                        }"
                    >
                        {{ variant.unit.short }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-span-2 flex flex-col items-center justify-center">
            <AdminNumberInput
                :model-value="variant.price"
                @update:model-value="handlePriceChange"
                :min="0"
                :is-money="true"
                :disabled="disabled"
            />
        </div>

        <div class="col-span-2 flex items-center justify-center">
            <AdminNumberInput
                :model-value="variant.stock"
                @update:model-value="handleStockChange"
                :min="0"
                :step="1"
                :disabled="disabled"
            />
        </div>

        <div class="col-span-2 flex justify-center">
            <BaseStatusToggle
                :model-value="variant.is_default"
                @update:model-value="handleDefaultChange"
                label="Главный"
                :disabled="disabled"
            />
        </div>

        <div class="col-span-1 flex justify-end gap-2">
            <AdminEditLink
                :href="
                    route('admin.catalog.edit', {
                        catalog: variant.id,
                        page: currentPage || 1,
                    })
                "
                :title="`Редактировать ${variant.name}`"
                :disabled="disabled"
            />

            <AdminDeleteButton
                @click="$emit('delete', variant)"
                title="Удалить"
                :disabled="disabled"
            />
        </div>
    </div>
</template>
