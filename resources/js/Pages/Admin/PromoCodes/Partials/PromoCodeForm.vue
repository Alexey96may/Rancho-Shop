<script setup lang="ts">
    import { useForm } from '@inertiajs/vue3';
    import { Link } from '@inertiajs/vue3';

    import { CalendarIcon, CheckIcon, ChevronLeftIcon } from '@heroicons/vue/24/outline';
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/dist/style.css';

    import { AdminPromoCode } from '@/types';

    const props = defineProps<{
        promo?: AdminPromoCode;
        isEdit?: boolean;
        returnPage?: number | string;
        typeOptions: Array<{ value: string; label: string }>;
    }>();

    const emit = defineEmits<{
        (e: 'submit', form: any): void;
    }>();

    const form = useForm({
        code: props.promo?.code ?? '',
        type: props.promo?.type ?? 'fixed',
        value: props.promo?.value ?? 0,
        min_order_amount: props.promo?.min_order_amount ?? 0,
        max_discount: props.promo?.max_discount ?? null,
        usage_limit: props.promo?.usage_limit ?? null,
        // V-Calendar отлично работает с ISO строками или объектами Date
        expires_at: props.promo?.expires_at ?? null,
        is_active: props.promo?.is_active ?? true,
    });

    const handleSubmit = () => {
        emit('submit', form);
    };
</script>

<template>
    <form
        @submit.prevent="handleSubmit"
        class="shadow-2xl max-w-4xl space-y-8 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8"
        aria-labelledby="form-title"
    >
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div class="col-span-full space-y-2">
                <label
                    for="code"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    Код купона
                </label>
                <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    placeholder="SUMMER2026"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-black uppercase text-white transition-all focus:border-orange-500 focus:ring-0"
                    :class="{ 'border-red-500/50': form.errors.code }"
                    :aria-invalid="!!form.errors.code"
                    aria-describedby="code-error"
                />
                <p
                    v-if="form.errors.code"
                    id="code-error"
                    class="ml-4 text-[10px] font-bold uppercase italic text-red-500"
                >
                    {{ form.errors.code }}
                </p>
            </div>

            <div class="space-y-2">
                <label
                    for="type"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Тип скидки</label
                >
                <select
                    id="type"
                    v-model="form.type"
                    class="w-full appearance-none rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500 focus:ring-0"
                >
                    <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
            </div>

            <div class="space-y-2">
                <label
                    for="value"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    Значение ({{ form.type === 'percent' ? '%' : 'в копейках' }})
                </label>
                <input
                    id="value"
                    v-model="form.value"
                    type="number"
                    min="0"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500"
                    :class="{ 'border-red-500/50': form.errors.value }"
                    :aria-invalid="!!form.errors.value"
                />
            </div>

            <div class="space-y-2">
                <label
                    for="min_order_amount"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    Мин. сумма заказа (в копейках)
                </label>
                <input
                    id="min_order_amount"
                    v-model="form.min_order_amount"
                    type="number"
                    min="0"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500"
                    :class="{ 'border-red-500/50': form.errors.min_order_amount }"
                />
                <p v-if="form.errors.min_order_amount" class="ml-4 text-[10px] text-red-500">
                    {{ form.errors.min_order_amount }}
                </p>
            </div>

            <div v-if="form.type === 'percent'" class="space-y-2">
                <label
                    for="max_discount"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    Макс. сумма скидки (в копейках)
                </label>
                <input
                    id="max_discount"
                    v-model="form.max_discount"
                    type="number"
                    min="0"
                    placeholder="Без ограничений"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500"
                    :class="{ 'border-red-500/50': form.errors.max_discount }"
                />
                <p v-if="form.errors.max_discount" class="ml-4 text-[10px] text-red-500">
                    {{ form.errors.max_discount }}
                </p>
            </div>

            <div class="space-y-2">
                <label
                    for="expires_at"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Срок действия</label
                >
                <DatePicker
                    v-model="form.expires_at"
                    mode="dateTime"
                    is-dark
                    is24hr
                    hide-time-header
                    :popover="{ visibility: 'click', placement: 'bottom' }"
                >
                    <template #default="{ inputValue, inputEvents }">
                        <div class="relative">
                            <input
                                id="expires_at"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500"
                                :value="inputValue"
                                v-on="inputEvents"
                                readonly
                                placeholder="Выберите дату и время"
                            />
                            <CalendarIcon
                                class="pointer-events-none absolute right-4 top-4 h-5 w-5 text-slate-500"
                            />
                        </div>
                    </template>
                </DatePicker>
            </div>

            <div class="space-y-2">
                <label
                    for="usage_limit"
                    class="ml-4 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Лимит использований</label
                >
                <input
                    id="usage_limit"
                    v-model="form.usage_limit"
                    type="number"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-bold text-white transition-all focus:border-orange-500"
                    placeholder="∞"
                />
            </div>

            <div class="col-span-full pt-4">
                <label class="flex cursor-pointer items-center gap-4">
                    <div class="relative">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="peer sr-only"
                            id="is_active"
                        />
                        <div
                            class="h-6 w-11 rounded-full bg-slate-800 transition-all peer-checked:bg-orange-600 peer-focus:ring-2 peer-focus:ring-orange-500/50"
                        ></div>
                        <div
                            class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-all peer-checked:left-6"
                        ></div>
                    </div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-white"
                    >
                        Промокод активен
                    </span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-slate-800/50 pt-8">
            <button
                type="submit"
                :disabled="form.processing"
                class="group flex items-center gap-3 rounded-2xl bg-orange-600 px-12 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 disabled:opacity-50"
            >
                <CheckIcon v-if="!form.processing" class="h-4 w-4" />
                <span
                    v-else
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                ></span>
                {{ isEdit ? 'Сохранить изменения' : 'Создать промокод' }}
            </button>

            <Link
                :href="route('admin.promocodes.index', { page: returnPage })"
                class="flex items-center gap-2 px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 transition-all hover:text-white"
            >
                <ChevronLeftIcon class="h-4 w-4" />
                Отмена
            </Link>
        </div>
    </form>
</template>
