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

With Docker instead (browser chat on http://127.0.0.1:8765, your docs folder mounted read-only,
optional Ollama as a second service):

```bash
DOCS_DIR=/path/to/your/docs docker compose up -d --build
```
See [INSTALL.md, Run with Docker](INSTALL.md#run-with-docker).

Files:
- `askdocs.py` - the tool (single file, Python 3.9+)
- `requirements.txt` - PDF, DOCX and HTML readers
- `Dockerfile`, `docker-compose.yml`, `docker-entrypoint.sh` - run it in Docker
- `sample-docs/` - documents of a fictional company to try it
- `tests/test_e2e.py` - end-to-end self-test; `tests/gen_samples.py` rebuilds the sample PDF/DOCX

Limits: no OCR for scanned PDFs; keyword retrieval (no embeddings), so very different
wording between question and document can miss; answers need human review.

## License tiers

| License | Price | Sites |
|---|---|---|
| Single site | **$29** | 1 site |
| 5 sites | **$59** | up to 5 sites |
| Unlimited | **$129** | unlimited sites |

Every license includes a **14-day refund**, **6 months of support** and **12 months of updates**.
The code itself stays GPL-2.0-or-later; the license covers the packaged kit, support and updates.
To get a license, email LaborX (https://laborx.com/freelancers/users/id475124) (crypto: USDC on BNB Chain or Base).

License: GPL-2.0-or-later (see ../LICENSE). Contact: LaborX (https://laborx.com/freelancers/users/id475124) · Remote · Worldwide
