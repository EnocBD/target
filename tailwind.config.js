/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/views/admin/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        'primary': {
          DEFAULT: '#253761',
          50:  '#E8EDF5',
          100: '#C5D4E8',
          200: '#A3BBDB',
          300: '#81A2CE',
          400: '#5F89C1',
          500: '#253761', // default
          600: '#1F2E52',
          700: '#192543',
          800: '#131C34',
          900: '#0D1325',
        },
        'text-primary': '#181818',
      },
      fontFamily: {
        'sans': ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
