import globals from 'globals';
import pluginVue from 'eslint-plugin-vue';

export default [
  {
    ignores: ['dist/**', 'coverage/**', 'playwright-report/**', 'test-results/**'],
  },
  ...pluginVue.configs['flat/recommended'],
  {
    files: ['src/**/*.{js,vue}'],
    languageOptions: {
      globals: {
        ...globals.browser,
      },
      ecmaVersion: 'latest',
      sourceType: 'module',
    },
    rules: {
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      'vue/multi-word-component-names': 'off',
      'vue/require-default-prop': 'off',
      'vue/max-attributes-per-line': 'off',
      'vue/singleline-html-element-content-newline': 'off',
      'vue/html-self-closing': 'off',
      'vue/attributes-order': 'off',
      'vue/multiline-html-element-content-newline': 'off',
    },
  },
  {
    files: ['src/**/*.test.js', 'src/**/*.spec.js'],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        vi: 'readonly',
      },
    },
  },
];
