import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // VITE_DEV_HOST=wms.test — use when APP_URL is localhost but the site runs on Herd/Valet
    const devHost = (() => {
        if (env.VITE_DEV_HOST) {
            return env.VITE_DEV_HOST;
        }
        if (env.APP_URL) {
            try {
                return new URL(env.APP_URL).hostname;
            } catch {
                // ignore invalid APP_URL
            }
        }
        return 'localhost';
    })();

    const isLocalHost = ['localhost', '127.0.0.1', '::1'].includes(devHost);
    // Herd / Valet secure sites use *.test — only then look for TLS certs
    const useHerdTls = !isLocalHost && (devHost.endsWith('.test') || devHost.endsWith('.localhost'));

    const laravelPluginOptions = {
        input: [
            'resources/js/app.js',
            'resources/sass/app.scss',
        ],
        refresh: [
            'resources/views/**',
            'resources/sass/**',
            'resources/css/**',
            'public/css/**',
            'public/js/**',
            'routes/**',
            'app/**',
        ],
    };

    if (useHerdTls) {
        laravelPluginOptions.detectTls = devHost;
    }

    return {
        plugins: [laravel(laravelPluginOptions)],
        server: useHerdTls
            ? {
                host: devHost,
                hmr: { host: devHost },
            }
            : {
                host: 'localhost',
                port: 5173,
                strictPort: true,
            },
    };
});
