<?php
/**
 * SOS QUÍMICA - GERENCIAMENTO DE PROCEDIMENTOS (CRUD)
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$pdo = getConexao();

// Busca procedimentos completos
$sql = "SELECT p.*, c.nome AS categoria_nome, c.icone AS categoria_icone 
        FROM procedimentos p
        INNER JOIN categorias_acidentes c ON p.categoria_id = c.id
        ORDER BY p.id DESC";
$procedimentos = $pdo->query($sql)->fetchAll();

$tituloPagina = 'Gerenciar Procedimentos de Emergência | SOS Química';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Topo da Gestão (Verde Musgo) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-musgo-100 dark:border-stone-800 pb-5">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= url('admin/index.php') ?>" class="text-xs text-stone-500 hover:text-stone-700 dark:hover:text-stone-300">
                    <i class="fas fa-arrow-left mr-1"></i> Painel Principal
                </a>
                <span class="text-stone-300">/</span>
                <span class="text-xs font-bold text-musgo-700 dark:text-musgo-400">Procedimentos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                Gerenciar Protocolos de Emergência
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-0.5">
                Cadastre, edite ou remova protocolos. Todas as ações geram histórico em logs de auditoria.
            </p>
        </div>

        <div>
            <a href="<?= url('admin/form_procedimento.php') ?>" class="px-5 py-2.5 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-musgo-700/20 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Procedimento
            </a>
        </div>
    </div>

    <!-- LISTAGEM (RESPONSIVA MOBILE-FIRST) -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        
        <?php if (empty($procedimentos)): ?>
            <div class="p-12 text-center text-stone-500">
                <i class="fas fa-kit-medical text-4xl mb-3 text-musgo-400"></i>
                <p class="text-sm font-semibold">Nenhum procedimento cadastrado no momento.</p>
                <a href="<?= url('admin/form_procedimento.php') ?>" class="mt-3 inline-block px-4 py-2 bg-musgo-700 text-white rounded-xl text-xs font-bold">
                    Cadastrar o primeiro
                </a>
            </div>
        <?php else: ?>

            <!-- Versão Desktop: Tabela Completa -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-musgo-50/60 dark:bg-stone-800/80 border-b border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">ID</th>
                            <th class="py-3.5 px-4">Título do Procedimento</th>
                            <th class="py-3.5 px-4">Categoria</th>
                            <th class="py-3.5 px-4">Reagentes Típicos</th>
                            <th class="py-3.5 px-4">Gravidade</th>
                            <th class="py-3.5 px-4 text-right">Ações Rápidas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-700 dark:text-stone-300 font-medium">
                        <?php foreach ($procedimentos as $p): ?>
                            <tr class="hover:bg-musgo-50/40 dark:hover:bg-stone-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-mono text-stone-400">
                                    #<?= $p['id'] ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-stone-900 dark:text-white max-w-xs">
                                    <a href="<?= url('detalhes.php?id=' . $p['id']) ?>" class="hover:text-musgo-700 dark:hover:text-musgo-400 transition-colors">
                                        <?= htmlspecialchars($p['titulo']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-stone-600 dark:text-stone-400">
                                        <i class="fas <?= htmlspecialchars($p['categoria_icone']) ?> text-musgo-600"></i>
                                        <?= htmlspecialchars($p['categoria_nome']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-stone-500 max-w-xs truncate" title="<?= htmlspecialchars($p['reagente_comum']) ?>">
                                    <?= htmlspecialchars($p['reagente_comum']) ?>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <?= getBadgeGravidade($p['nivel_gravidade']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="<?= url('detalhes.php?id=' . $p['id']) ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-musgo-100 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-600 dark:text-stone-300" title="Ver Protocolo">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="<?= url('admin/form_procedimento.php?id=' . $p['id']) ?>" class="p-1.5 rounded-lg bg-musgo-100 hover:bg-musgo-200 dark:bg-musgo-950/60 dark:hover:bg-musgo-900 text-musgo-800 dark:text-musgo-300" title="Editar">
                                            <i class="fas fa-pen-to-square text-xs"></i>
                                        </a>
                                        <!-- Botão que aciona o MODAL seguro de confirmação (sem confirm nativo) -->
                                        <a 
                                            href="<?= url('actions/excluir_procedimento.php?id=' . $p['id']) ?>" 
                                            data-confirm="Tem certeza de que deseja excluir o procedimento '<?= htmlspecialchars(addslashes($p['titulo'])) ?>'? O registro completo será preservado no log de auditoria."
                                            data-confirm-title="Confirmar Exclusão de Protocolo"
                                            class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-950/60 dark:hover:bg-red-900 text-red-600 dark:text-red-400" 
                                            title="Excluir com Auditoria"
                                        >
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Versão Mobile: Cards -->
            <div class="md:hidden divide-y divide-stone-100 dark:divide-stone-800">
                <?php foreach ($procedimentos as $p): ?>
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-stone-400 font-mono">ID #<?= $p['id'] ?></span>
                            <?= getBadgeGravidade($p['nivel_gravidade']) ?>
                        </div>

                        <h3 class="text-sm font-bold text-stone-900 dark:text-white">
                            <?= htmlspecialchars($p['titulo']) ?>
                        </h3>

                        <div class="text-xs text-stone-500 space-y-0.5">
                            <div><strong class="text-stone-700 dark:text-stone-300">Categoria:</strong> <?= htmlspecialchars($p['categoria_nome']) ?></div>
                            <div class="truncate"><strong class="text-stone-700 dark:text-stone-300">Reagentes:</strong> <?= htmlspecialchars($p['reagente_comum']) ?></div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100 dark:border-stone-800">
                            <a href="<?= url('detalhes.php?id=' . $p['id']) ?>" class="px-3 py-1.5 rounded-lg bg-stone-100 dark:bg-stone-800 text-xs font-semibold">
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                            <a href="<?= url('admin/form_procedimento.php?id=' . $p['id']) ?>" class="px-3 py-1.5 rounded-lg bg-musgo-100 dark:bg-musgo-950/80 text-musgo-800 dark:text-musgo-300 text-xs font-semibold">
                                <i class="fas fa-pen mr-1"></i> Editar
                            </a>
                            <a 
                                href="<?= url('actions/excluir_procedimento.php?id=' . $p['id']) ?>" 
                                data-confirm="Deseja realmente remover o procedimento '<?= htmlspecialchars(addslashes($p['titulo'])) ?>'?"
                                data-confirm-title="Confirmar Exclusão"
                                class="px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-950/80 text-red-700 dark:text-red-300 text-xs font-semibold"
                            >
                                <i class="fas fa-trash-can mr-1"></i> Excluir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
