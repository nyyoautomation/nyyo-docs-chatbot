# Ask-your-docs in Docker. Build and run with docker compose (see INSTALL.md, "Run with Docker").
# License: GPL-2.0-or-later. Contact: LaborX (https://laborx.com/freelancers/users/id475124)
FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1 \
    ASKDOCS_INDEX_DIR=/data/index \
    ASKDOCS_DOCS_DIR=/docs \
    ASKDOCS_PORT=8765

WORKDIR /app
COPY requirements.txt ./
RUN pip install -r requirements.txt

COPY askdocs.py docker-entrypoint.sh ./
COPY sample-docs/ ./sample-docs/
COPY tests/ ./tests/

# Run as an unprivileged user. /docs (your documents) is mounted read-only; /data keeps the index.
RUN useradd --create-home --uid 10001 askdocs \
 && mkdir -p /data/index /docs \
 && chown -R askdocs:askdocs /data \
 && chmod 755 /app/docker-entrypoint.sh
USER askdocs
VOLUME ["/data"]
EXPOSE 8765

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD python -c "import os,urllib.request; urllib.request.urlopen('http://127.0.0.1:%s/api/info' % os.environ.get('ASKDOCS_PORT','8765'), timeout=4)" || exit 1

ENTRYPOINT ["/app/docker-entrypoint.sh"]
# Default: index the mounted documents, then serve the browser chat.
CMD ["start"]
