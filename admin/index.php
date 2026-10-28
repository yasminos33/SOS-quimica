<?php
/**
 * SOS QUÍMICA - PAINEL ADMINISTRATIVO / DASHBOARD
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 * Regra: "Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist."
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Proteção da rota
exigirLogin();

$pdo = getConexao();

// Métricas do Dashboard
$totalProc = (int)$pdo->query("SELECT COUNT(*) AS total FROM procedimentos")->fetch()['total'];
$totalCriticos = (int)$pdo->query("SELECT COUNT(*) AS total FROM procedimentos WHERE nivel_gravidade = 'CRITICA'")->fetch()['total'];
$totalLogs = (int)$pdo->query("SELECT COUNT(*) AS total FROM logs_auditoria")->fetch()['total'];
$totalUsuarios = (int)$pdo->query("SELECT COUNT(*) AS total FROM usuarios")->fetch()['total'];

// Últimos 8 eventos de auditoria
$stmtUltimosLogs = $pdo->query("SELECT * FROM logs_auditoria ORDER BY data_hora DESC LIMIT 8");
$ultimosLogs = $stmtUltimosLogs->fetchAll();

$tituloPagina = 'Painel do Laboratório | SOS Química';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Boas-Vindas e Ações Rápidas (Verde Musgo) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-musgo-100 dark:border-stone-800 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-musgo-100 dark:bg-musgo-950/60 text-musgo-800 dark:text-musgo-300 text-xs font-bold mb-2 border border-musgo-300 dark:border-musgo-800">
                <i class="fas fa-circle-check text-musgo-600"></i> Sessão Autenticada
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                Painel de Gestão do Laboratório
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                Conectado como <strong class="text-stone-800 dark:text-stone-200"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong> (<?= htmlspecialchars($_SESSION['usuario_cargo'] ?? 'Monitor') ?>).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="<?= url('admin/form_procedimento.php') ?>" class="px-4 py-2.5 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-musgo-700/20 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Procedimento
            </a>
            <a href="<?= url('admin/gerenciar.php') ?>" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs sm:text-sm transition-all flex items-center gap-2">
                <i class="fas fa-list-check"></i> Gerenciar Lista
            </a>
        </div>
    </div>

    <!-- CARDS DE ESTATÍSTICAS (MÉTRICAS DO SISTEMA) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <div class="bg-white dark:bg-stone-900 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-stone-400 tracking-wider">Procedimentos</span>
                <div class="w-8 h-8 rounded-lg bg-musgo-100 dark:bg-musgo-950/60 text-musgo-700 flex items-center justify-center text-sm">
                    <i class="fas fa-book-medical"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">
                <?= $totalProc ?>
            </div>
            <p class="text-[11px] text-stone-500">Protocolos ativos no sistema</p>
        </div>

        <div class="bg-white dark:bg-stone-900 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-stone-400 tracking-wider">Gravidade Crítica</span>
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-950/60 text-red-600 flex items-center justify-center text-sm">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-red-600">
                <?= $totalCriticos ?>
            </div>
            <p class="text-[11px] text-stone-500">Exigem resposta < 1 minuto</p>
        </div>

        <div class="bg-white dark:bg-stone-900 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-stone-400 tracking-wider">Logs de Auditoria</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">
                <?= $totalLogs ?>
            </div>
            <p class="text-[11px] text-stone-500">Ações rastreadas para auditoria</p>
        </div>

        <div class="bg-white dark:bg-stone-900 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-stone-400 tracking-wider">Monitores / Admins</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-users-gear"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white">
                <?= $totalUsuarios ?>
            </div>
            <p class="text-[11px] text-stone-500">Senhas tratadas com bcrypt</p>
        </div>

    </div>

    <!-- SEÇÃO DUPLA: ATALHOS DE GESTÃO + AUDITORIA RECENTE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna 1: Ações do Painel -->
        <div class="space-y-4">
            <h2 class="text-sm font-black uppercase tracking-wider text-stone-400">
                Menu de Administração
            </h2>

            <div class="space-y-2.5">
                <a href="<?= url('admin/form_procedimento.php') ?>" class="p-4 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-musgo-500 shadow-sm flex items-center gap-3.5 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-musgo-100 dark:bg-musgo-950/60 text-musgo-700 group-hover:bg-musgo-700 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-stone-900 dark:text-white">Cadastrar Novo Procedimento</h4>
                        <p class="text-xs text-stone-500">Adicione novo protocolo com auditoria</p>
                    </div>
                </a>

                <a href="<?= url('admin/gerenciar.php') ?>" class="p-4 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-musgo-500 shadow-sm flex items-center gap-3.5 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 group-hover:bg-stone-900 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-stone-900 dark:text-white">Gerenciar Procedimentos</h4>
                        <p class="text-xs text-stone-500">Edite, exclua e audite protocolos</p>
                    </div>
                </a>

                <a href="<?= url('historico.php') ?>" class="p-4 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-musgo-500 shadow-sm flex items-center gap-3.5 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-stone-900 dark:text-white">Relatório Completo de Auditoria</h4>
                        <p class="text-xs text-stone-500">Consulte logs para compliance</p>
                    </div>
                </a>

                <a href="<?= url('admin/usuarios.php') ?>" class="p-4 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 hover:border-musgo-500 shadow-sm flex items-center gap-3.5 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-stone-900 dark:text-white">Gerenciar Monitores (Bcrypt)</h4>
                        <p class="text-xs text-stone-500">Controle de acessos com hash seguro</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Coluna 2: Últimos Eventos de Auditoria -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-wider text-stone-400">
                    Últimas Alterações e Acessos
                </h2>
                <a href="<?= url('historico.php') ?>" class="text-xs font-bold text-musgo-700 hover:text-musgo-800 dark:text-musgo-400">
                    Ver todos os logs <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm divide-y divide-stone-100 dark:divide-stone-800 overflow-hidden">
                <?php foreach ($ultimosLogs as $log): ?>
                    <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3 truncate">
                            <span class="w-2 h-2 rounded-full shrink-0 <?= $log['acao'] === 'DELETE' ? 'bg-red-500' : ($log['acao'] === 'UPDATE' ? 'bg-blue-500' : 'bg-musgo-600') ?>"></span>
                            <div class="truncate">
                                <span class="font-bold text-stone-900 dark:text-white block truncate"><?= htmlspecialchars($log['descricao']) ?></span>
                                <span class="text-[11px] text-stone-400"><?= htmlspecialchars($log['usuario_nome']) ?> • <?= formatarDataHora($log['data_hora']) ?></span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <?php if (!empty($log['dados_anteriores']) || !empty($log['dados_novos'])): ?>
                                <button 
                                    type="button" 
                                    onclick='modalVerDetalhesLog(
                                        "Auditoria do Evento #<?= $log['id'] ?>",
                                        <?= json_encode($log['descricao']) ?>,
                                        <?= json_encode($log['dados_anteriores']) ?>,
                                        <?= json_encode($log['dados_novos']) ?>
                                    )'
                                    class="px-2.5 py-1 rounded-lg bg-musgo-50 dark:bg-stone-800 hover:bg-musgo-100 dark:hover:bg-stone-700 text-musgo-800 dark:text-musgo-300 font-bold text-[11px]"
                                >
                                    <i class="fas fa-eye mr-1"></i> Ver Dados
                                </button>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-stone-100 dark:bg-stone-800 text-stone-500">
                                    <?= htmlspecialchars($log['acao']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
