=== Nyyo Docs Chatbot ===
Contributors: nyyoautomation
Tags: chatbot, faq, knowledge base, support, widget
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A FAQ chatbot that answers from your own pages and posts and shows the source page. Shortcode or floating widget. No external service by default.

== Description ==

Nyyo Docs Chatbot adds a small chat box to your site. Visitors type a question and get the best matching sentence from your own published content, with links to the source pages so they can check the answer.

* **Answers from your content only.** The plugin builds a search index of your published pages and posts (you choose the content types) and searches it on your server.
* **Shows its sources.** Every answer lists the page it came from, with an excerpt and a link.
* **No external service by default.** In the default mode the plugin makes no request to any third-party server. It does not use AI, cookies or tracking.
* **Shortcode or floating widget.** Use `[nyyo_docs_chat]` in any page, or turn on the floating button for the whole site.
* **Index kept up to date.** The index is rebuilt automatically after you publish, update or delete content, and you can rebuild it at any time from the settings page.
* **Optional external mode.** If you already host your own chatbot page, you can embed it instead (see External services below).
* **Translation-ready.** All interface text can be translated.

The answers are taken word for word from your content. Review important pages so the chatbot gives correct information.

All features are free. There is no paid version of this plugin and no feature is locked.

== Installation ==

1. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**, choose the plugin ZIP and click **Install Now**. You can also upload the `nyyo-docs-chatbot` folder to `/wp-content/plugins/`.
2. Click **Activate**.
3. Go to **Settings > Nyyo Docs Chatbot**. Keep **This site** as the answer source and pick the content types to answer from (Pages and Posts by default).
4. Add the shortcode `[nyyo_docs_chat]` to a page, or tick **Show a floating chat button on all public pages**. Click **Save Changes**.
5. Open your site and ask a question. Optional: click **Rebuild index now** after large imports.

Shortcode option: `[nyyo_docs_chat height="600"]` sets the height of the inline chat in pixels (320 to 1600).

== Frequently Asked Questions ==

= Does it send my content or my visitors' questions anywhere? =

Not in the default **This site** mode. Questions go to your own site's REST API (`/wp-json/nyyo-docs-chat/v1/ask`) and are answered from the index stored in your database. The plugin does not log questions.

= Which content is used? =

Only published, public, non password-protected items of the content types you select. Drafts, private posts and password-protected posts are never indexed. The REST route only returns excerpts of content that is already public.

= Does it use AI? =

No. It uses keyword search (BM25) and returns sentences from your content. It does not generate text, so it does not invent answers, but it may pick a less relevant passage when the question uses very different words.

= How big a site can it handle? =

It indexes up to the 500 most recently modified items. The index is stored as a non-autoloaded option.

= Can I use my own hosted chatbot instead? =

Yes. Choose **External chatbot page** in the settings and enter its URL. See External services below.

= What happens when I delete the plugin? =

Its settings and index are removed from the database.

== External services ==

This plugin can embed an external chatbot page. This is **off by default** and only happens if an administrator selects **External chatbot page** in **Settings > Nyyo Docs Chatbot**.

**External chatbot page (URL set by the administrator)**

* What it is: a web page that you choose, shown inside a frame on your site.
* Default URL: `https://nyyoautomation.github.io/nyyo-docs-chatbot/`, the public demo of the Nyyo Docs Chatbot. It uses fictional sample data and is hosted on GitHub Pages, a service provided by GitHub, Inc. Replace it with your own chatbot page.
* What is sent and when: only in External mode, when a visitor opens a page that contains the shortcode or clicks the floating chat button, the visitor's browser loads that URL. The host of the page receives the usual web request data (IP address, browser user agent, the referring page) and any question the visitor types into the embedded page. The default demo page answers inside the visitor's browser and does not send questions to a server.
* GitHub terms of service: https://docs.github.com/en/site-policy/github-terms/github-terms-of-service
* GitHub privacy statement: https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement
* If you enter another URL, the terms and privacy policy of that page's owner apply, and you should list that service in your own privacy policy.

In the default **This site** mode, no external service is used.

== Privacy ==

The plugin does not set cookies, does not track visitors, and does not store the questions they ask. In the default mode all processing happens on your own server.

== Screenshots ==

1. The chat answering from a site page, with the source link.
2. The settings page.

== Changelog ==

= 1.1.0 =
* New: answers from your own published content (local index and search on your server), with source links. This is now the default.
* New: External mode is opt-in; no external request is made by default.
* New: content type selection, welcome message, sources per answer, "Rebuild index" button, settings link.
* New: translation-ready strings, uninstall cleanup.
* Changed: license is now GPLv2 or later.

= 1.0.0 =
* Initial release: shortcode, floating widget and settings page.

== Upgrade Notice ==

= 1.1.0 =
The chatbot now answers from your own content by default. If you used an external chatbot URL, select "External chatbot page" in the settings again.
