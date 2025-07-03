/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
     "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/preline/variants.css",
  ],
  darkMode: 'class', // or 'media' or false
  theme: {
    extend: {},
  },
  plugins: [
    require('preline/plugin'),

  ],
}

