import type { Ziggy as ZiggyType } from 'ziggy-js';

declare global {
    // eslint-disable-next-line no-var
    var Ziggy: typeof ZiggyType & { routes: Record<string, any> };
}

export {};
