# Shopify install (free kit)

**Free install kit included** — no add-on fee.

## Steps (theme snippet)
1. In Shopify Admin → Online Store → Themes → … → Edit code.
2. Upload `assets-nyyo-chat.js` as theme asset `nyyo-chat.js` (Assets → Add a new asset).
3. Create snippet `nyyo-chat-embed.liquid` (copy from `snippets/nyyo-chat-embed.liquid`).
4. Open `layout/theme.liquid`, before `</body>` add:
   ```liquid
   {% render 'nyyo-chat-embed' %}
   ```
5. Save and preview the storefront — floating “Ask docs” button opens the chatbot.

Optional: add theme settings for `nyyo_bot_url` / `nyyo_chat_label` in `config/settings_schema.json` (on request, free — we add them at handover).

## Theme app embed
A full Theme App Extension (Partner app) is **on request (free)** when you want install via Customize → App embeds without editing theme code.
