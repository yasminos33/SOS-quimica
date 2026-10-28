<?php
/**
 * SOS QUÍMICA - CABEÇALHO GLOBAL (MobileFirst + Tailwind CSS)
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist."
 * Regra: "no site nao sera necessario o login , o usuario tera que selecionar ou digitar o problema"
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/funcoes.php';

$paginaAtual = basename($_SERVER['PHP_SELF']);
$usuarioLogado = usuarioEstaLogado();
$tituloPagina = $tituloPagina ?? 'SOS Química | Atendimento Imediato a Acidentes de Laboratório';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>

    <!-- Tailwind CSS CDN com Paleta Verde Musgo -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        musgo: {
                            50: '#f3f6f3',
                            100: '#e3ece3',
                            200: '#c8d9c8',
                            300: '#a3c0a3',
                            400: '#7aa27a',
                            500: '#578457',
                            600: '#426b42',
                            700: '#355535', // Verde Musgo Clássico
                            800: '#2c442c', // Verde Musgo Profundo
                            900: '#253925',
                            950: '#121e12',
                        }
                    },
                    animation: {
                        'pulse-fast': 'pulse 1.2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Google Fonts Inter / Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Estilo Customizado Adicional -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        code, pre {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-stone-50 dark:bg-stone-950 text-stone-800 dark:text-stone-100 flex flex-col min-h-screen antialiased selection:bg-musgo-600 selection:text-white">

    <!-- BARRA DE EMERGÊNCIA CRÍTICA (PALETA VERDE MUSGO PROFUNDO) -->
    <div class="bg-gradient-to-r from-musgo-950 via-musgo-900 to-musgo-950 text-white text-xs font-semibold py-2 px-3 shadow-md sticky top-0 z-40 border-b border-musgo-800/80">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                </span>
                <span class="uppercase tracking-wider font-extrabold text-[11px] sm:text-xs text-musgo-100">Linha Direta de Emergência:</span>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 overflow-x-auto text-[11px] sm:text-xs py-0.5">
                <a href="tel:192" class="bg-white/10 hover:bg-white/20 px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5 whitespace-nowrap font-bold text-emerald-200 hover:text-white" title="Ligue para o SAMU">
                    <i class="fas fa-truck-medical text-yellow-300"></i> SAMU: 192
                </a>
                <a href="tel:193" class="bg-white/10 hover:bg-white/20 px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5 whitespace-nowrap font-bold text-emerald-200 hover:text-white" title="Ligue para os Bombeiros">
                    <i class="fas fa-fire-extinguisher text-amber-300"></i> Bombeiros: 193
                </a>
                <a href="tel:08007226001" class="bg-white/10 hover:bg-white/20 px-2.5 py-1 rounded-md transition-colors flex items-center gap-1.5 whitespace-nowrap font-bold hidden md:inline-flex text-emerald-200 hover:text-white" title="Centro de Informação Toxicológica Nacional">
                    <i class="fas fa-headset text-emerald-300"></i> CIT Intoxicações: 0800 722 6001
                </a>
            </div>
        </div>
    </div>

    <!-- NAVEGAÇÃO PRINCIPAL (VERDE MUSGO) -->
    <header class="bg-white dark:bg-stone-900 border-b border-musgo-100 dark:border-musgo-900 shadow-sm relative z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo Marca Verde Musgo -->
                <div class="flex items-center gap-3">
                    <a href="<?= url('index.php') ?>" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-tr from-musgo-800 to-musgo-600 flex items-center justify-center text-white shadow-lg shadow-musgo-900/25 group-hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-flask-vial text-lg sm:text-xl text-musgo-100"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-lg sm:text-2xl font-black tracking-tight text-stone-900 dark:text-white">SOS</span>
                                <span class="text-lg sm:text-2xl font-black tracking-tight text-musgo-700 dark:text-musgo-400">Química</span>
                            </div>
                            <p class="text-[10px] sm:text-xs text-stone-500 dark:text-stone-400 font-medium">Guia Rápido de Acidentes em Laboratório</p>
                        </div>
                    </a>
                </div>

                <!-- Links Desktop -->
                <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                    <a href="<?= url('index.php') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 <?= $paginaAtual === 'index.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/70 dark:bg-musgo-950/70 font-bold' : 'text-stone-600 dark:text-stone-300 hover:text-musgo-800 dark:hover:text-white hover:bg-musgo-50 dark:hover:bg-musgo-950/40' ?>">
                        <i class="fas fa-kit-medical text-musgo-600"></i> SOS Imediato
                    </a>

                    <a href="<?= url('pesquisa.php') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 <?= $paginaAtual === 'pesquisa.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/70 dark:bg-musgo-950/70 font-bold' : 'text-stone-600 dark:text-stone-300 hover:text-musgo-800 dark:hover:text-white hover:bg-musgo-50 dark:hover:bg-musgo-950/40' ?>">
                        <i class="fas fa-magnifying-glass text-musgo-600"></i> Pesquisa & Reagentes
                    </a>

                    <a href="<?= url('historico.php') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 <?= $paginaAtual === 'historico.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/70 dark:bg-musgo-950/70 font-bold' : 'text-stone-600 dark:text-stone-300 hover:text-musgo-800 dark:hover:text-white hover:bg-musgo-50 dark:hover:bg-musgo-950/40' ?>">
                        <i class="fas fa-clipboard-list text-musgo-600"></i> Histórico & Auditoria
                    </a>

                    <div class="h-6 w-px bg-stone-200 dark:bg-stone-700 mx-2"></div>

                    <?php if ($usuarioLogado): ?>
                        <a href="<?= url('admin/index.php') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold text-musgo-900 dark:text-musgo-200 bg-musgo-100/80 dark:bg-musgo-900/60 hover:bg-musgo-200/80 dark:hover:bg-musgo-800 flex items-center gap-2">
                            <i class="fas fa-user-shield text-musgo-700 dark:text-musgo-300"></i> Painel Admin
                        </a>
                        <a href="<?= url('actions/logout.php') ?>" class="p-2 rounded-lg text-stone-500 hover:text-red-600 dark:text-stone-400 dark:hover:text-red-400 transition-colors" title="Encerrar Sessão">
                            <i class="fas fa-right-from-bracket"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= url('admin/login.php') ?>" class="px-3.5 py-2 rounded-lg text-xs font-bold text-musgo-800 dark:text-musgo-300 border border-musgo-300 dark:border-musgo-700 hover:bg-musgo-50 dark:hover:bg-musgo-950/40 flex items-center gap-1.5 transition-colors">
                            <i class="fas fa-lock text-musgo-600"></i> Acesso Docente / Monitor
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Botão Menu Mobile -->
                <div class="flex items-center gap-2 md:hidden">
                    <button type="button" onclick="alternarMenuMobile()" class="p-2 rounded-xl text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors" aria-label="Abrir menu">
                        <i id="icone-menu-mobile" class="fas fa-bars text-xl"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Menu Dropdown Mobile -->
        <div id="menu-mobile" class="hidden md:hidden border-t border-musgo-100 dark:border-musgo-900 bg-white dark:bg-stone-900 px-4 py-3 space-y-2">
            <a href="<?= url('index.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= $paginaAtual === 'index.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/80 dark:bg-musgo-950/70 font-bold' : 'text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800' ?>">
                <i class="fas fa-kit-medical mr-2 text-musgo-600"></i> SOS Imediato (Emergência)
            </a>
            <a href="<?= url('pesquisa.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= $paginaAtual === 'pesquisa.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/80 dark:bg-musgo-950/70 font-bold' : 'text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800' ?>">
                <i class="fas fa-magnifying-glass mr-2 text-musgo-600"></i> Pesquisa & Reagentes
            </a>
            <a href="<?= url('historico.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold <?= $paginaAtual === 'historico.php' ? 'text-musgo-800 dark:text-musgo-300 bg-musgo-100/80 dark:bg-musgo-950/70 font-bold' : 'text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800' ?>">
                <i class="fas fa-clipboard-list mr-2 text-musgo-600"></i> Histórico & Auditoria
            </a>

            <div class="border-t border-stone-100 dark:border-stone-800 my-2 pt-2">
                <?php if ($usuarioLogado): ?>
                    <a href="<?= url('admin/index.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-musgo-800 dark:text-musgo-300 bg-musgo-50 dark:bg-musgo-950/40">
                        <i class="fas fa-user-shield mr-2"></i> Painel do Laboratório
                    </a>
                    <a href="<?= url('actions/logout.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-red-600">
                        <i class="fas fa-right-from-bracket mr-2"></i> Encerrar Sessão
                    </a>
                <?php else: ?>
                    <a href="<?= url('admin/login.php') ?>" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-stone-700 dark:text-stone-300">
                        <i class="fas fa-lock mr-2 text-stone-400"></i> Acesso Docente / Monitor
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        function alternarMenuMobile() {
            const menu = document.getElementById('menu-mobile');
            const icone = document.getElementById('icone-menu-mobile');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icone.classList.replace('fa-bars', 'fa-times');
            } else {
                menu.classList.add('hidden');
                icone.classList.replace('fa-times', 'fa-bars');
            }
        }
    </script>

    <!-- Componentes de Diálogo e Mensagens Obrigatórios -->
    <?php require_once __DIR__ . '/toast.php'; ?>
    <?php require_once __DIR__ . '/modal.php'; ?>

    <!-- Conteúdo Dinâmico Principal -->
    <main class="flex-1">
