#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Final comprehensive fix - only replace complete known bad sequences
"""

import sys

def fix_all_encoding_issues(input_file, output_file):
    """Fix all remaining encoding issues with precise replacements"""

    print(f"Reading file: {input_file}")
    with open(input_file, 'rb') as f:
        content = f.read()

    original_size = len(content)
    print(f"Original file size: {original_size} bytes\n")

    # Precise replacements - only complete multi-byte sequences
    # Based on analysis: the pattern is c383c283c382c2XX where XX varies
    replacements = {
        # Degree symbol ° (part of °C)
        # Pattern: ÃÂÃÂÃÂÃÂ° → °
        # In bytes: c383 c283 c382 c282 c383 c282 c382 c2b0 → c2b0
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x82\xc3\x83\xc2\x82\xc3\x82\xc2\xb0': b'\xc2\xb0',

        # En-dash –
        # The bytes show: c383c283c382c2a2c383c282c382c280c383c282c382c293
        # This looks like a complex corruption of – (e2 80 93)
        # Let me map the exact sequence
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\xa2\xc3\x83\xc2\x82\xc3\x82\xc2\x80\xc3\x83\xc2\x82\xc3\x82\xc2\x93': b'\xe2\x80\x93',

        # Copyright ©
        # Pattern similar to degree: c383c283c382c28ec383c282c382c2a9
        # This should be c2a9
        b'\xc3\x83\xc2\x83\xc3\x82\xc2\x8e\xc3\x83\xc2\x82\xc3\x82\xc2\xa9': b'\xc2\xa9',
    }

    print("Replacing corrupted special character sequences:")
    total_replacements = 0
    total_bytes_saved = 0

    for wrong_bytes, correct_bytes in sorted(replacements.items(), key=lambda x: len(x[0]), reverse=True):
        count = content.count(wrong_bytes)
        if count > 0:
            try:
                correct_char = correct_bytes.decode('utf-8')
                symbol_name = {
                    '\xb0': 'degree (°)',
                    '–': 'en-dash (–)',
                    '©': 'copyright (©)',
                }.get(correct_char, correct_char)

                print(f"  {symbol_name}: {count} occurrences")
                total_replacements += count
                bytes_saved = (len(wrong_bytes) - len(correct_bytes)) * count
                total_bytes_saved += bytes_saved
                content = content.replace(wrong_bytes, correct_bytes)
            except Exception as e:
                print(f"  {correct_bytes.hex()}: {count} occurrences")
                total_replacements += count
                content = content.replace(wrong_bytes, correct_bytes)

    new_size = len(content)
    print(f"\nTotal replacements: {total_replacements}")
    print(f"Bytes saved: {total_bytes_saved}")
    print(f"New file size: {new_size} bytes")
    print(f"Size reduction: {original_size - new_size} bytes")

    print(f"\nWriting fixed file: {output_file}")
    with open(output_file, 'wb') as f:
        f.write(content)

    # Comprehensive verification
    try:
        test_content = content.decode('utf-8')
        print("\n" + "="*80)
        print("✓ Output file is valid UTF-8!")

        # Count all German characters and special symbols
        print("\nAll German characters and special symbols found:")

        chars_to_check = {
            'ä': 'lowercase a-umlaut',
            'ö': 'lowercase o-umlaut',
            'ü': 'lowercase u-umlaut',
            'Ä': 'uppercase A-umlaut',
            'Ö': 'uppercase O-umlaut',
            'Ü': 'uppercase U-umlaut',
            'ß': 'sharp s (eszett)',
            '°': 'degree symbol',
            '–': 'en-dash',
            '©': 'copyright',
        }

        total_good = 0
        for char, description in chars_to_check.items():
            count = test_content.count(char)
            if count > 0:
                print(f"  ✓ '{char}' ({description}): {count}")
                total_good += count

        print(f"\nTotal correct special characters: {total_good}")

        # Check for any remaining bad patterns
        bad_patterns = ['ÃÂ', 'Ã¼', 'Ã¶', 'Ã¤', 'Ãœ', 'Ã„', 'Ã–']
        print("\nChecking for remaining encoding issues:")
        remaining_count = 0
        for pattern in bad_patterns:
            count = test_content.count(pattern)
            if count > 0:
                print(f"  ⚠ Found '{pattern}': {count} times")
                remaining_count += count

        if remaining_count == 0:
            print("  ✓ NO remaining encoding issues detected!")
            print("\n" + "="*80)
            print("✓✓✓ ALL ENCODING ISSUES FIXED! ✓✓✓")
            print("="*80)
        else:
            print(f"\n⚠ Warning: {remaining_count} issues may remain")

        return total_replacements, remaining_count

    except Exception as e:
        print(f"\n✗ Error validating output: {e}")
        import traceback
        traceback.print_exc()
        return total_replacements, -1

if __name__ == '__main__':
    input_file = '/home/user/BGG/datenbank.sql'
    output_file = '/home/user/BGG/datenbank_completely_fixed.sql'

    try:
        fixed, remaining = fix_all_encoding_issues(input_file, output_file)

        print(f"\n{'='*80}")
        print(f"SUMMARY:")
        print(f"  Fixed: {fixed} special characters")
        print(f"  Remaining issues: {remaining if remaining >= 0 else 'unknown'}")
        print(f"  Output: {output_file}")
        print(f"{'='*80}")

    except Exception as e:
        print(f"\n✗ Fatal error: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc()
        sys.exit(1)
