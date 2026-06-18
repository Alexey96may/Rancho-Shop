/**
 * Одиночная запись статистики за день
 * Соответствует App\Http\Resources\Admin\DailySalesResource
 */
export interface DailySalesDataItem {
    label: string; // Форматированная дата, например "13 Май"
    revenue: number; // Выручка в рублях (уже разделенная на 100)
    orders: number; // Количество заказов
    avg_check: number; // Средний чек в рублях
}

/**
 * Коллекция данных, которую возвращает DailySalesResource::collection()
 */
export interface DailySalesResource {
    data: DailySalesDataItem[];
}
