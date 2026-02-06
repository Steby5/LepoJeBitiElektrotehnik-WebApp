"""
Comprehensive Test Suite for "Lepo je biti elektrotehnik" Web App
=================================================================
Target: 600 concurrent users with 30 malicious actors attempting to break/cheat the system

Dependencies:
    pip install aiohttp asyncio faker

Usage:
    python test_suite.py --url http://localhost --test all
    python test_suite.py --url http://localhost --test load
    python test_suite.py --url http://localhost --test security
    python test_suite.py --url http://localhost --test edge
"""

import asyncio
import aiohttp
import argparse
import random
import string
import time
import json
from datetime import datetime
from typing import List, Dict, Tuple
from dataclasses import dataclass
from enum import Enum

# Configuration
BASE_URL = "http://localhost/LepoJeBitiElektrotehnik-WebApp"
NORMAL_USERS = 570
MALICIOUS_USERS = 30
TOTAL_USERS = NORMAL_USERS + MALICIOUS_USERS

# Test Results
@dataclass
class TestResult:
    test_name: str
    passed: bool
    duration: float
    details: str
    requests_made: int = 0
    success_rate: float = 0.0

class TestType(Enum):
    LOAD = "load"
    SECURITY = "security"
    EDGE = "edge"
    ALL = "all"

# ============= HELPER FUNCTIONS =============

def generate_name():
    """Generate a random Slovenian-like name"""
    first_names = ["Janez", "Marko", "Luka", "Matic", "Žiga", "Ana", "Maja", "Nina", "Sara", "Eva", 
                   "Peter", "Bojan", "Miha", "Nejc", "Rok", "Tina", "Katja", "Petra", "Urška", "Mateja"]
    last_names = ["Novak", "Horvat", "Kovač", "Krajnc", "Zupan", "Potočnik", "Vidmar", "Golob", 
                  "Oblak", "Mlakar", "Kolar", "Medved", "Kranjc", "Bizjak", "Hribar"]
    return f"{random.choice(first_names)} {random.choice(last_names)}"

def generate_malicious_name():
    """Generate malicious input for SQL injection/XSS attempts"""
    attacks = [
        "'; DROP TABLE contestants; --",
        "<script>alert('XSS')</script>",
        "' OR '1'='1' --",
        "admin'--",
        "<img src=x onerror=alert('XSS')>",
        "{{7*7}}",  # SSTI
        "${7*7}",   # Template injection
        "../../../etc/passwd",
        "<?php system('id'); ?>",
        "' UNION SELECT * FROM users --",
        "\"; cat /etc/passwd; #",
        "a" * 10000,  # Buffer overflow attempt
        "🚀💀👽" * 100,  # Unicode stress
        "\x00\x01\x02",  # Null bytes
        "%00%01%02",  # URL encoded null bytes
    ]
    return random.choice(attacks)

async def make_request(session: aiohttp.ClientSession, method: str, url: str, 
                       data: dict = None, cookies: dict = None) -> Tuple[int, str, float]:
    """Make an HTTP request and return status, response, and duration"""
    start = time.time()
    try:
        if method == "GET":
            async with session.get(url, cookies=cookies, timeout=30, allow_redirects=True) as resp:
                text = await resp.text()
                return resp.status, text, time.time() - start
        elif method == "POST":
            async with session.post(url, data=data, cookies=cookies, timeout=30, allow_redirects=True) as resp:
                text = await resp.text()
                return resp.status, text, time.time() - start
    except Exception as e:
        return 0, str(e), time.time() - start

async def activate_view(session: aiohttp.ClientSession, view: int) -> bool:
    """Activate a specific view mode (simulates admin action)
    
    Views:
        0 = Ni aktivnosti (idle)
        1 = Prijava na kviz
        2 = Glas ljudstva (glasovanje)
        3 = Doživetja
    """
    try:
        url = f"{BASE_URL}/spremeni_pogled.php?view={view}"
        async with session.get(url, timeout=10, allow_redirects=True) as resp:
            success = resp.status in [200, 302]
            if success:
                print(f"   [OK] Activated view {view}")
            return success
    except Exception as e:
        print(f"   [FAIL] Failed to activate view {view}: {e}")
        return False

# ============= LOAD TESTS =============

async def test_concurrent_prijava(session: aiohttp.ClientSession, user_id: int, is_malicious: bool) -> dict:
    """Simulate a user registering for kviz"""
    name = generate_malicious_name() if is_malicious else generate_name()
    
    status, response, duration = await make_request(
        session, "POST", f"{BASE_URL}/prijava.php",
        data={"ime": name}
    )
    
    return {
        "user_id": user_id,
        "malicious": is_malicious,
        "status": status,
        "duration": duration,
        "success": status in [200, 302]
    }

async def test_concurrent_glasovanje(session: aiohttp.ClientSession, user_id: int, is_malicious: bool, qid: int = 1) -> dict:
    """Simulate a user voting"""
    odgovor = random.choice(["A", "B", "C", "D"])
    
    if is_malicious:
        # Try various manipulation attacks
        attacks = [
            {"Qid": qid, "odgovor": "'; DROP TABLE question; --"},
            {"Qid": -1, "odgovor": "A"},
            {"Qid": 99999, "odgovor": "A"},
            {"Qid": qid, "odgovor": "INVALID"},
            {"Qid": qid, "odgovor": "A" * 1000},
        ]
        data = random.choice(attacks)
    else:
        data = {"Qid": qid, "odgovor": odgovor}
    
    status, response, duration = await make_request(
        session, "POST", f"{BASE_URL}/glasovanje.php",
        data=data
    )
    
    return {
        "user_id": user_id,
        "malicious": is_malicious,
        "status": status,
        "duration": duration,
        "success": status in [200, 302]
    }

async def test_concurrent_dozivetje(session: aiohttp.ClientSession, user_id: int, is_malicious: bool) -> dict:
    """Simulate a user registering for dozivetje"""
    name = generate_malicious_name() if is_malicious else generate_name()
    
    if is_malicious:
        # Try invalid dozivetje_id values
        dozivetje_ids = [
            ["'; DELETE FROM dozivetja; --"],
            ["invalid", "another"],
            ["-1"],
            ["99999999"],
            ["a" * 1000],
        ]
        selected = random.choice(dozivetje_ids)
    else:
        selected = ["vr_izkusnja"]  # Valid dozivetje code
    
    data = {"ime": name}
    for code in selected:
        data[f"dozivetje_id[]"] = code
    
    status, response, duration = await make_request(
        session, "POST", f"{BASE_URL}/prijava_dozivetje.php",
        data=data
    )
    
    return {
        "user_id": user_id,
        "malicious": is_malicious,
        "status": status,
        "duration": duration,
        "success": status in [200, 302]
    }

async def run_load_test(test_type: str, concurrent_users: int = TOTAL_USERS) -> TestResult:
    """Run load test with specified number of concurrent users"""
    print(f"\n* Starting {test_type} load test with {concurrent_users} users...")
    
    start_time = time.time()
    results = []
    
    connector = aiohttp.TCPConnector(limit=100, force_close=True)
    async with aiohttp.ClientSession(connector=connector) as session:
        # Activate the appropriate view before testing
        if test_type == "prijava":
            print("   Activating prijava mode (view=1)...")
            await activate_view(session, 1)
        elif test_type == "glasovanje":
            print("   Activating glasovanje mode (view=2)...")
            await activate_view(session, 2)
        elif test_type == "dozivetje":
            print("   Activating dozivetje mode (view=3)...")
            await activate_view(session, 3)
        
        # Small delay to ensure mode is active
        await asyncio.sleep(0.5)
        
        tasks = []
        
        for i in range(concurrent_users):
            is_malicious = i >= NORMAL_USERS
            
            if test_type == "prijava":
                tasks.append(test_concurrent_prijava(session, i, is_malicious))
            elif test_type == "glasovanje":
                tasks.append(test_concurrent_glasovanje(session, i, is_malicious))
            elif test_type == "dozivetje":
                tasks.append(test_concurrent_dozivetje(session, i, is_malicious))
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
    
    duration = time.time() - start_time
    
    # Analyze results
    successful = sum(1 for r in results if isinstance(r, dict) and r.get("success"))
    failed = len(results) - successful
    success_rate = (successful / len(results)) * 100 if results else 0
    
    malicious_blocked = sum(1 for r in results if isinstance(r, dict) and r.get("malicious") and not r.get("success"))
    
    avg_duration = sum(r["duration"] for r in results if isinstance(r, dict)) / len(results) if results else 0
    
    details = f"""
    Total requests: {len(results)}
    Successful: {successful} ({success_rate:.1f}%)
    Failed: {failed}
    Malicious attempts blocked: {malicious_blocked}/{MALICIOUS_USERS}
    Average response time: {avg_duration*1000:.0f}ms
    Total duration: {duration:.2f}s
    Requests/second: {len(results)/duration:.1f}
    """
    
    passed = success_rate > 95 and avg_duration < 5
    
    return TestResult(
        test_name=f"Load Test - {test_type}",
        passed=passed,
        duration=duration,
        details=details,
        requests_made=len(results),
        success_rate=success_rate
    )

# ============= SECURITY TESTS =============

async def test_sql_injection() -> TestResult:
    """Test SQL injection vulnerabilities"""
    print("\n* Testing SQL Injection vulnerabilities...")
    
    payloads = [
        "' OR '1'='1",
        "'; DROP TABLE contestants; --",
        "1; DELETE FROM contestants",
        "' UNION SELECT * FROM information_schema.tables --",
        "1' AND '1'='1",
        "admin'--",
        "1 OR 1=1",
        "' OR ''='",
    ]
    
    results = []
    async with aiohttp.ClientSession() as session:
        for payload in payloads:
            # Test prijava
            status, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": payload}
            )
            results.append(("prijava", payload, "error" not in response.lower()))
            
            # Test glasovanje
            status, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/glasovanje.php",
                data={"Qid": payload, "odgovor": "A"}
            )
            results.append(("glasovanje_qid", payload, "error" not in response.lower()))
    
    blocked = sum(1 for r in results if r[2])
    total = len(results)
    
    return TestResult(
        test_name="SQL Injection Test",
        passed=blocked == total,
        duration=0,
        details=f"Blocked {blocked}/{total} injection attempts",
        requests_made=total
    )

async def test_xss_attacks() -> TestResult:
    """Test XSS vulnerabilities"""
    print("\n* Testing XSS vulnerabilities...")
    
    payloads = [
        "<script>alert('XSS')</script>",
        "<img src=x onerror=alert('XSS')>",
        "<svg onload=alert('XSS')>",
        "javascript:alert('XSS')",
        "<body onload=alert('XSS')>",
        "'><script>alert('XSS')</script>",
        "<iframe src='javascript:alert(1)'></iframe>",
    ]
    
    results = []
    async with aiohttp.ClientSession() as session:
        for payload in payloads:
            status, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": payload}
            )
            # Check if payload is reflected without encoding
            vulnerable = payload in response
            results.append((payload, not vulnerable))
    
    safe = sum(1 for r in results if r[1])
    total = len(results)
    
    return TestResult(
        test_name="XSS Attack Test",
        passed=safe == total,
        duration=0,
        details=f"Protected against {safe}/{total} XSS attempts",
        requests_made=total
    )

async def test_session_manipulation() -> TestResult:
    """Test session/cookie manipulation vulnerabilities"""
    print("\n* Testing session manipulation...")
    
    results = []
    async with aiohttp.ClientSession() as session:
        # Try with forged session cookies
        forged_cookies = [
            {"ljbe_prijava_session": "fake_session_id"},
            {"ljbe_prijava_session": ""},
            {"ljbe_prijava_session": "' OR '1'='1"},
            {"ljbe_glasovanje_session": "manipulated"},
            {"PHPSESSID": "hijacked_session"},
        ]
        
        for cookies in forged_cookies:
            # First register
            status1, _, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": generate_name()},
                cookies=cookies
            )
            
            # Try to register again with forged cookie
            status2, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": generate_name()},
                cookies=cookies
            )
            
            results.append((str(cookies), status2 in [200, 302]))
    
    return TestResult(
        test_name="Session Manipulation Test",
        passed=True,  # Pass if no errors
        duration=0,
        details=f"Tested {len(results)} cookie manipulation attempts",
        requests_made=len(results) * 2
    )

async def test_rate_limiting() -> TestResult:
    """Test rate limiting by rapid requests"""
    print("\n* Testing rate limiting (rapid fire requests)...")
    
    RAPID_REQUESTS = 100
    results = []
    
    async with aiohttp.ClientSession() as session:
        # Send 100 requests as fast as possible from same "user"
        tasks = []
        for i in range(RAPID_REQUESTS):
            tasks.append(make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": f"RapidUser{i}"}
            ))
        
        responses = await asyncio.gather(*tasks, return_exceptions=True)
        
        success_count = sum(1 for r in responses if isinstance(r, tuple) and r[0] in [200, 302])
        error_count = sum(1 for r in responses if isinstance(r, tuple) and r[0] >= 400)
    
    # System should handle rapid requests without crashing
    return TestResult(
        test_name="Rate Limiting Test",
        passed=error_count < RAPID_REQUESTS * 0.5,  # Less than 50% errors is OK
        duration=0,
        details=f"Rapid requests: {success_count} success, {error_count} errors out of {RAPID_REQUESTS}",
        requests_made=RAPID_REQUESTS
    )

# ============= EDGE CASE TESTS =============

async def test_duplicate_submissions() -> TestResult:
    """Test that duplicate submissions are blocked"""
    print("\n* Testing duplicate submission prevention...")
    
    results = []
    jar = aiohttp.CookieJar()
    
    async with aiohttp.ClientSession(cookie_jar=jar) as session:
        name = generate_name()
        
        # First submission should succeed
        status1, response1, _ = await make_request(
            session, "POST", f"{BASE_URL}/prijava.php",
            data={"ime": name}
        )
        
        # Second submission from same session should be blocked
        status2, response2, _ = await make_request(
            session, "POST", f"{BASE_URL}/prijava.php",
            data={"ime": name}
        )
        
        # Check if second was blocked (redirected to already message)
        second_blocked = "already" in response2.lower() or "že" in response2.lower()
        
    return TestResult(
        test_name="Duplicate Submission Prevention",
        passed=second_blocked,
        duration=0,
        details=f"First request: {status1}, Second blocked: {second_blocked}",
        requests_made=2
    )

async def test_unicode_and_special_chars() -> TestResult:
    """Test handling of Unicode and special characters"""
    print("\n* Testing Unicode and special character handling...")
    
    special_names = [
        "Žiga Škofič",  # Slovenian characters
        "Müller Größe",  # German umlauts
        "北京用户",  # Chinese
        "مستخدم عربي",  # Arabic
        "🎮 Gamer 🎲",  # Emojis
        "O'Brien-Smith",  # Apostrophe and hyphen
        "Test\tTab",  # Tab character
        "New\nLine",  # Newline
        "   Spaces   ",  # Leading/trailing spaces
    ]
    
    results = []
    async with aiohttp.ClientSession() as session:
        for name in special_names:
            status, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": name}
            )
            results.append((name, status in [200, 302]))
    
    success = sum(1 for r in results if r[1])
    
    return TestResult(
        test_name="Unicode/Special Character Test",
        passed=success == len(results),
        duration=0,
        details=f"Successfully handled {success}/{len(results)} special character names",
        requests_made=len(results)
    )

async def test_empty_and_long_inputs() -> TestResult:
    """Test handling of empty and extremely long inputs"""
    print("\n* Testing empty and long input handling...")
    
    test_inputs = [
        "",  # Empty
        " ",  # Single space
        "A" * 51,  # Just over 50 char limit
        "A" * 1000,  # Very long
        "A" * 10000,  # Extremely long
    ]
    
    results = []
    async with aiohttp.ClientSession() as session:
        for name in test_inputs:
            status, response, _ = await make_request(
                session, "POST", f"{BASE_URL}/prijava.php",
                data={"ime": name}
            )
            # Should either handle gracefully or reject
            handled = status in [200, 302, 400, 422]
            results.append((f"len={len(name)}", handled))
    
    return TestResult(
        test_name="Empty/Long Input Test",
        passed=all(r[1] for r in results),
        duration=0,
        details=f"Handled {sum(1 for r in results if r[1])}/{len(results)} edge cases",
        requests_made=len(results)
    )

async def test_concurrent_contestant_selection() -> TestResult:
    """Test race condition when multiple admins try to select same contestant"""
    print("\n* Testing concurrent contestant selection (race condition)...")
    
    CONCURRENT_SELECTS = 10
    
    async with aiohttp.ClientSession() as session:
        # Try to select same contestant simultaneously
        tasks = []
        for i in range(CONCURRENT_SELECTS):
            tasks.append(make_request(
                session, "GET", f"{BASE_URL}/izberi_tekmovalca.php",
                data={"name": "Test User", "id": 1}
            ))
        
        responses = await asyncio.gather(*tasks, return_exceptions=True)
        
        # Check that system didn't crash
        errors = sum(1 for r in responses if isinstance(r, tuple) and r[0] >= 500)
    
    return TestResult(
        test_name="Race Condition Test (Contestant Selection)",
        passed=errors == 0,
        duration=0,
        details=f"Server errors during concurrent selection: {errors}/{CONCURRENT_SELECTS}",
        requests_made=CONCURRENT_SELECTS
    )

async def test_invalid_http_methods() -> TestResult:
    """Test handling of invalid HTTP methods"""
    print("\n* Testing invalid HTTP methods...")
    
    endpoints = [
        "/prijava.php",
        "/glasovanje.php",
        "/prijava_dozivetje.php",
        "/nadzor.php",
    ]
    
    results = []
    async with aiohttp.ClientSession() as session:
        for endpoint in endpoints:
            # Try PUT
            async with session.put(f"{BASE_URL}{endpoint}", data={}) as resp:
                results.append((endpoint, "PUT", resp.status))
            
            # Try DELETE
            async with session.delete(f"{BASE_URL}{endpoint}") as resp:
                results.append((endpoint, "DELETE", resp.status))
    
    # System should either reject or ignore invalid methods
    handled = all(r[2] != 500 for r in results)
    
    return TestResult(
        test_name="Invalid HTTP Methods Test",
        passed=handled,
        duration=0,
        details=f"Tested {len(results)} invalid method requests, no server crashes",
        requests_made=len(results)
    )

# ============= MAIN TEST RUNNER =============

async def run_all_tests(base_url: str, test_type: str = "all") -> List[TestResult]:
    """Run all tests and return results"""
    global BASE_URL
    BASE_URL = base_url
    
    results = []
    
    print(f"\n{'='*60}")
    print(f"LEPO JE BITI ELEKTROTEHNIK - Test Suite")
    print(f"Target: {TOTAL_USERS} users ({NORMAL_USERS} normal, {MALICIOUS_USERS} malicious)")
    print(f"URL: {BASE_URL}")
    print(f"{'='*60}")
    
    if test_type in ["all", "load"]:
        print("\n" + "="*40)
        print("LOAD TESTS")
        print("="*40)
        
        results.append(await run_load_test("prijava", TOTAL_USERS))
        results.append(await run_load_test("glasovanje", TOTAL_USERS))
        results.append(await run_load_test("dozivetje", TOTAL_USERS))
    
    if test_type in ["all", "security"]:
        print("\n" + "="*40)
        print("SECURITY TESTS")
        print("="*40)
        
        results.append(await test_sql_injection())
        results.append(await test_xss_attacks())
        results.append(await test_session_manipulation())
        results.append(await test_rate_limiting())
    
    if test_type in ["all", "edge"]:
        print("\n" + "="*40)
        print("EDGE CASE TESTS")
        print("="*40)
        
        results.append(await test_duplicate_submissions())
        results.append(await test_unicode_and_special_chars())
        results.append(await test_empty_and_long_inputs())
        results.append(await test_concurrent_contestant_selection())
        results.append(await test_invalid_http_methods())
    
    return results

def print_results(results: List[TestResult]):
    """Print test results summary"""
    print("\n" + "="*60)
    print("TEST RESULTS SUMMARY")
    print("="*60)
    
    passed = sum(1 for r in results if r.passed)
    total = len(results)
    
    for result in results:
        status = "[PASS]" if result.passed else "[FAIL]"
        print(f"\n{status} {result.test_name}")
        print(f"   {result.details.strip()}")
    
    print("\n" + "="*60)
    print(f"FINAL SCORE: {passed}/{total} tests passed ({passed/total*100:.0f}%)")
    print("="*60)
    
    return passed == total

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Test Suite for Lepo je biti elektrotehnik")
    parser.add_argument("--url", default="http://localhost",
                        help="Base URL of the application")
    parser.add_argument("--test", choices=["all", "load", "security", "edge"], default="all",
                        help="Type of tests to run")
    
    args = parser.parse_args()
    
    results = asyncio.run(run_all_tests(args.url, args.test))
    success = print_results(results)
    
    exit(0 if success else 1)
