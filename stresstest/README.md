# Testiranje in Simulacija (Python orodja)

Ta mapa vsebuje Python skripte za stresno testiranje, varnostno preverjanje in hitro populacijo baze s testnimi podatki.

## 📋 Zahteve

Za delovanje vseh skript potrebujete Python 3.x in naslednje knjižnice:

```bash
pip install requests aiohttp faker
```

---

## 🚀 Glavna orodja

### 1. Celovita testna suita (`test_suite.py`)

Glavno orodje za preverjanje stabilnosti in varnosti aplikacije pod visoko obremenitvijo.

- **Load testi**: Simulacija do 600 sočasnih uporabnikov (prijave, glasovanje, doživetja).
- **Varnostni testi**: Avtomatsko preverjanje SQL injection, XSS in manipulacije sej.
- **Robni primeri**: Testiranje unicode znakov, praznih vnosov in race-condition stanja.

**Uporaba:**

```bash
# Zaženi vse teste
python test_suite.py --url http://localhost/LepoJeBitiElektrotehnik-WebApp --test all

# Samo load testi (hitrost in zmogljivost)
python test_suite.py --test load

# Samo varnostni testi
python test_suite.py --test security
```

### 2. Pomočnik za populacijo podatkov (`prijave.py`)

Skripta za hitro polnjenje baze s testnimi uporabniki (generira realistična slovenska imena).

**Zmožnosti:**

- **Samodejna zaznava**: Skripta sama najde aktivna doživetja na vstopni strani.
- **Generiranje imen**: Uporablja nabor 100+ slovenskih imen in priimkov.
- **Prilagodljivost**: Omogoča nastavitev števila prijav za kviz in doživetja posebej.

**Primeri uporabe:**

```bash
# Napolni kviz s 100 prijavami in vsako doživetje s 30 prijavami
python prijave.py -k 100 -d 30

# Prijavi ljudi na specifičen URL (npr. produkcija)
python prijave.py --url http://elektrotehnika.info -k 50

# Ročna določitev kod doživetij
python prijave.py -d 10 -c vr_izkusnja escape_room
```

---

## 🛠️ Hitri testi

### Preprosta obremenitev (`GET_load.py`)

Minimalistična skripta za hitro preverjanje odzivnosti strežnika. Uporablja `ThreadPoolExecutor` za pošiljanje 400 sočasnih GET zahtev na vstopno stran.

**Uporaba:**

```bash
python GET_load.py
```

---

## 📈 Razlaga rezultatov (`test_suite.py`)

Po končanem testu boste prejeli izpisa:

- **Success Rate**: Odstotek uspešnih zahtev (cilj > 95%).
- **Average Response Time**: Povprečen čas odziva strežnika (cilj < 500ms).
- **Security Score**: Število ustavljenih zlonamernih napadov.

---

**Opozorilo**: Skripte so namenjene testiranju v lokalnem okolju ali na strežnikih, kjer imate dovoljenje za izvajanje stresnih testov.
