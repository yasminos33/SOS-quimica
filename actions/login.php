<?php
/**
 * SOS QUÍMICA - PROCESSAMENTO DE LOGIN DE MONITOR / DOCENTE
 * 
 * Regra: "Tratar senhas de usuários com hash bcrypt"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/login.php'));
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');

if (!$email || empty($senha)) {
    definirToast('error', 'Por favor, informe um e-mail válido e a sua senha.', 'Dados Incompletos');
    header('Location: ' . url('admin/login.php'));
    exit;
}

$pdo = getConexao();

// Busca usuário por e-mail com PDO seguro
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

// Verificação de senha com BCRYPT nativo
if ($usuario && password_verify($senha, $usuario['senha'])) {
    
    // Autenticação bem-sucedida: inicia sessão segura
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_cargo'] = $usuario['cargo'];

    // Registrar log de auditoria
    registrarLogAuditoria(
        $pdo,
        'LOGIN',
        'usuarios',
        $usuario['id'],
        "Autenticação bem-sucedida do usuário '{$usuario['nome']}' ({$usuario['email']})."
    );

    definirToast('success', "Bem-vindo de volta, {$usuario['nome']}!", 'Acesso Autorizado');
    header('Location: ' . url('admin/index.php'));
    exit;

} else {
    // Falha na autenticação
    registrarLogAuditoria(
        $pdo,
        'LOGIN',
        'usuarios',
        null,
        "Tentativa de login malsucedida para o e-mail: '{$email}'."
    );

    definirToast('error', 'E-mail ou senha incorretos. Verifique suas credenciais de acesso.', 'Falha de Autenticação');
    header('Location: ' . url('admin/login.php'));
    exit;
}
