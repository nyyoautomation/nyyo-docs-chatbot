"""End-to-end test on the fictional sample docs.

    python tests/test_e2e.py                    # offline mode (no AI)
    python tests/test_e2e.py --provider ollama  # local AI
    OPENAI_API_KEY=... python tests/test_e2e.py --provider openai [--base-url ...]
"""
import argparse, json, subprocess, sys, tempfile
from pathlib import Path

HERE = Path(__file__).resolve().parent.parent
CASES = [  # question, facts that must be in the answer, file that must be cited
    ("How long is the warranty on a tent?", ["3"], "warranty-handbook.pdf"),
    ("Who pays return shipping for an approved warranty claim?", ["Lumen Trail"], "warranty-handbook.pdf"),
    ("How much are meals reimbursed on business travel?", ["40"], "employee-onboarding.docx"),
    ("How many paid vacation days per year?", ["25"], "employee-onboarding.docx"),
    ("How many days do I have to return unused items?", ["45"], "returns-policy.md"),
    ("What does express shipping cost?", ["15"], "shipping-notes.txt"),
    ("When is customer support available?", ["Monday", "Saturday"], "support-page.html"),
    ("What is the capital of Peru?", ["could not find"], None),
]

def run(args):
    r = subprocess.run([sys.executable, str(HERE / "askdocs.py")] + args, capture_output=True, text=True)
    if r.returncode:
        raise SystemExit(r.stderr or r.stdout)
    return r.stdout

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--provider", default="none")
    ap.add_argument("--model"); ap.add_argument("--base-url")
    a = ap.parse_args()
    idx = tempfile.mkdtemp(prefix="askdocs-test-")
    print(run(["--index-dir", idx, "index", str(HERE / "sample-docs")]).strip())
    extra = ["--provider", a.provider] + (["--model", a.model] if a.model else []) + (["--base-url", a.base_url] if a.base_url else [])
    passed = 0
    for q, facts, file in CASES:
        r = json.loads(run(["--index-dir", idx, "ask", q, "--json"] + extra))
        cited_files = {s["file"] for s in r["sources"] if s["n"] in r["cited"]}
        ok = all(f.lower() in r["answer"].lower() for f in facts)
        ok = ok and (file is None and not r["sources"] or file in cited_files)
        ok = ok and not r["meta"].get("unsupported_sentences")
        passed += ok
        print(f"{'PASS' if ok else 'FAIL'}  {q}\n      -> {r['answer']}\n      cited: {sorted(cited_files) or '-'}  {r['meta'].get('model', '')}")
    print(f"\n{passed}/{len(CASES)} passed (provider: {a.provider})")
    sys.exit(0 if passed == len(CASES) else 1)

if __name__ == "__main__":
    main()
