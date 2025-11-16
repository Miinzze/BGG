#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Fix multi-level encoding corruption in SQL dump.
The file has UTF-8 bytes that were re-encoded multiple times.
"""

import sys

def fix_encoding(input_file, output_file):
    """Fix the multi-level encoding corruption."""

    print(f"Reading file: {input_file}")
    with open(input_file, 'rb') as f:
        content = f.read()

    original_size = len(content)
    print(f"Original file size: {original_size} bytes")

    # Byte-level replacements for corrupted umlauts
    # These are the actual byte sequences found in the file
    replacements = {
        # ü: UTF-8 would be \xc3\xbc
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xbc': b'\xc3\xbc',

        # ä: UTF-8 would be \xc3\xa4
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xa4': b'\xc3\xa4',

        # ö: UTF-8 would be \xc3\xb6
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\xb6': b'\xc3\xb6',

        # Ü: UTF-8 would be \xc3\x9c
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc5\x93': b'\xc3\x9c',

        # Ä: UTF-8 would be \xc3\x84
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x84': b'\xc3\x84',

        # Ö: UTF-8 would be \xc3\x96
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x96': b'\xc3\x96',

        # ß: UTF-8 would be \xc3\x9f
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x83\xc3\x83\xc2\x82\xc3\x82\xc2\x9f': b'\xc3\x9f',
    }

    print("\nReplacing corrupted byte sequences:")
    total_replacements = 0

    for wrong_bytes, correct_bytes in replacements.items():
        count = content.count(wrong_bytes)
        if count > 0:
            try:
                # Decode to show what it represents
                correct_char = correct_bytes.decode('utf-8')
                print(f"  {wrong_bytes.hex()} → '{correct_char}': {count} times")
                total_replacements += count
                content = content.replace(wrong_bytes, correct_bytes)
            except:
                print(f"  {wrong_bytes.hex()} → {correct_bytes.hex()}: {count} times")
                total_replacements += count
                content = content.replace(wrong_bytes, correct_bytes)

    new_size = len(content)
    print(f"\nTotal replacements: {total_replacements}")
    print(f"New file size: {new_size} bytes (reduced by {original_size - new_size} bytes)")

    print(f"\nWriting fixed file: {output_file}")
    with open(output_file, 'wb') as f:
        f.write(content)

    # Verify the output is valid UTF-8
    try:
        test_content = content.decode('utf-8')
        print("✓ Output file is valid UTF-8")
    except:
        print("⚠ Warning: Output file may have encoding issues")

    print("\nDone!")
    return total_replacements

if __name__ == '__main__':
    input_file = '/home/user/BGG/datenbank.sql'
    output_file = '/home/user/BGG/datenbank_fixed.sql'

    try:
        count = fix_encoding(input_file, output_file)
        if count > 0:
            print(f"\n✓ Successfully fixed {count} encoding errors!")
            print(f"Fixed file saved as: {output_file}")
        else:
            print("\n⚠ No encoding errors found in expected patterns!")
    except Exception as e:
        print(f"\n✗ Error: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        sys.exit(1)
