import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
    ],

    safelist: [
        'bg-rancho-olive',
        'bg-rancho-forest',
        'bg-amber-600',
        'bg-emerald-600',
        'bg-stone-500',
        'scroll-reveal',
        'scroll-reveal--visible',
        'text-rancho-ember',
        'bg-rancho-ember',
        'ring-rancho-ember',
    ],

    theme: {
        extend: {
            colors: {
                rancho: {
                    // Цвет акварельной бумаги
                    paper: '#FCFAF5',
                    // Глубокий зеленый для лого и заголовков
                    forest: '#1C3F34',
                    // Хвойный для кнопок
                    pine: '#3B7558',
                    // Приглушенный оливковый
                    olive: '#597D5B',
                    // Голубой (горы/небо)
                    sky: '#A8C4CB',
                    // Желтый (цветы на лугу)
                    buttercup: '#E3B44B',

                    ink: {
                        50: '#F2EFE7', // было #F7F5F0 — теперь заметно темнее paper
                        100: '#E8E3D6', // было #EDEAE1
                        200: '#D2CBB9', // было #DDD8CB — границы станут чётче
                        300: '#B5AC96', // было #C4BEAE
                        400: '#948B74', // было #A39C8A
                        500: '#756D57', // было #837B68
                        600: '#5A5342', // было #655E4E
                        700: '#403A2D', // было #4A4438
                        800: '#2A261D', // было #2F2C24
                        900: '#1A1712', // было #1E1C17
                        950: '#0F0D09', // было #12110D
                    },

                    ember: '#D97706', // тёплый оранжевый в тон buttercup
                },
            },
            fontFamily: {
                header: ['Spectral', 'serif'],
                sans: ['Montserrat', 'sans-serif'],
            },
            container: {
                center: true,
                padding: {
                    DEFAULT: '1rem',
                    sm: '2rem',
                    lg: '4rem',
                },
            },
        },
        boxShadow: {
            'rancho-sm': '0 2px 10px rgba(28, 63, 52, 0.2)',
        },
    },

    future: {
        hoverOnlyWhenSupported: true,
    },

    plugins: [forms, require('@tailwindcss/typography')],
};
