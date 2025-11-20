
const config = window.fcAutoposterAdmin || {
    restUrl: '/wp-json/fc-autoposter/v1/',
    nonce: ''
};

const headers = {
    'Content-Type': 'application/json',
    'X-WP-Nonce': config.nonce
};

async function handleResponse(response) {
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || 'API Error');
    }
    return data;
}

export const api = {
    get: async (endpoint) => {
        const response = await fetch(`${config.restUrl}${endpoint}`, {
            method: 'GET',
            headers
        });
        return handleResponse(response);
    },
    post: async (endpoint, data) => {
        const response = await fetch(`${config.restUrl}${endpoint}`, {
            method: 'POST',
            headers,
            body: JSON.stringify(data)
        });
        return handleResponse(response);
    },
    put: async (endpoint, data) => {
        const response = await fetch(`${config.restUrl}${endpoint}`, {
            method: 'PUT',
            headers,
            body: JSON.stringify(data)
        });
        return handleResponse(response);
    },
    delete: async (endpoint) => {
        const response = await fetch(`${config.restUrl}${endpoint}`, {
            method: 'DELETE',
            headers
        });
        return handleResponse(response);
    }
};
