module.exports = {
  darkMode: 'class',

  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],

  theme: {
    extend: {
      colors: {
        primary: {
          50: "#EAF7EF",
          100: "#D5F0DF",
          200: "#ABE1BF",
          300: "#81D29F",
          400: "#57C37F",
          500: "#34C759",
          600: "#30B653",
          700: "#2AA14A",
          800: "#1F7A38",
          900: "#145224",
        },

        success: {
          50: "#ECFDF5",
          100: "#D1FAE5",
          500: "#10B981",
          600: "#059669",
        },

        danger: {
          50: "#FEF2F2",
          100: "#FEE2E2",
          500: "#EF4444",
          600: "#DC2626",
        },

        warning: {
          50: "#FFFBEB",
          100: "#FEF3C7",
          500: "#F59E0B",
          600: "#D97706",
        },

        dark: {
          bg: {
            primary: "#0A0E0D",
            secondary: "#111918",
            tertiary: "#1A2421",
            elevated: "#1F2D28",
          },
          border: {
            primary: "#1F2D28",
            secondary: "#2A3A34",
          },
          text: {
            primary: "#E8F9F1",
            secondary: "#9EBAAA",
            tertiary: "#6B8A7A",
          },
        },

        slate: {
          50: "#f8fafc",
          100: "#f1f5f9",
          200: "#e2e8f0",
          300: "#cbd5e1", // FIXED
          400: "#94a3b8",
          500: "#64748b",
          600: "#475569",
          700: "#334155",
          800: "#1e293b",
          900: "#0f172a",
        },

        apple: {
          gray: "#F5F5F7",
          hairline: "rgba(0, 0, 0, 0.06)",
        },
      },

      fontFamily: {
        sans: [
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "Roboto",
          "Helvetica Neue",
          "Arial",
          "sans-serif",
        ],
      },

      boxShadow: {
        ios: "0 1px 2px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.06)",
        mac: "0 8px 30px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04)",
        "mac-sm": "0 2px 8px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.03)",
        "mac-inset": "inset 0 1px 2px rgba(0, 0, 0, 0.04)",
      },

      backdropBlur: {
        mac: "20px",
      },

      transitionTimingFunction: {
        DEFAULT: "cubic-bezier(0.4, 0, 0.2, 1)",
      },
    },
  },

  plugins: [],
};