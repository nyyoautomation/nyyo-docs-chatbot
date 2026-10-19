(async function () {
  const messagesEl = document.getElementById("messages");
  const inputEl = document.getElementById("q");
  const sendBtn = document.getElementById("send");
  const chipsEl = document.getElementById("chips");
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // Always start at the top so hero + intro are visible
  window.scrollTo(0, 0);
  if ("scrollRestoration" in history) history.scrollRestoration = "manual";

  const examples = [
    "How long does shipping take?",
    "What is your return policy?",
    "How much is the Growth plan?",
    "What are support hours?",
    "Do you accept crypto?",
  ];

  examples.forEach((q) => {
    const b = document.createElement("button");
    b.type = "button";
    b.textContent = q;
    b.addEventListener("click", () => ask(q));
    chipsEl.appendChild(b);
  });

  function addMsg(role, html) {
    const div = document.createElement("div");
    div.className = "msg " + role;
    div.innerHTML =
      '<div class="who">' + (role === "user" ? "You" : "Nyyo demo bot") + "</div>" + html;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
  }

  function addTyping() {
    const div = document.createElement("div");
    div.className = "msg bot";
    div.id = "typing-indicator";
    div.innerHTML =
      '<div class="who">Nyyo demo bot</div><div class="typing-row" aria-label="Thinking"><span></span><span></span><span></span></div>';
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
  }

  let index = null;
  try {
    const docs = window.NYYO_DOCS || (await (await fetch("./data/docs.json")).json());
    index = NyyoBM25.buildIndex(docs);
    addMsg(
      "bot",
      "Hi — I answer from a <strong>sample store FAQ</strong> (shipping, returns, pricing…). Click a suggestion or type a question. Answers include the source section."
    );
  } catch (e) {
    addMsg("bot", "Could not load sample docs. Open this page via GitHub Pages (not as a raw file).");
    return;
  }

  let busy = false;

  function ask(query) {
    query = (query || "").trim();
    if (!query || !index || busy) return;
    busy = true;
    sendBtn.disabled = true;
    addMsg("user", escapeHtml(query));

    const ans = NyyoBM25.answer(index, query, 3);
    let html = "<p>" + escapeHtml(ans.text) + "</p>";
    if (ans.citations.length) {
      html += '<div class="citations">';
      ans.citations.forEach((c) => {
        html +=
          '<div class="cite"><strong>' +
          escapeHtml(c.title) +
          "</strong> · " +
          escapeHtml(c.source) +
          "<br>" +
          escapeHtml(c.snippet) +
          "</div>";
      });
      html += "</div>";
    }

    const delay = reduceMotion ? 0 : 450 + Math.min(600, query.length * 12);
    const typing = reduceMotion ? null : addTyping();

    setTimeout(() => {
      if (typing) typing.remove();
      addMsg("bot", html);
      busy = false;
      sendBtn.disabled = false;
      // Do not focus input — focuses scroll the page on mobile and hide the hero
    }, delay);
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  sendBtn.addEventListener("click", () => {
    ask(inputEl.value);
    inputEl.value = "";
  });
  inputEl.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      ask(inputEl.value);
      inputEl.value = "";
    }
  });
})();
