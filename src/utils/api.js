/**
 * API Kommunikációs Réteg - Axios Interceptorokkal
 *
 * Ez a modul kezeli az összes backend API kommunikációt.
 * Automatikusan JWT-be kódolja a kéréseket és dekódolja a válaszokat.
 *
 * Főbb funkciók:
 * 1. Request Interceptor: JWT token hozzáadása és request body aláírása
 * 2. Response Interceptor: Response body dekódolása és ellenőrzése
 *
 * Használat:
 * import api from './utils/api';
 * const response = await api.post('/login.php', { email, password });
 */

import axios from 'axios';
import { sign, verify } from './jwt';

/**
 * Axios példány létrehozása alapértelmezett beállításokkal
 *
 * baseURL: A backend API elérési útja
 * headers: Alapértelmezett Content-Type beállítás
 */
const api = axios.create({
    baseURL: 'http://localhost:8000/api', // Backend API URL (módosítsd ha máshol fut)
    headers: {
        'Content-Type': 'application/json',
    },
});

/**
 * Request Interceptor - Kimenő kérések feldolgozása
 *
 * Ez a middleware minden kimenő kérés előtt lefut és:
 * 1. Hozzáadja az Authorization headert a JWT tokennel (ha van)
 * 2. JWT-be kódolja a request body-t (ha van adat)
 *
 * A backend így { payload: "jwt_token" } formátumban kapja meg az adatokat.
 */
api.interceptors.request.use(
    async (config) => {
        // JWT token hozzáadása az Authorization headerhez (ha van mentve)
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        // Request body aláírása JWT-be (ha van adat)
        if (config.data) {
            const signedPayload = await sign(config.data);
            config.data = { payload: signedPayload };
        }

        return config;
    },
    (error) => {
        // Hiba esetén tovább dobjuk a hibát
        return Promise.reject(error);
    }
);

/**
 * Response Interceptor - Bejövő válaszok feldolgozása
 *
 * Ez a middleware minden bejövő válasz után lefut és:
 * 1. Ellenőrzi, hogy a válasz tartalmaz-e JWT-be kódolt payload-ot
 * 2. JWT aláírás ellenőrzése és dekódolás
 * 3. A dekódolt adatok visszaadása
 *
 * Így az alkalmazás többi része már a nyers adatokkal dolgozik.
 */
api.interceptors.response.use(
    async (response) => {
        // Ha a válasz JWT-be kódolt payload-ot tartalmaz
        if (response.data && response.data.payload) {
            const decodedData = await verify(response.data.payload);

            // Ha az aláírás érvényes, lecseréljük a response.data-t
            if (decodedData) {
                response.data = decodedData;
            }
        }
        return response;
    },
    (error) => {
        // Hiba esetén tovább dobjuk a hibát
        return Promise.reject(error);
    }
);

export default api;
