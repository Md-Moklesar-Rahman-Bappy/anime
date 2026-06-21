import axios from 'axios';

window.axios = axios;

/*
|--------------------------------------------------------------------------
| DEFAULT HEADERS
|--------------------------------------------------------------------------
*/

// ✅ Laravel AJAX header
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ✅ CSRF Token (IMPORTANT)
const token = document.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

/*
|--------------------------------------------------------------------------
| RESPONSE INTERCEPTOR (OPTIONAL BUT USEFUL)
|--------------------------------------------------------------------------
*/
window.axios.interceptors.response.use(
    response => response,
    error => {
        // ✅ Debug in development
        if (import.meta.env.DEV) {
            console.error('[Axios Error]', error);
        }

        return Promise.reject(error);
    }
);