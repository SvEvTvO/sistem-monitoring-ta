import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#0245EC', // Primary[cite: 4]
                    light: '#E5ECFE',   // Primary Light[cite: 4]
                    soft: '#F2F6FF',    // Primary Soft[cite: 4]
                    dark: '#011C5E',    // Primary Dark[cite: 4]
                },
                accent: {
                    DEFAULT: '#D9FF3A', // Accent[cite: 4]
                    light: '#F0FFB0',   // Accent Light[cite: 4]
                    dark: '#576617',    // Accent Dark[cite: 4]
                },
                neutral: {
                    bg: '#F7F8FA',      // Background Light[cite: 4]
                    surface: '#FFFFFF', // Surface[cite: 4]
                    surfaceSecondary: '#F1F3F6', // Surface Secondary[cite: 4]
                    border: '#E2E5EA',  // Border[cite: 4]
                    borderStrong: '#CBD0D8', // Border Strong[cite: 4]
                },
                text: {
                    primary: '#0B0F14',   // Text Primary[cite: 4]
                    secondary: '#5D6470', // Text Secondary[cite: 4]
                    muted: '#8A9099',     // Text Muted[cite: 4]
                },
                semantic: {
                    success: '#16A34A', // Success[cite: 4]
                    successBg: '#DCFCE7',
                    warning: '#F59E0B', // Warning[cite: 4]
                    warningBg: '#FEF3C7',
                    danger: '#DC2626',  // Danger[cite: 4]
                    dangerBg: '#FEE2E2',
                    info: '#0EA5E9',    // Info[cite: 4]
                    infoBg: '#E0F2FE',
                }
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans], // Opsional: Inter cocok untuk tema Luma-inspired
            },
        },
    },
    plugins: [forms],
};