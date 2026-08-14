"""Wrap safe static Blade text and accessibility attributes in __()."""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "resources" / "views"
PROTECTED = re.compile(r"<(script|style)\b.*?</\1>|{{--.*?--}}|@php.*?@endphp|<\?php.*?\?>", re.I | re.S)
TEXT = re.compile(r">([^<>{}@]+)<")
ATTR = re.compile(r"\b(placeholder|title|aria-label|alt)=(['\"])(.*?)\2", re.I | re.S)
WORDS = re.compile(r"[A-Za-zÀ-ÿ]{2}")
SKIP_EXACT = {
    "StudentEdge", "Politeknik Besut", "EN", "BM", "BETA", "DOCX", "PDF",
    "CSV", "JSON", "JHEP", "TPSA", "TPSP", "TPA", "IC", "NRIC", "OKU",
    "B40", "table", "students", "scholarships", "offenses",
    "fine_payment_applications", "&times;",
}


def safe(value: str) -> bool:
    stripped = " ".join(value.split())
    if not stripped or stripped in SKIP_EXACT or not WORDS.search(stripped):
        return False
    if any(token in stripped for token in ("$", "->", "??", "::", "@", "{{", "}}")):
        return False
    if re.fullmatch(r"[A-Za-z0-9_.:/+@-]+", stripped) and ("." in stripped or "/" in stripped):
        return False
    # These are almost always fragments left around Blade control expressions.
    if stripped.endswith(")") or stripped.startswith(("if ", "foreach ", "for ")):
        return False
    return True


def php_quote(value: str) -> str:
    return value.replace("\\", "\\\\").replace("'", "\\'")


def transform_segment(segment: str) -> str:
    def text_replace(match: re.Match[str]) -> str:
        raw = match.group(1)
        stripped = raw.strip()
        if not safe(stripped):
            return match.group(0)
        leading = raw[: len(raw) - len(raw.lstrip())]
        trailing = raw[len(raw.rstrip()) :]
        return f">{leading}{{{{ __('{php_quote(stripped)}') }}}}{trailing}<"

    segment = TEXT.sub(text_replace, segment)

    def attr_replace(match: re.Match[str]) -> str:
        name, quote, raw = match.groups()
        if not safe(raw):
            return match.group(0)
        return f'{name}="{{{{ __(\'{php_quote(raw.strip())}\') }}}}"'

    return ATTR.sub(attr_replace, segment)


def main() -> None:
    changed = 0
    for path in ROOT.rglob("*.blade.php"):
        source = path.read_text(encoding="utf-8", errors="ignore")
        pieces: list[str] = []
        cursor = 0
        for match in PROTECTED.finditer(source):
            pieces.append(transform_segment(source[cursor : match.start()]))
            pieces.append(match.group(0))
            cursor = match.end()
        pieces.append(transform_segment(source[cursor:]))
        result = "".join(pieces)
        if result != source:
            path.write_text(result, encoding="utf-8")
            changed += 1
    print(f"Localized safe static literals in {changed} Blade templates.")


if __name__ == "__main__":
    main()
