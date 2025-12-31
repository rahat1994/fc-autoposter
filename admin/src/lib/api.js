
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
    },
    contentInstructions: {
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return api.get(`content-instructions?${queryString}`);
        },
        get: (id) => api.get(`content-instructions/${id}`),
        create: (data) => api.post('content-instructions', data),
        update: (id, data) => api.put(`content-instructions/${id}`, data),
        delete: (id) => api.delete(`content-instructions/${id}`),
        retry: (id) => api.post(`${resource}/retry`, { id })
    },

    agents: {
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return api.get(`agents?${queryString}`);
        }
    },

    posts: {
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return api.get(`fa_fc_posts?${queryString}`);
        },
        delete: (id) => api.delete(`fa_fc_posts/${id}`)
    },

    fcomSpaces:{
        getAll: () => api.get(`fcom-spaces`)
    }


};
