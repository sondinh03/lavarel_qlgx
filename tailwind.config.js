/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php", // ← Livewire 2 nằm đúng chỗ này
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#E8F9F1",
          100: "#D1F4E3",
          500: "#25D366", // Main
          600: "#20AA52", // Hover
          700: "#1A8744", // Dark
        },
      },
    },
  },
  plugins: [],
};
