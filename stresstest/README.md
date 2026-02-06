# Test Suite - Lepo je biti elektrotehnik

Celovita zbirka testov za testiranje web aplikacije.

## Zahteve

```bash
pip install aiohttp
```

## Uporaba

### Vsi testi

````bash
```bash
python test_suite.py --url http://localhost --test all
````

### Samo load testi (600 uporabnikov)

```bash
python test_suite.py --url http://localhost --test load
```

### Samo varnostni testi

```bash
python test_suite.py --url http://localhost --test security
```

### Samo robni primeri

```bash
python test_suite.py --url http://localhost --test edge
```

## Konfiguracija

Privzeto testira z:

- 570 normalnih uporabnikov
- 30 zlonamernih uporabnikov (napadi)
- Skupaj 600 sočasnih uporabnikov

## Testi

### Load testi

| Test       | Opis                             |
| ---------- | -------------------------------- |
| Prijava    | 600 sočasnih prijav na kviz      |
| Glasovanje | 600 sočasnih glasov              |
| Doživetje  | 600 sočasnih prijav na doživetja |

### Varnostni testi

| Test                 | Opis                              |
| -------------------- | --------------------------------- |
| SQL Injection        | 8 različnih SQL injection napadov |
| XSS                  | 7 različnih XSS napadov           |
| Session Manipulation | Pokušaj lažnega session/cookie    |
| Rate Limiting        | 100 hitrih zaporednih zahtev      |

### Robni primeri

| Test                 | Opis                                        |
| -------------------- | ------------------------------------------- |
| Duplicate Prevention | Ali se dvojne prijave blokirajo             |
| Unicode              | Slovenščina, kitajščina, arabščina, emojiji |
| Empty/Long Inputs    | Prazna, predolga in ekstremno dolga imena   |
| Race Condition       | 10 sočasnih izbir istega tekmovalca         |
| Invalid HTTP Methods | PUT, DELETE na forme                        |

## Zlonamerni napadi (30 uporabnikov)

Simulirani napadi vključujejo:

- SQL injection (`'; DROP TABLE contestants; --`)
- XSS (`<script>alert('XSS')</script>`)
- Buffer overflow (`'A' * 10000`)
- Path traversal (`../../../etc/passwd`)
- Template injection (`{{7*7}}`)
- Null bytes (`\x00\x01\x02`)
- Unicode stress (100x emojiji)

## Rezultati

Testi se uspešno zaključijo če:

- [PASS] Load testi: >95% uspešnost, povprečni odziv <5s
- [PASS] Varnostni testi: vsi napadi blokirani
- [PASS] Robni primeri: vsi primeri pravilno obdelani

## Primer izhoda

```
============================================================
LEPO JE BITI ELEKTROTEHNIK - Test Suite
Target: 600 users (570 normal, 30 malicious)
URL: http://localhost
============================================================

LOAD TESTS
========================================
* Starting prijava load test with 600 users...

[PASS] Load Test - prijava
   Total requests: 600
   Successful: 598 (99.7%)
   Failed: 2
   Average response time: 234ms
   Requests/second: 28.4

============================================================
FINAL SCORE: 12/12 tests passed (100%)
============================================================
```
