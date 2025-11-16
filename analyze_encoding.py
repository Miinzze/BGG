#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Analyze the actual encoding issues in the SQL file
"""

# Read a sample with the problematic text
with open('/home/user/BGG/datenbank.sql', 'rb') as f:
    content = f.read()

# Search for the pattern from line 1519 that we saw earlier
search_text = b'Wartungscheckliste f'
idx = content.find(search_text)

if idx != -1:
    # Get 100 bytes around this location
    sample = content[idx:idx+200]
    print("Raw bytes sample:")
    print(sample)
    print("\n" + "="*80 + "\n")

    print("Hexadecimal representation:")
    print(sample.hex())
    print("\n" + "="*80 + "\n")

    print("Attempting different decodings:")

    # Try different encodings
    encodings = ['utf-8', 'latin1', 'cp1252', 'iso-8859-1']
    for enc in encodings:
        try:
            decoded = sample.decode(enc)
            print(f"\n{enc}:")
            print(decoded[:100])
        except:
            print(f"\n{enc}: FAILED")

    # Check specific byte sequences
    print("\n" + "="*80 + "\n")
    print("Looking for specific byte patterns:")

    # Common UTF-8 byte sequences for umlauts
    patterns = {
        b'\xc3\xa4': 'ä (UTF-8)',
        b'\xc3\xb6': 'ö (UTF-8)',
        b'\xc3\xbc': 'ü (UTF-8)',
        b'\xc3\x84': 'Ä (UTF-8)',
        b'\xc3\x96': 'Ö (UTF-8)',
        b'\xc3\x9c': 'Ü (UTF-8)',
        b'\xc3\x9f': 'ß (UTF-8)',
    }

    for pattern, description in patterns.items():
        count = content.count(pattern)
        print(f"{description}: {count} occurrences")
else:
    print("Pattern not found!")
