<?php
/**
 * SOS QUÍMICA - PÁGINA DE HISTÓRICO E AUDITORIA DO SISTEMA
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer 
 * alteração feita por qualquer usuário, para auditorias futuras."
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$pdo = getConexao();

// Filtros de Auditoria
$filtroAcao = trim(filter_input(INPUT_GET, 'acao', FILTER_DEFAULT) ?? '');
$busca = trim(filter_input(INPUT_GET, 'busca', FILTER_DEFAULT) ?? '');

// Paginação segura
$pagina = max(1, filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1);
$porPagina = 20;
$offset = ($pagina - 1) * $porPagina;

// Construção da consulta segura com Prepared Statements
$where = " WHERE 1=1";
$params = [];

if (!empty($filtroAcao)) {
    $where .= " AND acao = ?";
    $params[] = strtoupper($filtroAcao);
}

if (!empty($busca)) {
    $where .= " AND (descricao LIKE ? OR usuario_nome LIKE ? OR tabela_afetada LIKE ? OR ip LIKE ?)";
    $like = "%{$busca}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Total de registros para paginação
$stmtTotal = $pdo->prepare("SELECT COUNT(*) AS total FROM logs_auditoria" . $where);
$stmtTotal->execute($params);
$totalRegistros = (int)$stmtTotal->fetch()['total'];
$totalPaginas = max(1, ceil($totalRegistros / $porPagina));

// Busca dos logs ordenados por data decrescente
$sqlLogs = "SELECT * FROM logs_auditoria" . $where . " ORDER BY data_hora DESC LIMIT " . (int)$porPagina . " OFFSET " . (int)$offset;
$stmtLogs = $pdo->prepare($sqlLogs);
$stmtLogs->execute($params);
$logs = $stmtLogs->fetchAll();

$tituloPagina = 'Histórico e Auditoria de Registros | SOS Química';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Cabeçalho da Página de Histórico (Verde Musgo) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-musgo-100 dark:border-stone-800 pb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-musgo-100 dark:bg-musgo-950/60 text-musgo-800 dark:text-musgo-300 text-xs font-bold mb-2 border border-musgo-300 dark:border-musgo-800">
                <i class="fas fa-shield-halved text-musgo-600"></i> Registro Oficial para Auditorias Futuras
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                Histórico & Logs de Auditoria
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                Todas as alterações, novos procedimentos, exclusões e consultas são rastreadas e preservadas para controle de conformidade e segurança.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-musgo-50 dark:bg-stone-800 text-xs font-bold text-musgo-800 dark:text-musgo-300 border border-musgo-200 dark:border-stone-700">
                <i class="fas fa-database text-musgo-600 mr-1"></i> Total de Logs: <?= $totalRegistros ?>
            </span>
        </div>
    </div>

    <!-- FILTROS DE HISTÓRICO -->
    <form method="GET" action="<?= url('historico.php') ?>" class="bg-white dark:bg-stone-900 p-4 sm:p-5 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <div class="sm:col-span-6 relative">
                <label for="busca" class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                    Buscar no histórico:
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-musgo-600"><i class="fas fa-search text-xs"></i></span>
                    <input 
                        type="text" 
                        name="busca" 
                        id="busca" 
                        value="<?= htmlspecialchars($busca) ?>" 
                        placeholder="Pesquisar por descrição, usuário, IP ou tabela..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                    >
                </div>
            </div>

            <div class="sm:col-span-4">
                <label for="acao" class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                    Tipo de Ação:
                </label>
                <select 
                    name="acao" 
                    id="acao" 
                    class="w-full px-3 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                >
                    <option value="">Todas as Ações</option>
                    <option value="INSERT" <?= $filtroAcao === 'INSERT' ? 'selected' : '' ?>>INSERT (Cadastros Novos)</option>
                    <option value="UPDATE" <?= $filtroAcao === 'UPDATE' ? 'selected' : '' ?>>UPDATE (Modificações)</option>
                    <option value="DELETE" <?= $filtroAcao === 'DELETE' ? 'selected' : '' ?>>DELETE (Remoções)</option>
                    <option value="SEARCH" <?= $filtroAcao === 'SEARCH' ? 'selected' : '' ?>>SEARCH (Consultas de Socorro)</option>
                    <option value="LOGIN" <?= $filtroAcao === 'LOGIN' ? 'selected' : '' ?>>LOGIN (Autenticações)</option>
                    <option value="LOGOUT" <?= $filtroAcao === 'LOGOUT' ? 'selected' : '' ?>>LOGOUT (Saídas)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>

        </div>
    </form>

    <!-- TABELA / CARDS DE LOGS (RESPONSIVO MOBILE-FIRST) -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        
        <?php if (empty($logs)): ?>
            <div class="p-12 text-center text-stone-500">
                <i class="fas fa-clipboard-check text-4xl mb-3 text-stone-400"></i>
                <p class="text-sm font-semibold">Nenhum registro de log encontrado com os filtros atuais.</p>
            </div>
        <?php else: ?>
            
            <!-- Visão Tabela Desktop -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-musgo-50/60 dark:bg-stone-800/80 border-b border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Data / Hora</th>
                            <th class="py-3.5 px-4">Ação</th>
                            <th class="py-3.5 px-4">Usuário</th>
                            <th class="py-3.5 px-4">Descrição do Evento</th>
                            <th class="py-3.5 px-4">Tabela / ID</th>
                            <th class="py-3.5 px-4">IP</th>
                            <th class="py-3.5 px-4 text-center">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-700 dark:text-stone-300 font-medium">
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-musgo-50/40 dark:hover:bg-stone-800/40 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-stone-500 dark:text-stone-400 font-mono text-[11px]">
                                    <?= formatarDataHora($log['data_hora']) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php
                                        $badgeColor = 'bg-stone-100 text-stone-700';
                                        if ($log['acao'] === 'INSERT') $badgeColor = 'bg-musgo-100 text-musgo-800 dark:bg-musgo-950/60 dark:text-musgo-300';
                                        elseif ($log['acao'] === 'UPDATE') $badgeColor = 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300';
                                        elseif ($log['acao'] === 'DELETE') $badgeColor = 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300';
                                        elseif ($log['acao'] === 'SEARCH') $badgeColor = 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300';
                                    ?>
                                    <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] tracking-wide uppercase <?= $badgeColor ?>">
                                        <?= htmlspecialchars($log['acao']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-stone-900 dark:text-white">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fas fa-user-circle text-stone-400"></i>
                                        <span><?= htmlspecialchars($log['usuario_nome']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate" title="<?= htmlspecialchars($log['descricao']) ?>">
                                    <?= htmlspecialchars($log['descricao']) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono text-stone-500 text-[11px]">
                                    <?= htmlspecialchars($log['tabela_afetada'] ?? '-') ?> <?= $log['registro_id'] ? '#' . $log['registro_id'] : '' ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono text-stone-400 text-[11px]">
                                    <?= htmlspecialchars($log['ip']) ?>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <?php if (!empty($log['dados_anteriores']) || !empty($log['dados_novos'])): ?>
                                        <button 
                                            type="button" 
                                            onclick='modalVerDetalhesLog(
                                                "Auditoria do Log #<?= $log['id'] ?>",
                                                <?= json_encode($log['descricao']) ?>,
                                                <?= json_encode($log['dados_anteriores']) ?>,
                                                <?= json_encode($log['dados_novos']) ?>
                                            )'
                                            class="p-1.5 rounded-lg bg-musgo-100 hover:bg-musgo-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-musgo-800 dark:text-musgo-300 font-bold text-xs transition-colors"
                                            title="Ver dados JSON"
                                        >
                                            <i class="fas fa-code"></i> Inspecionar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-stone-300 dark:text-stone-600">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Visão Cards Mobile (Priorizando MobileFirst) -->
            <div class="lg:hidden divide-y divide-stone-100 dark:divide-stone-800">
                <?php foreach ($logs as $log): ?>
                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono text-stone-400"><?= formatarDataHora($log['data_hora']) ?></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                                <?= htmlspecialchars($log['acao']) ?>
                            </span>
                        </div>

                        <p class="text-xs font-semibold text-stone-900 dark:text-white leading-snug">
                            <?= htmlspecialchars($log['descricao']) ?>
                        </p>

                        <div class="flex items-center justify-between text-[11px] text-stone-500 pt-1">
                            <span><i class="fas fa-user text-stone-400 mr-1"></i> <?= htmlspecialchars($log['usuario_nome']) ?></span>
                            <span class="font-mono"><?= htmlspecialchars($log['ip']) ?></span>
                        </div>

                        <?php if (!empty($log['dados_anteriores']) || !empty($log['dados_novos'])): ?>
                            <div class="pt-2">
                                <button 
                                    type="button" 
                                    onclick='modalVerDetalhesLog(
                                        "Auditoria do Log #<?= $log['id'] ?>",
                                        <?= json_encode($log['descricao']) ?>,
                                        <?= json_encode($log['dados_anteriores']) ?>,
                                        <?= json_encode($log['dados_novos']) ?>
                                    )'
                                    class="w-full py-1.5 rounded-lg bg-musgo-50 dark:bg-stone-800 text-musgo-800 dark:text-musgo-300 text-xs font-bold text-center block"
                                >
                                    <i class="fas fa-code mr-1"></i> Ver Dados Alterados no Modal
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>

    <!-- PAGINAÇÃO -->
    <?php if ($totalPaginas > 1): ?>
        <div class="flex items-center justify-between pt-4 border-t border-stone-200 dark:border-stone-800 text-xs">
            <span class="text-stone-500">Página <?= $pagina ?> de <?= $totalPaginas ?></span>
            <div class="flex items-center gap-1">
                <?php if ($pagina > 1): ?>
                    <a href="?p=<?= $pagina - 1 ?>&acao=<?= urlencode($filtroAcao) ?>&busca=<?= urlencode($busca) ?>" class="px-3 py-1.5 rounded-lg border border-stone-300 dark:border-stone-700 font-bold hover:bg-stone-100 dark:hover:bg-stone-800">
                        Anterior
                    </a>
                <?php endif; ?>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?p=<?= $pagina + 1 ?>&acao=<?= urlencode($filtroAcao) ?>&busca=<?= urlencode($busca) ?>" class="px-3 py-1.5 rounded-lg border border-stone-300 dark:border-stone-700 font-bold hover:bg-stone-100 dark:hover:bg-stone-800">
                        Próxima
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
