/// <reference types="vitest" />
import legacy from "@vitejs/plugin-legacy";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import { visualizer } from "rollup-plugin-visualizer";
import { defineConfig } from "vite";
import { imagetools } from "vite-imagetools";
import viteCompression from "vite-plugin-compression";
import { ViteImageOptimizer } from "vite-plugin-image-optimizer";
import svgLoader from "vite-svg-loader";

export default defineConfig(({ isSsrBuild }) => ({
    plugins: [
        laravel({
            input: ["resources/scss/app.scss", "resources/js/app.ts"],
            ssr: "resources/js/ssr.ts",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        legacy({
            targets: ["defaults", "not IE 11"],
        }),
        viteCompression({
            algorithm: "brotliCompress",
            ext: ".br",
        }),
        svgLoader(),
        ViteImageOptimizer({
            test: /\.(jpe?g|png|gif|tiff|webp|svg|avif)$/i,
            exclude: undefined,
            include: undefined,
            includePublic: true,
            logStats: true,
            webp: { quality: 80 },
            avif: { quality: 70 },
            png: { quality: 80 },
            jpeg: { quality: 80 },
        }),
        visualizer({
            filename: "stats.html",
            gzipSize: true,
            brotliSize: true,
        }),
        imagetools({
            defaultDirectives: (url) => {
                if (url.searchParams.has("smart")) {
                    return new URLSearchParams(
                        "format=avif;webp;jpg&as=picture",
                    );
                }
                return new URLSearchParams();
            },
        }),
    ],
    ssr: {
        noExternal: ["@inertiajs/server"],
    },
    build: {
        minify: "terser",
        cssMinify: true,
        rollupOptions: {
            output: {
                inlineDynamicImports: isSsrBuild,
                manualChunks(id) {
                    if (id.includes("node_modules")) {
                        return id
                            .toString()
                            .split("node_modules/")[1]
                            .split("/")[0]
                            .toString();
                    }
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    test: {
        globals: true,
        environment: "jsdom",
        coverage: {
            provider: "v8",
            reporter: ["text", "json", "html"],
        },
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
            "@css": path.resolve(__dirname, "./resources/scss"),
            "@img": path.resolve(__dirname, "./resources/images"),
        },
    },
}));
