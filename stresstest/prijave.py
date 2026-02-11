import requests
import random
import time
import argparse
import re

# List of typical Slovenian names
FIRST_NAMES = [
    "Janez", "Marija", "Ivan", "Ana", "Anton", "Maja", "Franc", "Irena", "Jože", "Mojca",
    "Andrej", "Mateja", "Marko", "Nina", "Luka", "Barbara", "Peter", "Andreja", "Stanislav", "Petra",
    "Matej", "Nataša", "Tomaž", "Jožica", "Filip", "Janja", "Aleš", "Milena", "Rok", "Tatjana",
    "Bojan", "Simona", "Gregor", "Danijela", "Miha", "Sabina", "Robert", "Tjaša", "Žiga", "Anja",
    "Borut", "Ema", "Tadej", "Zala", "Matjaž", "Klara", "Branko", "Sara", "Martin", "Lara",
    "Jure", "Metka", "Uroš", "Katja", "Teja", "David", "Alenka", "Klemen", "Majda",
    "Dejan", "Branka", "Primož", "Vlasta", "Mitja", "Breda", "Gašper", "Darja", "Blaž", "Vera",
    "Stane", "Marta", "Zoran", "Vida", "Drago", "Silva", "Milan", "Marija", "Dušan", "Neža",
    "Brane", "Helena", "Iztok", "Špela", "Srečko", "Urška", "Marjan", "Saša", "Damjan", "Jasna"
]

LAST_NAMES = [
    "Novak", "Horvat", "Krajnc", "Zupančič", "Potočnik", "Kovačič", "Mlakar", "Kos", "Vidmar", "Golob",
    "Turk", "Božič", "Korošec", "Bizjak", "Rajh", "Hribar", "Kozak", "Kavčič", "Rozman", "Kastelic",
    "Oblak", "Žagar", "Hočevar", "Koren", "Kralj", "Knez", "Zupan", "Pirc", "Logar", "Sever",
    "Jereb", "Godec", "Vovk", "Rus", "Kovač", "Marolt", "Petek", "Pavlič", "Dolar",
    "Humar", "Šinkovec", "Lah", "Blažič", "Zorko", "Leban", "Kalan", "Kolar", "Erjavec", "Jelen"
]

def generate_name():
    return f"{random.choice(FIRST_NAMES)} {random.choice(LAST_NAMES)}"

def get_experience_codes(base_url):
    print(f"Discovering active experience codes from {base_url}...")
    try:
        resp = requests.get(base_url, timeout=10)
        if resp.status_code == 200:
            codes = re.findall(r'name="dozivetje_id\[\]"\s+value="([^"]+)"', resp.text)
            if codes:
                unique_codes = list(set(codes))
                print(f"  Found codes: {', '.join(unique_codes)}")
                return unique_codes
            else:
                print("  [!] No experience codes found in HTML. Are you in the right 'View'?")
    except Exception as e:
        print(f"  [!] Error discovering codes: {e}")
    return []

def run_quiz(base_url, count, verbose=False):
    print(f"\n--- Quiz: {count} registrations ---")
    url = f"{base_url}/prijava.php"
    success = 0
    for i in range(count):
        name = generate_name()
        try:
            resp = requests.post(url, data={'ime': name, 'submit': 'Pošlji'}, timeout=10, allow_redirects=True)
            if resp.status_code == 200:
                # Check if we were redirected to an error or already page
                if "message=already" in resp.url:
                    if verbose: print(f"  [SKIP] {name} - Already registered (Cookie/Session logic)")
                else:
                    success += 1
                    if i % 10 == 0 or verbose: print(f"  [{i+1}/{count}] Registered: {name}")
            else:
                print(f"  [FAIL] {name} - HTTP {resp.status_code}")
        except Exception as e:
            if verbose: print(f"  [ERROR] {name}: {e}")
        time.sleep(0.05)
    print(f"Done! Created {success} new quiz registrations.")

def run_exp(base_url, count_per_exp, codes, verbose=False):
    if not codes:
        print("\n[!] No experience codes to process.")
        return
    
    print(f"\n--- Experiences: {count_per_exp} per code ({len(codes)} codes) ---")
    url = f"{base_url}/prijava_dozivetje.php"
    total_success = 0
    for code in codes:
        print(f"  Processing {code}...")
        success = 0
        for i in range(count_per_exp):
            name = generate_name()
            try:
                resp = requests.post(url, data={
                    'ime': name, 
                    'dozivetje_id[]': [code], 
                    'submit': 'Pošlji'
                }, timeout=10, allow_redirects=True)
                if resp.status_code == 200:
                    if "message=already" in resp.url:
                         if verbose: print(f"    [SKIP] {name} - Already registered")
                    else:
                        success += 1
                        if i % 5 == 0 or verbose: print(f"    [{i+1}/{count_per_exp}] Registered: {name}")
                else:
                    print(f"    [FAIL] {name} - HTTP {resp.status_code}")
            except Exception as e:
                if verbose: print(f"    [ERROR] {name}: {e}")
            time.sleep(0.05)
        print(f"    [OK] Experience {code}: {success}/{count_per_exp}")
        total_success += success
    print(f"Done! Created {total_success} new experience registrations.")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Simple population script')
    parser.add_argument('-u', '--url', type=str, default='http://localhost', help='App URL')
    parser.add_argument('-k', type=int, default=0, help='Number of quiz registrations')
    parser.add_argument('-d', type=int, default=0, help='Number of registrations per experience')
    parser.add_argument('-c', '--codes', type=str, nargs='+', help='Experience codes (optional, auto-detected if not provided)')
    parser.add_argument('-v', '--verbose', action='store_true', help='Show detailed progress')

    args = parser.parse_args()
    
    # Strip trailing slash from URL
    base_url = args.url.rstrip('/')

    if args.k > 0:
        run_quiz(base_url, args.k, args.verbose)
    
    if args.d > 0:
        codes = args.codes if args.codes else get_experience_codes(base_url)
        run_exp(base_url, args.d, codes, args.verbose)

    if args.k == 0 and args.d == 0:
        print("Usage: python prijave.py -k 100 -d 30")
        print("Example: python prijave.py --url http://elektrotehnika.info -v -k 10")
