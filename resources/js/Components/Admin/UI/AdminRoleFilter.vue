<script setup lang="ts">
    defineProps<{
        roles: Array<{ value: string; label: string }>;
        selectedRole: string | null;
    }>();

    defineEmits(['change']);
</script>

<template>
    <div class="flex gap-2 overflow-auto py-2">
        <button
            type="button"
            @click="$emit('change', null)"
            class="rounded-xl px-4 py-2 text-xs font-black transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
            :class="
                !selectedRole
                    ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                    : 'bg-rancho-ink-900 text-rancho-ink-500 hover:bg-rancho-ink-800 hover:text-rancho-ink-200'
            "
        >
            Все
        </button>

        <button
            v-for="role in roles"
            :key="role.value"
            type="button"
            @click="$emit('change', role.value)"
            class="rounded-xl px-4 py-2 text-xs font-black transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
            :class="
                selectedRole === role.value
                    ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                    : 'bg-rancho-ink-900 text-rancho-ink-500 hover:bg-rancho-ink-800 hover:text-rancho-ink-200'
            "
        >
            {{ role.label }}
        </button>
    </div>
</template>
