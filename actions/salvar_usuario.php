<?php
/**
 * SOS QUÍMICA - SALVAR / EDITAR USUÁRIO MONITOR OU PROFESSOR
 * 
 * Regra: "Tratar senhas de usuários com hash bcrypt"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/usuarios.php'));
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome = trim($_POST['nome'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$cargo = trim($_POST['cargo'] ?? 'Monitor de Laboratório');
$senha = trim($_POST['senha'] ?? '');
$ativo = isset($_POST['ativo']) ? 1 : 0;

if (empty($nome) || !$email) {
    definirToast('error', 'Nome e e-mail válido são campos obrigatórios.', 'Dados Inválidos');
    header('Location: ' . url('admin/usuarios.php'));
    exit;
}

$pdo = getConexao();

try {
    if ($id) {
        // MODO EDIÇÃO
        $stmtOld = $pdo->prepare("SELECT id, nome, email, cargo, ativo FROM usuarios WHERE id = ?");
        $stmtOld->execute([$id]);
        $dadosAntigos = $stmtOld->fetch();

        if (!empty($senha)) {
            // Se nova senha foi informada, gera hash BCRYPT seguro
            $hashBcrypt = password_hash($senha, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, cargo = ?, ativo = ?, senha = ?, atualizado_em = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $cargo, $ativo, $hashBcrypt, $id]);
        } else {
            // Atualiza sem alterar a senha existente
            $sql = "UPDATE usuarios SET nome = ?, email = ?, cargo = ?, ativo = ?, atualizado_em = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $cargo, $ativo, $id]);
        }

        $dadosNovos = ['id' => $id, 'nome' => $nome, 'email' => $email, 'cargo' => $cargo, 'ativo' => $ativo];

        registrarLogAuditoria(
            $pdo,
            'UPDATE',
            'usuarios',
            $id,
            "Atualização dos dados do usuário '{$nome}' ({$email}) com senha tratada em bcrypt.",
            $dadosAntigos,
            $dadosNovos
        );

        definirToast('success', "Usuário '{$nome}' atualizado com sucesso!", 'Usuário Modificado');

    } else {
        // MODO CADASTRO NOVO
        if (empty($senha) || strlen($senha) < 6) {
            definirToast('error', 'A senha provisória deve conter no mínimo 6 caracteres.', 'Senha Fraca');
            header('Location: ' . url('admin/usuarios.php'));
            exit;
        }

        // HASH BCRYPT OBRIGATÓRIO (Regra Core)
        $hashBcrypt = password_hash($senha, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (nome, email, senha, cargo, ativo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $hashBcrypt, $cargo, $ativo]);
        $novoId = (int)$pdo->lastInsertId();

        $dadosNovos = ['id' => $novoId, 'nome' => $nome, 'email' => $email, 'cargo' => $cargo, 'ativo' => $ativo];

        registrarLogAuditoria(
            $pdo,
            'INSERT',
            'usuarios',
            $novoId,
            "Criação de novo usuário '{$nome}' ({$email}) com hash bcrypt por {$_SESSION['usuario_nome']}.",
            null,
            $dadosNovos
        );

        definirToast('success', "Novo usuário '{$nome}' criado com sucesso com criptografia bcrypt!", 'Usuário Cadastrado');
    }

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'UNIQUE') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
        definirToast('error', 'Este endereço de e-mail já está cadastrado para outro usuário.', 'E-mail Duplicado');
    } else {
        definirToast('error', 'Erro ao salvar usuário: ' . $e->getMessage(), 'Erro no Banco');
    }
}

header('Location: ' . url('admin/usuarios.php'));
exit;
