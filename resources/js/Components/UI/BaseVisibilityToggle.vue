<script setup lang="ts">
    import { computed } from 'vue';

    import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

    interface Props {
        active: boolean;
        disabled?: boolean;
        activeTitle?: string;
        inactiveTitle?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        disabled: false,
        activeTitle: 'Опубликовано',
        inactiveTitle: 'Черновик',
    });

    const emit = defineEmits<{
        (e: 'toggle'): void;
    }>();

    const currentTitle = computed(() => (props.active ? props.activeTitle : props.inactiveTitle));

    const handleClick = () => {
        if (!props.disabled) {
            emit('toggle');
        }
    };
</script>

<template>
    <button
        type="button"
        @click="handleClick"
        :disabled="disabled"
        :aria-pressed="active"
        :aria-label="currentTitle"
        :title="currentTitle"
        class="group relative inline-flex items-center justify-center rounded-xl p-2.5 transition-all focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:ring-offset-2 focus:ring-offset-slate-900 active:scale-90"
        :class="[
            disabled
                ? 'cursor-not-allowed bg-slate-800 text-slate-600 opacity-40'
                : active
                  ? 'shadow-sm bg-emerald-500/10 text-emerald-500 shadow-emerald-500/5 hover:bg-emerald-500/20'
                  : 'bg-rose-500/10 text-rose-500/60 hover:bg-rose-500/20 hover:text-rose-500',
        ]"
    >
        <div class="relative h-5 w-5">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-1 opacity-0 rotate-12"
                enter-to-class="translate-y-0 opacity-100 rotate-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-75"
                mode="out-in"
            >
                <component
                    class="h-5 w-5 transition-transform"
                    :class="{ 'group-hover:scale-110': !disabled }"
                    :is="active ? EyeIcon : EyeSlashIcon"
                    :key="active ? 'visible' : 'hidden'"
                    aria-hidden="true"
                />
            </Transition>
        </div>

        <span class="sr-only">{{ currentTitle }}</span>
    </button>
</template>
