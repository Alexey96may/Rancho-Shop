<script setup lang="ts">
    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';

    const props = defineProps<{
        features: Record<string, string>;
    }>();

    const emit = defineEmits(['update:features']);

    const addFeature = () => {
        const newFeatures = { ...props.features };
        const newKey = `Параметр ${Object.keys(newFeatures).length + 1}`;
        newFeatures[newKey] = '';
        emit('update:features', newFeatures);
    };

    const removeFeature = (key: string) => {
        const newFeatures = { ...props.features };
        delete newFeatures[key];
        emit('update:features', newFeatures);
    };

    const renameKey = (oldKey: string, newKey: string) => {
        if (oldKey === newKey || !newKey) return;
        const newFeatures = { ...props.features };
        const value = newFeatures[oldKey];
        delete newFeatures[oldKey];
        newFeatures[newKey] = value;
        emit('update:features', newFeatures);
    };
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">Доп. параметры</h3>
            <button
                type="button"
                @click="addFeature"
                class="rounded-xl bg-slate-800 px-4 py-2 text-[10px] font-black uppercase text-orange-500 transition-colors hover:bg-slate-700"
            >
                + Добавить поле
            </button>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <div
                v-for="(value, key) in features"
                :key="key"
                class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-2 pl-4 focus-within:border-orange-500/50"
            >
                <input
                    type="text"
                    :value="key"
                    @change="(e) => renameKey(String(key), (e.target as HTMLInputElement).value)"
                    class="w-1/3 border-none bg-transparent p-2 text-[11px] font-bold uppercase text-orange-500 focus:ring-0"
                />

                <div class="h-6 w-px bg-slate-800"></div>

                <input
                    v-model="features[key]"
                    type="text"
                    class="flex-1 border-none bg-transparent p-2 text-sm text-white focus:ring-0"
                    placeholder="Значение"
                />

                <AdminDeleteButton
                    @click="removeFeature(String(key))"
                    title="Удалить параметр"
                    class="opacity-25 group-hover:opacity-75"
                />
            </div>
        </div>
    </div>
</template>
