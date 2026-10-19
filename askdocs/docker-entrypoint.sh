#!/bin/sh
# Ask-your-docs container entry point.
#   start (default)  index the documents in $ASKDOCS_DOCS_DIR, then serve the browser chat
#   anything else    passed to askdocs.py, e.g.  ask "What is the refund window?"
set -e
if [ "${1:-start}" = "start" ]; then
  docs="${ASKDOCS_DOCS_DIR:-/docs}"
  if [ -z "$(ls -A "$docs" 2>/dev/null)" ]; then
    echo "No documents found in $docs, using the fictional sample documents instead."
    echo "Put your files in the folder set by DOCS_DIR (see INSTALL.md) and restart."
    docs=/app/sample-docs
  fi
  python /app/askdocs.py index "$docs"
  # 0.0.0.0 inside the container only; docker-compose.yml publishes the port on 127.0.0.1.
  exec python /app/askdocs.py serve --host 0.0.0.0 --port "${ASKDOCS_PORT:-8765}"
fi
exec python /app/askdocs.py "$@"
