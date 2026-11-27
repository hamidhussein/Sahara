import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Headings (Humanitarian pairing: editorial serif + clean sans body). Bold only, Latin only, to keep it light.
                bunny('Merriweather', {
                    weights: [700],
                    subsets: ['latin'],
                }),
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
                // Loaded only on Urdu content via @fonts(['noto-nastaliq-urdu', 'noto-naskh-arabic']).
                bunny('Noto Nastaliq Urdu', {
                    weights: [400, 700],
                    subsets: ['arabic'],
                    preload: false,
                }),
                bunny('Noto Naskh Arabic', {
                    weights: [400, 600],
                    subsets: ['arabic'],
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
