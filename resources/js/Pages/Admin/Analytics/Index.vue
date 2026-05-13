<script setup lang="ts">
    import { computed } from 'vue';

    import { Deferred, router, usePage } from '@inertiajs/vue3';

    import { CurrencyDollarIcon, ShoppingBagIcon, UsersIcon } from '@heroicons/vue/24/outline';
    import type { ApexOptions } from 'apexcharts';
    import VueApexCharts from 'vue3-apexcharts';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminStatCard from '@/Components/Admin/UI/AdminStatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { DailySalesResource } from '@/types';
    import { formatMoney } from '@/utils/format';

    interface AnalyticsPageProps {
        overview: {
            total_revenue: number;
            orders_count: number;
            users_count: number;
        };
        filters: { days: number };
        charts?: {
            sales: DailySalesResource;
        };
    }

    const props = withDefaults(defineProps<AnalyticsPageProps>(), {
        filters: () => ({ days: 30 }),
    });

    defineOptions({ layout: AdminLayout });

    const periods = [
        { label: '7д', value: 7 },
        { label: '30д', value: 30 },
        { label: '90д', value: 90 },
        { label: 'Всё время', value: 0 },
    ];

    const page = usePage();

    const setPeriod = (value: number) => {
        router.get(
            route('admin.analytics.index'),
            { days: value },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['charts', 'filters'],
            },
        );
    };

    const apexchart = VueApexCharts;

    // ApexCharts
    const chartOptions = computed<ApexOptions>(() => ({
        chart: {
            type: 'area',
            toolbar: { show: true },
            zoom: { enabled: true },
            fontFamily: 'inherit',
            background: 'transparent',
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2, colors: ['#6366f1'] },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100],
            },
        },
        xaxis: {
            categories: props.charts?.sales.data.map((s) => s.label) || [],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: '#64748b', fontSize: '10px' },
                rotate: -45,
            },
        },
        yaxis: {
            labels: {
                style: { colors: '#64748b' },
                formatter: (val: number) => `${val.toLocaleString()} ₽`,
            },
        },
        grid: { borderColor: '#1e293b', strokeDashArray: 4 },
        theme: { mode: 'dark' },
        tooltip: { x: { show: false }, theme: 'dark' },
    }));

    const series = computed(() => [
        {
            name: 'Выручка',
            data: props.charts?.sales.data.map((s) => s.revenue) || [],
        },
    ]);

    const isProcessing = computed(
        () => page.component === 'Admin/Analytics/Index' && !!page.props.loading,
    );

    const computedTotalRev = computed(() => formatMoney(props.overview.total_revenue));
    const computedOrdersCount = computed(() => props.overview.orders_count);
    const computedUsersCount = computed(() => props.overview.users_count);
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Аналитика" subtitle="Данные обновляются в реальном времени" />
    </Teleport>

    <div class="space-y-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <AdminStatCard
                label="Общая выручка"
                :value="computedTotalRev"
                :icon="CurrencyDollarIcon"
                labelColor="text-green-500"
            />

            <AdminStatCard
                label="Заказов всего"
                :value="computedOrdersCount"
                :icon="ShoppingBagIcon"
            />

            <AdminStatCard label="Клиентов" :value="computedUsersCount" :icon="UsersIcon" />
        </div>

        <!-- Charts Section (Lazy Loading with Deferred) -->
        <div class="rounded-3xl border border-slate-800/50 bg-slate-900/50 p-8">
            <h3
                class="mb-8 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400"
            >
                <span class="h-2 w-2 animate-pulse rounded-full bg-indigo-500"></span>
                <span v-if="filters?.days > 0"
                    >Динамика продаж за последние {{ filters?.days }} дней</span
                >
                <span v-else>Динамика продаж за всё время</span>
            </h3>

            <div class="mb-8 flex gap-1 rounded-xl border border-slate-800 bg-slate-950 p-1">
                <button
                    v-for="period in periods"
                    :key="period.value"
                    @click="setPeriod(period.value)"
                    :disabled="isProcessing"
                    class="rounded-lg px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest transition-all"
                    :class="[
                        filters?.days === period.value
                            ? 'shadow-lg bg-indigo-600 text-white shadow-indigo-500/20'
                            : 'text-slate-500 hover:text-slate-300',
                    ]"
                >
                    {{ period.label }}
                </button>
            </div>

            <Deferred data="charts">
                <!-- Скелетон (Fallback) -->
                <template #fallback>
                    <div
                        class="flex h-[350px] w-full flex-col items-center justify-center space-y-4"
                    >
                        <AdminLoader text="Сборка данных" />
                        <div
                            class="h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-slate-800/50"
                        >
                            <div
                                class="h-full animate-[progress_2s_ease-in-out_infinite] bg-indigo-500"
                            ></div>
                        </div>
                    </div>
                </template>

                <!-- Основной контент (Анимация Vue) -->
                <Transition name="chart-fade" appear>
                    <div v-if="charts" class="h-[350px]">
                        <apexchart
                            width="100%"
                            height="100%"
                            :options="chartOptions"
                            :series="series"
                        />
                    </div>
                </Transition>
            </Deferred>
        </div>
    </div>
</template>

<style scoped>
    @keyframes progress {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }

    .chart-fade-enter-active {
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .chart-fade-enter-from {
        opacity: 0;
        transform: scale(0.98) translateY(10px);
    }
</style>
