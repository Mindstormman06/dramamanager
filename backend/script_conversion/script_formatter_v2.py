import fitz  # PyMuPDF
import re
import sys

def parse_script(pdf_path):
    try:
        doc = fitz.open(pdf_path)
    except Exception as e:
        print(f"Error opening PDF: {e}")
        return

    full_text = ""
    for page in doc:
        full_text += page.get_text()

    lines = full_text.splitlines()
    parsed_script = []
    dialogue_accumulator = []

    scene_header_pattern = re.compile(r'^(INT\.|EXT\.).+', re.IGNORECASE)
    character_pattern = re.compile(
        r'^([A-Z][A-Z\s\-]+)(?:\s*\(.*?\))?$')

    for line in lines:
        line = line.strip()
        if not line:
            continue

        if scene_header_pattern.match(line):
            if dialogue_accumulator:
                parsed_script.append("  " + "\n  ".join(dialogue_accumulator))
                dialogue_accumulator = []
            parsed_script.append(f"\n{line.upper()}\n")
        elif character_pattern.match(line):
            if dialogue_accumulator:
                parsed_script.append("  " + "\n  ".join(dialogue_accumulator))
                dialogue_accumulator = []
            parsed_script.append(f"{line}")
        elif line.startswith('(') and line.endswith(')'):
            parsed_script.append(f"  {line}")
        else:
            dialogue_accumulator.append(line)

    if dialogue_accumulator:
        parsed_script.append("  " + "\n  ".join(dialogue_accumulator))

    output_text = "\n".join(parsed_script)

    output_path = pdf_path.replace(".pdf", "_formatted.txt")
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(output_text)

    print(f"Formatted script saved to: {output_path}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python script_formatter.py <path_to_pdf>")
    else:
        parse_script(sys.argv[1])
