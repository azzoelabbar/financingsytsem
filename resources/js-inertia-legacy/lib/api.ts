import type { ApiFailure, ApiSuccess } from '@/types/api';

export class ApiError extends Error {
    status: number;
    code: string;
    errors?: Record<string, string[]>;

    constructor(
        message: string,
        status: number,
        code = 'error',
        errors?: Record<string, string[]>,
    ) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.code = code;
        this.errors = errors;
    }
}

let loginRedirectStarted = false;

function readCookie(name: string): string | null {
    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`));
    return match
        ? decodeURIComponent(match.split('=').slice(1).join('='))
        : null;
}

function isAuthPath(pathname: string): boolean {
    return (
        pathname === '/login' ||
        pathname === '/register' ||
        pathname.startsWith('/forgot-password') ||
        pathname.startsWith('/reset-password') ||
        pathname.startsWith('/two-factor') ||
        pathname.startsWith('/email/') ||
        pathname === '/'
    );
}

async function ensureCsrfCookie(): Promise<void> {
    await fetch('/sanctum/csrf-cookie', {
        credentials: 'include',
        headers: { Accept: 'application/json' },
    });
}

type RequestOptions = {
    body?: unknown;
    query?: Record<string, string | number | boolean | undefined | null>;
    headers?: Record<string, string>;
    _retried?: boolean;
};

function buildUrl(path: string, query?: RequestOptions['query']): string {
    const url = new URL(window.location.origin);
    if (path.startsWith('http')) {
        return path;
    }
    if (path.startsWith('/api/')) {
        url.pathname = path;
    } else if (path.startsWith('api/')) {
        url.pathname = `/${path}`;
    } else {
        url.pathname = `/api/v1/${path.replace(/^\//, '')}`;
    }
    if (query) {
        Object.entries(query).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });
    }
    return url.toString();
}

function redirectToLoginOnce(): void {
    if (loginRedirectStarted || isAuthPath(window.location.pathname)) {
        return;
    }
    loginRedirectStarted = true;
    window.location.assign('/login');
}

async function request<T>(
    method: string,
    path: string,
    options: RequestOptions = {},
): Promise<ApiSuccess<T>> {
    await ensureCsrfCookie();
    const companyId = localStorage.getItem('erp.companyId');
    const bookId = localStorage.getItem('erp.bookId');
    const xsrf = readCookie('XSRF-TOKEN');

    const headers: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers,
    };
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    }
    if (companyId) {
        headers['X-Company-Id'] = companyId;
    }
    if (bookId) {
        headers['X-Book-Id'] = bookId;
    }
    if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(buildUrl(path, options.query), {
        method,
        credentials: 'include',
        headers,
        body:
            options.body === undefined
                ? undefined
                : JSON.stringify(options.body),
    });

    // Stale CSRF — refresh once and retry instead of bouncing the page.
    if (response.status === 419 && !options._retried) {
        await ensureCsrfCookie();
        return request<T>(method, path, { ...options, _retried: true });
    }

    if (response.status === 401) {
        redirectToLoginOnce();
        throw new ApiError('Unauthenticated', 401, 'unauthenticated');
    }

    const json = (await response.json().catch(() => null)) as
        | ApiSuccess<T>
        | ApiFailure
        | null;

    if (!response.ok) {
        const failure = json as ApiFailure | null;
        const message =
            failure?.error?.message ||
            failure?.message ||
            `Request failed (${response.status})`;
        throw new ApiError(
            message,
            response.status,
            failure?.error?.code || 'http_error',
            failure?.errors,
        );
    }

    if (!json || (json as ApiFailure).success === false) {
        const failure = json as ApiFailure;
        throw new ApiError(
            failure?.error?.message || 'Unexpected API response',
            response.status,
            failure?.error?.code || 'invalid_response',
        );
    }

    return json as ApiSuccess<T>;
}

export const api = {
    get: <T>(path: string, query?: RequestOptions['query']) =>
        request<T>('GET', path, { query }),
    post: <T>(path: string, body?: unknown) =>
        request<T>('POST', path, { body }),
    put: <T>(path: string, body?: unknown) =>
        request<T>('PUT', path, { body }),
    delete: <T>(path: string) => request<T>('DELETE', path),
};
