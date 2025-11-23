/**
 * Alkalmazás Belépési Pont (Entry Point)
 *
 * Ez a fájl az alkalmazás gyökere, amely:
 * 1. Betölti a React alkalmazást a DOM-ba
 * 2. Beállítja a Router-t (BrowserRouter) az útvonal-kezeléshez
 * 3. Beállítja az AuthProvider-t az autentikációs context biztosítására
 *
 * Provider hierarchia:
 * - React.StrictMode: Fejlesztési módban extra ellenőrzések
 * - BrowserRouter: URL-alapú navigáció
 * - AuthProvider: Globális autentikációs állapot
 * - App: Fő alkalmazás komponens
 */

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import { AuthProvider } from './context/AuthProvider';
import './index.css';

// React alkalmazás renderelése a 'root' elembe
ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <BrowserRouter>
      <AuthProvider>
        <App />
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>,
);
