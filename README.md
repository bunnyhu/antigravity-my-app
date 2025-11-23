# Előszó - ez még embertől

*Ez a kód nem tartalmaz semmilyen emberi fejlesztési elemet, beleértve ez a README.md fájlt is és a GIT commitok leírását is, de még ennek a bekezdésnek a tartalmát is néha az AI javasolta. A project célja a Google új Antigravity környezetének és a Gemini 3 Pro model tesztelése. A forráskód dokumentálására elfogyott a Gemini kredit, így azt Sonnet 4.5 thinking model segítségével készítettem. Ezekhez csak promptot írtam méghozzá magyarul, és teszteltem - de a tesztelést is az AI írta le, mit csináljak. Ha valami nem tetszett leírtam emberi mondatokkal, ha hibát kaptam csak bemásoltam. 4 forduló után a kód hibátlanul futott. Itt tartunk most 2025 év végén. Innentől pedig minden betű AI generált.*

# React + PHP User Management Demo

Ez egy demonstrációs alkalmazás, amely bemutatja egy modern full-stack webalkalmazás működését React frontenddel és natív PHP backenddel. A projekt célja a biztonságos kommunikáció és a felhasználókezelés demonstrálása.

## 🤖 AI Fejlesztés

Ez a projekt az **Antigravity** fejlesztői környezetben készült a **Gemini 3 pro** mesterséges intelligencia modell segítségével.

A fejlesztés során keletkezett dokumentációk (implementációs terv, walkthrough, feladatlista) megtalálhatóak az `_ai_docs` mappában:
- [Implementation Plan](_ai_docs/implementation_plan.md)
- [Walkthrough](_ai_docs/walkthrough.md)
- [Task List](_ai_docs/task.md)

## 🛠 Fejlesztés Menete és Technológiák

A projekt során a következő technológiákat és megoldásokat alkalmaztuk:

### Frontend
- **React (Vite)**: A gyors és modern felhasználói felületért.
- **Axios Interceptors**: A kommunikáció automatikus titkosítására és hitelesítésére. Minden kérés és válasz JWT payload-ba van csomagolva.
- **Web Crypto API**: A böngésző natív kriptográfiai funkcióinak használata a JWT aláírására és ellenőrzésére külső könyvtárak nélkül.
- **Tailwind-szerű CSS**: Egyedi CSS változók és utility osztályok a modern megjelenésért.

### Backend
- **Natív PHP**: Keretrendszer nélküli, tiszta PHP implementáció a működés mélyebb megértéséhez.
- **PDO**: Biztonságos adatbázis-kezelés MySQL-hez.
- **Custom JWT Implementation**: Saját JWT kezelő osztály (`JWTUtils`) a tokenek generálására, validálására, valamint az adatcsomagok kódolására/dekódolására.
- **Környezeti Változók**: `.env` fájlok használata a konfiguráció (adatbázis, titkos kulcsok) kezelésére.

### Biztonsági Funkciók
- **Teljes JWT Kommunikáció**: Nem csak a hitelesítés, hanem minden adatcsere (request body és response body) JWT-be van csomagolva és aláírva, így biztosítva az adatok integritását.
- **Shared Secret**: A frontend és backend egy közös titkos kulcsot használ az üzenetek aláírására (demonstrációs célból).

## 🚀 Telepítés

### Előfeltételek
- Node.js és npm
- PHP
- MySQL szerver

### 1. Adatbázis Beállítása
1. Győződj meg róla, hogy a MySQL szerver fut.
2. Futtasd a migrációs scriptet a gyökérkönyvtárból:
   ```bash
   php backend/migrations/migrate.php
   ```
   Ez létrehozza az adatbázist (ha nem létezik), a táblákat, és feltölti tesztadatokkal.

### 2. Backend Beállítása
1. Lépj a `backend` mappába.
2. Hozz létre egy `.env` fájlt a következő tartalommal (módosítsd az adatokat a saját rendszerednek megfelelően):
   ```env
   DB_HOST=localhost
   DB_NAME=react_php_auth
   DB_USER=root
   DB_PASS=root
   JWT_SECRET=SECRET1234567890
   ```
3. Indítsd el a PHP szervert:
   ```bash
   php -S localhost:8000
   ```

### 3. Frontend Beállítása
1. Lépj a gyökérkönyvtárba (`my-app`).
2. Hozz létre egy `.env` fájlt:
   ```env
   VITE_JWT_SECRET=SECRET1234567890
   ```
3. Telepítsd a függőségeket:
   ```bash
   npm install
   ```
4. Indítsd el a fejlesztői szervert:
   ```bash
   npm run dev
   ```

## 🖥 Használat

Nyisd meg a böngészőben a frontend által kiírt URL-t (általában `http://localhost:5173`).

### Funkciók
- **Bejelentkezés**: JWT alapú hitelesítés.
- **Regisztráció**: Új felhasználók létrehozása.
- **Admin Felület**: Felhasználók listázása, törlése és szerepkörök módosítása (csak adminoknak).

## 🧪 Teszt Adatok

A rendszer előre feltöltött felhasználókkal érkezik a teszteléshez. A jelszó minden esetben: `password`

| Email | Szerepkör | Jelszó |
|-------|-----------|--------|
| `admin@example.com` | **Admin** (teljes hozzáférés) | `password` |
| `manager@example.com` | **Manager** | `password` |
| `user@example.com` | **User** (korlátozott hozzáférés) | `password` |

> **Megjegyzés**: A kommunikáció ellenőrzéséhez nyisd meg a böngésző fejlesztői eszközeit (F12) és figyeld a Network fület. Látni fogod, hogy a kérések és válaszok tartalma JWT tokenekbe van csomagolva (`payload` mező).

