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
          // Light Mode (giữ nguyên)
          50: "#E8F9F1",
          100: "#D1F4E3",
          500: "#25D366", // Main
          600: "#20AA52", // Hover
          700: "#1A8744", // Dark

          // Dark Mode (thêm mới)
          400: "#2EE876", // Sáng hơn cho dark mode
          800: "#156534", // Tối hơn cho nền dark
          900: "#0F4A26", // Rất tối cho accents
        },
        // Màu nền Dark Mode
        dark: {
          bg: {
            primary: "#0A0E0D", // Nền chính (gần đen, hơi xanh)
            secondary: "#111918", // Nền thứ cấp
            tertiary: "#1A2421", // Nền card/container
            elevated: "#1F2D28", // Nền nổi (modal, dropdown)
          },
          border: {
            primary: "#1F2D28", // Border chính
            secondary: "#2A3A34", // Border nhấn mạnh
          },
          text: {
            primary: "#E8F9F1", // Text chính (xanh nhạt)
            secondary: "#9EBAAA", // Text phụ
            tertiary: "#6B8A7A", // Text mờ nhất
          },
        },
      },
    },
  },
  plugins: [],
};
