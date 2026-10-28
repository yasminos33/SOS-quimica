<?php
/**
 * SOS QUÍMICA - DETALHES COMPLETOS DO PROCEDIMENTO DE SOCORRO
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist."
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$pdo = getConexao();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definirToast('error', 'Procedimento não especificado ou inválido.', 'Erro de Parâmetro');
    header('Location: ' . url('index.php'));
    exit;
}

// Consulta segura com PDO Prepared Statements
$sql = "SELECT p.*, c.nome AS categoria_nome, c.icone AS categoria_icone, c.cor AS categoria_cor, u.nome AS autor_nome
        FROM procedimentos p
        INNER JOIN categorias_acidentes c ON p.categoria_id = c.id
        LEFT JOIN usuarios u ON p.criado_por = u.id
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$procedimento = $stmt->fetch();

if (!$procedimento) {
    definirToast('error', 'O procedimento solicitado não foi encontrado no banco de dados.', 'Registro Inexistente');
    header('Location: ' . url('index.php'));
    exit;
}

// Registra auditoria da consulta de emergência
registrarLogAuditoria(
    $pdo,
    'SEARCH',
    'procedimentos',
    $procedimento['id'],
    "Visualização detalhada do protocolo de emergência: '{$procedimento['titulo']}'"
);

$tituloPagina = "SOS: {$procedimento['titulo']} | SOS Química";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Navegação de Retorno e Ações Rápidas -->
    <div class="flex items-center justify-between gap-4 nao-imprimir">
        <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-stone-600 dark:text-stone-300 hover:text-musgo-700 dark:hover:text-musgo-300 transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar para Emergências
        </a>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="imprimirFichaEmergencia()" 
                class="px-3 py-1.5 rounded-lg border border-stone-300 dark:border-stone-700 hover:bg-musgo-50 dark:hover:bg-stone-800 text-xs font-bold text-stone-700 dark:text-stone-300 flex items-center gap-1.5 transition-colors shadow-sm"
                title="Imprimir Ficha de Laboratório"
            >
                <i class="fas fa-print text-musgo-600"></i> Imprimir Ficha
            </button>
            <button 
                type="button" 
                onclick="copiarParaAreaTransferencia(window.location.href, 'Link do Procedimento')" 
                class="px-3 py-1.5 rounded-lg border border-stone-300 dark:border-stone-700 hover:bg-musgo-50 dark:hover:bg-stone-800 text-xs font-bold text-stone-700 dark:text-stone-300 flex items-center gap-1.5 transition-colors shadow-sm"
                title="Copiar Link"
            >
                <i class="fas fa-share-nodes text-musgo-600"></i> Compartilhar
            </button>
        </div>
    </div>

    <!-- CARTÃO PRINCIPAL DA FICHA DE EMERGÊNCIA (IMPRIMÍVEL) -->
    <div class="imprimir-ficha bg-white dark:bg-stone-900 border-2 border-musgo-600/70 rounded-3xl shadow-xl overflow-hidden">
        
        <!-- Faixa de Alerta Superior (Verde Musgo Profundo) -->
        <div class="bg-gradient-to-r from-musgo-950 via-musgo-900 to-musgo-800 text-white p-6 sm:p-8 border-b border-musgo-700">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <span class="inline-flex items-center gap-2 text-xs uppercase font-extrabold tracking-wider bg-white/10 px-3 py-1 rounded-full text-musgo-100 border border-musgo-600/40">
                    <i class="fas <?= htmlspecialchars($procedimento['categoria_icone']) ?> text-emerald-300"></i>
                    <?= htmlspecialchars($procedimento['categoria_nome']) ?>
                </span>
                <div>
                    <?= getBadgeGravidade($procedimento['nivel_gravidade']) ?>
                </div>
            </div>

            <h1 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight text-white">
                <?= htmlspecialchars($procedimento['titulo']) ?>
            </h1>

            <p class="text-xs sm:text-sm text-musgo-200 mt-2 font-medium">
                Protocolo oficial para resposta imediata de acidentes em laboratório de química.
            </p>
        </div>

        <div class="p-6 sm:p-8 space-y-8">

            <!-- Informações Rápidas de Identificação -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="p-4 rounded-2xl bg-musgo-50/70 dark:bg-stone-800/60 border border-musgo-200/80 dark:border-stone-800">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-musgo-800 dark:text-musgo-300 block mb-1">
                        <i class="fas fa-flask text-musgo-600 mr-1.5"></i> Reagentes Típicos / Envolvidos:
                    </span>
                    <p class="text-sm font-semibold text-stone-900 dark:text-white">
                        <?= htmlspecialchars($procedimento['reagente_comum']) ?>
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-musgo-50/70 dark:bg-stone-800/60 border border-musgo-200/80 dark:border-stone-800">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-musgo-800 dark:text-musgo-300 block mb-1">
                        <i class="fas fa-stethoscope text-musgo-600 mr-1.5"></i> Sintomas / Sinais Visíveis:
                    </span>
                    <p class="text-sm font-semibold text-stone-900 dark:text-white">
                        <?= htmlspecialchars($procedimento['sintomas'] ?: 'Reação imediata ao contato/exposição.') ?>
                    </p>
                </div>

            </div>

            <!-- BLOCO 1: O QUE FAZER (PASSO A PASSO SEQUENCIAL) -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b-2 border-musgo-600">
                    <div class="w-8 h-8 rounded-lg bg-musgo-700 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-stone-900 dark:text-white">
                        O que FAZER imediatamente (Passo a Passo)
                    </h2>
                </div>

                <div class="space-y-3">
                    <?php 
                        $passos = explode("\n", $procedimento['passo_a_passo']);
                        foreach ($passos as $passo):
                            $passoLimpo = trim($passo);
                            if (empty($passoLimpo)) continue;
                    ?>
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-musgo-50/80 dark:bg-musgo-950/30 border border-musgo-200 dark:border-musgo-900/60">
                            <span class="mt-0.5 w-6 h-6 rounded-full bg-musgo-700 text-white font-black text-xs flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-[10px]"></i>
                            </span>
                            <div class="text-sm sm:text-base text-stone-800 dark:text-stone-100 font-medium leading-relaxed">
                                <?= nl2br(htmlspecialchars($passoLimpo)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- BLOCO 2: O QUE NUNCA FAZER (CONDUTAS PROIBIDAS CRÍTICAS) -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b-2 border-amber-600">
                    <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-amber-700 dark:text-amber-400">
                        O que NUNCA fazer (Erros Graves e Fatais)
                    </h2>
                </div>

                <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border-2 border-amber-300 dark:border-amber-800 space-y-3">
                    <?php 
                        $proibidos = explode("\n", $procedimento['o_que_nao_fazer']);
                        foreach ($proibidos as $p):
                            $pLimpo = trim($p);
                            if (empty($pLimpo)) continue;
                    ?>
                        <div class="flex items-start gap-3 text-amber-950 dark:text-amber-200">
                            <i class="fas fa-triangle-exclamation text-amber-600 text-base mt-0.5 shrink-0"></i>
                            <div class="text-xs sm:text-sm font-semibold leading-relaxed">
                                <?= nl2br(htmlspecialchars($pLimpo)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- BLOCO 3: EPIs E EQUIPAMENTOS NECESSÁRIOS -->
            <?php if (!empty($procedimento['equipamento_protecao'])): ?>
                <div class="space-y-3">
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-shield-halved text-musgo-600"></i> EPIs e Equipamentos do Socorrista:
                    </h3>
                    <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/50 border border-stone-200 dark:border-stone-800 text-xs sm:text-sm text-stone-800 dark:text-stone-200 leading-relaxed font-medium">
                        <?= nl2br(htmlspecialchars($procedimento['equipamento_protecao'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BLOCO 4: QUANDO CHAMAR SOCORRO MÉDICO -->
            <?php if (!empty($procedimento['quando_chamar_socorro'])): ?>
                <div class="space-y-3">
                    <h3 class="text-sm sm:text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-phone-volume text-musgo-700"></i> Critérios para Acionar Socorro Externo:
                    </h3>
                    <div class="p-4 rounded-2xl bg-musgo-50/60 dark:bg-musgo-950/30 border border-musgo-200 dark:border-musgo-900 text-xs sm:text-sm text-musgo-950 dark:text-musgo-200 leading-relaxed font-medium">
                        <?= nl2br(htmlspecialchars($procedimento['quando_chamar_socorro'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- LINHA DE CONTATO DIRETO DE EMERGÊNCIA -->
            <div class="p-5 rounded-2xl bg-musgo-950 text-white flex flex-col sm:flex-row items-center justify-between gap-4 border border-musgo-800">
                <div>
                    <h4 class="font-bold text-sm text-emerald-300 uppercase tracking-wider mb-1">
                        <i class="fas fa-bell animate-ping mr-1"></i> Linhas Diretas Oficiais de Urgência
                    </h4>
                    <p class="text-xs text-musgo-100">
                        <?= htmlspecialchars($procedimento['contatos_emergencia']) ?>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="tel:192" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs flex items-center gap-1.5 shadow-md transition-all">
                        <i class="fas fa-phone"></i> LIGAR 192
                    </a>
                    <a href="tel:08007226001" class="px-3 py-2 rounded-xl bg-musgo-800 hover:bg-musgo-700 text-musgo-100 font-bold text-xs flex items-center gap-1.5 transition-all">
                        <i class="fas fa-headset"></i> CIT 0800
                    </a>
                </div>
            </div>

            <!-- Metadados de Auditoria na Página -->
            <div class="pt-4 border-t border-stone-100 dark:border-stone-800 flex flex-wrap items-center justify-between text-[11px] text-stone-400 gap-2">
                <span>Procedimento ID #<?= $procedimento['id'] ?> • Cadastrado por: <?= htmlspecialchars($procedimento['autor_nome'] ?? 'Responsável Técnico') ?></span>
                <span>Última revisão: <?= formatarDataHora($procedimento['atualizado_em']) ?></span>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
