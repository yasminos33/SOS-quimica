<?php
/**
 * SOS QUÍMICA - PÁGINA INICIAL (SOS IMEDIATO)
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "no site nao sera necessario o login , o usuario tera que selecionar ou digitar o problema"
 * Regra: "cria um site que deve mostrar o que deve ser feito quando acontece algum acidente de laboratório"
 * Regra: "sendo rapido e eficiente facil de usar tendo suporte para auxiliar em pesquisas tambem"
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$pdo = getConexao();

// Busca categorias de acidentes
$stmtCat = $pdo->query("SELECT * FROM categorias_acidentes ORDER BY ordem ASC");
$categorias = $stmtCat->fetchAll();

// Busca procedimentos completos com o nome da categoria
$sqlProc = "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug, c.icone AS categoria_icone, c.cor AS categoria_cor 
            FROM procedimentos p
            INNER JOIN categorias_acidentes c ON p.categoria_id = c.id
            ORDER BY 
                CASE p.nivel_gravidade
                    WHEN 'CRITICA' THEN 1
                    WHEN 'ALTA' THEN 2
                    WHEN 'MEDIA' THEN 3
                    ELSE 4
                END ASC, p.id ASC";
$stmtProc = $pdo->query($sqlProc);
$procedimentos = $stmtProc->fetchAll();

// Registrar no log que houve consulta ao SOS (sem travar)
registrarLogAuditoria($pdo, 'SEARCH', 'procedimentos', null, 'Acesso à página principal de SOS Emergência do laboratório.');

$tituloPagina = 'SOS Química | Atendimento Rápido a Acidentes em Laboratório';
require_once __DIR__ . '/includes/header.php';
?>

<!-- SEÇÃO HERO DE EMERGÊNCIA (PALETA VERDE MUSGO) -->
<section class="bg-gradient-to-b from-musgo-100/70 via-stone-50 to-white dark:from-musgo-950/40 dark:via-stone-950 dark:to-stone-900 border-b border-musgo-100 dark:border-musgo-900 pt-8 pb-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center space-y-4">
        
        <!-- Alerta de Pânico Imediato Verde Musgo -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-musgo-100 dark:bg-musgo-950/80 border border-musgo-300 dark:border-musgo-800 text-musgo-800 dark:text-musgo-300 text-xs sm:text-sm font-bold shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-ping"></span>
            <span>ACIDENTE AGORA? NÃO PRECISA DE LOGIN! DIGITE OU CLIQUE ABAIXO</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-black text-stone-900 dark:text-white tracking-tight leading-tight">
            O que fazer em caso de <span class="text-musgo-700 dark:text-musgo-400 underline decoration-musgo-500 decoration-wavy decoration-2">acidente químico?</span>
        </h1>

        <p class="text-sm sm:text-base text-stone-600 dark:text-stone-300 max-w-2xl mx-auto leading-relaxed">
            Consulte instantaneamente o protocolo de primeiros socorros de laboratório. Encontre reagentes, procedimentos de segurança e condutas proibidas.
        </p>

        <!-- CAMPO DE DIGITAÇÃO DO PROBLEMA (BUSCA RÁPIDA LIVE-SEARCH) -->
        <div class="pt-2 max-w-2xl mx-auto">
            <div class="relative flex items-center shadow-xl rounded-2xl overflow-hidden border-2 border-musgo-600 focus-within:ring-4 focus-within:ring-musgo-500/20 transition-all bg-white dark:bg-stone-900">
                <div class="pl-4 text-musgo-600 text-lg">
                    <i class="fas fa-magnifying-glass"></i>
                </div>
                <input 
                    type="text" 
                    id="input-busca-problema" 
                    placeholder="Digite o reagente ou o problema (ex: ácido sulfúrico, queimadura, olhos...)" 
                    class="w-full py-4 pl-3 pr-10 text-sm sm:text-base text-stone-900 dark:text-white bg-transparent focus:outline-none placeholder-stone-400 dark:placeholder-stone-500 font-medium"
                    autocomplete="off"
                >
                <div class="pr-3">
                    <span class="text-[11px] font-bold text-musgo-600 uppercase tracking-widest hidden sm:inline">Busca Live</span>
                </div>
            </div>
            <div class="flex items-center justify-between mt-2 px-1 text-xs text-stone-500 dark:text-stone-400">
                <span id="contador-resultados"><?= count($procedimentos) ?> procedimentos disponíveis</span>
                <span class="text-musgo-700 dark:text-musgo-300 font-semibold"><i class="fas fa-bolt"></i> Resposta Instantânea</span>
            </div>
        </div>

    </div>
</section>

<!-- SELEÇÃO RÁPIDA POR CATEGORIAS (BOTÕES DE ACESSO IMEDIATO) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xs font-black text-stone-400 dark:text-stone-500 uppercase tracking-widest">
            Ou selecione pelo tipo de acidente:
        </h2>
    </div>

    <!-- Grade responsiva Mobile-First de Botões de Emergência -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5 sm:gap-3">
        
        <button 
            type="button" 
            onclick="filtrarPorCategoria('todos')" 
            data-slug="todos"
            class="btn-filtro-categoria ring-2 ring-musgo-600 bg-musgo-100 dark:bg-musgo-950/70 border-musgo-600 p-3 rounded-xl border flex flex-col items-center justify-center text-center gap-1.5 hover:bg-musgo-100 transition-all group"
        >
            <div class="w-8 h-8 rounded-lg bg-musgo-700 text-white flex items-center justify-center text-sm shadow-sm">
                <i class="fas fa-list-check"></i>
            </div>
            <span class="text-[11px] font-bold text-stone-800 dark:text-stone-200 leading-tight">Todos</span>
        </button>

        <?php foreach ($categorias as $cat): ?>
            <button 
                type="button" 
                onclick="filtrarPorCategoria('<?= htmlspecialchars($cat['slug']) ?>')" 
                data-slug="<?= htmlspecialchars($cat['slug']) ?>"
                class="btn-filtro-categoria p-3 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 flex flex-col items-center justify-center text-center gap-1.5 hover:border-musgo-400 hover:bg-musgo-50/50 dark:hover:bg-stone-800 transition-all group"
            >
                <div class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-musgo-700 dark:text-musgo-400 group-hover:bg-musgo-700 group-hover:text-white flex items-center justify-center text-sm transition-colors shadow-sm">
                    <i class="fas <?= htmlspecialchars($cat['icone']) ?>"></i>
                </div>
                <span class="text-[11px] font-bold text-stone-800 dark:text-stone-200 leading-tight line-clamp-2">
                    <?= htmlspecialchars(str_replace(['Queimadura por ', 'Acidental', ' Perigosos', ' Contaminada'], '', $cat['nome'])) ?>
                </span>
            </button>
        <?php endforeach; ?>

    </div>
</section>

<!-- LISTAGEM DOS CARDS DE PROCEDIMENTOS (MOBILE FIRST) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 pb-16">
    
    <!-- Mensagem de Nenhum Resultado Encontrado -->
    <div id="resultado-vazio" class="hidden py-16 text-center space-y-4 max-w-md mx-auto">
        <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-2xl">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-lg font-bold text-stone-900 dark:text-white">Nenhum procedimento exato encontrado</h3>
        <p class="text-xs text-stone-600 dark:text-stone-400">
            Tente buscar por outro termo, ou ligue imediatamente para a central de emergência para instruções diretas.
        </p>
        <div class="flex justify-center gap-3">
            <a href="tel:192" class="px-4 py-2 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs flex items-center gap-2">
                <i class="fas fa-phone"></i> Ligar SAMU 192
            </a>
            <button type="button" onclick="filtrarPorCategoria('todos')" class="px-4 py-2 rounded-xl border border-stone-300 dark:border-stone-700 font-semibold text-xs">
                Ver todos os procedimentos
            </button>
        </div>
    </div>

    <!-- Grade de Procedimentos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($procedimentos as $proc): ?>
            <article 
                class="card-procedimento procedimento-card bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 shadow-sm hover:shadow-md flex flex-col justify-between transition-all"
                data-titulo="<?= htmlspecialchars($proc['titulo']) ?>"
                data-reagentes="<?= htmlspecialchars($proc['reagente_comum']) ?>"
                data-sintomas="<?= htmlspecialchars($proc['sintomas'] ?? '') ?>"
                data-categoria="<?= htmlspecialchars($proc['categoria_slug']) ?>"
            >
                <div class="space-y-3">
                    
                    <!-- Cabeçalho do Card: Categoria e Gravidade -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-500 dark:text-stone-400">
                            <i class="fas <?= htmlspecialchars($proc['categoria_icone']) ?> text-musgo-600"></i>
                            <span class="truncate max-w-[170px]"><?= htmlspecialchars($proc['categoria_nome']) ?></span>
                        </span>
                        <?= getBadgeGravidade($proc['nivel_gravidade']) ?>
                    </div>

                    <!-- Título do Procedimento -->
                    <h3 class="text-base sm:text-lg font-bold text-stone-900 dark:text-white leading-snug">
                        <a href="<?= url('detalhes.php?id=' . $proc['id']) ?>" class="hover:text-musgo-700 dark:hover:text-musgo-400 transition-colors">
                            <?= htmlspecialchars($proc['titulo']) ?>
                        </a>
                    </h3>

                    <!-- Reagentes Típicos -->
                    <div class="p-2.5 rounded-xl bg-musgo-50/60 dark:bg-stone-800/60 border border-musgo-100 dark:border-stone-800 text-xs">
                        <span class="font-bold text-musgo-900 dark:text-musgo-200 block mb-0.5">
                            <i class="fas fa-flask text-musgo-600 mr-1"></i> Reagentes associados:
                        </span>
                        <p class="text-stone-600 dark:text-stone-300 line-clamp-2">
                            <?= htmlspecialchars($proc['reagente_comum']) ?>
                        </p>
                    </div>

                    <!-- Prévia da Primeira Ação de Socorro -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-black uppercase tracking-wider text-musgo-700 dark:text-musgo-400 flex items-center gap-1">
                            <i class="fas fa-hand-holding-medical"></i> Ação Imediata 1:
                        </span>
                        <p class="text-xs text-stone-700 dark:text-stone-300 line-clamp-3 leading-relaxed">
                            <?php 
                                $passos = explode("\n", $proc['passo_a_passo']);
                                echo htmlspecialchars($passos[0] ?? $proc['passo_a_passo']);
                            ?>
                        </p>
                    </div>

                    <!-- Alerta de Conduta Proibida (O que não fazer) -->
                    <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-950 dark:text-amber-200">
                        <span class="font-extrabold uppercase text-[10px] tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1">
                            <i class="fas fa-ban"></i> NUNCA FAÇA:
                        </span>
                        <p class="line-clamp-2 mt-0.5 font-medium">
                            <?php 
                                $proibidos = explode("\n", $proc['o_que_nao_fazer']);
                                echo htmlspecialchars(ltrim($proibidos[0] ?? '', '- '));
                            ?>
                        </p>
                    </div>

                </div>

                <!-- Botão de Ação: Ver Procedimento Completo -->
                <div class="pt-4 mt-4 border-t border-stone-100 dark:border-stone-800">
                    <a 
                        href="<?= url('detalhes.php?id=' . $proc['id']) ?>" 
                        class="w-full py-2.5 px-4 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-musgo-700/30"
                    >
                        <span>Ver Protocolo Completo</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

            </article>
        <?php endforeach; ?>
    </div>

</section>

<!-- BANNER DE ORIENTAÇÃO LEGAL DE LABORATÓRIO -->
<section class="bg-musgo-100/60 dark:bg-musgo-950/30 border-y border-musgo-200 dark:border-musgo-900/60 py-6 px-4">
    <div class="max-w-4xl mx-auto flex items-start gap-3">
        <div class="text-musgo-700 dark:text-musgo-400 text-xl mt-0.5 shrink-0">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="text-xs text-musgo-950 dark:text-musgo-200 leading-relaxed">
            <strong class="font-bold">Regra Geral de Segurança Laboratorial:</strong>
            Em qualquer acidente envolvendo agentes químicos ou térmicos, comunique imediatamente o professor responsável ou o técnico de laboratório da sala. Nenhum procedimento neste sistema substitui a avaliação clínica médica de urgência. Em caso de dúvida, ligue <strong>192 (SAMU)</strong> ou <strong>0800 722 6001 (CIT)</strong>.
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
