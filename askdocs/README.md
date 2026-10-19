# Ask-your-docs: chat with your own documents, with citations

A small command-line tool (plus a local browser chat) that reads your PDF, DOCX,
Markdown, TXT and HTML files, indexes them on your computer, and answers questions
with the file and page or section of every source.

- **Offline mode** (default): no AI, no key, nothing leaves your computer.
- **Local AI**: Ollama on your own machine. Nothing leaves your computer.
- **Online AI**: any OpenAI-compatible API with **your own key**. Only the question
  and the matching excerpts are sent to that provider.

Quick start:

```bash
pip install -r requirements.txt
python askdocs.py index sample-docs
python askdocs.py ask "How long is the warranty on a tent?"
python askdocs.py serve        # browser chat on http://127.0.0.1:8765
```

Step-by-step install for Mac, Windows and Linux: **[INSTALL.md](INSTALL.md)**.

Files:
- `askdocs.py` - the tool (single file, Python 3.9+)
- `requirements.txt` - PDF, DOCX and HTML readers
- `sample-docs/` - documents of a fictional company to try it
- `tests/test_e2e.py` - end-to-end self-test; `tests/gen_samples.py` rebuilds the sample PDF/DOCX

Limits: no OCR for scanned PDFs; keyword retrieval (no embeddings), so very different
wording between question and document can miss; answers need human review.

License: GPL-2.0-or-later (see ../LICENSE). Contact: LaborX (https://laborx.com/freelancers/users/id475124) · Remote · Worldwide
