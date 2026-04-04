import forms from '@tailwindcss/forms';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        colors: {
            rancho: {
                // Тот самый цвет акварельной бумаги
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
            },
        },
        extend: {
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
            'rancho-sm': '0 2px 10px rgba(28, 63, 52, 0.05)',
        },
    },

    future: {
        hoverOnlyWhenSupported: true,
    },

    plugins: [forms],
};
