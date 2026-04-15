export function formatMoney(value: number | null | undefined): string {
    if (!value) return '0 ₽';

    const rubles = value / 100;

    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(rubles);
}

export function formatDistance(value: number | null | undefined): string {
    if (!value) return '0 км';

    const km = value / 1000;

    return (
        new Intl.NumberFormat('ru-RU', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 2,
        }).format(km) + ' км'
    );
}
