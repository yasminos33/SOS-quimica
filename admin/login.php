<?php
/**
 * SOS QUÍMICA - TELA DE LOGIN PARA MONITORES E PROFESSORES
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "no site nao sera necessario o login , o usuario tera que selecionar ou digitar o problema"
 * Regra: "Tratar senhas de usuários com hash bcrypt"
 * Regra: "Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist."
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Se já estiver logado, redireciona ao painel
if (usuarioEstaLogado()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$tituloPagina = 'Acesso Docente / Monitor | SOS Química';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[75vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Cartão de Advertência SOS -->
        <div class="bg-musgo-50 dark:bg-musgo-950/40 border border-musgo-200 dark:border-musgo-800 rounded-2xl p-4 text-xs text-musgo-900 dark:text-musgo-200 flex items-start gap-3">
            <i class="fas fa-circle-info text-musgo-600 text-base mt-0.5 shrink-0"></i>
            <div>
                <strong class="font-bold">Aviso para Estudantes:</strong>
                O uso do sistema de socorro a acidentes químicos <strong>NÃO requer login</strong>. 
                Se você está passando por uma emergência, <a href="<?= url('index.php') ?>" class="font-bold underline text-musgo-700 hover:text-musgo-800 dark:text-musgo-300">clique aqui para voltar à tela SOS Imediata</a>.
            </div>
        </div>

        <!-- Formulário de Login Administrativo -->
        <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xl p-6 sm:p-8 space-y-6">
            
            <div class="text-center space-y-2">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-musgo-100 dark:bg-musgo-950/60 text-musgo-700 dark:text-musgo-300 flex items-center justify-center text-2xl shadow-sm border border-musgo-200">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="text-2xl font-black text-stone-900 dark:text-white tracking-tight">
                    Acesso Restrito
                </h1>
                <p class="text-xs text-stone-500 dark:text-stone-400">
                    Área para professores, monitores e técnicos de laboratório gerenciarem protocolos e auditoria.
                </p>
            </div>

            <form method="POST" action="<?= url('actions/login.php') ?>" class="space-y-4">
                
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5">
                        E-mail Cadastrado:
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-musgo-600"><i class="fas fa-envelope"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            required 
                            placeholder="ex: admin@sosquimica.com" 
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                        >
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                            Senha de Acesso:
                        </label>
                        <span class="text-[10px] text-musgo-600 font-medium">Bcrypt protegido</span>
                    </div>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-musgo-600"><i class="fas fa-key"></i></span>
                        <input 
                            type="password" 
                            name="senha" 
                            id="senha" 
                            required 
                            placeholder="••••••••" 
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:outline-none focus:ring-2 focus:ring-musgo-600 font-medium"
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-musgo-700 hover:bg-musgo-800 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-musgo-700/25 transition-all flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-right-to-bracket"></i>
                        <span>Entrar no Painel</span>
                    </button>
                </div>

            </form>

            <!-- Credenciais de Demonstração / Instalação -->
            <div class="p-4 rounded-2xl bg-musgo-50/60 dark:bg-stone-800/60 border border-musgo-200 dark:border-stone-800 text-xs text-stone-600 dark:text-stone-400 space-y-1">
                <span class="font-bold text-musgo-900 dark:text-musgo-200 block text-[11px] uppercase tracking-wider">
                    <i class="fas fa-key text-musgo-600 mr-1"></i> Acesso Padrão de Demonstração:
                </span>
                <div><strong>E-mail:</strong> <code class="bg-white dark:bg-stone-900 px-1.5 py-0.5 rounded border border-stone-200 dark:border-stone-700 text-musgo-700 font-bold">admin@sosquimica.com</code></div>
                <div><strong>Senha:</strong> <code class="bg-white dark:bg-stone-900 px-1.5 py-0.5 rounded border border-stone-200 dark:border-stone-700 text-musgo-700 font-bold">admin123</code></div>
            </div>

            <div class="text-center">
                <a href="<?= url('index.php') ?>" class="text-xs font-bold text-stone-500 hover:text-stone-800 dark:hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar à Consulta Pública de Emergência
                </a>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
