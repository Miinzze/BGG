#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Check for ALL possible encoding issues in the SQL file
"""

import re

def check_encodings(filename):
    """Check for various encoding issues"""

    with open(filename, 'rb') as f:
        content = f.read()

    print(f"Checking file: {filename}")
    print(f"File size: {len(content)} bytes")
    print("="*80)

    # Pattern 1: Check for Ã with following characters (Latin-1 interpretation of UTF-8)
    patterns_latin1_utf8 = {
        b'\xc3\x83': 'Ã (double-encoded Ã or start of triple-encoded umlaut)',
        b'\xc3\xa4': 'ä (correct UTF-8)',
        b'\xc3\xb6': 'ö (correct UTF-8)',
        b'\xc3\xbc': 'ü (correct UTF-8)',
        b'\xc3\x84': 'Ä (correct UTF-8)',
        b'\xc3\x96': 'Ö (correct UTF-8)',
        b'\xc3\x9c': 'Ü (correct UTF-8)',
        b'\xc3\x9f': 'ß (correct UTF-8)',
    }

    print("\n1. Checking byte patterns:")
    for pattern, description in patterns_latin1_utf8.items():
        count = content.count(pattern)
        if count > 0:
            print(f"   {pattern.hex()}: {description} - {count} occurrences")

    # Pattern 2: Try to decode as UTF-8 and check for Ã followed by specific chars
    try:
        text = content.decode('utf-8')

        # These patterns indicate wrong encoding display
        bad_patterns = [
            (r'Ã¤', 'ä displayed as Latin-1'),
            (r'Ã¶', 'ö displayed as Latin-1'),
            (r'Ã¼', 'ü displayed as Latin-1'),
            (r'Ã„', 'Ä displayed as Latin-1'),
            (r'Ã–', 'Ö displayed as Latin-1'),
            (r'Ãœ', 'Ü displayed as Latin-1'),
            (r'ÃŸ', 'ß displayed as Latin-1'),
            (r'Ã\x83', 'Triple-encoded start'),
        ]

        print("\n2. Checking for wrong encoding patterns in text:")
        found_issues = False
        for pattern, description in bad_patterns:
            matches = list(re.finditer(pattern, text))
            if matches:
                found_issues = True
                print(f"   '{pattern}': {description} - {len(matches)} occurrences")
                # Show first few examples
                for match in matches[:3]:
                    start = max(0, match.start() - 20)
                    end = min(len(text), match.end() + 20)
                    context = text[start:end].replace('\n', ' ')
                    print(f"      ...{context}...")

        if not found_issues:
            print("   ✓ No encoding display issues found in text!")

        # Pattern 3: Count correct umlauts
        print("\n3. Correct umlauts found:")
        correct_umlauts = ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß']
        for char in correct_umlauts:
            count = text.count(char)
            if count > 0:
                print(f"   '{char}': {count} occurrences")

    except UnicodeDecodeError as e:
        print(f"\n   ✗ File is not valid UTF-8: {e}")

    print("\n" + "="*80)

if __name__ == '__main__':
    check_encodings('/home/user/BGG/datenbank.sql')
