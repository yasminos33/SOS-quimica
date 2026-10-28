<?php
/**
 * SOS QUÍMICA - COMPONENTE MODAL MODERNO
 * 
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 */
?>
<!-- Modal Base Reutilizável -->
<div id="sos-modal-overlay" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity duration-200">
    <div id="sos-modal-card" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform scale-95 transition-transform duration-200">
        
        <!-- Topo com Ícone e Título -->
        <div class="p-5 flex items-start gap-4 border-b border-slate-100 dark:border-slate-800">
            <div id="sos-modal-icon-container" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                <i id="sos-modal-icon" class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="flex-1 pr-2">
                <h3 id="sos-modal-title" class="text-lg font-bold text-slate-900 dark:text-white leading-tight">
                    Aviso do Sistema
                </h3>
                <p id="sos-modal-subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    SOS Química - Protocolo de Segurança
                </p>
            </div>
            <button type="button" onclick="fecharModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg transition-colors" aria-label="Fechar">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <div id="sos-modal-body" class="p-6 text-sm text-slate-700 dark:text-slate-300 leading-relaxed max-h-[65vh] overflow-y-auto">
            Mensagem do modal...
        </div>

        <!-- Rodapé com Ações -->
        <div id="sos-modal-footer" class="p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <button id="sos-modal-btn-cancelar" type="button" onclick="fecharModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-sm">
                Cancelar
            </button>
            <button id="sos-modal-btn-confirmar" type="button" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow-md shadow-red-600/20 transition-all text-sm flex items-center gap-2">
                <span>Confirmar</span>
            </button>
        </div>

    </div>
</div>

<script>
/**
 * Gerenciador do Modal que substitui caixas de mensagens nativas
 */
let modalConfirmacaoCallback = null;
let modalCancelamentoCallback = null;

function abrirModal() {
    const overlay = document.getElementById('sos-modal-overlay');
    const card = document.getElementById('sos-modal-card');
    if (!overlay || !card) return;

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    requestAnimationFrame(() => {
        card.classList.remove('scale-95');
        card.classList.add('scale-100');
    });
}

function fecharModal() {
    const overlay = document.getElementById('sos-modal-overlay');
    const card = document.getElementById('sos-modal-card');
    if (!overlay || !card) return;

    card.classList.remove('scale-100');
    card.classList.add('scale-95');
    setTimeout(() => {
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
        if (typeof modalCancelamentoCallback === 'function') {
            modalCancelamentoCallback();
        }
        modalConfirmacaoCallback = null;
        modalCancelamentoCallback = null;
    }, 150);
}

/**
 * Substitui o window.alert tradicional por um MODAL elegante
 */
function modalAlerta(mensagem, titulo = 'Aviso Importante', tipo = 'warning') {
    const iconContainer = document.getElementById('sos-modal-icon-container');
    const icon = document.getElementById('sos-modal-icon');
    const titleEl = document.getElementById('sos-modal-title');
    const bodyEl = document.getElementById('sos-modal-body');
    const btnCancelar = document.getElementById('sos-modal-btn-cancelar');
    const btnConfirmar = document.getElementById('sos-modal-btn-confirmar');

    titleEl.textContent = titulo;
    bodyEl.innerHTML = mensagem;

    // Configura ícone e cores de acordo com o tipo
    if (tipo === 'error' || tipo === 'danger') {
        iconContainer.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400';
        icon.className = 'fas fa-triangle-exclamation';
        btnConfirmar.className = 'px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition-all text-sm';
    } else if (tipo === 'success') {
        iconContainer.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400';
        icon.className = 'fas fa-circle-check';
        btnConfirmar.className = 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-all text-sm';
    } else {
        iconContainer.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-musgo-100 text-musgo-700 dark:bg-musgo-950/60 dark:text-musgo-300';
        icon.className = 'fas fa-info-circle';
        btnConfirmar.className = 'px-5 py-2.5 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-semibold transition-all text-sm';
    }

    btnCancelar.classList.add('hidden');
    btnConfirmar.textContent = 'Entendido';
    btnConfirmar.onclick = () => fecharModal();

    abrirModal();
}

/**
 * Substitui o window.confirm nativo por um MODAL seguro
 */
function modalConfirmacao(mensagem, titulo = 'Confirmar Ação', aoConfirmar = null, aoCancelar = null, textoConfirmar = 'Sim, continuar', corBotao = 'danger') {
    const iconContainer = document.getElementById('sos-modal-icon-container');
    const icon = document.getElementById('sos-modal-icon');
    const titleEl = document.getElementById('sos-modal-title');
    const bodyEl = document.getElementById('sos-modal-body');
    const btnCancelar = document.getElementById('sos-modal-btn-cancelar');
    const btnConfirmar = document.getElementById('sos-modal-btn-confirmar');

    titleEl.textContent = titulo;
    bodyEl.innerHTML = mensagem;

    iconContainer.className = 'w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400';
    icon.className = 'fas fa-question-circle';

    btnCancelar.classList.remove('hidden');
    btnCancelar.textContent = 'Cancelar';

    if (corBotao === 'danger') {
        btnConfirmar.className = 'px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow-md shadow-red-600/20 transition-all text-sm flex items-center gap-2';
    } else {
        btnConfirmar.className = 'px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md shadow-blue-600/20 transition-all text-sm flex items-center gap-2';
    }

    btnConfirmar.textContent = textoConfirmar;

    modalConfirmacaoCallback = aoConfirmar;
    modalCancelamentoCallback = aoCancelar;

    btnConfirmar.onclick = () => {
        const cb = modalConfirmacaoCallback;
        fecharModal();
        if (typeof cb === 'function') {
            cb();
        }
    };

    abrirModal();
}

/**
 * Modal especial para visualização de alterações/dados de auditoria
 */
function modalVerDetalhesLog(titulo, descricao, dadosAntigosJson, dadosNovosJson) {
    let html = `<div class="space-y-4">
        <div>
            <span class="text-xs uppercase font-bold text-slate-500">Descrição do Evento:</span>
            <p class="font-medium text-slate-900 dark:text-slate-100">${descricao}</p>
        </div>`;

    if (dadosAntigosJson && dadosAntigosJson !== 'null') {
        try {
            const parsed = typeof dadosAntigosJson === 'string' ? JSON.parse(dadosAntigosJson) : dadosAntigosJson;
            html += `
            <div>
                <span class="text-xs font-bold text-red-600 uppercase">Estado Anterior:</span>
                <pre class="mt-1 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 rounded-lg text-xs overflow-x-auto text-red-900 dark:text-red-200">${JSON.stringify(parsed, null, 2)}</pre>
            </div>`;
        } catch (e) {
            html += `<div><span class="text-xs font-bold text-red-600">Estado Anterior:</span><pre class="mt-1 p-2 bg-slate-100 dark:bg-slate-800 text-xs rounded">${dadosAntigosJson}</pre></div>`;
        }
    }

    if (dadosNovosJson && dadosNovosJson !== 'null') {
        try {
            const parsedNew = typeof dadosNovosJson === 'string' ? JSON.parse(dadosNovosJson) : dadosNovosJson;
            html += `
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase">Novo Estado / Dados Gravados:</span>
                <pre class="mt-1 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 rounded-lg text-xs overflow-x-auto text-emerald-900 dark:text-emerald-200">${JSON.stringify(parsedNew, null, 2)}</pre>
            </div>`;
        } catch (e) {
            html += `<div><span class="text-xs font-bold text-emerald-600">Novo Estado:</span><pre class="mt-1 p-2 bg-slate-100 dark:bg-slate-800 text-xs rounded">${dadosNovosJson}</pre></div>`;
        }
    }

    html += `</div>`;

    modalAlerta(html, titulo, 'info');
}

// Sobrescrever window.alert nativo para cumprir a regra global com rigor
window.nativeAlert = window.alert;
window.alert = function(msg) {
    modalAlerta(msg, 'Mensagem do Sistema');
};
</script>
