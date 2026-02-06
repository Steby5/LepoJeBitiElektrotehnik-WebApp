# Lepo je biti elektrotehnik - WEB app

Spletni vmesnik kviza "Lepo je biti elektrotehnik". Prilagojeno za uporabo na mobilnih napravah in upravljanje z računalnikom.

## Funkcionalnosti

### Kviz

- **Prijava na kviz** - udeleženci oddajo svoje ime
- **Izbira tekmovalca** - nadzornik izbere tekmovalca iz seznama prijavljenih
- **Glas ljudstva** - občinstvo glasuje za pravilni odgovor (A/B/C/D)
- **Prikaz rezultatov** - prikaže odstotke glasov

### Doživetja

- **Prijava na doživetja** - udeleženci izberejo doživetja in oddajo ime
- **Upravljanje doživetij** - JSON uvoz, izbira/odstranitev udeležencev
- **Omejitev mest** - prikaz prostih mest za vsako doživetje

### Zaščita pred podvajanjem

- **Session-based blokiranje** - vsaka naprava lahko odda samo 1 prijavo/glas na sejo
- **Nova seja pri menjavi stanja** - ko nadzornik aktivira nov način, se odpre nova seja

## Struktura projekta

### Glavne strani

| Datoteka    | Opis                                |
| ----------- | ----------------------------------- |
| `index.php` | Domača stran s prijavami            |
| `vodic.php` | Prikaz za mobilne naprave (QR scan) |

### Nadzorne plošče

| Datoteka               | Opis                               |
| ---------------------- | ---------------------------------- |
| `nadzor.php`           | Nadzor kviza (prijave, glasovanje) |
| `nadzor_dozivetja.php` | Nadzor doživetij                   |

### API (AJAX)

| Datoteka                   | Opis                             |
| -------------------------- | -------------------------------- |
| `api_nadzor.php`           | JSON podatki za kviz nadzor      |
| `api_nadzor_dozivetja.php` | JSON podatki za doživetja nadzor |

### Obdelava obrazcev

| Datoteka                | Opis                          |
| ----------------------- | ----------------------------- |
| `prijava.php`           | Obdelava prijave na kviz      |
| `prijava_dozivetje.php` | Obdelava prijave na doživetja |
| `glasovanje.php`        | Obdelava glasovanja           |
| `vnesi_vprasanje.php`   | Vnos novega vprašanja         |

### Upravljanje

| Datoteka                       | Opis                           |
| ------------------------------ | ------------------------------ |
| `izberi_tekmovalca.php`        | Izbira tekmovalca              |
| `odstrani_tekmovalca.php`      | Odstrani tekmovalca            |
| `pocisti_prijave.php`          | Počisti vse prijave            |
| `izberi_dozivetje.php`         | Izbira udeleženca za doživetje |
| `odstrani_dozivetje.php`       | Odstrani udeleženca            |
| `pocisti_dozivetja.php`        | Počisti vse prijave doživetij  |
| `pocisti_eno_dozivetje.php`    | Počisti eno doživetje          |
| `nalozi_dozivetja.php`         | Naloži JSON z doživetji        |
| `nastavi_prikaz_dozivetja.php` | Nastavi prikaz doživetja       |
| `spremeni_pogled.php`          | Menjava stanja/pogleda         |

### "Hvala" strani

| Datoteka               | Opis                           |
| ---------------------- | ------------------------------ |
| `hvala_prijava.php`    | Po prijavi na kviz             |
| `hvala_dozivetje.php`  | Po prijavi na doživetja        |
| `hvala_glasovanje.php` | Po glasovanju                  |
| `rezultati.php`        | Prikaz rezultatov glasovanja   |
| `prikaz_dozivetja.php` | Prikaz doživetja za udeležence |

### Konfiguracijske datoteke

| Datoteka                 | Opis                         |
| ------------------------ | ---------------------------- |
| `pogled.txt`             | Trenutni način prikaza (0-3) |
| `prijava_session.txt`    | ID seje za prijave           |
| `glasovanje_session.txt` | ID seje za glasovanje        |
| `dozivetja_session.txt`  | ID seje za doživetja         |
| `izbran_tekmovalec.txt`  | Ime trenutnega tekmovalca    |
| `prikaz_dozivetje.txt`   | ID prikazanega doživetja     |

## Načini prikaza

| Vrednost | Način           | Opis                       |
| -------- | --------------- | -------------------------- |
| 0        | Ni aktivnosti   | Mirovanje                  |
| 1        | Prijava na kviz | Odprt obrazec za prijavo   |
| 2        | Glas ljudstva   | Odprto glasovanje          |
| 3        | Doživetja       | Odprt obrazec za doživetja |

## Zahteve

- **PHP** 8.1+
- **MySQL** strežnik
- **XAMPP** (za lokalni razvoj) ali VPS
- SSL certifikat (priporočeno Let's Encrypt)

### Za produkcijo

- VPS s 4 jedri, 8 GB RAM, 1Gbps omrežje
- Testirano do 600 sočasnih uporabnikov

## Namestitev

1. Kopiraj datoteke v `htdocs` (XAMPP) ali web root
2. Uvozi bazo iz `mysql/` mape
3. Nastavi `server_data.php` s podatki za povezavo
4. Obišči `nadzor.php` za upravljanje

## Baza podatkov

### Tabele

- `contestants` - prijavljeni na kviz (ID, name, time, izbran)
- `question` - vprašanja za glasovanje
- `dozivetja` - aktivna doživetja
- `dozivetja_prijave` - prijave na doživetja

## Licenca

MIT License - UL FE 2026
