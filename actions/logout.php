<?php
/**
 * SOS QUÍMICA - ENCERRAMENTO SEGURO DE SESSÃO (LOGOUT)
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita"
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

if (usuarioEstaLogado()) {
    $pdo = getConexao();
    registrarLogAuditoria(
        $pdo,
        'LOGOUT',
        'usuarios',
        $_SESSION['usuario_id'],
        "Encerramento de sessão voluntário do usuário '{$_SESSION['usuario_nome']}'."
    );
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Destrói a sessão
if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

// Reinicia sessão limpa para exibir toast
session_start();
definirToast('info', 'Sessão encerrada com segurança.', 'Desconectado');
header('Location: ' . url('index.php'));
exit;
