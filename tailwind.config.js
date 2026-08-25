/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                white: "#FFFFFF",
                orange: "#FFA500",
            },
            animation: {
                typing: "typing 5s steps(40, end)",
                "blink-caret": "blink-caret .75s step-end infinite",
            },
            keyframes: {
                typing: {
                    "0%": { width: "0" },
                    "100%": { width: "100%" },
                },
                "blink-caret": {
                    "0%, 100%": { borderColor: "transparent" },
                    "50%": { borderColor: "orange" },
                },
            },

            screens: {
                'scr-2xl': '1920px',
                'scr-xl': '1440px',
                'scr-lg': '1280px',
            },
            container: {
                center: true,
                padding: {
                    DEFAULT: '1rem',
                    'scr-lg': '6rem',
                    'scr-2xl': '12rem',
                },
            },
        },
    },
    plugins: [],
};
