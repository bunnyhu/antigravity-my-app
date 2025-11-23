/**
 * JWT Aláírás és Ellenőrzés - Web Crypto API
 *
 * Ez a modul a böngésző natív Web Crypto API-ját használja JWT tokenek
 * aláírására és ellenőrzésére. HS256 (HMAC SHA256) algoritmust implementál.
 *
 * Két fő funkció:
 * - sign(): Adatok aláírása JWT formátumba
 * - verify(): JWT token ellenőrzése és dekódolása
 *
 * FONTOS: Az ugyanazon titkos kulcsot (SECRET_KEY) kell használni, mint a backend!
 */

// Titkos kulcs betöltése környezeti változóból
const SECRET_KEY = import.meta.env.VITE_JWT_SECRET || "SECRET1234567890";

/**
 * Titkos kulcs importálása Web Crypto API-hoz
 *
 * A Web Crypto API `CryptoKey` objektumot vár, ezért a string kulcsot
 * először importálni kell HMAC használatra.
 *
 * @param {string} secret - A titkos kulcs string formában
 * @returns {Promise<CryptoKey>} - A Web Crypto API által használható kulcs
 */
async function importKey(secret) {
    const enc = new TextEncoder();
    return window.crypto.subtle.importKey(
        "raw",
        enc.encode(secret),
        { name: "HMAC", hash: "SHA-256" },
        false,
        ["sign", "verify"]
    );
}

/**
 * Base64URL kódolás
 *
 * A JWT szabvány szerint Base64URL formátumot kell használni (nem sima Base64).
 * Ez azt jelenti: + → -, / → _, és eltávolítjuk a padding (=) karaktereket.
 *
 * @param {ArrayBuffer|Uint8Array} str - A kódolandó adat
 * @returns {string} - Base64URL kódolt string
 */
function base64UrlEncode(str) {
    return btoa(String.fromCharCode(...new Uint8Array(str)))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');
}

/**
 * Base64URL dekódolás
 *
 * Visszaállítja a Base64URL formátumot sima Base64-re, majd dekódolja.
 *
 * @param {string} str - A dekódolandó Base64URL string
 * @returns {Uint8Array} - A dekódolt adat bájttömbként
 */
function base64UrlDecode(str) {
    // Base64URL → Base64 konverziószabály
    str = str.replace(/-/g, '+').replace(/_/g, '/');

    // Padding hozzáadása, ha szükséges
    while (str.length % 4) {
        str += '=';
    }

    return Uint8Array.from(atob(str), c => c.charCodeAt(0));
}

/**
 * JWT token generálása (aláírás)
 *
 * Létrehoz egy JWT tokent a megadott payload alapján, amely tartalmazza:
 * - Header: JWT típus és algoritmus (HS256)
 * - Payload: A átadott adatok
 * - Signature: HMAC SHA256 aláírás
 *
 * @param {Object} payload - Az aláírandó adatok (bármilyen szerializálható objektum)
 * @returns {Promise<string>} - A generált JWT token (header.payload.signature formátumban)
 *
 * Példa:
 * const token = await sign({ email: 'user@example.com', password: 'pass123' });
 */
export async function sign(payload) {
    const header = { typ: "JWT", alg: "HS256" };
    const enc = new TextEncoder();

    // Header és payload Base64URL kódolása
    const encodedHeader = base64UrlEncode(enc.encode(JSON.stringify(header)));
    const encodedPayload = base64UrlEncode(enc.encode(JSON.stringify(payload)));

    // HMAC SHA256 aláírás generálása Web Crypto API-val
    const key = await importKey(SECRET_KEY);
    const signature = await window.crypto.subtle.sign(
        "HMAC",
        key,
        enc.encode(`${encodedHeader}.${encodedPayload}`)
    );

    const encodedSignature = base64UrlEncode(signature);

    // JWT token összeállítása
    return `${encodedHeader}.${encodedPayload}.${encodedSignature}`;
}

/**
 * JWT token ellenőrzése és dekódolása
 *
 * Ellenőrzi a token aláírását és dekódol ja a payload-ot, ha érvényes.
 * NEM ellenőrzi a lejárati időt (exp), mert ez az API kommunikáció
 * integritásának ellenőrzésére szolgál, nem autentikációra.
 *
 * @param {string} token - Az ellenőrzendő JWT token
 * @returns {Promise<Object|null>} - A dekódolt payload objektum vagy null, ha érvénytelen
 *
 * Példa:
 * const data = await verify(response.data.payload);
 * if (data) {
 *   console.log('Token valid, data:', data);
 * }
 */
export async function verify(token) {
    if (!token) return null;

    // Token szét bontása 3 részre
    const [encodedHeader, encodedPayload, encodedSignature] = token.split('.');
    if (!encodedHeader || !encodedPayload || !encodedSignature) return null;

    // Aláírás ellenőrzése Web Crypto API-val
    const key = await importKey(SECRET_KEY);
    const enc = new TextEncoder();

    const isValid = await window.crypto.subtle.verify(
        "HMAC",
        key,
        base64UrlDecode(encodedSignature),
        enc.encode(`${encodedHeader}.${encodedPayload}`)
    );

    // Ha az aláírás érvénytelen, visszalép
    if (!isValid) return null;

    // Payload dekódolása és visszaadása
    const dec = new TextDecoder();
    return JSON.parse(dec.decode(base64UrlDecode(encodedPayload)));
}
