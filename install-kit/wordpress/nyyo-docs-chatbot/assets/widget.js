(function () {
  if (typeof NyyoDocsChat === "undefined" || !NyyoDocsChat.autoInject) return;
  var btn = document.createElement("button");
  btn.type = "button";
  btn.className = "nyyo-docs-chat-fab" + (NyyoDocsChat.position === "bottom-left" ? " left" : "");
  btn.textContent = NyyoDocsChat.buttonLabel || "Ask docs";
  btn.setAttribute("aria-expanded", "false");
  var panel = document.createElement("div");
  panel.className = "nyyo-docs-chat-panel" + (NyyoDocsChat.position === "bottom-left" ? " left" : "");
  var iframe = document.createElement("iframe");
  iframe.src = NyyoDocsChat.botUrl;
  iframe.title = "Nyyo docs chatbot";
  iframe.loading = "lazy";
  panel.appendChild(iframe);
  btn.addEventListener("click", function () {
    var open = panel.classList.toggle("open");
    btn.setAttribute("aria-expanded", open ? "true" : "false");
  });
  document.addEventListener("DOMContentLoaded", function () {
    document.body.appendChild(panel);
    document.body.appendChild(btn);
  });
})();
