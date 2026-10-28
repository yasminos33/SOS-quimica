<?php
/**
 * SOS QUÍMICA - FUNÇÕES AUXILIARES E REGRAS DE NEGÓCIO GLOBAIS
 * 
 * Regra: "Separe os arquivos de forma lógica... nunca faça o sistema como um monolito"
 * Regra: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita"
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Registra log de auditoria no banco de dados para auditorias futuras
 * 
 * @param PDO $pdo Instância do banco de dados
 * @param string $acao Ex: INSERT, UPDATE, DELETE, SEARCH, LOGIN, LOGOUT
 * @param string|null $tabela Ex: procedimentos, usuarios
 * @param int|null $registroId ID do registro afetado
 * @param string $descricao Descrição legível da alteração realizada
 * @param mixed $dadosAntigos Dados antes da modificação (array ou objeto)
 * @param mixed $dadosNovos Dados novos após a modificação (array ou objeto)
 * @return bool
 */
function registrarLogAuditoria(
    PDO $pdo,
    string $acao,
    ?string $tabela = null,
    ?int $registroId = null,
    string $descricao = '',
    $dadosAntigos = null,
    $dadosNovos = null
): bool {
    try {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioNome = $_SESSION['usuario_nome'] ?? 'Visitante do Laboratório';

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ip = explode(',', $ip)[0]; // Sanitiza se houver múltiplos proxies
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido', 0, 255);

        $jsonAntigos = $dadosAntigos !== null ? json_encode($dadosAntigos, JSON_UNESCAPED_UNICODE) : null;
        $jsonNovos = $dadosNovos !== null ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null;

        $sql = "INSERT INTO logs_auditoria 
                (usuario_id, usuario_nome, acao, tabela_afetada, registro_id, descricao, dados_anteriores, dados_novos, ip, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $usuarioId,
            $usuarioNome,
            strtoupper(trim($acao)),
            $tabela,
            $registroId,
            $descricao,
            $jsonAntigos,
            $jsonNovos,
            $ip,
            $userAgent
        ]);
    } catch (Exception $e) {
        // Falha no log não deve interromper o fluxo crítico de atendimento de socorro
        error_log("Erro ao salvar log de auditoria: " . $e->getMessage());
        return false;
    }
}

/**
 * Define uma mensagem TOAST na sessão para exibição imediata
 * 
 * @param string $tipo 'success' | 'error' | 'warning' | 'info'
 * @param string $mensagem Mensagem para o usuário
 * @param string|null $titulo Título opcional
 */
function definirToast(string $tipo, string $mensagem, ?string $titulo = null): void {
    $_SESSION['toast'] = [
        'tipo'     => in_array($tipo, ['success', 'error', 'warning', 'info']) ? $tipo : 'info',
        'mensagem' => htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'),
        'titulo'   => $titulo ? htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') : null
    ];
}

/**
 * Resgata e limpa o TOAST da sessão
 * @return array|null
 */
function obterToast(): ?array {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Gera badge HTML estilizado em Tailwind para níveis de gravidade de emergência
 */
function getBadgeGravidade(string $gravidade): string {
    $gravidade = strtoupper(trim($gravidade));
    switch ($gravidade) {
        case 'CRITICA':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-700 border border-red-300 dark:bg-red-900/40 dark:text-red-300 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span> CRÍTICA
                    </span>';
        case 'ALTA':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-900/40 dark:text-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> ALTA
                    </span>';
        case 'MEDIA':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-300 dark:bg-yellow-900/40 dark:text-yellow-300">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> MÉDIA
                    </span>';
        case 'BAIXA':
        default:
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> BAIXA
                    </span>';
    }
}

/**
 * Formata data/hora para padrão brasileiro
 */
function formatarDataHora(?string $dataHora): string {
    if (!$dataHora) return 'N/D';
    $timestamp = strtotime($dataHora);
    return date('d/m/Y \à\s H:i:s', $timestamp);
}

/**
 * Verifica se há usuário administrador ou monitor logado
 */
function usuarioEstaLogado(): bool {
    return !empty($_SESSION['usuario_id']);
}

/**
 * Exige login para acessar rotas restritas de edição/auditoria
 */
function exigirLogin(): void {
    if (!usuarioEstaLogado()) {
        definirToast('error', 'Acesso restrito. Faça login para gerenciar procedimentos e logs de auditoria.', 'Autenticação Necessária');
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

/**
 * Determina a URL base dinâmica para links e assets
 * Normaliza barras invertidas do Windows para evitar URLs relativas ao protocolo
 */
function obterUrlBase(): string {
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    if ($script === '' || $script === '/' || $script === '.') {
        return '';
    }
    $dir = str_replace('\\', '/', dirname($script));
    // Remove pastas internas para encontrar a raiz do projeto
    $base = preg_replace('#/(admin|actions|includes).*$#', '', $dir);
    $base = trim($base, "/\\.");
    return $base === '' ? '' : '/' . $base;
}

/**
 * Gera URLs limpas e seguras para links e rotas do sistema
 * Garante compatibilidade tanto na raiz quanto em subpastas (XAMPP/Apache)
 * 
 * @param string $caminho Ex: 'detalhes.php?id=1', 'admin/index.php'
 * @return string
 */
function url(string $caminho = ''): string {
    $base = obterUrlBase();
    $caminho = ltrim($caminho, "/\\");
    if ($caminho === '') {
        return $base === '' ? '/' : $base;
    }
    return $base === '' ? '/' . $caminho : $base . '/' . $caminho;
}
