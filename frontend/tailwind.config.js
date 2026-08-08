/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        playfair: ['Playfair Display', 'serif'],
        karla: ['Karla', 'sans-serif'],
        worksans: ['Work Sans', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
      },
      colors: {
        'daba-cream': '#f8f6f1',
        'daba-cream-light': '#fdfcf8',
        'daba-cream-alt': '#fbfaf6',
        'daba-navy': '#1b2b47',
        'daba-navy-deep': '#030046',
        'daba-navy-marine': '#1d3557',
        'daba-blue-medium': '#4f708a',
        'daba-slate': '#5a6474',
        'daba-slate-light': '#6b7280',
        'daba-green': '#6f8766',
        'daba-orange': '#ce4600',
        'daba-orange-alt': '#d65a31',
        'daba-border': '#dcd9cf',
        'daba-border-light': '#e5e7eb',
      },
      borderRadius: {
        'card': '8px',
        'btn': '20px',
        'pill': '9999px',
      },
      boxShadow: {
        'card-light': '0px 1px 2px 0px rgba(0,0,0,0.05)',
        'card-elevated': '0px 10px 15px -3px rgba(0,0,0,0.1)',
        'hero-accent': '0px 20px 50px -25px rgba(27,43,71,0.28)',
      }
    },
  },
  plugins: [],
}
