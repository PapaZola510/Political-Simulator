import { router } from '@inertiajs/react';

export function post(url: string, data?: Record<string, unknown>) {
    const formData = data ? Object.entries(data).reduce((acc, [key, value]) => {
        acc.append(key, String(value));

        return acc;
    }, new globalThis.FormData()) : undefined;
    
    router.post(url, formData, {
        preserveScroll: true,
        preserveState: true,
    });
}
