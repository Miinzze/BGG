#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Complete fix for ALL multi-level encoded umlauts.
Handles all German umlauts with proper byte-level replacement.
"""

import sys

def fix_complete_encoding(input_file, output_file):
    """Fix all multi-level encoding corruption."""

    print(f"Reading file: {input_file}")
    with open(input_file, 'rb') as f:
        content = f.read()

    original_size = len(content)
    print(f"Original file size: {original_size} bytes")

    # Complete mapping of all corrupted byte sequences
    # Based on the pattern found: c383c283c382c283c383c282c382c2XX
    # where XX is the last byte that indicates the original character

    replacements = {
        # Small umlauts (already fixed in first pass, but keeping for completeness)
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xbc': b'\xc3\xbc',  # ü
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xa4': b'\xc3\xa4',  # ä
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xb6': b'\xc3\xb6',  # ö

        # Capital umlauts - THIS IS THE ISSUE
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x9c': b'\xc3\x9c',  # Ü
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x84': b'\xc3\x84',  # Ä
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x96': b'\xc3\x96',  # Ö

        # ß
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x9f': b'\xc3\x9f',  # ß
    }

    print("\nReplacing all corrupted byte sequences:")
    total_replacements = 0
    total_bytes_saved = 0

    for wrong_bytes, correct_bytes in sorted(replacements.items(), key=lambda x: len(x[0]), reverse=True):
        count = content.count(wrong_bytes)
        if count > 0:
            try:
                correct_char = correct_bytes.decode('utf-8')
                print(f"  '{correct_char}': {count} occurrences")
                total_replacements += count
                bytes_saved = (len(wrong_bytes) - len(correct_bytes)) * count
                total_bytes_saved += bytes_saved
                content = content.replace(wrong_bytes, correct_bytes)
            except:
                print(f"  {wrong_bytes.hex()[:40]}... → {correct_bytes.hex()}: {count} times")
                total_replacements += count
                bytes_saved = (len(wrong_bytes) - len(correct_bytes)) * count
                total_bytes_saved += bytes_saved
                content = content.replace(wrong_bytes, correct_bytes)

    new_size = len(content)
    print(f"\nTotal replacements: {total_replacements}")
    print(f"Bytes saved: {total_bytes_saved}")
    print(f"New file size: {new_size} bytes")
    print(f"Size reduction: {original_size - new_size} bytes")

    print(f"\nWriting fixed file: {output_file}")
    with open(output_file, 'wb') as f:
        f.write(content)

    # Verify and test
    try:
        test_content = content.decode('utf-8')
        print("\n✓ Output file is valid UTF-8")

        # Test for specific words
        test_words = [
            'Überprüfungen',
            'Übernahme',
            'Überwachung',
            'überprüfen',
            'für',
            'Prüfung',
            'Prüfprotokoll',
            'gemäß',
        ]

        print("\nTesting for correctly encoded words:")
        found_count = 0
        for word in test_words:
            count = test_content.count(word)
            if count > 0:
                print(f"  ✓ '{word}': {count} occurrences")
                found_count += count

        print(f"\nTotal correct German words found: {found_count}")

        # Check for remaining encoding issues
        bad_patterns = ['Ã¼', 'Ã¶', 'Ã¤', 'Ãœ', 'Ã–', 'Ã„', 'ÃŸ', 'ÃÂ']
        remaining_issues = 0
        for pattern in bad_patterns:
            count = test_content.count(pattern)
            if count > 0:
                print(f"  ⚠ Still found '{pattern}': {count} times")
                remaining_issues += count

        if remaining_issues == 0:
            print("\n✓ No remaining encoding issues detected!")
        else:
            print(f"\n⚠ Warning: {remaining_issues} potential encoding issues remain")

    except Exception as e:
        print(f"⚠ Warning: {e}")

    print("\nDone!")
    return total_replacements

if __name__ == '__main__':
    input_file = '/home/user/BGG/datenbank.sql'
    output_file = '/home/user/BGG/datenbank_complete_fix.sql'

    try:
        count = fix_complete_encoding(input_file, output_file)
        print(f"\n{'='*80}")
        if count > 0:
            print(f"✓ Successfully fixed {count} encoding errors!")
            print(f"✓ Fixed file saved as: {output_file}")
        else:
            print("✓ File already has correct encoding!")
    except Exception as e:
        print(f"\n✗ Error: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        sys.exit(1)
