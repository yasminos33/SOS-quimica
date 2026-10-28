<?php
/**
 * SOS QUÍMICA - GESTÃO DE USUÁRIOS (MONITORES / PROFESSORES)
 * Paleta: Verde Musgo Profissional
 * 
 * Regra: "Tratar senhas de usuários com hash bcrypt"
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$pdo = getConexao();

// Busca lista de usuários
$usuarios = $pdo->query("SELECT id, nome, email, cargo, ativo, criado_em, atualizado_em FROM usuarios ORDER BY id ASC")->fetchAll();

$tituloPagina = 'Gestão de Usuários & Senhas Bcrypt | SOS Química';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Topo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-musgo-100 dark:border-stone-800 pb-5">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= url('admin/index.php') ?>" class="text-xs text-stone-500 hover:text-stone-700 dark:hover:text-stone-300">
                    <i class="fas fa-arrow-left mr-1"></i> Painel
                </a>
                <span class="text-stone-300">/</span>
                <span class="text-xs font-bold text-musgo-700 dark:text-musgo-400">Usuários</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 dark:text-white tracking-tight">
                Controle de Acesso & Usuários
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-0.5">
                Todas as senhas são obrigatoriamente criptografadas com o algoritmo <strong>BCRYPT</strong>.
            </p>
        </div>

        <button 
            type="button" 
            onclick="abrirFormNovoUsuario()" 
            class="px-5 py-2.5 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-musgo-700/20 transition-all flex items-center gap-2"
        >
            <i class="fas fa-user-plus"></i> Novo Usuário
        </button>
    </div>

    <!-- FORMULÁRIO DE CADASTRO / EDIÇÃO RÁPIDA (TOGGLE) -->
    <div id="container-form-usuario" class="hidden bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 p-6 shadow-md">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-100 dark:border-stone-800">
            <h3 id="form-usuario-titulo" class="font-bold text-base text-stone-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-shield text-musgo-600"></i> Cadastrar Novo Monitor / Professor
            </h3>
            <button type="button" onclick="fecharFormUsuario()" class="text-stone-400 hover:text-stone-600 p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="<?= url('actions/salvar_usuario.php') ?>" class="space-y-4">
            <input type="hidden" name="id" id="usuario_id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">
                        Nome Completo *
                    </label>
                    <input type="text" name="nome" id="nome" required class="w-full px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:ring-2 focus:ring-musgo-600">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">
                        E-mail de Acesso *
                    </label>
                    <input type="email" name="email" id="email" required class="w-full px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:ring-2 focus:ring-musgo-600">
                </div>

                <div>
                    <label for="cargo" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">
                        Cargo / Função
                    </label>
                    <input type="text" name="cargo" id="cargo" value="Monitor de Laboratório" class="w-full px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:ring-2 focus:ring-musgo-600">
                </div>

                <div>
                    <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">
                        Senha (Hash Bcrypt) <span id="hint-senha" class="text-stone-400 text-[10px] lowercase">(mínimo 6 caracteres)</span>
                    </label>
                    <input type="password" name="senha" id="senha" placeholder="••••••••" class="w-full px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm focus:ring-2 focus:ring-musgo-600">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="ativo" id="ativo" value="1" checked class="rounded border-stone-300 text-musgo-700 focus:ring-musgo-600">
                <label for="ativo" class="text-xs font-semibold text-stone-700 dark:text-stone-300">
                    Usuário com acesso ativo
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-stone-100 dark:border-stone-800">
                <button type="button" onclick="fecharFormUsuario()" class="px-4 py-2 rounded-xl border border-stone-300 text-xs font-bold">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-musgo-700 hover:bg-musgo-800 text-white font-bold text-xs shadow-md">
                    Salvar com Criptografia Bcrypt
                </button>
            </div>
        </form>
    </div>

    <!-- LISTA DE USUÁRIOS -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-musgo-50/60 dark:bg-stone-800/80 border-b border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">ID</th>
                        <th class="py-3.5 px-4">Nome</th>
                        <th class="py-3.5 px-4">E-mail</th>
                        <th class="py-3.5 px-4">Cargo</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Criptografia</th>
                        <th class="py-3.5 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-stone-700 dark:text-stone-300 font-medium">
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="hover:bg-musgo-50/40 dark:hover:bg-stone-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-stone-400">#<?= $u['id'] ?></td>
                            <td class="py-3.5 px-4 font-bold text-stone-900 dark:text-white">
                                <?= htmlspecialchars($u['nome']) ?>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-stone-600 dark:text-stone-300">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td class="py-3.5 px-4"><?= htmlspecialchars($u['cargo']) ?></td>
                            <td class="py-3.5 px-4">
                                <?php if ($u['ativo']): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        ATIVO
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600">
                                        INATIVO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 font-mono text-[11px] text-musgo-700 dark:text-musgo-400 bg-musgo-100/70 dark:bg-musgo-950/60 px-2 py-0.5 rounded">
                                    <i class="fas fa-lock text-[10px]"></i> BCRYPT
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button 
                                    type="button" 
                                    onclick='prepararEdicaoUsuario(<?= json_encode($u) ?>)'
                                    class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-musgo-100 dark:bg-stone-800 text-xs font-bold"
                                >
                                    <i class="fas fa-pen text-[10px] mr-1"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function abrirFormNovoUsuario() {
    document.getElementById('container-form-usuario').classList.remove('hidden');
    document.getElementById('form-usuario-titulo').innerHTML = '<i class="fas fa-user-plus text-musgo-600"></i> Cadastrar Novo Monitor / Professor';
    document.getElementById('usuario_id').value = '';
    document.getElementById('nome').value = '';
    document.getElementById('email').value = '';
    document.getElementById('cargo').value = 'Monitor de Laboratório';
    document.getElementById('senha').value = '';
    document.getElementById('senha').required = true;
    document.getElementById('hint-senha').textContent = '(mínimo 6 caracteres)';
    document.getElementById('ativo').checked = true;
    window.scrollTo({ top: 100, behavior: 'smooth' });
}

function fecharFormUsuario() {
    document.getElementById('container-form-usuario').classList.add('hidden');
}

function prepararEdicaoUsuario(usuario) {
    document.getElementById('container-form-usuario').classList.remove('hidden');
    document.getElementById('form-usuario-titulo').innerHTML = '<i class="fas fa-user-pen text-musgo-700"></i> Editar Usuário #' + usuario.id;
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('nome').value = usuario.nome;
    document.getElementById('email').value = usuario.email;
    document.getElementById('cargo').value = usuario.cargo;
    document.getElementById('senha').value = '';
    document.getElementById('senha').required = false;
    document.getElementById('hint-senha').textContent = '(deixe em branco para manter a senha atual)';
    document.getElementById('ativo').checked = usuario.ativo == 1;
    window.scrollTo({ top: 100, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
