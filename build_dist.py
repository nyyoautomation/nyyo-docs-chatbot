#!/usr/bin/env python3
"""Rebuild the downloadable ZIPs in dist/ (and the copy in install-kit/dist/). Python 3 only, no zip binary needed."""
import os, shutil, zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent
SKIP_PARTS = {"__pycache__", ".venv", ".askdocs", ".git"}

def files(path: Path):
    if path.is_file():
        yield path
        return
    for p in sorted(path.rglob("*")):
        rel = p.relative_to(ROOT).parts
        if p.is_file() and not any(x in SKIP_PARTS or x.startswith(".") for x in rel) and p.suffix != ".pyc":
            yield p

def build(out: Path, items, base: Path):
    out.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
        for item in items:
            for f in files(ROOT / item):
                info = zipfile.ZipInfo(f.relative_to(base).as_posix(), date_time=(2026, 10, 19, 0, 0, 0))
                info.external_attr = (0o755 if os.access(f, os.X_OK) else 0o644) << 16
                info.compress_type = zipfile.ZIP_DEFLATED
                z.writestr(info, f.read_bytes())
    print(f"{out.relative_to(ROOT)}  {out.stat().st_size} bytes")

wp = ROOT / "dist" / "nyyo-docs-chatbot-wordpress.zip"
build(wp, ["install-kit/wordpress/nyyo-docs-chatbot"], ROOT / "install-kit" / "wordpress")
build(ROOT / "dist" / "nyyo-docs-chatbot-install-kit.zip",
      ["LICENSE", "askdocs", "install-kit/README.md", "install-kit/wordpress", "install-kit/shopify",
       "install-kit/universal", "install-kit/bots"], ROOT)
shutil.copyfile(wp, ROOT / "install-kit" / "dist" / "nyyo-docs-chatbot-wordpress.zip")
