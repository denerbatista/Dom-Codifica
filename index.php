<?php
// index.php
// Rodar com: php -S localhost:8000
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cards em Memória (DOM + map)</title>

  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f2f2f2; }
    header { background: #222; color: #fff; padding: 15px; }
    main { max-width: 900px; margin: 0 auto; padding: 15px; display: grid; gap: 15px; }

    .box { background: #fff; padding: 15px; border-radius: 10px; }
    label { display: block; margin-top: 10px; font-size: 14px; }
    input, textarea, select {
      width: 100%; padding: 10px; margin-top: 5px;
      border: 1px solid #ccc; border-radius: 8px;
      font-size: 14px;
    }
    textarea { min-height: 90px; resize: vertical; }

    button {
      margin-top: 12px; padding: 10px 12px;
      border: 0; border-radius: 8px;
      cursor: pointer; font-weight: bold;
    }
    .btn { background: #2563eb; color: #fff; }
    .btn-danger { background: #ef4444; color: #fff; }

    .msg { margin-top: 10px; padding: 10px; border-radius: 8px; display: none; }
    .error { background: #ffe4e6; color: #9f1239; }
    .ok { background: #d1fae5; color: #065f46; }

    .top { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
    .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-top: 10px; }
    .card {
      border: 1px solid #ddd; border-radius: 10px;
      padding: 12px; background: #fff;
      display: grid; gap: 10px;
    }
    .card h3 { margin: 0; font-size: 16px; }
    .meta { font-size: 12px; color: #444; }
    .pill { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #eee; margin-right: 6px; }
    .desc { font-size: 14px; white-space: pre-wrap; }

    /* imagem do card */
    .thumb {
      width: 100%;
      height: 140px;
      object-fit: cover;
      border-radius: 10px;
      border: 1px solid #eee;
      background: #fafafa;
    }
  </style>
</head>

<body>
  <header>
    <h2>Cards em Memória (DOM + Array + map)</h2>
  </header>

  <main>
    <!-- FORM -->
    <section class="box">
      <h3>Criar Card</h3>

      <form id="formCard">
        <label>Título *</label>
        <input id="title" type="text" placeholder="Ex: Estudar JS" />

        <label>Descrição *</label>
        <textarea id="description" placeholder="Ex: Revisar DOM e fazer exercício..."></textarea>

        <label>Categoria *</label>
        <select id="category">
          <option value="">Selecione...</option>
          <option>Estudos</option>
          <option>Trabalho</option>
          <option>Pessoal</option>
        </select>

        <label>Prioridade *</label>
        <select id="priority">
          <option value="">Selecione...</option>
          <option>Alta</option>
          <option>Média</option>
          <option>Baixa</option>
        </select>

        <label>Imagem (URL opcional)</label>
        <input id="imageUrl" type="url" placeholder="https://exemplo.com/imagem.jpg" />

        <button class="btn" type="submit">Adicionar Card</button>
        <button class="btn-danger" type="button" id="btnClear" disabled>Limpar Tudo</button>

        <div id="msgError" class="msg error"></div>
        <div id="msgOk" class="msg ok"></div>
      </form>
    </section>

    <!-- LIST -->
    <section class="box">
      <div class="top">
        <strong id="count">0 cards</strong>
        <input id="filter" type="text" placeholder="Filtrar por texto..." style="max-width: 260px;">
      </div>

      <div id="cardsArea" class="cards"></div>
      <p id="empty" style="color:#666;">Nenhum card ainda. Crie o primeiro 👆</p>
    </section>
  </main>

  <script>
    // ======================================
    // 1) ESTADO (array em memória)
    // ======================================
    let cards = [];

    // ======================================
    // 2) PEGANDO ELEMENTOS DO DOM
    // ======================================
    const form = document.querySelector("#formCard");
    const titleEl = document.querySelector("#title");
    const descEl = document.querySelector("#description");
    const categoryEl = document.querySelector("#category");
    const priorityEl = document.querySelector("#priority");
    const imageUrlEl = document.querySelector("#imageUrl");

    const msgError = document.querySelector("#msgError");
    const msgOk = document.querySelector("#msgOk");

    const cardsArea = document.querySelector("#cardsArea");
    const countEl = document.querySelector("#count");
    const emptyEl = document.querySelector("#empty");

    const btnClear = document.querySelector("#btnClear");
    const filterEl = document.querySelector("#filter");

    // ======================================
    // 3) FUNÇÕES DE MENSAGEM
    // ======================================
    function showError(text) {
      msgOk.style.display = "none";
      msgOk.textContent = "";
      msgError.textContent = text;
      msgError.style.display = "block";
    }

    function showOk(text) {
      msgError.style.display = "none";
      msgError.textContent = "";
      msgOk.textContent = text;
      msgOk.style.display = "block";
    }

    function clearMessages() {
      msgError.style.display = "none";
      msgError.textContent = "";
      msgOk.style.display = "none";
      msgOk.textContent = "";
    }

    // ======================================
    // 4) VALIDAÇÃO BEM SIMPLES
    // ======================================
    function validate(data) {
      if (data.title.length < 3) return "Título precisa ter no mínimo 3 letras.";
      if (data.description.length < 10) return "Descrição precisa ter no mínimo 10 caracteres.";
      if (!data.category) return "Selecione uma categoria.";
      if (!data.priority) return "Selecione uma prioridade.";

      // imagem opcional: se preenchida, precisa ser http/https
      if (data.imageUrl) {
        const ok = data.imageUrl.startsWith("http://") || data.imageUrl.startsWith("https://");
        if (!ok) return "A imagem (URL) precisa começar com http:// ou https://";
      }

      return null;
    }

    // ======================================
    // 5) RENDER (usa map())
    // ======================================
    function render() {
      countEl.textContent = cards.length + " card(s)";
      btnClear.disabled = cards.length === 0;

      const q = filterEl.value.toLowerCase().trim();
      const visible = cards.filter(c => {
        const text = (c.title + " " + c.description + " " + c.category + " " + c.priority).toLowerCase();
        return text.includes(q);
      });

      if (cards.length === 0) {
        cardsArea.innerHTML = "";
        emptyEl.textContent = "Nenhum card ainda. Crie o primeiro 👆";
        emptyEl.style.display = "block";
        return;
      }

      if (visible.length === 0) {
        cardsArea.innerHTML = "";
        emptyEl.textContent = "Nada encontrado com esse filtro.";
        emptyEl.style.display = "block";
        return;
      }

      emptyEl.style.display = "none";

      const html = visible.map(card => {
        const imgHtml = card.imageUrl
          ? `<img class="thumb" src="${card.imageUrl}" alt="Imagem do card">`
          : "";

        return `
          <div class="card">
            ${imgHtml}
            <h3>${card.title}</h3>
            <div class="meta">
              <span class="pill">${card.category}</span>
              <span class="pill">${card.priority}</span>
            </div>
            <div class="desc">${card.description}</div>

            <button type="button" class="btn-danger" data-id="${card.id}">
              Remover
            </button>
          </div>
        `;
      }).join("");

      cardsArea.innerHTML = html;
    }

    // ======================================
    // 6) EVENTOS
    // ======================================
    form.addEventListener("submit", function(e) {
      e.preventDefault();
      clearMessages();

      const data = {
        title: titleEl.value.trim(),
        description: descEl.value.trim(),
        category: categoryEl.value,
        priority: priorityEl.value,
        imageUrl: imageUrlEl.value.trim()
      };

      const err = validate(data);
      if (err) {
        showError(err);
        return;
      }

      const newCard = {
        id: Date.now().toString(),
        title: data.title,
        description: data.description,
        category: data.category,
        priority: data.priority,
        imageUrl: data.imageUrl // pode ser "" (vazio)
      };

      cards.push(newCard);

      showOk("Card criado em memória ✅");
      form.reset();
      titleEl.focus();
      render();
    });

    btnClear.addEventListener("click", function() {
      cards = [];
      clearMessages();
      render();
    });

    filterEl.addEventListener("input", render);

    // Remover 1 card (delegação)
    cardsArea.addEventListener("click", function(e) {
      if (e.target.matches("button[data-id]")) {
        const id = e.target.getAttribute("data-id");
        cards = cards.filter(c => c.id !== id);
        render();
      }
    });

    render();
  </script>
</body>
</html>
