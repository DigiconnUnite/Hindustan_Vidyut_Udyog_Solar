/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./*.php', './components/**/*.php', './admin/**/*.php'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        primary: {
          50: '#f1f9f2',
          100: '#dcefe0',
          500: '#2f8f4e',
          600: '#25733e',
          700: '#1d5c32',
        },
        accent: {
          400: '#f7b955',
          500: '#f5a623',
          600: '#d6890f',
        },
        ink: '#03130a',
      },
      borderRadius: {
        '2xl': '1rem',
        '4xl': '2rem',
      },
    },
  },
  plugins: [],
};
