<script setup lang="ts">
    defineProps<{
        roles: Array<{ value: string; label: string }>;
        selectedRole: string | null;
    }>();

    defineEmits(['change']);
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <button
            @click="$emit('change', null)"
            class="rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest transition-all"
            :class="
                !selectedRole
                    ? 'shadow-lg bg-orange-600 text-white shadow-orange-600/20'
                    : 'bg-slate-900 text-slate-500 hover:bg-slate-800'
            "
        >
            Все
        </button>

        <button
            v-for="role in roles"
            :key="role.value"
            @click="$emit('change', role.value)"
            class="rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest transition-all"
            :class="
                selectedRole === role.value
                    ? 'shadow-lg bg-orange-600 text-white shadow-orange-600/20'
                    : 'bg-slate-900 text-slate-500 hover:bg-slate-800'
            "
        >
            {{ role.label }}
        </button>
    </div>
</template>
