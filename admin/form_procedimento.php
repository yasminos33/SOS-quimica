<?php
/**
 * SOS QUÍMICA - FORMULÁRIO DE CADASTRO / EDIÇÃO DE PROCEDIMENTO
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist."
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$pdo = getConexao();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$procedimento = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM procedimentos WHERE id = ?");
    $stmt->execute([$id]);
    $procedimento = $stmt->fetch();

    if (!$procedimento) {
        definirToast('error', 'Procedimento não encontrado para edição.', 'Erro');
        header('Location: ' . url('admin/gerenciar.php'));
        exit;
    }
}

// Busca categorias de acidentes
$categorias = $pdo->query("SELECT * FROM categorias_acidentes ORDER BY ordem ASC")->fetchAll();

$tituloPagina = ($procedimento ? "Editar Procedimento #{$procedimento['id']}" : 'Novo Procedimento de Emergência') . ' | SOS Química';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Navegação Superior -->
    <div class="flex items-center justify-between border-b border-musgo-100 dark:border-stone-800 pb-4">
        <div>
            <a href="<?= url('admin/gerenciar.php') ?>" class="text-xs font-bold text-stone-500 hover:text-musgo-700 dark:hover:text-white flex items-center gap-1.5 mb-1 transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar à Lista
            </a>
            <h1 class="text-2xl font-black text-stone-900 dark:text-white tracking-tight">
                <?= $procedimento ? 'Editar Protocolo de Emergência' : 'Cadastrar Novo Protocolo de Emergência' ?>
            </h1>
        </div>
        <span class="text-xs font-mono text-stone-400">
            <?= $procedimento ? "ID #{$procedimento['id']}" : 'Novo Registro' ?>
        </span>
    </div>

    <!-- FORMULÁRIO -->
    <form method="POST" action="<?= url('actions/salvar_procedimento.php') ?>" class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-sm p-6 sm:p-8 space-y-6">
        
        <?php if ($procedimento): ?>
            <input type="hidden" name="id" value="<?= $procedimento['id'] ?>">
        <?php endif; ?>

        <!-- Bloco 1: Identificação Básica -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            <div class="md:col-span-8">
                <label for="titulo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Título do Acidente / Procedimento *
                </label>
                <input 
                    type="text" 
                    name="titulo" 
                    id="titulo" 
                    required 
                    value="<?= htmlspecialchars($procedimento['titulo'] ?? '') ?>"
                    placeholder="Ex: Contato de Ácido Nítrico na Pele"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-semibold text-stone-900 dark:text-white"
                >
            </div>

            <div class="md:col-span-4">
                <label for="categoria_id" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Categoria do Acidente *
                </label>
                <select 
                    name="categoria_id" 
                    id="categoria_id" 
                    required
                    class="w-full px-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-semibold text-stone-900 dark:text-white"
                >
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($procedimento && $procedimento['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-8">
                <label for="reagente_comum" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Reagentes / Substâncias Associadas *
                </label>
                <input 
                    type="text" 
                    name="reagente_comum" 
                    id="reagente_comum" 
                    required 
                    value="<?= htmlspecialchars($procedimento['reagente_comum'] ?? '') ?>"
                    placeholder="Ex: Ácido Sulfúrico (H2SO4), Ácido Nítrico (HNO3)"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium text-stone-900 dark:text-white"
                >
            </div>

            <div class="md:col-span-4">
                <label for="nivel_gravidade" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Nível de Gravidade *
                </label>
                <select 
                    name="nivel_gravidade" 
                    id="nivel_gravidade" 
                    required
                    class="w-full px-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-bold"
                >
                    <option value="CRITICA" <?= ($procedimento && $procedimento['nivel_gravidade'] === 'CRITICA') ? 'selected' : '' ?>>CRÍTICA (Ação Imediata)</option>
                    <option value="ALTA" <?= (!$procedimento || $procedimento['nivel_gravidade'] === 'ALTA') ? 'selected' : '' ?>>ALTA</option>
                    <option value="MEDIA" <?= ($procedimento && $procedimento['nivel_gravidade'] === 'MEDIA') ? 'selected' : '' ?>>MÉDIA</option>
                    <option value="BAIXA" <?= ($procedimento && $procedimento['nivel_gravidade'] === 'BAIXA') ? 'selected' : '' ?>>BAIXA</option>
                </select>
            </div>

            <div class="md:col-span-12">
                <label for="sintomas" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Sintomas Rápidos Observáveis (Opcional)
                </label>
                <input 
                    type="text" 
                    name="sintomas" 
                    id="sintomas" 
                    value="<?= htmlspecialchars($procedimento['sintomas'] ?? '') ?>"
                    placeholder="Ex: Ardência forte, vermelhidão, tosse espasmódica, queimação ocular"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                >
            </div>

        </div>

        <div class="h-px bg-stone-100 dark:bg-stone-800"></div>

        <!-- Bloco 2: Ações Críticas de Socorro -->
        <div class="space-y-4">
            
            <div>
                <label for="passo_a_passo" class="block text-xs font-bold uppercase tracking-wider text-musgo-700 dark:text-musgo-400 mb-1.5">
                    <i class="fas fa-check-circle mr-1"></i> O QUE FAZER IMEDIATAMENTE (Passo a Passo numerado) *
                </label>
                <textarea 
                    name="passo_a_passo" 
                    id="passo_a_passo" 
                    rows="6" 
                    required 
                    placeholder="1. Levar a vítima ao lava-olhos ou chuveiro...&#10;2. Lavar por 15 a 20 minutos...&#10;3. Remover roupas contaminadas..."
                    class="w-full p-3.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium leading-relaxed"
                ><?= htmlspecialchars($procedimento['passo_a_passo'] ?? '') ?></textarea>
                <span class="text-[11px] text-stone-400">Instruções claras e diretas que o aluno ou socorrista deve executar sem hesitação.</span>
            </div>

            <div>
                <label for="o_que_nao_fazer" class="block text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-1.5">
                    <i class="fas fa-ban mr-1"></i> O QUE NUNCA FAZER (Avisos de Conduta Proibida e Riscos Fatais) *
                </label>
                <textarea 
                    name="o_que_nao_fazer" 
                    id="o_que_nao_fazer" 
                    rows="4" 
                    required 
                    placeholder="- NUNCA tente neutralizar ácido com soda cáustica na pele...&#10;- NÃO esfregue os olhos...&#10;- NÃO aplique óleos ou pomadas caseiras..."
                    class="w-full p-3.5 rounded-xl border border-amber-300 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/20 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium leading-relaxed text-amber-950 dark:text-amber-200"
                ><?= htmlspecialchars($procedimento['o_que_nao_fazer'] ?? '') ?></textarea>
                <span class="text-[11px] text-stone-400">Condutas errôneas populares que agravam a lesão química.</span>
            </div>

        </div>

        <div class="h-px bg-stone-100 dark:bg-stone-800"></div>

        <!-- Bloco 3: Equipamentos e Socorro Médico -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div>
                <label for="equipamento_protecao" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    EPIs e Equipamentos do Socorrista
                </label>
                <textarea 
                    name="equipamento_protecao" 
                    id="equipamento_protecao" 
                    rows="3" 
                    placeholder="Chuveiro de emergência, luvas de nitrila cano longo, máscara panorâmica com filtro químico..."
                    class="w-full p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600"
                ><?= htmlspecialchars($procedimento['equipamento_protecao'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="quando_chamar_socorro" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Quando Acionar Socorro Externo (SAMU/Bombeiros)
                </label>
                <textarea 
                    name="quando_chamar_socorro" 
                    id="quando_chamar_socorro" 
                    rows="3" 
                    placeholder="Sempre em queimaduras por ácidos concentrados, perda de consciência ou acometimento ocular..."
                    class="w-full p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600"
                ><?= htmlspecialchars($procedimento['quando_chamar_socorro'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="contatos_emergencia" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                    Linhas Diretas de Emergência (Texto Rodapé)
                </label>
                <input 
                    type="text" 
                    name="contatos_emergencia" 
                    id="contatos_emergencia" 
                    value="<?= htmlspecialchars($procedimento['contatos_emergencia'] ?? 'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001') ?>"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                >
            </div>

        </div>

        <!-- Botões de Envio -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
            <a href="<?= url('admin/gerenciar.php') ?>" class="px-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 font-bold text-xs sm:text-sm hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm shadow-lg shadow-musgo-700/25 transition-all flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span><?= $procedimento ? 'Atualizar Protocolo (Auditar)' : 'Salvar Novo Protocolo' ?></span>
            </button>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
