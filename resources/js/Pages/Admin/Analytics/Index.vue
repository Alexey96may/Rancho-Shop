<script setup lang="ts">
    import { PropType, computed, onMounted, onUnmounted, ref } from 'vue';

    import { Deferred, router, usePage } from '@inertiajs/vue3';

    import { CurrencyDollarIcon, ShoppingBagIcon, UsersIcon } from '@heroicons/vue/24/outline';
    import type { ApexOptions } from 'apexcharts';
    import VueApexCharts from 'vue3-apexcharts';

    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { DailySalesResource } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps({
        overview: {
            type: Object as PropType<{
                total_revenue: number;
                orders_count: number;
                users_count: number;
            }>,
            required: true,
        },
        filters: {
            type: Object as PropType<{ days: number }>,
            required: false,
            default: () => ({ days: 30 }),
        },
        charts: {
            type: Object as PropType<{ sales: DailySalesResource }>,
            required: false,
        },
    });

    defineOptions({ layout: AdminLayout });

    const periods = [
        { label: '7д', value: 7 },
        { label: '30д', value: 30 },
        { label: '90д', value: 90 },
        { label: 'Всё', value: 0 },
    ];

    const page = usePage();

    const setPeriod = (value: number) => {
        router.get(
            route('admin.analytics.index'),
            { days: value },
            { preserveState: true, preserveScroll: true, only: ['charts', 'filters'] },
        );
    };

    const apexchart = VueApexCharts;

    /* ===== Реактивная ширина экрана для ApexCharts ===== */
    const viewportWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1280);

    function handleResize() {
        if (typeof window === 'undefined') return;
        viewportWidth.value = window.innerWidth;
    }

    onMounted(() => {
        if (typeof window === 'undefined') return;
        window.addEventListener('resize', handleResize, { passive: true });
    });
    onUnmounted(() => {
        if (typeof window === 'undefined') return;
        window.removeEventListener('resize', handleResize);
    });

    const isMobile = computed(() => viewportWidth.value < 640);
    const isTablet = computed(() => viewportWidth.value >= 640 && viewportWidth.value < 1024);

    /* ===== Высота графика ===== */
    const chartHeight = computed(() => {
        if (isMobile.value) return 240;
        if (isTablet.value) return 300;
        return 350;
    });

    /* ===== ApexOptions — реактивно зависит от ширины ===== */
    const chartOptions = computed<ApexOptions>(() => ({
        chart: {
            type: 'area',
            toolbar: { show: !isMobile.value }, // на мобилке тулбар скрыт
            zoom: { enabled: !isMobile.value }, // зум только на десктопе
            fontFamily: 'inherit',
            background: 'transparent',
            sparkline: { enabled: false },
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: isMobile.value ? 1.5 : 2, colors: ['#6366f1'] },
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
            tickAmount: isMobile.value ? 4 : undefined,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: isMobile.value ? '9px' : '10px',
                },
                rotate: isMobile.value ? 0 : -45,
                hideOverlappingLabels: true,
                trim: true,
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: isMobile.value ? '9px' : '11px',
                },
                formatter: (val: number) => {
                    if (isMobile.value && Math.abs(val) >= 1000) {
                        return `${Math.round(val / 1000)}к`;
                    }
                    return `${val.toLocaleString()} ₽`;
                },
                minWidth: isMobile.value ? 32 : 60,
            },
        },
        grid: {
            borderColor: '#1e293b',
            strokeDashArray: 4,
            padding: { left: isMobile.value ? 0 : 10, right: isMobile.value ? 0 : 10 },
        },
        theme: { mode: 'dark' },
        tooltip: { x: { show: false }, theme: 'dark' },
        legend: { show: false },
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

    <div class="space-y-6 sm:space-y-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 lg:gap-6">
            <StatCard
                label="Общая выручка"
                :value="computedTotalRev"
                :icon="CurrencyDollarIcon"
                labelColor="text-green-500"
            />

            <StatCard label="Заказов всего" :value="computedOrdersCount" :icon="ShoppingBagIcon" />

            <StatCard
                label="Клиентов"
                :value="computedUsersCount"
                :icon="UsersIcon"
                class="sm:col-span-2 lg:col-span-1"
            />
        </div>

        <!-- Chart Card -->
        <div
            class="rounded-2xl border border-slate-800/50 bg-slate-900/50 py-4 sm:rounded-3xl sm:p-6 md:p-8"
        >
            <!-- Заголовок -->
            <h3
                class="mb-4 flex items-center gap-2 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 sm:mb-6 sm:text-[11px] sm:tracking-[0.3em] md:mb-8"
            >
                <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-indigo-500"></span>
                <span class="truncate">
                    <template v-if="filters?.days > 0">
                        Динамика продаж за {{ filters.days }} дней
                    </template>
                    <template v-else>Динамика продаж за всё время</template>
                </span>
            </h3>

            <!-- Переключатель периодов -->
            <div
                class="mb-4 flex w-full gap-0.5 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950 p-1 sm:mb-6 sm:inline-flex sm:w-auto sm:gap-1 md:mb-8"
            >
                <button
                    v-for="period in periods"
                    :key="period.value"
                    @click="setPeriod(period.value)"
                    :disabled="isProcessing"
                    class="flex-1 whitespace-nowrap rounded-lg px-2 py-1.5 text-[10px] font-bold uppercase tracking-widest transition-all sm:flex-none sm:px-4"
                    :class="[
                        filters?.days === period.value
                            ? 'shadow-lg bg-indigo-600 text-white shadow-indigo-500/20'
                            : 'text-slate-500 hover:text-slate-300',
                    ]"
                >
                    {{ period.label }}
                </button>
            </div>

            <!-- Deferred chart -->
            <Deferred data="charts">
                <template #fallback>
                    <div
                        class="flex w-full flex-col items-center justify-center space-y-4"
                        :style="{ height: chartHeight + 'px' }"
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

                <Transition name="chart-fade" appear>
                    <div v-if="charts" :style="{ height: chartHeight + 'px' }">
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

    /* Скрыть скроллбар переключателя периодов на мобилке */
    .overflow-x-auto::-webkit-scrollbar {
        display: none;
        height: 0;
    }
    .overflow-x-auto {
        scrollbar-width: none;
    }
</style>
