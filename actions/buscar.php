<?php
/**
 * SOS QUÍMICA - ENDPOINT AJAX / JSON PARA PESQUISA RÁPIDA
 * 
 * Regra: "no site nao sera necessario o login , o usuario tera que selecionar ou digitar o problema"
 * Regra: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections"
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

header('Content-Type: application/json; charset=utf-8');

$termo = trim(filter_input(INPUT_GET, 'q', FILTER_DEFAULT) ?? '');

if (empty($termo)) {
    echo json_encode(['sucesso' => true, 'total' => 0, 'resultados' => []]);
    exit;
}

$pdo = getConexao();

try {
    $sql = "SELECT p.id, p.titulo, p.reagente_comum, p.sintomas, p.nivel_gravidade, 
                   c.nome AS categoria_nome, c.icone AS categoria_icone
            FROM procedimentos p
            INNER JOIN categorias_acidentes c ON p.categoria_id = c.id
            WHERE p.titulo LIKE ? OR p.reagente_comum LIKE ? OR p.sintomas LIKE ?
            ORDER BY 
                CASE p.nivel_gravidade
                    WHEN 'CRITICA' THEN 1
                    WHEN 'ALTA' THEN 2
                    WHEN 'MEDIA' THEN 3
                    ELSE 4
                END ASC
            LIMIT 10";

    $like = "%{$termo}%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like, $like, $like]);
    $resultados = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'total' => count($resultados),
        'resultados' => $resultados
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Falha interna ao consultar procedimentos.']);
}
