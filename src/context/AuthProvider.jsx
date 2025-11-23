/**
 * Autentikációs Context Provider
 *
 * Ez a komponens kezeli az alkalmazás szintű autentikációs állapotot.
 * React Context API-t használ, hogy minden komponens hozzáférhessen
 * a bejelentkezett felhasználó adataihoz és az auth műveletekhez.
 *
 * Főbb funkciók:
 * - login(): Bejelentkezés
 * - register(): Regisztráció
 * - logout(): Kijelentkezés
 * - user: Az aktuális felhasználó adatai
 * - token: A JWT autentikációs token
 *
 * Használat:
 * import { useAuth } from './context/AuthProvider';
 * const { user, login, logout } = useAuth();
 */

import React, { createContext, useState, useEffect, useContext } from 'react';
import api from '../utils/api';

// Auth Context létrehozása
const AuthContext = createContext(null);

/**
 * AuthProvider komponens - Az alkalmazást körbeölelő provider
 *
 * @param {Object} props - React props
 * @param {ReactNode} props.children - Gyerek komponensek
 */
export const AuthProvider = ({ children }) => {
    // Állapotok: felhasználó, token, betöltési állapot
    const [user, setUser] = useState(null);
    const [token, setToken] = useState(localStorage.getItem('token') || null);
    const [loading, setLoading] = useState(true);

    /**
     * Inicializálás komponens betöltésekor
     *
     * Ha van token a localStorage-ben, betölti a felhasználó adatait.
     * Valódi alkalmazásban érdemes a tokent validálni a backenddel.
     */
    useEffect(() => {
        if (token) {
            // Felhasználó adatok betöltése localStorage-ből
            const storedUser = localStorage.getItem('user');
            if (storedUser) {
                setUser(JSON.parse(storedUser));
            }
        }
        setLoading(false);
    }, [token]);

    /**
     * Bejelentkezés
     *
     * Elküldi az email és jelszó kombinációt a backend-nek.
     * Sikeres bejelentkezés esetén menti a tokent és user adatokat.
     *
     * @param {string} email - Felhasználó email címe
     * @param {string} password - Felhasználó jelszava
     * @returns {Promise<Object>} - { success: boolean, message?: string }
     */
    const login = async (email, password) => {
        try {
            const response = await api.post('/login.php', { email, password });
            const { token, user } = response.data;

            // Token és user mentése localStorage-be és state-be
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));

            setToken(token);
            setUser(user);
            return { success: true };
        } catch (error) {
            console.error("Login failed", error);
            return {
                success: false,
                message: error.response?.data?.message || "Login failed"
            };
        }
    };

    /**
     * Regisztráció
     *
     * Új felhasználó létrehozása a megadott email és jelszóval.
     *
     * @param {string} email - Új felhasználó email címe
     * @param {string} password - Új felhasználó jelszava
     * @returns {Promise<Object>} - { success: boolean, message?: string }
     */
    const register = async (email, password) => {
        try {
            await api.post('/register.php', { email, password });
            return { success: true };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || "Registration failed"
            };
        }
    };

    /**
     * Kijelentkezés
     *
     * Törli a tokent és user adatokat localStorage-ből és state-ből.
     */
    const logout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setToken(null);
        setUser(null);
    };

    // Context Provider renderelése az értékekkel
    return (
        <AuthContext.Provider value={{ user, token, login, register, logout, loading }}>
            {children}
        </AuthContext.Provider>
    );
};

/**
 * useAuth Hook - Context elérése egyszerűbben
 *
 * Ez a custom hook lehetővé teszi, hogy bármelyik komponens
 * könnyen hozzáférjen az auth kontextushoz.
 *
 * @returns {Object} - Auth context értékek (user, token, login, register, logout, loading)
 *
 * Példa:
 * const { user, login } = useAuth();
 */
export const useAuth = () => useContext(AuthContext);
