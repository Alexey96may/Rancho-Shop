# 🐄 Rancho — Платформа для локальной фермы

Современная экосистема для управления продажами фермерской продукции (молоко, сыры) с автоматизацией жизненного цикла свежих продуктов.

## 🚀 Технологический стек

- **Backend:** Laravel 11 (PHP 8.3) + PostgreSQL
- **Frontend:** Vue 3 (Composition API) + TypeScript
- **Связующее звено:** Inertia.js (SSR Mode)
- **Сборка:** Vite + ImageOptimizer + Brotli
- **Стили:** Tailwind CSS
- **Хранение:** Spatie Media Library (Cloudinary/Local)

## 🛠 Особенности архитектуры

- **Daily Stock Logic:** Система автоматического обновления лимитов для свежего удоя через Cron-задачи.
- **API-First:** Готовая структура Resources для будущего Android-приложения.
- **Smart Media:** Автоматическая конвертация фото продуктов в форматы WebP/Avif при загрузке.
- **Money Handling:** Все расчеты ведутся в копейках (Integer) для исключения ошибок плавающей точки.

## 📦 Установка

1. **Клонируйте репозиторий:**
    ```bash
    git clone [https://github.com/your-repo/rancho.git](https://github.com/your-repo/rancho.git)
    cd rancho
    ```
