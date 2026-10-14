<script setup lang="ts">
    import { onBeforeUnmount } from 'vue';

    import { debounce } from 'lodash-es';
    import { PackageIcon } from 'lucide-vue-next';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import type { AdminProductVariantDTO, QuickUpdatePayload } from '@/types';

    const props = defineProps<{
        variant: AdminProductVariantDTO;
        disabled?: boolean;
        outOfStock?: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'quick-update', id: number, data: QuickUpdatePayload): void;
        (e: 'edit', variant: AdminProductVariantDTO): void;
        (e: 'delete', variant: AdminProductVariantDTO): void;
    }>();

    const debouncedPriceUpdate = debounce((id: number, price: number) => {
        emit('quick-update', id, { price });
    }, 500);

    const debouncedStockUpdate = debounce((id: number, stock: number) => {
        emit('quick-update', id, { stock });
    }, 500);

    const handlePriceChange = (newPrice: number | null | undefined) => {
        if (newPrice === undefined || newPrice === null || newPrice === props.variant.price) {
            return;
        }

        debouncedPriceUpdate(props.variant.id, newPrice);
    };

    const handleStockChange = (newStock: number | null | undefined) => {
        if (newStock === undefined || newStock === null || newStock === props.variant.stock) {
            return;
        }

        debouncedStockUpdate(props.variant.id, newStock);
    };

    const handleDefaultChange = (isDefault: boolean) => {
        emit('quick-update', props.variant.id, { is_default: isDefault });
    };

    onBeforeUnmount(() => {
        debouncedPriceUpdate.flush();
        debouncedStockUpdate.flush();
    });
</script>

<template>
    <div
        role="listitem"
        class="border-rancho-ink-800 bg-rancho-ink-900/40 hover:border-rancho-mint/40 hover:bg-rancho-ink-900/60 group relative grid grid-cols-1 items-center gap-4 rounded-2xl border p-4 transition-all sm:grid-cols-2 lg:grid-cols-12 lg:px-6 lg:py-3"
        :class="{
            'pointer-events-none scale-[0.98] opacity-40 grayscale-[0.5]': disabled,
            'border-rose-800': outOfStock,
        }"
    >
        <!-- ТОВАР + НАЗВАНИЕ (mobile: full width, desktop: col-span-5) -->
        <div class="flex items-center gap-4 sm:col-span-2 lg:col-span-4">
            <div
                class="border-rancho-ink-700 bg-rancho-ink-800 relative h-14 w-14 shrink-0 overflow-hidden rounded-2xl border"
            >
                <AppImage
                    v-if="variant.media?.main"
                    :src="variant.media.main.url"
                    :alt="variant.name"
                    context="product"
                    type="thumbnails"
                />

                <div
                    v-else
                    class="text-rancho-ink-500 flex h-full w-full items-center justify-center"
                >
                    <PackageIcon class="h-6 w-6" />
                </div>
            </div>

            <div class="min-w-0">
                <h3
                    class="group-hover:text-rancho-mint truncate text-sm font-black text-white transition-colors"
                    :class="{ 'text-rose-400': outOfStock }"
                >
                    {{ variant.product?.name || 'Без названия' }}
                </h3>

                <div class="text-rancho-ink-500 flex items-center gap-2 text-[11px] font-bold">
                    <span class="truncate" :class="{ 'text-rose-400': outOfStock }">
                        {{ variant.name }}
                    </span>

                    <span
                        v-if="variant.unit"
                        class="bg-rancho-ink-300 text-rancho-ink-800 group-hover:bg-rancho-ink-200 group-hover:text-rancho-ink-900 absolute right-0 top-0 -translate-y-[30%] translate-x-[30%] rounded px-1.5 py-0.5 text-[9px] uppercase tracking-tighter opacity-90 transition-colors lg:left-0 lg:right-auto lg:-translate-x-[30%]"
                        :class="{ 'text-rose-400': outOfStock }"
                    >
                        {{ variant.unit.short }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ЦЕНА (mobile: 1 колонка из 2, desktop: col-span-2) -->
        <div class="flex items-center justify-center lg:col-span-2">
            <AdminNumberInput
                :model-value="variant.price"
                @update:model-value="handlePriceChange"
                :min="0"
                :is-money="true"
                :disabled="disabled"
            />
        </div>

        <!-- СКЛАД (mobile: 1 колонка из 2, desktop: col-span-2) -->
        <div class="flex items-center justify-center lg:col-span-2">
            <AdminNumberInput
                :model-value="variant.stock"
                @update:model-value="handleStockChange"
                :min="0"
                :step="1"
                :disabled="disabled"
            />
        </div>

        <!-- TOGGLE (mobile: full width, desktop: col-span-2) -->
        <div class="flex justify-center sm:col-span-2 lg:col-span-2">
            <BaseStatusToggle
                :model-value="variant.is_default"
                @update:model-value="handleDefaultChange"
                label="Главный"
                :disabled="disabled"
            />
        </div>

        <!-- КНОПКИ (mobile: full width, desktop: col-span-1) -->
        <div class="flex justify-end gap-2 sm:col-span-2">
            <AdminEditButton
                @click="$emit('edit', variant)"
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
