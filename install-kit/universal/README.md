# Universal embed (Wix · Webflow · plain HTML)

**Free install kit included** with every docs-chatbot package.

## Install
1. Host `nyyo-chat.js` (or point to the copy in this repo / your CDN).
2. Paste before `</body>`:

```html
<script
  src="https://YOUR-HOST/nyyo-chat.js"
  data-bot-url="https://nyyoautomation.github.io/nyyo-docs-chatbot/"
  data-label="Ask docs"
  data-position="bottom-right"
  defer></script>
```

3. Replace `data-bot-url` with your production chatbot URL after handover.

### Wix
Settings → Custom code → Body end → paste the script.

### Webflow
Project settings → Custom code → Footer → paste the script. Publish.

Open `embed-example.html` locally to preview.
