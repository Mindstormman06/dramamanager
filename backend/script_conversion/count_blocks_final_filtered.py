
import re
import sys
from collections import defaultdict

def count_character_blocks(script_path):
    try:
        with open(script_path, "r", encoding="utf-8") as f:
            lines = f.readlines()
    except Exception as e:
        print(f"Error reading script: {e}")
        return

    character_pattern = re.compile(r'^([A-Z][A-Z\s\-]+)(?:\s*\(.*?\))?$')
    exclude_keywords = {"ACT", "COLD OPEN", "TALKING HEAD", "END OF", "FADE IN", "ANGLE ON", "SCENE", "DIALOGUE", "TRANSITION", "OFF-SCREEN", "INT.", "EXT.", "FACADE", "ANGLE", "CLOSE", "STAGE", "HURRY", "END", "LOGO"}
    counts = defaultdict(int)

    for i in range(len(lines) - 1):
        current_line = lines[i].strip()
        next_line_raw = lines[i + 1]
        next_line = next_line_raw.strip()

        if any(kw in current_line.upper() for kw in exclude_keywords):
            continue

        match = character_pattern.match(current_line)
        if match and next_line_raw[:1].isspace() and not any(kw in next_line.upper() for kw in exclude_keywords):
            name = match.group(1).strip()
            counts[name] += 1

    output_path = script_path.replace(".txt", "_character_blocks_final_filtered.txt")
    with open(output_path, "w", encoding="utf-8") as f:
        for character, count in sorted(counts.items(), key=lambda x: -x[1]):
            f.write(f"{character}: {count} blocks\n")

    print(f"Final filtered character block counts saved to: {output_path}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python count_blocks_final_filtered.py <formatted_script.txt>")
    else:
        count_character_blocks(sys.argv[1])
