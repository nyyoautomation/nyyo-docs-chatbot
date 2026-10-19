/** Minimal BM25 for in-browser RAG over JSON chunks. No API keys. */
(function (global) {
  function tokenize(text) {
    return String(text || "")
      .toLowerCase()
      .replace(/[^a-z0-9àâäéèêëïîôùûüç\s-]/gi, " ")
      .split(/\s+/)
      .filter(Boolean);
  }

  function buildIndex(docs) {
    const N = docs.length;
    const df = Object.create(null);
    const docsTokens = docs.map((d) => {
      const tokens = tokenize(d.title + " " + d.text);
      const tf = Object.create(null);
      for (const t of tokens) tf[t] = (tf[t] || 0) + 1;
      for (const t of Object.keys(tf)) df[t] = (df[t] || 0) + 1;
      return { id: d.id, title: d.title, source: d.source, text: d.text, tf, len: tokens.length };
    });
    const avgdl = docsTokens.reduce((s, d) => s + d.len, 0) / Math.max(N, 1);
    return { docs: docsTokens, df, N, avgdl, k1: 1.5, b: 0.75 };
  }

  function score(index, query) {
    const qTokens = tokenize(query);
    if (!qTokens.length) return [];
    const { docs, df, N, avgdl, k1, b } = index;
    const results = [];
    for (const doc of docs) {
      let s = 0;
      for (const t of qTokens) {
        const f = doc.tf[t] || 0;
        if (!f) continue;
        const idf = Math.log(1 + (N - (df[t] || 0) + 0.5) / ((df[t] || 0) + 0.5));
        const denom = f + k1 * (1 - b + b * (doc.len / avgdl));
        s += idf * ((f * (k1 + 1)) / denom);
      }
      if (s > 0) results.push({ ...doc, score: s });
    }
    results.sort((a, b) => b.score - a.score);
    return results;
  }

  function answer(index, query, topK) {
    topK = topK || 3;
    const hits = score(index, query).slice(0, topK);
    if (!hits.length) {
      return {
        text: "I couldn't find that in the sample docs. Try asking about shipping, returns, warranty, pricing, support hours, or privacy.",
        citations: [],
      };
    }
    const best = hits[0];
    // Pick the most relevant sentence(s) from the best doc
    const sentences = best.text.match(/[^.!?]+[.!?]+/g) || [best.text];
    const qSet = new Set(tokenize(query));
    const ranked = sentences
      .map((sent) => {
        const toks = tokenize(sent);
        let overlap = 0;
        for (const t of toks) if (qSet.has(t)) overlap++;
        return { sent: sent.trim(), overlap };
      })
      .sort((a, b) => b.overlap - a.overlap);
    const picked = ranked.filter((r) => r.overlap > 0).slice(0, 2).map((r) => r.sent);
    const body = picked.length ? picked.join(" ") : sentences.slice(0, 2).map((s) => s.trim()).join(" ");
    return {
      text: body,
      citations: hits.map((h) => ({
        id: h.id,
        title: h.title,
        source: h.source,
        score: Math.round(h.score * 100) / 100,
        snippet: h.text.slice(0, 160) + (h.text.length > 160 ? "…" : ""),
      })),
    };
  }

  global.NyyoBM25 = { buildIndex, score, answer, tokenize };
})(typeof window !== "undefined" ? window : globalThis);
