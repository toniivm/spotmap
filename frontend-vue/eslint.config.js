import globals from 'globals';
import pluginVue from 'eslint-plugin-vue';

export default [
  {
    files: ['src/**/*.{js,vue}'],
    languageOptions: {
      globals: {
        ...globals.browser,
      },
      ecmaVersion: 2022,
      sourceType: 'module',
    },
    plugins: {
      vue: pluginVue,
    },
    rules: {
      ...pluginVue.configs['vue3-recommended'].rules,
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      'vue/multi-word-component-names': 'off',
      'vue/require-default-prop': 'warn',
    },
  },
  {
    files: ['src/**/*.test.js', 'src/**/*.spec.js'],
    languageOptions: {
      globals: {
        ...globals.browser,
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        vi: 'readonly',
      },
    },
  },
];
