#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to fix double-encoded umlauts in SQL dump file.
Converts incorrectly encoded characters like ÃÂÃÂ¼ back to ü, etc.
"""

import sys
import codecs

def fix_umlauts(input_file, output_file):
    """
    Fix double-encoded umlauts in a file.

    Common double-encodings found:
    - ÃÂÃÂ¤ → ä
    - ÃÂÃÂ¶ → ö
    - ÃÂÃÂ¼ → ü
    - ÃÂÃÂ„ → Ä
    - ÃÂÃÂ– → Ö
    - ÃÂÃÂœ → Ü
    - ÃÂÃÂ → ß
    """

    # Mapping of wrong encodings to correct umlauts
    replacements = {
        'ÃÂÃÂ¤': 'ä',
        'ÃÂÃÂ¶': 'ö',
        'ÃÂÃÂ¼': 'ü',
        'ÃÂÃÂ„': 'Ä',
        'ÃÂÃÂ–': 'Ö',
        'ÃÂÃÂœ': 'Ü',
        'ÃÂÃÂ': 'ß',
        'Ã¤': 'ä',
        'Ã¶': 'ö',
        'Ã¼': 'ü',
        'Ã„': 'Ä',
        'Ã–': 'Ö',
        'Ãœ': 'Ü',
        'ÃŸ': 'ß',
    }

    print(f"Reading file: {input_file}")
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()

    original_length = len(content)

    # Count replacements
    replacement_count = {}
    for wrong, correct in replacements.items():
        count = content.count(wrong)
        if count > 0:
            replacement_count[wrong] = count
            content = content.replace(wrong, correct)

    print(f"\nReplacements made:")
    total_replacements = 0
    for wrong, count in sorted(replacement_count.items()):
        correct = replacements[wrong]
        print(f"  {wrong} → {correct}: {count} times")
        total_replacements += count

    print(f"\nTotal replacements: {total_replacements}")
    print(f"Original file size: {original_length} characters")
    print(f"New file size: {len(content)} characters")

    print(f"\nWriting fixed file: {output_file}")
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(content)

    print("Done!")
    return total_replacements

if __name__ == '__main__':
    input_file = '/home/user/BGG/datenbank.sql'
    output_file = '/home/user/BGG/datenbank_fixed.sql'

    try:
        count = fix_umlauts(input_file, output_file)
        if count > 0:
            print(f"\n✓ Successfully fixed {count} encoding errors!")
            print(f"Fixed file saved as: {output_file}")
        else:
            print("\n⚠ No encoding errors found!")
    except Exception as e:
        print(f"\n✗ Error: {e}", file=sys.stderr)
        sys.exit(1)
