# Atividade — Cards em Memória (DOM + Array + map())

## Objetivo
Criar uma página simples (sem banco de dados e **sem persistência**) que permita:
- Cadastrar **cards** via formulário;
- Guardar os dados em um **array em memória** (JavaScript);
- Renderizar a lista usando **`map()`**;
- Filtrar e ordenar os cards;
- Remover um card individualmente ou **limpar tudo**.

> Observação: ao atualizar a página, os cards são perdidos, pois ficam apenas na memória do navegador.

---

## Como executar
Você pode rodar de duas formas:

### Opção A — Rodando com PHP embutido (recomendado)
1. Salve o arquivo abaixo como `index.php`
2. No terminal, entre na pasta do arquivo e execute:
```bash
php -S localhost:8000
```
3. Abra no navegador:
- http://localhost:8000

### Opção B — Abrindo como HTML puro
1. Renomeie o arquivo para `index.html` (ou copie apenas o HTML)
2. Abra no navegador com duplo clique

---

## O que o projeto faz
### 1) Estado em memória
O array `cards` mantém os cards no JavaScript:
```js
let cards = [];
```

### 2) Adição de card
No `submit` do formulário:
- Valida com HTML5 (`checkValidity`)
- Valida com regras extras (JS)
- Cria objeto com `id` e `createdAt`
- Faz `cards.push(newCard)`
- Chama `render()`

### 3) Renderização com `map()`
O `map()` transforma cada item do array em um bloco HTML:
```js
visible.map(card => `...HTML...`).join("")
```
Isso evita ficar montando manualmente card por card e deixa o código mais claro.

### 4) Filtro e ordenação
- Filtro por texto em tempo real (`input`)
- Ordenação por:
  - Mais recentes
  - Prioridade
  - Título (A–Z)

### 5) Remoção
- Remoção individual com **delegação de eventos**
- Botão **Limpar tudo** zera o array

---

## Código completo (`index.php`)
> Copie e cole exatamente como está abaixo.

```php
<?php
// index.php (sem persistência: tudo em memória no navegador)
// Você pode rodar com: php -S localhost:8000
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cards em Memória (DOM + map)</title>
  <style>
    :root { font-family: system-ui, Arial, sans-serif; }
    body { margin: 0; background: #f6f7fb; color: #111; }
    header { padding: 18px 16px; background: #111; color: #fff; }
    header h1 { margin: 0; font-size: 18px; }
    main { max-width: 980px; margin: 0 auto; padding: 16px; display: grid; gap: 16px; }

    .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    @media (min-width: 900px) { .grid { grid-template-columns: 380px 1fr; } }

    .card-box { background: #fff; border-radius: 14px; padding: 14px; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .card-box h2 { margin: 0 0 10px; font-size: 16px; }

    label { display: block; font-size: 13px; margin: 10px 0 6px; color: #333; }
    input, select, textarea {
      width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #d8dbe6;
      outline: none; font-size: 14px; background: #fff;
    }
    textarea { min-height: 90px; resize: vertical; }

    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
    .actions { display: flex; gap: 10px; margin-top: 12px; }
    button {
      border: 0; border-radius: 12px; padding: 10px 12px; cursor: pointer;
      font-weight: 600;
    }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-ghost { background: #eef2ff; color: #1f2a5a; }
    .btn-danger { background: #ef4444; color: #fff; }
    button:disabled { opacity: .6; cursor: not-allowed; }

    .hint { font-size: 12px; color: #555; margin-top: 8px; }
    .error { margin-top: 10px; padding: 10px; background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; border-radius: 10px; display: none; }
    .success { margin-top: 10px; padding: 10px; background: #ecfeff; border: 1px solid #a5f3fc; color: #155e75; border-radius: 10px; display: none; }

    .toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
    .toolbar .left, .toolbar .right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .badge { font-size: 12px; background: #111; color: #fff; padding: 6px 10px; border-radius: 999px; }

    .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-top: 12px; }
    .mini-card {
      background: #fff; border-radius: 14px; padding: 12px; border: 1px solid #e7e9f3;
      box-shadow: 0 6px 16px rgba(0,0,0,.04);
      display: grid; gap: 8px;
    }
    .mini-card h3 { margin: 0; font-size: 14px; }
    .meta { font-size: 12px; color: #444; display: flex; gap: 8px; flex-wrap: wrap; }
    .pill { font-size: 12px; padding: 4px 8px; border-radius: 999px; background: #f1f5f9; }
    .pill.high { background: #fee2e2; }
    .pill.medium { background: #ffedd5; }
    .pill.low { background: #dcfce7; }
    .desc { font-size: 13px; color: #333; white-space: pre-wrap; }
    .mini-actions { display: flex; gap: 8px; justify-content: flex-end; }
    .mini-actions button { padding: 8px 10px; border-radius: 10px; }
    .empty { padding: 14px; text-align: center; color: #555; background: #fff; border-radius: 14px; border: 1px dashed #cbd5e1; }
  </style>
</head>
<body>
  <header>
    <h1>Cards em Memória — DOM + Array + map()</h1>
  </header>

  <main>
    <div class="grid">
      <!-- FORMULÁRIO -->
      <section class="card-box">
        <h2>Novo Card</h2>

        <form id="cardForm" novalidate>
          <label for="title">Título *</label>
          <input id="title" name="title" type="text" minlength="3" maxlength="40" placeholder="Ex: Revisar prova" required />

          <label for="description">Descrição *</label>
          <textarea id="description" name="description" minlength="10" maxlength="180" placeholder="Explique em 1 ou 2 linhas..." required></textarea>

          <div class="row">
            <div>
              <label for="category">Categoria *</label>
              <select id="category" name="category" required>
                <option value="">Selecione...</option>
                <option value="Estudos">Estudos</option>
                <option value="Trabalho">Trabalho</option>
                <option value="Pessoal">Pessoal</option>
              </select>
            </div>
            <div>
              <label for="priority">Prioridade *</label>
              <select id="priority" name="priority" required>
                <option value="">Selecione...</option>
                <option value="Alta">Alta</option>
                <option value="Média">Média</option>
                <option value="Baixa">Baixa</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div>
              <label for="dueDate">Data (opcional)</label>
              <input id="dueDate" name="dueDate" type="date" />
            </div>
            <div>
              <label for="tag">Tag (opcional)</label>
              <input id="tag" name="tag" type="text" maxlength="14" placeholder="Ex: urgente" />
            </div>
          </div>

          <div class="actions">
            <button class="btn-primary" type="submit">Adicionar</button>
            <button class="btn-ghost" type="button" id="btnReset">Limpar formulário</button>
          </div>

          <p class="hint">
            * Os cards <b>não</b> são salvos. Atualizou a página, perdeu (memória do navegador).
          </p>

          <div class="error" id="errorBox"></div>
          <div class="success" id="successBox"></div>
        </form>
      </section>

      <!-- LISTAGEM -->
      <section class="card-box">
        <div class="toolbar">
          <div class="left">
            <span class="badge" id="countBadge">0 cards</span>
            <button class="btn-danger" type="button" id="btnClearAll" disabled>Limpar tudo</button>
          </div>

          <div class="right">
            <input id="filterText" type="text" placeholder="Filtrar por texto..." />
            <select id="orderBy">
              <option value="recent">Mais recentes</option>
              <option value="priority">Prioridade</option>
              <option value="title">Título (A-Z)</option>
            </select>
          </div>
        </div>

        <div id="cardsArea">
          <div class="empty">Nenhum card ainda. Crie o primeiro pelo formulário 👈</div>
        </div>
      </section>
    </div>
  </main>

  <script>
    // ==============================
    // 1) ESTADO EM MEMÓRIA (array)
    // ==============================
    let cards = [];

    // ==============================
    // 2) SELETORES DO DOM
    // ==============================
    const form = document.querySelector("#cardForm");
    const titleEl = document.querySelector("#title");
    const descEl = document.querySelector("#description");
    const categoryEl = document.querySelector("#category");
    const priorityEl = document.querySelector("#priority");
    const dueDateEl = document.querySelector("#dueDate");
    const tagEl = document.querySelector("#tag");

    const errorBox = document.querySelector("#errorBox");
    const successBox = document.querySelector("#successBox");

    const cardsArea = document.querySelector("#cardsArea");
    const countBadge = document.querySelector("#countBadge");
    const btnClearAll = document.querySelector("#btnClearAll");
    const btnReset = document.querySelector("#btnReset");

    const filterText = document.querySelector("#filterText");
    const orderBy = document.querySelector("#orderBy");

    // ==============================
    // 3) HELPERS
    // ==============================
    function showError(message) {
      successBox.style.display = "none";
      successBox.textContent = "";

      errorBox.textContent = message;
      errorBox.style.display = "block";
    }

    function showSuccess(message) {
      errorBox.style.display = "none";
      errorBox.textContent = "";

      successBox.textContent = message;
      successBox.style.display = "block";
    }

    function clearMessages() {
      errorBox.style.display = "none";
      errorBox.textContent = "";
      successBox.style.display = "none";
      successBox.textContent = "";
    }

    function normalizeText(s) {
      return (s || "").toString().trim().toLowerCase();
    }

    function priorityRank(p) {
      // menor número = mais importante
      if (p === "Alta") return 1;
      if (p === "Média") return 2;
      return 3;
    }

    function validateFormData(data) {
      // Regras extras além do HTML5:
      // - título não pode ter só números
      // - descrição precisa ter pelo menos 2 palavras
      // - tag (se tiver) só letras/números/underscore e sem espaço
      const title = data.title.trim();
      const description = data.description.trim();
      const category = data.category.trim();
      const priority = data.priority.trim();
      const tag = data.tag.trim();

      if (title.length < 3) return "O título precisa ter pelo menos 3 caracteres.";
      if (/^\d+$/.test(title)) return "O título não pode ser apenas números.";

      const words = description.split(/\s+/).filter(Boolean);
      if (words.length < 2) return "A descrição precisa ter pelo menos 2 palavras.";
      if (description.length < 10) return "A descrição precisa ter pelo menos 10 caracteres.";

      if (!category) return "Selecione uma categoria.";
      if (!priority) return "Selecione uma prioridade.";

      if (tag && !/^[A-Za-z0-9_]{1,14}$/.test(tag)) {
        return "A tag deve ter até 14 caracteres e usar apenas letras, números ou underscore (_), sem espaços.";
      }

      // Data: se preenchida, não permitir data passada (opcional)
      if (data.dueDate) {
        const today = new Date();
        today.setHours(0,0,0,0);
        const chosen = new Date(data.dueDate + "T00:00:00");
        if (chosen < today) return "A data não pode ser no passado.";
      }

      return null; // ok
    }

    // ==============================
    // 4) RENDERIZAÇÃO COM map()
    // ==============================
    function render() {
      // 4.1 aplicar filtro
      const q = normalizeText(filterText.value);

      let visible = cards.filter(card => {
        if (!q) return true;
        const haystack = normalizeText(
          card.title + " " + card.description + " " + card.category + " " + (card.tag || "")
        );
        return haystack.includes(q);
      });

      // 4.2 aplicar ordenação
      const mode = orderBy.value;
      if (mode === "recent") {
        visible.sort((a, b) => b.createdAt - a.createdAt);
      } else if (mode === "priority") {
        visible.sort((a, b) => priorityRank(a.priority) - priorityRank(b.priority));
      } else if (mode === "title") {
        visible.sort((a, b) => a.title.localeCompare(b.title));
      }

      // 4.3 atualizar contador / botões
      countBadge.textContent = `${cards.length} card${cards.length === 1 ? "" : "s"}`;
      btnClearAll.disabled = cards.length === 0;

      // 4.4 renderizar HTML
      if (cards.length === 0) {
        cardsArea.innerHTML = `<div class="empty">Nenhum card ainda. Crie o primeiro pelo formulário 👈</div>`;
        return;
      }

      if (visible.length === 0) {
        cardsArea.innerHTML = `<div class="empty">Nada encontrado com esse filtro.</div>`;
        return;
      }

      // AQUI entra o map() -> transforma cada item do array em HTML de card
      const html = `
        <div class="cards">
          ${visible.map(card => `
            <article class="mini-card" data-id="${card.id}">
              <h3>${escapeHtml(card.title)}</h3>

              <div class="meta">
                <span class="pill">${escapeHtml(card.category)}</span>
                <span class="pill ${pillClass(card.priority)}">${escapeHtml(card.priority)}</span>
                ${card.dueDate ? `<span class="pill">📅 ${escapeHtml(formatDate(card.dueDate))}</span>` : ""}
                ${card.tag ? `<span class="pill">#${escapeHtml(card.tag)}</span>` : ""}
              </div>

              <div class="desc">${escapeHtml(card.description)}</div>

              <div class="mini-actions">
                <button class="btn-ghost" type="button" data-action="remove" data-id="${card.id}">Remover</button>
              </div>
            </article>
          `).join("")}
        </div>
      `;
      cardsArea.innerHTML = html;
    }

    function pillClass(priority) {
      if (priority === "Alta") return "high";
      if (priority === "Média") return "medium";
      return "low";
    }

    function formatDate(yyyy_mm_dd) {
      const [y, m, d] = yyyy_mm_dd.split("-");
      return `${d}/${m}/${y}`;
    }

    function escapeHtml(str) {
      // evita injetar HTML no card (segurança básica)
      return (str ?? "").toString()
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    // ==============================
    // 5) EVENTOS
    // ==============================
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      clearMessages();

      // HTML5 validation first
      if (!form.checkValidity()) {
        showError("Preencha os campos obrigatórios corretamente (título, descrição, categoria e prioridade).");
        return;
      }

      const data = {
        title: titleEl.value,
        description: descEl.value,
        category: categoryEl.value,
        priority: priorityEl.value,
        dueDate: dueDateEl.value,
        tag: tagEl.value
      };

      // JS validation
      const err = validateFormData(data);
      if (err) {
        showError(err);
        return;
      }

      // cria um card em memória
      const newCard = {
        id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()),
        ...data,
        createdAt: Date.now()
      };

      cards.push(newCard);

      showSuccess("Card criado em memória ✅");
      form.reset();
      titleEl.focus();
      render();
    });

    btnReset.addEventListener("click", () => {
      form.reset();
      clearMessages();
      titleEl.focus();
    });

    btnClearAll.addEventListener("click", () => {
      cards = [];
      clearMessages();
      render();
    });

    // filtro e ordenação atualizam em tempo real
    filterText.addEventListener("input", render);
    orderBy.addEventListener("change", render);

    // Remover individual (event delegation)
    cardsArea.addEventListener("click", (e) => {
      const btn = e.target.closest("button[data-action='remove']");
      if (!btn) return;

      const id = btn.getAttribute("data-id");
      cards = cards.filter(c => c.id !== id);
      render();
    });

    // render inicial
    render();
  </script>
</body>
</html>
```

---

## Sugestões de melhoria (opcional)
Se quiser evoluir depois:
- Persistir no `localStorage` para não perder ao atualizar
- Adicionar edição de card
- Adicionar status (feito / pendente)
- Exportar cards para JSON/CSV
