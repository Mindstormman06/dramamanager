import fitz  # PyMuPDF
import sys
import json
import os

if len(sys.argv) != 3:
    print(json.dumps({"error": "Usage: analyze_script.py 'CHAR1, CHAR2' path/to/file.pdf"}))
    sys.exit(1)

# Read args
character_input = sys.argv[1]
pdf_path = sys.argv[2]

if not os.path.exists(pdf_path):
    print(json.dumps({"error": "PDF file not found"}))
    sys.exit(1)

# Prepare data
characters = [c.strip() for c in character_input.split(',') if c.strip()]
counts = {c: {"mentions": 0, "lines": 0} for c in characters}

# Normalize for matching
character_lower_map = {c: c.lower() for c in characters}
character_upper_map = {c: c.upper() for c in characters}

# Process PDF
doc = fitz.open(pdf_path)

for page in doc:
    text = page.get_text()

    for c in characters:
        c_lower = character_lower_map[c]
        c_upper = character_upper_map[c]

        # Count lowercase mentions (case-insensitive)
        counts[c]["mentions"] += text.lower().count(c_lower)

        # Count FULL CAPS occurrences (used for line detection)
        counts[c]["lines"] += text.count(c_upper)

print(json.dumps(counts))
