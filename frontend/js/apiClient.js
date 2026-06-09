/**
 * Client REST condiviso — envelope { success, data }.
 * Infrastruttura pronta; i Presenter implementano le chiamate.
 */
const ApiClient = {
    API_BASE: '/api/v1',
    TOKEN_KEY: 'unipr_token',

    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    },

    setToken(token) {
        localStorage.setItem(this.TOKEN_KEY, token);
    },

    clearToken() {
        localStorage.removeItem(this.TOKEN_KEY);
    },

    isLoggedIn() {
        return Boolean(this.getToken());
    },

    logout() {
        this.clearToken();
        window.location.replace('login.html');
    },

    async request(method, path, body = null) {
        const headers = { 'Content-Type': 'application/json' };
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const options = { method, headers };
        if (body !== null) {
            options.body = JSON.stringify(body);
        }

        const res = await fetch(`${this.API_BASE}${path}`, options);
        const contentType = res.headers.get('Content-Type') || '';

        if (!contentType.includes('application/json')) {
            const text = await res.text();
            throw new Error(
                'Risposta non JSON dal server. Anteprima: ' + text.slice(0, 80)
            );
        }

        const envelope = await res.json();

        if (!envelope.success) {
            throw new Error(envelope.error || `Errore HTTP ${res.status}`);
        }

        return envelope.data;
    },

    get(path) {
        return this.request('GET', path);
    },

    post(path, body) {
        return this.request('POST', path, body);
    },

    deleteRequest(path) {
        return this.request('DELETE', path);
    },
};
