# AI chatbot on *your* documents (live demo)

> **For non-technical clients — start here**
>
> **What it does:** A chatbot that answers questions using only the documents you provide (FAQ, policies, product docs) — not random internet answers.
>
> **Try it in 1 click:** open the [**live demo**](https://nyyoautomation.github.io/nyyo-docs-chatbot/), click a suggested question (or type your own), and read the answer with its source section.
>
> **Business benefit:** fewer repetitive support emails (“shipping time?”, “refund window?”), faster staff onboarding. Typical build from **$149**. Delivery: 4 / 6 / 10 days by tier.

![Demo](docs/demo.gif)

**Live demo:** https://nyyoautomation.github.io/nyyo-docs-chatbot/  
**Brand:** [Nyyo Automation](https://github.com/nyyoautomation)

[![Python](https://img.shields.io/badge/Python-3776AB?style=flat&logo=python&logoColor=white)](#)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](#)
[![GitHub Pages](https://img.shields.io/badge/GitHub%20Pages-222222?style=flat&logo=github&logoColor=white)](#)
[![No API key](https://img.shields.io/badge/No_API_key-free_demo-3dd6c6)](#)

---

## What you get in this demo

- Sample **store FAQ** (shipping, returns, warranty, pricing, privacy, SLA)
- In-browser search (BM25) — **no signup, no paid API, no server**
- Answers that **cite the source** section

## How to run locally

```bash
# any static server
python3 -m http.server 8080
# open http://localhost:8080
```

Or just use the live Pages link above.

## What’s for sale

| | |
|---|---|
| **Product** | RAG chatbot on your PDF / Notion / FAQ — web widget or Telegram |
| **From** | **$149** (Starter) · $299 · $649 — delivery 4 / 6 / 10 days |
| **Includes** | Your docs ingested · source citations · 7-day bugfix · you own the code |

### Ask-your-docs self-service license

The self-hosted tool ([`askdocs/`](askdocs/), with Docker support) and the install kit, for your own documents and sites.

| License | Price | Sites |
|---|---|---|
| Single site | **$29** | 1 site |
| 5 sites | **$59** | up to 5 sites |
| Unlimited | **$129** | unlimited sites |

Every license includes a **14-day refund**, **6 months of support** and **12 months of updates**.
The code itself stays GPL-2.0-or-later; the license covers the packaged kit, support and updates.
[Order by email](mailto:LaborX (https://laborx.com/freelancers/users/id475124)?subject=Ask-your-docs%20order) (crypto: USDC on BNB Chain or Base).

The custom build above is a separate service.

→ [Packages & contact](https://github.com/nyyoautomation) · [LaborX — Nyyo Automation](https://laborx.com/freelancers/users/id475124)

## Hire me

1. Open an **issue** on this repo with: goal · deadline · budget  
2. Or message via **[LaborX — Nyyo Automation](https://laborx.com/freelancers/users/id475124)**

I reply with scope, price and ETA. Crypto: USDC on BNB Chain or Base.

## Limits (honest)

- This public demo uses **keyword search** over a fixed sample FAQ (fast & free). The `askdocs` tool adds your own documents and optional AI answers (local Ollama or your own API key).
- No OCR: scanned PDFs without a text layer are skipped.
- Not a replacement for legal advice; sample store content is fictional.


## Use it on your own documents (self-hosted)

The [`askdocs/`](askdocs/) folder is a small tool that loads **your** PDF, DOCX, Markdown, TXT and HTML files,
indexes them on your computer and answers with the file and page or section of each source.
It works offline (no AI), with a 100 % local AI through Ollama, or with an online AI using your own API key.
Step-by-step install for Mac, Windows and Linux: [askdocs/INSTALL.md](askdocs/INSTALL.md).

```bash
cd askdocs
pip install -r requirements.txt
python askdocs.py index /path/to/your/docs
python askdocs.py chat
```

Or with Docker (no Python install needed). Browser chat on http://127.0.0.1:8765:

```bash
cd askdocs
DOCS_DIR=/path/to/your/docs docker compose up -d --build
# optional 100 % local AI with Ollama as a second service:
DOCS_DIR=/path/to/your/docs ASKDOCS_PROVIDER=ollama docker compose --profile ollama up -d --build
docker compose exec ollama ollama pull qwen2.5:1.5b
```
Details: [askdocs/INSTALL.md](askdocs/INSTALL.md#run-with-docker).

## WordPress plugin

[`install-kit/wordpress/nyyo-docs-chatbot/`](install-kit/wordpress/nyyo-docs-chatbot/) answers visitors from the
site's own published pages and posts, on the site's server, and links the source page of every answer.
No external service by default; an external chatbot page can be embedded as an opt-in.
GPLv2 or later. ZIP: `dist/nyyo-docs-chatbot-wordpress.zip`.

## Downloads

Downloads on the [live demo](https://nyyoautomation.github.io/nyyo-docs-chatbot/#install):

- WordPress plugin ZIP, free (GPLv2 or later): `dist/nyyo-docs-chatbot-wordpress.zip`
- Full Ask-your-docs kit (tool for your own documents with Docker, WordPress plugin, Shopify snippet, universal script, Telegram/Slack/Discord templates): paid license $29 / $59 / $129, see above. [Order by email](mailto:LaborX (https://laborx.com/freelancers/users/id475124)?subject=Ask-your-docs%20order)

The free 1-click install kit stays included with every custom build. Anything not listed is **on request (free)** with a build.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE). Contact: LaborX (https://laborx.com/freelancers/users/id475124) · Remote · Worldwide
