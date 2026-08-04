/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'daba-cream': '#FEFDFA',
        'daba-cream-alt': '#EFEDE4',
        'daba-navy': '#1B2B47',
        'daba-navy-deep': '#030046',
        'daba-orange': '#CE4600',
        'daba-orange-dark': '#B83C00',
        'daba-green': '#6F8766',
        'daba-slate': '#5A6474',
        'daba-slate-dark': '#464650',
      },
    },
  },
  plugins: [],
}
