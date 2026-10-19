# Ask-your-docs: install guide (Mac, Windows, Linux)

Load your own documents (PDF, DOCX, Markdown, TXT, HTML), index them, and chat with them.
Every answer shows the file and page or section it came from.

Time needed: about 10 minutes the first time. No account needed with us.

> Human review: answers are drawn from your documents, but a person should check
> anything important before acting on it, especially with AI modes.

---

## Choose your mode

| Mode | What it needs | Where your data goes |
|---|---|---|
| `none` (default) | Nothing extra | Stays on your computer. No AI: returns the best matching passages. |
| `ollama` | Ollama + a model (about 1 GB download) | Stays on your computer. 100 % local AI. |
| `openai` | Your own API key from an OpenAI-compatible provider | Your question and the matching excerpts (not whole files) are sent to the provider you choose. Their terms and pricing apply. |

You can switch mode at any time with `--provider`.

---

## Step 1. Install Python 3.9 or newer

**Mac**
1. Open **Terminal** (Cmd + Space, type `Terminal`).
2. Type `python3 --version`. If you see 3.9 or newer, go to step 2.
3. Otherwise download the macOS installer from https://www.python.org/downloads/ and run it.

**Windows**
1. Download the Windows installer from https://www.python.org/downloads/
2. Run it and tick **"Add python.exe to PATH"** on the first screen, then click **Install Now**.
3. Open **PowerShell** (Start menu, type `PowerShell`) and type `python --version`.

**Linux** (Debian / Ubuntu; use your package manager on other distributions)
```bash
sudo apt update && sudo apt install -y python3 python3-venv python3-pip
python3 --version
```

In the commands below, Windows users type `python` where Mac/Linux users type `python3`.

## Step 2. Unzip the kit and open its folder

Unzip the download, then in your terminal go into the `askdocs` folder:

```bash
cd path/to/nyyo-docs-chatbot/askdocs
```
Tip: type `cd ` (with a space) and drag the folder into the terminal window.

## Step 3. Create a private Python environment and install the readers

**Mac / Linux**
```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

**Windows (PowerShell)**
```powershell
python -m venv .venv
.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```
If PowerShell says scripts are disabled, run once:
`Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`, answer `Y`, then activate again.

Next time, you only need the `activate` line before using the tool.

## Step 4. Try it on the sample documents

The `sample-docs` folder holds documents of a fictional company.

```bash
python askdocs.py index sample-docs
python askdocs.py ask "How long is the warranty on a tent?"
```

You should see an answer followed by `Sources:` with `warranty-handbook.pdf - page 1`.

## Step 5. Index YOUR documents

Put your files in one folder (subfolders are fine), then:

```bash
python askdocs.py index "/path/to/your/docs"
```
Windows example: `python askdocs.py index "C:\Users\you\Documents\Policies"`

- Supported: `.pdf`, `.docx`, `.md`, `.txt`, `.html` / `.htm`.
- Scanned PDFs without a text layer are skipped and listed (OCR is not included).
- Re-run the same command whenever your documents change. The index is stored in
  `.askdocs/index.json` next to the tool (or where `--index-dir` points).

## Step 6. Ask and chat

```bash
python askdocs.py ask "What is our refund window?"
python askdocs.py chat                 # interactive chat in the terminal, type exit to quit
python askdocs.py serve                # chat in your browser at http://127.0.0.1:8765
```

The browser chat only listens on your own computer (127.0.0.1).

## Step 7 (optional). 100 % local AI with Ollama

1. Install Ollama from https://ollama.com/download (Mac, Windows and Linux installers).
   Linux: `curl -fsSL https://ollama.com/install.sh | sh`
2. Download a model once (about 1 GB):
   ```bash
   ollama pull qwen2.5:1.5b
   ```
   Larger models such as `qwen2.5:7b` or `llama3.1:8b` give better wording but need more memory.
3. Ask with the local AI:
   ```bash
   python askdocs.py ask "Who pays return shipping?" --provider ollama
   python askdocs.py chat --provider ollama --model qwen2.5:7b
   ```

## Step 8 (optional). Online AI with your own API key

Any provider with an OpenAI-compatible chat API works. Set the key in the terminal you use:

**Mac / Linux**
```bash
export OPENAI_API_KEY="your-key"
# only if your provider is not OpenAI:
export OPENAI_BASE_URL="https://your-provider.example/v1"
python askdocs.py ask "What is our refund window?" --provider openai --model gpt-4o-mini
```

**Windows (PowerShell)**
```powershell
$env:OPENAI_API_KEY = "your-key"
$env:OPENAI_BASE_URL = "https://your-provider.example/v1"   # only if not OpenAI
python askdocs.py ask "What is our refund window?" --provider openai --model gpt-4o-mini
```

The key is read from the environment only. It is never written to disk by the tool.
Costs are billed by your provider according to their pricing.

To make a mode the default, set `ASKDOCS_PROVIDER` to `ollama` or `openai` (and
`ASKDOCS_MODEL` for the model) the same way.

## Step 9 (optional). Use your docs in the website widget

```bash
python askdocs.py export-web ../data/docs.json
```
This writes your passages in the format the static browser chatbot (`../index.html`)
reads. Everything in that file becomes public once you publish the site, so only
export documents that are meant to be public.

---

## How citations are checked

- The tool first finds the passages that best match the question (keyword search, on your computer).
- If nothing matches, it says "I could not find this in your documents." and no AI is called.
- In AI modes, each sentence of the answer is checked against the passages: most of its
  words and all of its numbers must appear in the cited passage. Wrong citation numbers are
  corrected or removed, and sentences that are not supported are flagged with `!` for you to check.

## Troubleshooting

| Message | Fix |
|---|---|
| `python3: command not found` / `python is not recognized` | Re-do step 1 (Windows: tick "Add python.exe to PATH"). |
| `No index at .askdocs/index.json` | Run `python askdocs.py index <folder>` first, from the same folder. |
| `PDF support needs pypdf` | Activate the environment (step 3) and run `pip install -r requirements.txt`. |
| `Cannot reach http://127.0.0.1:11434` | Start Ollama (open the app, or run `ollama serve`). |
| `HTTP 401` | Your API key is wrong or missing for that provider. |
| `skipped file.pdf: no text found` | The PDF is a scan. Export it with a text layer first. |

## Run the self-test

```bash
python tests/test_e2e.py                     # offline mode
python tests/test_e2e.py --provider ollama   # local AI
```

Questions: LaborX (https://laborx.com/freelancers/users/id475124) · Remote · Worldwide
