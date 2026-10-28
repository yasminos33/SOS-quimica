/**
 * SOS QUÍMICA - COMPORTAMENTO JAVASCRIPT GLOBAL
 * 
 * Regra: "no site nao sera necessario o login , o usuario tera que selecionar ou digitar o problema"
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

document.addEventListener('DOMContentLoaded', () => {
    inicializarBuscaRapida();
    inicializarConfirmacoesExclusao();
});

/**
 * Filtro em tempo real para seleção e digitação do problema na página inicial
 */
function inicializarBuscaRapida() {
    const inputBusca = document.getElementById('input-busca-problema');
    const cardsProcedimentos = document.querySelectorAll('.card-procedimento');
    const contadorResultados = document.getElementById('contador-resultados');
    const containerVazio = document.getElementById('resultado-vazio');

    if (!inputBusca || cardsProcedimentos.length === 0) return;

    inputBusca.addEventListener('input', () => {
        const termo = inputBusca.value.toLowerCase().trim();
        let encontrados = 0;

        cardsProcedimentos.forEach(card => {
            const titulo = card.getAttribute('data-titulo') || '';
            const reagentes = card.getAttribute('data-reagentes') || '';
            const sintomas = card.getAttribute('data-sintomas') || '';
            const categoria = card.getAttribute('data-categoria') || '';

            const textoGeral = `${titulo} ${reagentes} ${sintomas} ${categoria}`.toLowerCase();

            if (termo === '' || textoGeral.includes(termo)) {
                card.classList.remove('hidden');
                encontrados++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (contadorResultados) {
            contadorResultados.textContent = `${encontrados} procedimento(s) encontrado(s)`;
        }

        if (containerVazio) {
            if (encontrados === 0) {
                containerVazio.classList.remove('hidden');
            } else {
                containerVazio.classList.add('hidden');
            }
        }
    });
}

/**
 * Filtro por botão de categoria rápido
 */
function filtrarPorCategoria(slug) {
    const inputBusca = document.getElementById('input-busca-problema');
    const botoes = document.querySelectorAll('.btn-filtro-categoria');
    const cards = document.querySelectorAll('.card-procedimento');
    const containerVazio = document.getElementById('resultado-vazio');

    botoes.forEach(btn => {
        if (btn.getAttribute('data-slug') === slug) {
            btn.classList.add('ring-2', 'ring-musgo-600', 'bg-musgo-100', 'dark:bg-musgo-950/70', 'border-musgo-600');
        } else {
            btn.classList.remove('ring-2', 'ring-musgo-600', 'bg-musgo-100', 'dark:bg-musgo-950/70', 'border-musgo-600');
        }
    });

    if (inputBusca) inputBusca.value = '';

    let visiveis = 0;
    cards.forEach(card => {
        const catCard = card.getAttribute('data-categoria');
        if (slug === 'todos' || catCard === slug) {
            card.classList.remove('hidden');
            visiveis++;
        } else {
            card.classList.add('hidden');
        }
    });

    if (containerVazio) {
        if (visiveis === 0) {
            containerVazio.classList.remove('hidden');
        } else {
            containerVazio.classList.add('hidden');
        }
    }
}

/**
 * Intercepta botões com confirmação (exclusão, ações perigosas)
 * para abrir o MODAL em vez do confirm() nativo do navegador
 */
function inicializarConfirmacoesExclusao() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const msg = this.getAttribute('data-confirm') || 'Deseja realmente prosseguir com esta ação?';
            const titulo = this.getAttribute('data-confirm-title') || 'Confirmar Exclusão';
            const targetUrl = this.getAttribute('href');
            const targetForm = this.closest('form');

            modalConfirmacao(
                msg,
                titulo,
                () => {
                    if (targetUrl) {
                        window.location.href = targetUrl;
                    } else if (targetForm) {
                        targetForm.submit();
                    }
                },
                null,
                'Sim, Confirmar',
                'danger'
            );
        });
    });
}

/**
 * Função para imprimir a ficha de emergência com visual limpo
 */
function imprimirFichaEmergencia() {
    window.print();
}

/**
 * Copiar dados para área de transferência com feedback Toast
 */
function copiarParaAreaTransferencia(texto, descricao = 'Informação') {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarToast(`${descricao} copiado para a área de transferência!`, 'success');
        }).catch(() => {
            mostrarToast(`Não foi possível copiar automaticamente.`, 'error');
        });
    } else {
        const input = document.createElement('input');
        input.value = texto;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        mostrarToast(`${descricao} copiado!`, 'success');
    }
}
