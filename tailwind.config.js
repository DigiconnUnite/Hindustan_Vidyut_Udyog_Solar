/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./*.php', './components/**/*.php', './admin/**/*.php', './assets/js/*.js'],
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
      keyframes: {
        // Track holds two copies of the list; -50% lands exactly on the seam.
        marquee: {
          from: { transform: 'translateX(0)' },
          to: { transform: 'translateX(-50%)' },
        },
        'marquee-reverse': {
          from: { transform: 'translateX(-50%)' },
          to: { transform: 'translateX(0)' },
        },
      },
      animation: {
        marquee: 'marquee 60s linear infinite',
        'marquee-reverse': 'marquee-reverse 60s linear infinite',
      },
    },
  },
  plugins: [],
};
