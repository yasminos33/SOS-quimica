<?php
/**
 * SOS QUÍMICA - EXCLUSÃO DE PROCEDIMENTO DE EMERGÊNCIA
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer 
 * alteração feita por qualquer usuário, para auditorias futuras."
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Exige autenticação
exigirLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definirToast('error', 'ID do procedimento inválido para exclusão.', 'Parâmetro Inválido');
    header('Location: ' . url('admin/gerenciar.php'));
    exit;
}

$pdo = getConexao();

try {
    // 1. Recupera os dados completos antes de excluir para salvar no histórico de auditoria
    $stmtSelect = $pdo->prepare("SELECT * FROM procedimentos WHERE id = ?");
    $stmtSelect->execute([$id]);
    $dadosExcluidos = $stmtSelect->fetch();

    if (!$dadosExcluidos) {
        definirToast('error', 'Procedimento não encontrado ou já excluído.', 'Não Encontrado');
        header('Location: ' . url('admin/gerenciar.php'));
        exit;
    }

    // 2. Executa a exclusão com PDO
    $stmtDelete = $pdo->prepare("DELETE FROM procedimentos WHERE id = ?");
    $stmtDelete->execute([$id]);

    // 3. Registra auditoria completa do evento
    registrarLogAuditoria(
        $pdo,
        'DELETE',
        'procedimentos',
        $id,
        "Exclusão definitiva do procedimento #{$id}: '{$dadosExcluidos['titulo']}' por {$_SESSION['usuario_nome']}.",
        $dadosExcluidos,
        null
    );

    definirToast('success', "Procedimento '{$dadosExcluidos['titulo']}' excluído e registrado no histórico de auditoria.", 'Procedimento Removido');

} catch (PDOException $e) {
    definirToast('error', 'Não foi possível excluir o procedimento: ' . $e->getMessage(), 'Erro ao Excluir');
}

header('Location: ' . url('admin/gerenciar.php'));
exit;
