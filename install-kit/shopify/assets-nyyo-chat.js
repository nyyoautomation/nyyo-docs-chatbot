/**
 * Nyyo Docs Chatbot — universal embed (Wix / Webflow / plain HTML)
 * Usage:
 *   <script src="https://YOUR-HOST/install-kit/universal/nyyo-chat.js"
 *     data-bot-url="https://nyyoautomation.github.io/nyyo-docs-chatbot/"
 *     data-label="Ask docs"
 *     data-position="bottom-right"
 *     defer></script>
 */
(function () {
  var s = document.currentScript;
  if (!s) return;
  var botUrl = s.getAttribute("data-bot-url") || "https://nyyoautomation.github.io/nyyo-docs-chatbot/";
  var label = s.getAttribute("data-label") || "Ask docs";
  var position = s.getAttribute("data-position") || "bottom-right";
  var left = position === "bottom-left";

  var css = document.createElement("style");
  css.textContent =
    ".nyyo-fab{position:fixed;z-index:99999;bottom:20px;" +
    (left ? "left:20px;" : "right:20px;") +
    "border:0;border-radius:999px;cursor:pointer;padding:12px 18px;" +
    "font:600 14px/1.2 system-ui,sans-serif;color:#041018;" +
    "background:linear-gradient(135deg,#3d8bff,#39ff88);" +
    "box-shadow:0 8px 28px rgba(61,139,255,.35)}" +
    ".nyyo-panel{position:fixed;z-index:99998;bottom:72px;" +
    (left ? "left:20px;" : "right:20px;") +
    "width:min(400px,calc(100vw - 24px));height:min(640px,calc(100vh - 100px));" +
    "border:0;border-radius:16px;overflow:hidden;display:none;background:#050814;" +
    "box-shadow:0 20px 50px rgba(0,0,0,.45)}.nyyo-panel.open{display:block}" +
    ".nyyo-panel iframe{width:100%;height:100%;border:0}";
  document.head.appendChild(css);

  function mount() {
    var panel = document.createElement("div");
    panel.className = "nyyo-panel";
    var iframe = document.createElement("iframe");
    iframe.src = botUrl;
    iframe.title = "Nyyo docs chatbot";
    iframe.loading = "lazy";
    panel.appendChild(iframe);
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "nyyo-fab";
    btn.textContent = label;
    btn.setAttribute("aria-expanded", "false");
    btn.addEventListener("click", function () {
      var open = panel.classList.toggle("open");
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.body.appendChild(panel);
    document.body.appendChild(btn);
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", mount);
  else mount();
})();
