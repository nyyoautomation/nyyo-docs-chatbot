=== Nyyo Docs Chatbot ===
Contributors: nyyoautomation
Tags: chatbot, faq, knowledge base, search, support
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Answers visitor questions from your own pages and posts, with a link to the source page. Runs on your server. No external service by default.

== Description ==

Nyyo Docs Chatbot adds a small chat box to your site. A visitor types a question and gets the best matching sentence from your own published content, plus links to the pages it came from, so they can check the answer themselves.

It is a good fit for help centers, shipping and returns pages, product documentation and other FAQ-style content that visitors keep asking about.

**What it does**

* **Answers from your content only.** The plugin builds a search index of your published pages and posts (you choose the content types) and searches it on your own server.
* **Shows its sources.** Every answer lists the page or pages it came from, with an excerpt and a link. You choose how many sources to show (1 to 5).
* **No external service by default.** In the default mode the plugin makes no request to any third-party server. It does not use AI, cookies or tracking.
* **Shortcode or floating widget.** Put `[nyyo_docs_chat]` in any page, or turn on a floating chat button for the whole site (bottom right or bottom left).
* **Index kept up to date.** When you publish, update, trash or delete content, the index is cleared and rebuilt on the next question. You can also rebuild it from the settings page.
* **Lightweight.** The front-end script and stylesheet load only on pages that use the shortcode, or on all pages when the floating widget is on. The chat box itself only loads when a visitor opens it.
* **Optional external mode.** If you already host your own chatbot page, you can show it in a frame instead (see External services below).
* **Translation-ready.** All interface text can be translated, and you can set your own button label and welcome message.

**How answers work**

The plugin uses keyword search (BM25) over passages of your content and returns the sentence that best matches the question, quoted word for word. It does not generate text, so it does not make up answers. A passage is only used when at least half of the meaningful words in the question appear in it. If nothing matches, the visitor is told the answer was not found on the site.

**Good to know**

* Because answers are quoted from your pages, keep important pages accurate and up to date.
* Matching works on words, not meaning. A question phrased with very different words from your content may not find an answer, or may return a less relevant passage. Using the words your visitors use in your pages helps.
* Word matching is tuned for English (English stop words and a simple English word-ending rule). Content in other languages can still be searched, but results are usually less precise.
* Up to the 500 most recently modified items are indexed.

All features are free. There is no paid version of this plugin and no feature is locked.

== Installation ==

1. In your WordPress admin, go to **Plugins > Add New Plugin**, search for "Nyyo Docs Chatbot", then click **Install Now** and **Activate**. You can also upload the plugin ZIP from **Plugins > Add New Plugin > Upload Plugin**, or upload the `nyyo-docs-chatbot` folder to `/wp-content/plugins/`.
2. Go to **Settings > Nyyo Docs Chatbot**. Keep **This site** as the answer source and pick the content types to answer from (Pages and Posts by default).
3. Add the shortcode `[nyyo_docs_chat]` to a page, or tick **Show a floating chat button on all public pages**. Click **Save Changes**.
4. Open your site and ask a question. Optional: click **Rebuild index now** after a large import.

Shortcode option: `[nyyo_docs_chat height="600"]` sets the height of the inline chat in pixels (320 to 1600, default 560).

== Frequently Asked Questions ==

= Does it send my content or my visitors' questions anywhere? =

Not in the default **This site** mode. Questions go to your own site's REST API (`/wp-json/nyyo-docs-chat/v1/ask`) and are answered from the index stored in your database. The plugin does not log or store questions.

= Which content is used? =

Only published, public, non password-protected items of the content types you select. Drafts, private posts, password-protected posts and media attachments are never indexed. The REST route only returns excerpts of content that is already public.

= Does it use AI? =

No. It uses keyword search (BM25) and returns sentences from your content. It does not generate text, so it does not invent answers, but it may pick a less relevant passage when the question uses very different words from your pages.

= Can it read PDF or Word files from my Media Library? =

No. It only reads the text of your published posts, pages and other public content types. To make a document searchable, publish its content as a page.

= Does it work with page builders and custom post types? =

It reads the saved post content of any public content type you tick in the settings. Text stored outside the post content (for example in some page builder data or custom fields) is not indexed.

= How big a site can it handle? =

It indexes up to the 500 most recently modified items. The index is stored as a single option that is not autoloaded, so it is not loaded on every page view.

= Can I change the button text and the welcome message? =

Yes, in **Settings > Nyyo Docs Chatbot**. Leave them empty to use the default translatable text.

= Can I use my own hosted chatbot instead? =

Yes. Choose **External chatbot page** in the settings and enter its URL. See External services below.

= What happens when I delete the plugin? =

Its settings and index are removed from the database.

== External services ==

This plugin can embed an external chatbot page. This is **off by default** and only happens if an administrator selects **External chatbot page** in **Settings > Nyyo Docs Chatbot**.

**External chatbot page (URL set by the administrator)**

* What it is: a web page that you choose, shown inside a frame on your site. The URL field is empty by default and the plugin has no built-in address: External mode only works after an administrator enters the URL of their own chatbot page. If the field is empty, the plugin stays in **This site** mode.
* What is sent and when: only in External mode, when a visitor opens a page that contains the shortcode or clicks the floating chat button, the visitor's browser loads that URL. The host of the page receives the usual web request data (IP address, browser user agent, the referring page) and any question the visitor types into the embedded page.
* Terms and privacy: the page is run by you or by a provider you choose, so that provider's terms of service and privacy policy apply. List that service in your own privacy policy.

In the default **This site** mode, no external service is used.

== Privacy ==

The plugin does not set cookies, does not track visitors, and does not store the questions they ask. In the default mode all processing happens on your own server.

== Screenshots ==

1. The inline chat added with the `[nyyo_docs_chat]` shortcode, answering from a site page and linking to the source.
2. The floating chat button, opened on a page, with the answer and its source.
3. The settings page: answer source, content types, floating widget, button label, welcome message, sources per answer and the local index.

== Changelog ==

= 1.1.0 =
* New: answers from your own published content (local index and search on your server), with source links. This is now the default.
* New: External mode is opt-in; no external request is made by default.
* New: the External chatbot URL is empty by default. External mode needs a URL entered by the administrator.
* New: content type selection, welcome message, sources per answer, "Rebuild index" button, settings link.
* New: translation-ready strings, uninstall cleanup.
* Changed: license is now GPLv2 or later.

= 1.0.0 =
* Initial release: shortcode, floating widget and settings page.

== Upgrade Notice ==

= 1.1.0 =
The chatbot now answers from your own content by default. If you used an external chatbot URL, select "External chatbot page" in the settings again.
