import fitz  # PyMuPDF
import sys
import json
import os
import re

# --------------------
# Character auto-detect function
# --------------------
def extract_caps_names_from_layout(doc):
    candidates = {}

    for page in doc:
        page_width = page.rect.width
        blocks = page.get_text("blocks")

        for block in blocks:
            if len(block) < 5:
                continue

            x0, y0, x1, y1 = block[0:4]
            text = str(block[4]).strip()

            if not text or len(text) < 3:
                continue

            if not text.isupper():
                continue

            if re.match(r'^(INT\.|EXT\.|CUT TO|FADE|BLACKOUT|SCENE|ANGLE ON)', text):
                continue

            # Match character names like "JAMES", "MRS. DOLLINGER", "JAMES (CONT'D)", "MAYA (O.S.)"
            if not re.fullmatch(r"[A-Z .'\-]+(?: \([A-Z .'\-]+\))?", text):
                continue


            if len(text.split()) > 4:
                continue

            # Check if roughly centered
            text_center = (x0 + x1) / 2
            page_center = page_width / 2
            distance_from_center = abs(page_center - text_center)

            if distance_from_center > page_width * 0.2:
                continue
            
            base_name = re.sub(r" \(.*?\)", "", text).strip()
            candidates[base_name] = candidates.get(base_name, 0) + 1
            
    # PASS 2: Match any line with (CONT'D) or similar
    for page in doc:
        raw_lines = page.get_text().splitlines()

        for line in raw_lines:
            clean = line.strip()

            if "(CONT" not in clean.upper():
                continue  # Not a continuation line

            # Normalize: remove everything in parentheses
            base_name = re.sub(r"\s*\(.*?\)", "", clean).strip().upper()

            if len(base_name) < 2 or len(base_name.split()) > 4:
                continue

            # Count it
            candidates[base_name] = candidates.get(base_name, 0) + 1


            
    # Re-scan mentions over full script
    full_text = "\n".join([page.get_text() for page in doc])
    result = {}
    for name, line_count in candidates.items():
        mentions = len(re.findall(rf'\b{re.escape(name)}\b', full_text, flags=re.IGNORECASE))
        result[name] = {"mentions": mentions, "lines": line_count}
        
    filtered_result = {}

    for name, data in result.items():
        # Skip act headers and transitions
        if re.match(r"^(ACT|END OF|COLD OPEN|SCENE|ANGLE ON|FADE IN|TALKING HEAD)", name):
            continue
        
        if "TALKING HEAD" in name.upper():
            continue

        # Skip anything with numbers unless it's like "MOM" or "DAD"
        if re.search(r'\d', name) and not re.match(r"(STUDENT|KID|PERSON|GUARD) \d+", name):
            continue

        # Skip anything over 3 words — often junk
        if len(name.split()) > 3:
            continue

        filtered_result[name] = data

    return filtered_result


    return result





# --------------------
# Entry point
# --------------------
if len(sys.argv) != 3:
    print(json.dumps({"error": "Usage: analyze_script.py 'CHAR1, CHAR2' path/to/file.pdf"}))
    sys.exit(1)

character_input = sys.argv[1]
pdf_path = sys.argv[2]

if not os.path.exists(pdf_path):
    print(json.dumps({"error": "PDF file not found"}))
    sys.exit(1)

# Read entire script once
doc = fitz.open(pdf_path)
full_text = ""
for page in doc:
    full_text += page.get_text()

# --------------------
# AUTO-DETECT MODE
# --------------------
if character_input == "AUTO_DETECT":
    result = extract_caps_names_from_layout(doc)
    print(json.dumps(result))
    sys.exit(0)


# --------------------
# MANUAL MODE
# --------------------
characters = [c.strip() for c in character_input.split(',') if c.strip()]
counts = {c: {"mentions": 0, "lines": 0} for c in characters}
character_lower_map = {c: c.lower() for c in characters}
character_upper_map = {c: c.upper() for c in characters}

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
