<?php
/**
 * Környezeti változók betöltése .env fájlból
 *
 * Ez a modul felelős a .env fájl beolvasásáért és a környezeti változók
 * ($_ENV, $_SERVER, putenv) beállításáért. A függvény egyszerű kulcs=érték
 * párokat dolgoz fel és kihagyja a kommenteket (#-sal kezdődő sorokat).
 */

/**
 * .env fájl betöltése és feldolgozása
 *
 * A függvény beolvassa a megadott .env fájlt és minden KEY=VALUE sort
 * hozzáad a PHP környezeti változóihoz ($_ENV, $_SERVER, putenv).
 *
 * @param string $path A .env fájl teljes elérési útja
 *
 * Példa .env fájl:
 * DB_HOST=localhost
 * DB_NAME=mydb
 * # Ez egy komment
 * JWT_SECRET=secret123
 */
function loadEnv($path)
{
    // Ha a fájl nem létezik, kilépünk
    if (!file_exists($path)) {
        return;
    }

    // Beolvassuk a fájl sorait (üres sorok és sorvégek nélkül)
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Minden sort feldolgozunk
    foreach ($lines as $line) {
        // Kommentek kihagyása (# karakterrel kezdődő sorok)
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE szétválasztása (max 2 részre, ha az érték tartalmaz = jelet)
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Csak akkor állítjuk be, ha még nincs beállítva (meglévők elsőbbséget élveznek)
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Betöltjük a backend gyökérkönyvtárában található .env fájlt
loadEnv(__DIR__ . '/../.env');
?>