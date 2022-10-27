const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/Views/**/*.{html, js}',
  ],
  theme: {
    extend: {
        fontFamily: {
            sans: ['Lato', ...defaultTheme.fontFamily.sans],
        },
    },
  },
  plugins: [],
}
