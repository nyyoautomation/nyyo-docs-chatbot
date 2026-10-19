"""
Nyyo Docs Chatbot — Telegram bridge template (free install kit).
Uses python-telegram-bot v21+. Answers with a link to your docs chatbot
or proxies simple FAQ text you paste in FAQ below.

Setup:
  1. Talk to @BotFather → /newbot → copy token
  2. export TELEGRAM_BOT_TOKEN=...
  3. pip install python-telegram-bot==21.6
  4. python bot_template.py
"""
from __future__ import annotations

import os
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import Application, CommandHandler, MessageHandler, filters, ContextTypes

BOT_URL = os.environ.get("NYYO_BOT_URL", "https://nyyoautomation.github.io/nyyo-docs-chatbot/")
FAQ = {
    "shipping": "Standard shipping takes 5–8 business days. Full policy in the web assistant.",
    "returns": "Unused items can be returned within 30 days. Details in the web assistant.",
    "pricing": "See Growth / Starter plans in the web assistant.",
}

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    kb = InlineKeyboardMarkup(
        [[InlineKeyboardButton("Open docs assistant", url=BOT_URL)]]
    )
    await update.message.reply_text(
        "Hi — I can open the Nyyo docs assistant or answer quick FAQ keywords (shipping, returns, pricing).",
        reply_markup=kb,
    )

async def help_cmd(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    await update.message.reply_text("/start · /help · or type: shipping | returns | pricing")

async def on_text(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    text = (update.message.text or "").strip().lower()
    for key, ans in FAQ.items():
        if key in text:
            await update.message.reply_text(ans + f"\n\nFull assistant: {BOT_URL}")
            return
    await update.message.reply_text(
        f"I am not sure. Open the full docs assistant:\n{BOT_URL}"
    )

def main() -> None:
    token = os.environ.get("TELEGRAM_BOT_TOKEN")
    if not token:
        raise SystemExit("Set TELEGRAM_BOT_TOKEN")
    app = Application.builder().token(token).build()
    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("help", help_cmd))
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, on_text))
    app.run_polling()

if __name__ == "__main__":
    main()
