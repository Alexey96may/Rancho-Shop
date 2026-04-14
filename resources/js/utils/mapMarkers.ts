export function createShopMarkerEl() {
    const el = document.createElement('div');

    el.innerHTML = `
<svg width="42" height="42" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
    <!-- pin -->
    <path d="M32 2C20.4 2 11 11.4 11 23c0 15.2 21 39 21 39s21-23.8 21-39C53 11.4 43.6 2 32 2z"
          fill="#3B7558" stroke="#1C3F34" stroke-width="2"/>

    <!-- barn -->
    <g transform="translate(18,18)">
        <rect x="4" y="10" width="20" height="16" rx="2" fill="#F5F5F5"/>
        
        <!-- roof -->
        <path d="M4 10 L14 2 L24 10 Z" fill="#1C3F34"/>

        <!-- door -->
        <rect x="12" y="18" width="6" height="8" rx="1" fill="#3B7558"/>

        <!-- window -->
        <rect x="6" y="14" width="4" height="4" fill="#DDEEE5"/>
    </g>
</svg>
`;

    el.className = 'map-marker-shop';
    return el;
}

export function createUserMarkerEl() {
    const el = document.createElement('div');

    el.innerHTML = `
<svg width="40" height="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
    <!-- pin -->
    <path d="M32 2C20.4 2 11 11.4 11 23c0 15.2 21 39 21 39s21-23.8 21-39C53 11.4 43.6 2 32 2z"
          fill="#1C3F34"/>

    <!-- user dot -->
    <circle cx="32" cy="24" r="10" fill="#FCFAF5"/>

    <!-- head -->
    <circle cx="32" cy="22" r="4" fill="#1C3F34"/>

    <!-- shoulders -->
    <path d="M24 30c2-4 14-4 16 0v4H24v-4z" fill="#1C3F34"/>
</svg>
`;

    el.className = 'map-marker-user';
    return el;
}
