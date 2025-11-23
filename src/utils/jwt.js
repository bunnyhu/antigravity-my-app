

const SECRET_KEY = import.meta.env.VITE_JWT_SECRET || "SECRET1234567890"; // Fallback for safety, but env should be used

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

function base64UrlEncode(str) {
    return btoa(String.fromCharCode(...new Uint8Array(str)))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');
}

function base64UrlDecode(str) {
    str = str.replace(/-/g, '+').replace(/_/g, '/');
    while (str.length % 4) {
        str += '=';
    }
    return Uint8Array.from(atob(str), c => c.charCodeAt(0));
}

export async function sign(payload) {
    const header = { typ: "JWT", alg: "HS256" };
    const enc = new TextEncoder();

    const encodedHeader = base64UrlEncode(enc.encode(JSON.stringify(header)));
    const encodedPayload = base64UrlEncode(enc.encode(JSON.stringify(payload)));

    const key = await importKey(SECRET_KEY);
    const signature = await window.crypto.subtle.sign(
        "HMAC",
        key,
        enc.encode(`${encodedHeader}.${encodedPayload}`)
    );

    const encodedSignature = base64UrlEncode(signature);

    return `${encodedHeader}.${encodedPayload}.${encodedSignature}`;
}

export async function verify(token) {
    if (!token) return null;

    const [encodedHeader, encodedPayload, encodedSignature] = token.split('.');
    if (!encodedHeader || !encodedPayload || !encodedSignature) return null;

    const key = await importKey(SECRET_KEY);
    const enc = new TextEncoder();

    const isValid = await window.crypto.subtle.verify(
        "HMAC",
        key,
        base64UrlDecode(encodedSignature),
        enc.encode(`${encodedHeader}.${encodedPayload}`)
    );

    if (!isValid) return null;

    const dec = new TextDecoder();
    return JSON.parse(dec.decode(base64UrlDecode(encodedPayload)));
}
