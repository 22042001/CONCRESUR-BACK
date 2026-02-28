/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#fff4ec',
                    100: '#ffe4cf',
                    500: '#d66322',
                    600: '#b4511a',
                    700: '#8f3f13',
                },
            },
            boxShadow: {
                soft: '0 10px 30px rgba(15, 23, 42, 0.08)',
            },
        },
    },
    plugins: [],
};
