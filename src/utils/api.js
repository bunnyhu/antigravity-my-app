import axios from 'axios';
import { sign, verify } from './jwt';

const api = axios.create({
    baseURL: 'http://localhost:8000/api', // Adjust if your PHP server runs elsewhere
    headers: {
        'Content-Type': 'application/json',
    },
});

// Add a request interceptor to inject the token and sign payload
api.interceptors.request.use(
    async (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        if (config.data) {
            const signedPayload = await sign(config.data);
            config.data = { payload: signedPayload };
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add a response interceptor to decode payload
api.interceptors.response.use(
    async (response) => {
        if (response.data && response.data.payload) {
            const decodedData = await verify(response.data.payload);
            if (decodedData) {
                response.data = decodedData;
            }
        }
        return response;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default api;
