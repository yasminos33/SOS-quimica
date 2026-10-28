<?php
/**
 * SOS QUÍMICA - COMPONENTE TOAST DE NOTIFICAÇÕES
 * 
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */
$toastSessao = function_exists('obterToast') ? obterToast() : null;
?>
<!-- Container flutuante para TOASTS com prioridade visual MobileFirst -->
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none px-4 sm:px-0"></div>

<script>
/**
 * Exibe um TOAST dinâmico na interface
 * 
 * @param {string} mensagem Texto da notificação
 * @param {string} tipo 'success' | 'error' | 'warning' | 'info'
 * @param {string} titulo Título opcional
 * @param {number} duracao Tempo em ms (padrão 4500ms)
 */
function mostrarToast(mensagem, tipo = 'info', titulo = '', duracao = 4500) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto transform transition-all duration-300 ease-out translate-y-[-10px] opacity-0 shadow-xl rounded-xl p-4 border flex items-start gap-3 backdrop-blur-md text-sm';

    let icone = 'fa-info-circle';
    let estilos = 'bg-musgo-900/95 text-white border-musgo-500/40';

    if (tipo === 'success') {
        icone = 'fa-check-circle';
        estilos = 'bg-musgo-800/95 text-musgo-50 border-musgo-500/50 shadow-musgo-950/40';
        if (!titulo) titulo = 'Sucesso';
    } else if (tipo === 'error') {
        icone = 'fa-triangle-exclamation';
        estilos = 'bg-red-900/95 text-red-50 border-red-500/60 shadow-red-950/40';
        if (!titulo) titulo = 'Atenção / Erro';
    } else if (tipo === 'warning') {
        icone = 'fa-exclamation-circle';
        estilos = 'bg-amber-900/95 text-amber-50 border-amber-500/50 shadow-amber-950/40';
        if (!titulo) titulo = 'Aviso de Segurança';
    } else {
        if (!titulo) titulo = 'Informação';
    }

    toast.className += ` ${estilos}`;

    toast.innerHTML = `
        <div class="text-xl mt-0.5 shrink-0">
            <i class="fas ${icone}"></i>
        </div>
        <div class="flex-1">
            ${titulo ? `<h4 class="font-bold text-xs uppercase tracking-wider mb-0.5 opacity-90">${titulo}</h4>` : ''}
            <div class="leading-snug">${mensagem}</div>
        </div>
        <button type="button" class="text-white/70 hover:text-white shrink-0 ml-1 p-1" onclick="this.parentElement.remove()" aria-label="Fechar">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;

    container.appendChild(toast);

    // Animação de entrada
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-[-10px]', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto-remoção com transição
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0', 'scale-95');
        setTimeout(() => toast.remove(), 300);
    }, duracao);
}

// Dispara toast pendente da sessão PHP se existir
<?php if ($toastSessao): ?>
document.addEventListener('DOMContentLoaded', () => {
    mostrarToast(
        <?= json_encode($toastSessao['mensagem']) ?>,
        <?= json_encode($toastSessao['tipo']) ?>,
        <?= json_encode($toastSessao['titulo'] ?? '') ?>
    );
});
<?php endif; ?>
</script>
