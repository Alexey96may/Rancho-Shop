/**
 * 🏠 Маркер фермы — маленький пин с амбаром
 */
export function createShopMarkerEl(): HTMLDivElement | undefined {
    if (typeof document === 'undefined') return;

    const el = document.createElement('div');
    el.className = 'map-marker-shop';

    el.innerHTML = `
<svg width="36" height="46" viewBox="0 0 36 46" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 3px 4px rgba(28, 63, 52, 0.4));">
    <defs>
        <linearGradient id="shop-pin-gradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#4A8A6B"/>
            <stop offset="100%" stop-color="#3B7558"/>
        </linearGradient>
    </defs>

    <!-- PIN BODY -->
    <path d="M18 1.5C9.7 1.5 3 8.2 3 16.5c0 10.5 15 28 15 28s15-17.5 15-28C33 8.2 26.3 1.5 18 1.5z"
          fill="url(#shop-pin-gradient)"
          stroke="#1C3F34"
          stroke-width="1.8"
          stroke-linejoin="round"/>

    <!-- INNER WHITE CIRCLE -->
    <circle cx="18" cy="16.5" r="10.5" fill="#FCFAF5" stroke="#1C3F34" stroke-width="1"/>

    <!-- BARN -->
    <g transform="translate(8, 8)">
        <!-- Barn walls -->
        <rect x="3" y="6.5" width="12" height="8.5" rx="1" fill="#FCFAF5" stroke="#1C3F34" stroke-width="0.9"/>

        <!-- Roof -->
        <path d="M2 6.5 L9 1.5 L16 6.5 Z" fill="#1C3F34" stroke="#1C3F34" stroke-width="0.9" stroke-linejoin="round"/>

        <!-- Door -->
        <rect x="7" y="10.5" width="4" height="4.5" fill="#3B7558" stroke="#1C3F34" stroke-width="0.7"/>

        <!-- Window left -->
        <rect x="4" y="8.5" width="2.5" height="2.5" fill="#DDEEE5" stroke="#1C3F34" stroke-width="0.6"/>

        <!-- Window right -->
        <rect x="11.5" y="8.5" width="2.5" height="2.5" fill="#DDEEE5" stroke="#1C3F34" stroke-width="0.6"/>
    </g>
</svg>
`;

    return el;
}

/**
 * 📍 Маркер пользователя — маленький оранжевый пин
 */
export function createUserMarkerEl(): HTMLDivElement | undefined {
    if (typeof document === 'undefined') return;

    const el = document.createElement('div');
    el.className = 'map-marker-user';

    el.innerHTML = `
<svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 3px 5px rgba(217, 119, 6, 0.45));">
    <defs>
        <linearGradient id="user-pin-gradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#E88A2A"/>
            <stop offset="100%" stop-color="#D97706"/>
        </linearGradient>
    </defs>

    <!-- PIN BODY -->
    <path d="M16 1.5C8.5 1.5 2.5 7.5 2.5 15c0 9.5 13.5 24.5 13.5 24.5S29.5 24.5 29.5 15C29.5 7.5 23.5 1.5 16 1.5z"
          fill="url(#user-pin-gradient)"
          stroke="#A85A00"
          stroke-width="1.6"
          stroke-linejoin="round"/>

    <!-- INNER WHITE CIRCLE -->
    <circle cx="16" cy="15" r="9" fill="#FCFAF5" stroke="#A85A00" stroke-width="1"/>

    <!-- USER AVATAR -->
    <g transform="translate(16, 15)">
        <!-- Head -->
        <circle cx="0" cy="-2.5" r="3.2" fill="#D97706"/>
        <!-- Shoulders -->
        <path d="M-5 5.5c0-3 2.2-5 5-5s5 2 5 5"
              fill="#D97706"
              stroke="#A85A00"
              stroke-width="0.5"/>
    </g>

    <!-- PULSE DOT -->
    <circle cx="16" cy="15" r="9.5" fill="none" stroke="#D97706" stroke-width="0.8" opacity="0.45">
        <animate attributeName="r" values="9.5;13;9.5" dur="2s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.45;0;0.45" dur="2s" repeatCount="indefinite"/>
    </circle>
</svg>
`;

    return el;
}
