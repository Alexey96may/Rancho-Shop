<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Link, useForm, usePage } from '@inertiajs/vue3';

    import { CurrencyDollarIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import type { Order, ResourceSingle, SharedData } from '@/types';
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
        address: null, // todo
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    /** Цвета ролей — в тёплой палитре */
    const roleColors: Record<string, string> = {
        emerald: 'border-rancho-pine/30 bg-rancho-pine/15 text-rancho-pine',
        orange: 'border-rancho-ember/30 bg-rancho-ember/15 text-rancho-ember',
        blue: 'border-rancho-sky/40 bg-rancho-sky/15 text-rancho-forest',
        rose: 'border-rose-500/30 bg-rose-500/15 text-rose-300',
        slate: 'border-rancho-ink-700 bg-rancho-ink-800 text-rancho-ink-300',
        buttercup: 'border-rancho-buttercup/40 bg-rancho-buttercup/15 text-rancho-buttercup',
    };

    const submit = () => {
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
        });
    };

    const computedTotalOrders = computed(() => props.stats?.total_orders || 0);
    const computedTotalSpent = computed(() => formatMoney(props.stats?.total_spent || 0));

    const computedLatestOrderPrice = computed(() =>
        formatMoney(props.latestOrder?.data.total_price || 0),
    );
</script>

<template>
    <div class="space-y-8">
        <!-- ROLE -->
        <div v-scroll-reveal class="flex items-center justify-between gap-2">
            <span class="text-rancho-ink-500 block text-xs font-medium"> Ваша роль на сайте: </span>
            <span
                v-if="user?.role"
                class="inline-block rounded-xl border px-3 py-1 text-xs font-black uppercase tracking-wider"
                :class="roleColors[user.role.color] || roleColors.slate"
            >
                {{ user.role.label }}
            </span>
        </div>

        <!-- STATS -->
        <div v-scroll-reveal class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <StatCard
                label="Заказов оформлено"
                :value="computedTotalOrders"
                :icon="ShoppingBagIcon"
                label-color="text-rancho-pine"
            />
            <StatCard
                label="Сумма покупок"
                :value="computedTotalSpent"
                :icon="CurrencyDollarIcon"
                label-color="text-rancho-buttercup"
            />
        </div>

        <!-- LATEST ORDER -->
        <div
            v-scroll-reveal
            v-if="latestOrder"
            class="border-rancho-ink-800 from-rancho-ink-950 to-rancho-ink-900/60 flex flex-col gap-4 rounded-2xl border bg-gradient-to-r p-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <span
                    class="text-rancho-ink-400 block text-[10px] font-black uppercase tracking-wider"
                >
                    Последний заказ
                </span>
                <div class="text-rancho-ink-300 text-xs">
                    От
                    <BaseSmartTime :date="latestOrder?.data.created_at" theme="dark" />
                    на сумму
                    <span class="font-bold text-white">{{ computedLatestOrderPrice }}</span>
                </div>
            </div>

            <Link
                :href="route('profile.orders.index')"
                class="border-rancho-ink-700 bg-rancho-ink-800 text-rancho-ink-200 hover:bg-rancho-ink-700 inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-black uppercase tracking-widest transition-colors hover:border-rancho-buttercup/50 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40"
            >
                Все заказы
            </Link>
        </div>

        <hr class="border-rancho-ink-800" />

        <!-- FORM -->
        <form v-scroll-reveal @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="Имя"
                    placeholder="Ваше имя..."
                    :disabled="form.processing"
                    theme="dark"
                />

                <BaseInput
                    v-model="form.email"
                    v-model:error="form.errors.email"
                    label="Электронная почта"
                    placeholder="Ваш email..."
                    :disabled="form.processing"
                    type="email"
                    theme="dark"
                />

                <BaseInput
                    v-model="form.phone"
                    v-model:error="form.errors.phone"
                    label="Номер телефона"
                    placeholder="+7 (___) ___-__-__"
                    :disabled="form.processing"
                    type="tel"
                    theme="dark"
                />
            </div>

            <hr class="border-rancho-ink-800" />

            <!-- PASSWORD -->
            <div class="space-y-4">
                <h3 class="text-rancho-ink-500 text-sm font-black uppercase tracking-wider">
                    Изменение пароля
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <BaseInput
                        v-model="form.current_password"
                        v-model:error="form.errors.current_password"
                        label="Текущий пароль"
                        placeholder="Введите текущий пароль..."
                        :disabled="form.processing"
                        type="password"
                        theme="dark"
                    />

                    <BaseInput
                        v-model="form.password"
                        v-model:error="form.errors.password"
                        label="Новый пароль"
                        placeholder="Ваш новый пароль..."
                        :disabled="form.processing"
                        type="password"
                        theme="dark"
                    />

                    <BaseInput
                        v-model="form.password_confirmation"
                        v-model:error="form.errors.password_confirmation"
                        label="Подтвердите пароль"
                        placeholder="Повторите новый пароль..."
                        :disabled="form.processing"
                        type="password"
                        theme="dark"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end pt-2">
                <BaseSubmitButton :processing="form.processing" label="Сохранить" />
            </div>
        </form>
    </div>
</template>
