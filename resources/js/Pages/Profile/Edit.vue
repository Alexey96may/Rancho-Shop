<script setup lang="ts">
    import { PropType, computed } from 'vue';

    import { Link, useForm, usePage } from '@inertiajs/vue3';

    import { CurrencyDollarIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import { Order, ResourceSingle, SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: ProfileLayout });

    const props = defineProps({
        latestOrder: {
            type: Object as PropType<ResourceSingle<Order> | null>,
            required: true,
            default: null,
        },
        stats: {
            type: Object as PropType<{
                total_orders: number;
                total_spent: number;
            }>,
            required: true,
            validator: (value: unknown) => {
                const obj = value as Record<string, unknown>;

                return (
                    typeof obj?.total_orders === 'number' && typeof obj?.total_spent === 'number'
                );
            },
        },
    });

    const page = usePage<SharedData>();
    const user = page.props.auth?.user?.data;

    const form = useForm({
        name: user?.name || '',
        email: user?.email || '',
        phone: user?.phone || '',
        address: null, //todo
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const roleColors: Record<string, string> = {
        emerald: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        orange: 'border-orange-500/20 bg-orange-500/10 text-orange-400',
        blue: 'border-blue-500/20 bg-blue-500/10 text-blue-400',
        rose: 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        slate: 'border-slate-700 bg-slate-800 text-slate-400',
    };

    const submit = () => {
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
            onError: () => alert('HHHHH'),
        });
    };

    const computedTotalOrders = computed(() => props.stats?.total_orders || 0);
    const computedTotalSpent = computed(() => formatMoney(props.stats?.total_spent || 0));

    const computedLatestOrderPrice = computed(() =>
        formatMoney(props.latestOrder?.data.total_price || 0),
    );
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="mb-1 block text-xs font-medium text-slate-500"
                    >Ваша роль на сайте:</span
                >
                <span
                    v-if="user?.role"
                    class="inline-block rounded-xl border px-3 py-1 text-xs font-black uppercase tracking-wider"
                    :class="roleColors[user.role.color] || roleColors.slate"
                >
                    {{ user.role.label }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <StatCard
                label="Заказов оформлено"
                :value="computedTotalOrders"
                :icon="ShoppingBagIcon"
                labelColor="text-green-500"
            />
            <StatCard
                label="Сумма покупок"
                :value="computedTotalSpent"
                :icon="CurrencyDollarIcon"
            />
        </div>

        <div
            v-if="latestOrder"
            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-800/60 bg-gradient-to-r from-slate-950 to-slate-900/50 p-4"
        >
            <div class="space-y-1">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400"
                    >Последний заказ</span
                >
                <div class="text-xs text-slate-300">
                    От
                    <BaseSmartTime :date="latestOrder?.data.created_at" />
                    на сумму
                    <span class="font-bold text-white">{{ computedLatestOrderPrice }}</span>
                </div>
            </div>

            <Link
                :href="route('profile.orders.index')"
                class="bg-slate-850 rounded-xl border border-slate-800 px-3 py-2 text-xs font-bold text-slate-300 transition-colors hover:bg-slate-800 hover:text-white"
            >
                Все заказы
            </Link>
        </div>

        <hr class="border-slate-900" />

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="Имя"
                    placeholder="Ваше имя..."
                    :disabled="form.processing"
                />

                <BaseInput
                    v-model="form.email"
                    v-model:error="form.errors.email"
                    label="Электронная почта"
                    placeholder="Ваш email..."
                    :disabled="form.processing"
                    type="email"
                />

                <BaseInput
                    v-model="form.phone"
                    v-model:error="form.errors.phone"
                    label="Номер телефона"
                    placeholder="Ваш email..."
                    :disabled="form.processing"
                    type="tel"
                />
            </div>

            <hr class="border-slate-900" />

            <div class="space-y-4">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-500">
                    Изменение пароля
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <BaseInput
                        v-model="form.current_password"
                        v-model:error="form.errors.current_password"
                        label="Текущий пароль"
                        placeholder="Введите ваш текущий пароль..."
                        :disabled="form.processing"
                        type="password"
                    />

                    <BaseInput
                        v-model="form.password"
                        v-model:error="form.errors.password"
                        label="Новый пароль"
                        placeholder="Ваш новый пароль..."
                        :disabled="form.processing"
                        type="password"
                    />

                    <BaseInput
                        v-model="form.password_confirmation"
                        v-model:error="form.errors.password_confirmation"
                        label="Подтвердите пароль"
                        placeholder="Подтвердите ваш новый пароль..."
                        :disabled="form.processing"
                        type="password"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <BaseSubmitButton
                    :processing="form.processing"
                    :label="form.processing ? 'Сохранение...' : 'Сохранить изменения'"
                />

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in duration-300"
                    leave-to-class="opacity-0"
                >
                    <span v-if="form.recentlySuccessful" class="text-xs font-bold text-emerald-400">
                        ✓ Изменения сохранены
                    </span>
                </Transition>
            </div>
        </form>
    </div>
</template>
