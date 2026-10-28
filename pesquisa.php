<?php
/**
 * SOS QUÍMICA - GUIA DE PESQUISA QUÍMICA E SUPORTE LABORATORIAL
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "sendo rapido e eficiente facil de usar tendo suporte para auxiliar em pesquisas tambem"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$pdo = getConexao();

// Captura filtros da URL
$termo = trim(filter_input(INPUT_GET, 'q', FILTER_DEFAULT) ?? '');
$categoriaId = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$gravidade = trim(filter_input(INPUT_GET, 'gravidade', FILTER_DEFAULT) ?? '');

// Categorias para o seletor
$categorias = $pdo->query("SELECT * FROM categorias_acidentes ORDER BY ordem ASC")->fetchAll();

// Construção segura da query dinâmica com PDO Prepared Statements
$sql = "SELECT p.*, c.nome AS categoria_nome, c.icone AS categoria_icone, c.cor AS categoria_cor
        FROM procedimentos p
        INNER JOIN categorias_acidentes c ON p.categoria_id = c.id
        WHERE 1=1";
$params = [];

if (!empty($termo)) {
    $sql .= " AND (p.titulo LIKE ? OR p.reagente_comum LIKE ? OR p.sintomas LIKE ? OR p.passo_a_passo LIKE ?)";
    $like = "%{$termo}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($categoriaId) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoriaId;
}

if (!empty($gravidade) && in_array($gravidade, ['CRITICA', 'ALTA', 'MEDIA', 'BAIXA'])) {
    $sql .= " AND p.nivel_gravidade = ?";
    $params[] = $gravidade;
}

$sql .= " ORDER BY p.titulo ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll();

// Registra auditoria se houve busca com termo
if (!empty($termo)) {
    registrarLogAuditoria(
        $pdo,
        'SEARCH',
        'procedimentos',
        null,
        "Pesquisa de suporte químico pelo termo: '{$termo}' (Resultados: " . count($resultados) . ")"
    );
}

$tituloPagina = 'Pesquisa de Reagentes & Suporte Laboratorial | SOS Química';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Cabeçalho da Seção de Pesquisa -->
    <div class="text-center max-w-3xl mx-auto space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-musgo-100 dark:bg-musgo-950/60 text-musgo-800 dark:text-musgo-300 text-xs font-bold border border-musgo-300 dark:border-musgo-800">
            <i class="fas fa-microscope text-musgo-600"></i> Suporte à Pesquisa & Identificação Rápida
        </div>
        <h1 class="text-2xl sm:text-4xl font-black text-stone-900 dark:text-white tracking-tight">
            Central de Pesquisa de Reagentes & Riscos
        </h1>
        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400">
            Filtre por reagente químico, fórmula molecular, sintoma ou classe de periculosidade para obter orientações imediatas de manuseio seguro e contenção.
        </p>
    </div>

    <!-- FORMULÁRIO DE PESQUISA AVANÇADA (MOBILE FIRST) -->
    <form method="GET" action="<?= url('pesquisa.php') ?>" class="bg-white dark:bg-stone-900 p-5 sm:p-6 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 sm:gap-4">
            
            <!-- Campo de Termo -->
            <div class="md:col-span-6 relative">
                <label for="q" class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Nome do Reagente / Fórmula / Sintoma:
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-musgo-600"><i class="fas fa-search"></i></span>
                    <input 
                        type="text" 
                        name="q" 
                        id="q" 
                        value="<?= htmlspecialchars($termo) ?>"
                        placeholder="Ex: H2SO4, NaOH, éter, ardor nos olhos..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                    >
                </div>
            </div>

            <!-- Filtro de Categoria -->
            <div class="md:col-span-3">
                <label for="categoria" class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Tipo de Ocorrência:
                </label>
                <select 
                    name="categoria" 
                    id="categoria" 
                    class="w-full px-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                >
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoriaId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro de Gravidade -->
            <div class="md:col-span-3">
                <label for="gravidade" class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">
                    Nível de Risco:
                </label>
                <select 
                    name="gravidade" 
                    id="gravidade" 
                    class="w-full px-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                >
                    <option value="">Todas as Gravidades</option>
                    <option value="CRITICA" <?= $gravidade === 'CRITICA' ? 'selected' : '' ?>>Crítica (Ação < 1 min)</option>
                    <option value="ALTA" <?= $gravidade === 'ALTA' ? 'selected' : '' ?>>Alta</option>
                    <option value="MEDIA" <?= $gravidade === 'MEDIA' ? 'selected' : '' ?>>Média</option>
                    <option value="BAIXA" <?= $gravidade === 'BAIXA' ? 'selected' : '' ?>>Baixa</option>
                </select>
            </div>

        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-stone-100 dark:border-stone-800">
            <span class="text-xs text-stone-500 dark:text-stone-400">
                <?= count($resultados) ?> resultado(s) encontrado(s)
            </span>
            <div class="flex items-center gap-2">
                <a href="<?= url('pesquisa.php') ?>" class="px-4 py-2 rounded-xl border border-stone-300 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-xs font-bold text-stone-600 dark:text-stone-300 transition-colors">
                    Limpar Filtros
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white text-xs font-bold shadow-md shadow-musgo-700/20 transition-all flex items-center gap-1.5">
                    <i class="fas fa-filter"></i> Filtrar Reagentes
                </button>
            </div>
        </div>

    </form>

    <!-- RESULTADOS DA PESQUISA -->
    <div class="space-y-4">
        <?php if (empty($resultados)): ?>
            <div class="p-12 text-center bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-musgo-50 dark:bg-stone-800 text-musgo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-flask"></i>
                </div>
                <h3 class="text-base font-bold text-stone-800 dark:text-white">Nenhum resultado com os filtros informados</h3>
                <p class="text-xs text-stone-500 max-w-md mx-auto">
                    Tente buscar por termos mais genéricos como "ácido", "álcali", "fogo", "olhos" ou limpe os filtros para navegar em todos os itens.
                </p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($resultados as $item): ?>
                    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 shadow-sm hover:border-musgo-500 transition-all flex flex-col justify-between">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-stone-500 flex items-center gap-1.5">
                                    <i class="fas <?= htmlspecialchars($item['categoria_icone']) ?> text-musgo-600"></i>
                                    <?= htmlspecialchars($item['categoria_nome']) ?>
                                </span>
                                <?= getBadgeGravidade($item['nivel_gravidade']) ?>
                            </div>

                            <h3 class="text-base font-bold text-stone-900 dark:text-white">
                                <a href="<?= url('detalhes.php?id=' . $item['id']) ?>" class="hover:text-musgo-700 dark:hover:text-musgo-400 transition-colors">
                                    <?= htmlspecialchars($item['titulo']) ?>
                                </a>
                            </h3>

                            <div class="text-xs text-stone-600 dark:text-stone-400 space-y-1">
                                <div><strong class="text-stone-700 dark:text-stone-200 font-semibold">Compostos:</strong> <?= htmlspecialchars($item['reagente_comum']) ?></div>
                                <?php if (!empty($item['sintomas'])): ?>
                                    <div><strong class="text-stone-700 dark:text-stone-200 font-semibold">Sintomas:</strong> <?= htmlspecialchars($item['sintomas']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                            <span class="text-[11px] text-stone-400 font-medium">ID #<?= $item['id'] ?></span>
                            <a href="<?= url('detalhes.php?id=' . $item['id']) ?>" class="text-xs font-bold text-musgo-700 hover:text-musgo-800 flex items-center gap-1">
                                Ver Ficha Completa <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- GUIA RÁPIDO DE SÍMBOLOS GHS DE PERIGO QUÍMICO -->
    <div class="bg-musgo-950 text-white rounded-3xl p-6 sm:p-8 space-y-6 border border-musgo-800">
        <div>
            <h2 class="text-lg sm:text-xl font-black text-white flex items-center gap-2">
                <i class="fas fa-triangle-exclamation text-amber-400"></i> Pictogramas Globais de Perigo (GHS / ABNT)
            </h2>
            <p class="text-xs text-musgo-200 mt-1">
                Guia visual rápido para identificação dos rótulos de frascos reagentes no laboratório:
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-biohazard text-amber-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Corrosivo</h4>
                <p class="text-[10px] text-musgo-200">Destrói tecidos e metais (Ácidos/Bases)</p>
            </div>
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-fire-flame-curved text-orange-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Inflamável</h4>
                <p class="text-[10px] text-musgo-200">Solventes voláteis com baixo ponto de fulgor</p>
            </div>
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-skull-crossbones text-rose-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Tóxico Agudo</h4>
                <p class="text-[10px] text-musgo-200">Perigo grave ou fatal por ingestão/inalação</p>
            </div>
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-lungs text-yellow-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Irritante Respiratório</h4>
                <p class="text-[10px] text-musgo-200">Vapores ácidos, gases halogenados</p>
            </div>
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-circle-radiation text-purple-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Oxidante Forte</h4>
                <p class="text-[10px] text-musgo-200">Pode alimentar e acelerar incêndios</p>
            </div>
            <div class="p-3 rounded-xl bg-musgo-900/80 border border-musgo-800 text-center space-y-1">
                <i class="fas fa-hand-holding-droplet text-emerald-400 text-2xl"></i>
                <h4 class="text-xs font-bold">Sensibilizante</h4>
                <p class="text-[10px] text-musgo-200">Reações alérgicas severas na pele</p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
