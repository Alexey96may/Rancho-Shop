module.exports = {
    root: true,
    env: {
        node: true,
    },
    extends: [
        "plugin:vue/vue3-essential",
        "eslint:recommended",
        "@vue/eslint-config-typescript",
        "@vue/eslint-config-prettier/skip-formatting",
    ],
    rules: {
        "vue/multi-word-component-names": "off",
        "@typescript-eslint/no-explicit-any": "warn",
        "no-console": process.env.NODE_ENV === "production" ? "warn" : "off",
    },
};
