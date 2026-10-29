<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import DeliveryZoneManager from '@/Components/Admin/Shared/DeliveryZoneManager.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { DeliveryZone, ResourceCollection } from '@/types';

    import { SETTINGS_LABELS, SETTINGS_SECTIONS } from './Data/settingsStructure';

    type SettingType = 'string' | 'integer' | 'boolean' | 'json';

    interface Setting {
        key: string;
        value: string | number | boolean | DeliveryZone[];
        type: SettingType;
    }

    const props = defineProps({
        settings: {
            type: Object as PropType<ResourceCollection<Setting>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "settings" prop must be a ResourceCollection containing a "data" array.',
                    );
                }
                return hasData;
            },
        },
    });

    defineOptions({ layout: AdminLayout });

    const activeSection = ref('general');

    const form = useForm({
        settings: props.settings.data.map((s) => ({
            key: s.key,
            value: s.value,
            type: s.type,
        })),
    });

    const currentFields = computed(() => {
        const section = SETTINGS_SECTIONS.find((s) => s.id === activeSection.value);
        return form.settings.filter((s) => section?.keys.includes(s.key));
    });

    const submit = () => {
        form.post(route('admin.settings.bulk'), {
            preserveScroll: true,
        });
    };

    const formatLabel = (key: string) => SETTINGS_LABELS[key] || key;

    const typeOptions = [
        { value: 'open', label: 'Открыт (Принимаем заказы)' },
        { value: 'closed', label: 'Закрыт' },
        { value: 'maintenance', label: 'Технические работы' },
    ];

    const asZones = (value: any): DeliveryZone[] => {
        return value as DeliveryZone[];
    };
</script>

<template>
    <div class="space-y-6">
        <Teleport to="#admin-header-content">
            <AdminPageHeader title="Модерация Настроек" subtitle="Управление настройками сайта" />
        </Teleport>

        <!-- TABS -->
        <nav
            class="no-scrollbar flex items-center gap-2 overflow-x-auto py-2"
            role="tablist"
            aria-label="Разделы настроек"
        >
            <button
                v-for="section in SETTINGS_SECTIONS"
                :key="section.id"
                type="button"
                @click="activeSection = section.id"
                role="tab"
                :aria-selected="activeSection === section.id"
                class="flex shrink-0 items-center gap-2 whitespace-nowrap rounded-2xl px-5 py-3 text-sm font-bold outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
                :class="
                    activeSection === section.id
                        ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                        : 'border border-rancho-ink-800 bg-rancho-ink-800/40 text-rancho-ink-400 hover:bg-rancho-ink-800 hover:text-rancho-ink-200'
                "
            >
                <component :is="section.icon" class="h-4 w-4" aria-hidden="true" />
                {{ section.label }}
            </button>
        </nav>

        <!-- CONTENT -->
        <main
            class="min-h-[500px] rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:rounded-3xl sm:p-6 md:p-8"
        >
            <form @submit.prevent="submit" class="space-y-6 overflow-hidden">
                <Transition name="fade-slide">
                    <div :key="activeSection" class="space-y-6">
                        <div
                            v-for="(field, index) in currentFields"
                            :key="field.key"
                            class="group flex flex-col gap-2 border-b border-rancho-ink-800/60 pb-6 last:border-0"
                        >
                            <!-- BOOLEAN -->
                            <BaseSwitch
                                v-if="field.type === 'boolean'"
                                :model-value="!!field.value"
                                @update:model-value="field.value = $event"
                                :label="formatLabel(field.key)"
                                active-text="Включено"
                                inactive-text="Выключено"
                                :disabled="form.processing"
                                theme="dark"
                            />

                            <!-- SHOP STATUS -->
                            <BaseSelect
                                v-else-if="field.key === 'shop_status'"
                                v-model="field.value"
                                :options="typeOptions"
                                :label="formatLabel(field.key)"
                                value-key="value"
                                label-key="label"
                                variant="admin"
                                class="w-full lg:w-64"
                            />

                            <!-- DELIVERY ZONES -->
                            <div v-else-if="field.key === 'delivery_zones'">
                                <DeliveryZoneManager
                                    :model-value="asZones(field.value)"
                                    @update:model-value="field.value = $event"
                                />
                            </div>

                            <!-- JSON -->
                            <AdminBaseTextarea
                                v-else-if="field.type === 'json' || field.key === 'ai_bot_prompt'"
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                :model-value="String(field.value)"
                                @update:model-value="field.value = $event"
                                :error="form.errors[`settings.${index}.value`]"
                                :disabled="form.processing"
                                rows="6"
                            />

                            <!-- INTEGER -->
                            <AdminNumberInput
                                v-else-if="field.type === 'integer'"
                                :model-value="Number(field.value)"
                                @update:model-value="field.value = Number($event)"
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                v-model:error="form.errors.settings"
                                :min="0"
                                :disabled="form.processing"
                            />

                            <!-- STRING -->
                            <BaseInput
                                v-else
                                :id="field.key"
                                :label="formatLabel(field.key)"
                                :model-value="String(field.value)"
                                @update:model-value="field.value = String($event)"
                                v-model:error="form.errors.settings"
                                :disabled="form.processing"
                                theme="dark"
                            />

                            <p class="text-[10px] italic text-rancho-ink-500">
                                Ключ в БД: {{ field.key }}
                            </p>
                        </div>
                    </div>
                </Transition>

                <!-- FOOTER -->
                <div
                    class="mt-8 flex flex-col gap-4 border-t border-rancho-ink-800 pt-6 sm:flex-row sm:items-center sm:justify-between sm:pt-8"
                >
                    <div class="text-xs text-rancho-ink-500">
                        Изменения применяются сразу ко всей системе
                    </div>

                    <BaseCreateButton
                        type="submit"
                        :label="form.processing ? 'Сохраняем...' : 'Сохранить всё'"
                        :disabled="form.processing"
                        class="self-end sm:self-auto"
                    />
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.3s ease;
    }

    .fade-slide-leave-active {
        width: 100%;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(20px);
    }

    .fade-slide-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-slide-enter-active,
        .fade-slide-leave-active {
            transition: none;
        }
    }
</style>
