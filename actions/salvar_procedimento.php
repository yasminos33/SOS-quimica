<?php
/**
 * SOS QUÍMICA - SALVAR / EDITAR PROCEDIMENTO DE EMERGÊNCIA
 * 
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Exige autenticação de monitor / docente
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/gerenciar.php'));
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$categoriaId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$reagenteComum = trim($_POST['reagente_comum'] ?? '');
$sintomas = trim($_POST['sintomas'] ?? '');
$nivelGravidade = trim($_POST['nivel_gravidade'] ?? 'ALTA');
$passoAPasso = trim($_POST['passo_a_passo'] ?? '');
$oQueNaoFazer = trim($_POST['o_que_nao_fazer'] ?? '');
$equipamentoProtecao = trim($_POST['equipamento_protecao'] ?? '');
$quandoChamarSocorro = trim($_POST['quando_chamar_socorro'] ?? '');
$contatosEmergencia = trim($_POST['contatos_emergencia'] ?? 'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001');

// Validação dos campos obrigatórios de socorro
if (empty($titulo) || empty($reagenteComum) || empty($passoAPasso) || empty($oQueNaoFazer) || !$categoriaId) {
    definirToast('error', 'Preencha todos os campos obrigatórios (Título, Categoria, Reagentes, Passo a Passo e O que NÃO Fazer).', 'Validação de Dados');
    $redirect = $id ? "admin/form_procedimento.php?id={$id}" : "admin/form_procedimento.php";
    header('Location: ' . url($redirect));
    exit;
}

if (!in_array($nivelGravidade, ['CRITICA', 'ALTA', 'MEDIA', 'BAIXA'])) {
    $nivelGravidade = 'ALTA';
}

$pdo = getConexao();

try {
    if ($id) {
        // MODO EDIÇÃO: Recupera estado anterior para manter histórico fiel
        $stmtOld = $pdo->prepare("SELECT * FROM procedimentos WHERE id = ?");
        $stmtOld->execute([$id]);
        $dadosAnteriores = $stmtOld->fetch();

        if (!$dadosAnteriores) {
            definirToast('error', 'Procedimento não encontrado para atualização.', 'Erro');
            header('Location: ' . url('admin/gerenciar.php'));
            exit;
        }

        // Executa UPDATE com PDO Prepared Statement
        $sql = "UPDATE procedimentos SET 
                categoria_id = ?,
                titulo = ?,
                reagente_comum = ?,
                sintomas = ?,
                nivel_gravidade = ?,
                passo_a_passo = ?,
                o_que_nao_fazer = ?,
                equipamento_protecao = ?,
                quando_chamar_socorro = ?,
                contatos_emergencia = ?,
                atualizado_em = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $categoriaId,
            $titulo,
            $reagenteComum,
            $sintomas,
            $nivelGravidade,
            $passoAPasso,
            $oQueNaoFazer,
            $equipamentoProtecao,
            $quandoChamarSocorro,
            $contatosEmergencia,
            $id
        ]);

        $dadosNovos = [
            'categoria_id' => $categoriaId,
            'titulo' => $titulo,
            'reagente_comum' => $reagenteComum,
            'nivel_gravidade' => $nivelGravidade,
            'passo_a_passo' => $passoAPasso,
            'o_que_nao_fazer' => $oQueNaoFazer
        ];

        // Registra log completo de auditoria para auditorias futuras
        registrarLogAuditoria(
            $pdo,
            'UPDATE',
            'procedimentos',
            $id,
            "Atualização do procedimento #{$id}: '{$titulo}' por {$_SESSION['usuario_nome']}.",
            $dadosAnteriores,
            $dadosNovos
        );

        definirToast('success', "Procedimento '{$titulo}' atualizado com sucesso e auditado no sistema!", 'Procedimento Atualizado');

    } else {
        // MODO INSERÇÃO: Novo procedimento cadastrado
        $sql = "INSERT INTO procedimentos 
                (categoria_id, titulo, reagente_comum, sintomas, nivel_gravidade, passo_a_passo, o_que_nao_fazer, equipamento_protecao, quando_chamar_socorro, contatos_emergencia, criado_por) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $categoriaId,
            $titulo,
            $reagenteComum,
            $sintomas,
            $nivelGravidade,
            $passoAPasso,
            $oQueNaoFazer,
            $equipamentoProtecao,
            $quandoChamarSocorro,
            $contatosEmergencia,
            $_SESSION['usuario_id']
        ]);

        $novoId = (int)$pdo->lastInsertId();

        $dadosNovos = [
            'id' => $novoId,
            'categoria_id' => $categoriaId,
            'titulo' => $titulo,
            'reagente_comum' => $reagenteComum,
            'nivel_gravidade' => $nivelGravidade
        ];

        // Registra log de auditoria
        registrarLogAuditoria(
            $pdo,
            'INSERT',
            'procedimentos',
            $novoId,
            "Cadastro de novo procedimento #{$novoId}: '{$titulo}' por {$_SESSION['usuario_nome']}.",
            null,
            $dadosNovos
        );

        definirToast('success', "Novo procedimento '{$titulo}' cadastrado com sucesso!", 'Procedimento Criado');
    }

} catch (PDOException $e) {
    definirToast('error', 'Erro ao salvar procedimento: ' . $e->getMessage(), 'Falha no Banco');
}

header('Location: ' . url('admin/gerenciar.php'));
exit;
