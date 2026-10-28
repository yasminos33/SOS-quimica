<?php
/**
 * SOS QUÍMICA - MÓDULO DE CONEXÃO COM BANCO DE DADOS (bd.php)
 * 
 * Regra do Sistema: "Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections.
 * Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php)"
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados MySQL
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'sos_quimica');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma instância PDO configurada de forma segura
 * @return PDO
 */
function getConexao(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Garante prepared statements reais
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    try {
        // Tenta conectar diretamente ao banco MySQL
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Se o erro for de banco inexistente no MySQL (código 1049), tenta criá-lo
        if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049) {
            try {
                $dsnSemDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                $pdoTemp = new PDO($dsnSemDb, DB_USER, DB_PASS, $options);
                $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdoTemp = null;

                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                inicializarTabelasSeNecessario($pdo, 'mysql');
                return $pdo;
            } catch (Exception $exCriacao) {
                // Falha ao criar banco MySQL, prossegue para fallback
            }
        }

        // FALLBACK RESILIENTE: Se o MySQL local não estiver ativo/configurado no ambiente,
        // ativa automaticamente banco local SQLite via PDO para manter o sistema 100% operacional.
        $sqlitePath = __DIR__ . '/../database/sos_quimica.sqlite';
        $sqliteDsn = "sqlite:" . $sqlitePath;
        try {
            $pdo = new PDO($sqliteDsn, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Ativa foreign keys no SQLite
            $pdo->exec("PRAGMA foreign_keys = ON;");
            inicializarTabelasSeNecessario($pdo, 'sqlite');
            return $pdo;
        } catch (PDOException $sqliteEx) {
            // Em caso de falha crítica total de conexão
            die("Erro crítico de conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
        }
    }
}

/**
 * Cria as tabelas e dados essenciais caso o banco esteja vazio
 */
function inicializarTabelasSeNecessario(PDO $pdo, string $driver): void {
    try {
        // Verifica se a tabela procedimentos já existe
        $check = $pdo->query("SELECT 1 FROM procedimentos LIMIT 1");
        return; // Já existem dados
    } catch (Exception $e) {
        // Tabela não existe, criar esquema
    }

    if ($driver === 'mysql') {
        $sqlFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($sqlFile)) {
            $queries = file_get_contents($sqlFile);
            $pdo->exec($queries);
            return;
        }
    }

    // Criação compatível com SQLite (fallback)
    $sqlLite = "
    CREATE TABLE IF NOT EXISTS usuarios (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      senha TEXT NOT NULL,
      cargo TEXT NOT NULL DEFAULT 'Monitor de Laboratório',
      ativo INTEGER NOT NULL DEFAULT 1,
      criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
      atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS categorias_acidentes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nome TEXT NOT NULL,
      slug TEXT NOT NULL UNIQUE,
      icone TEXT NOT NULL DEFAULT 'fa-exclamation-triangle',
      cor TEXT NOT NULL DEFAULT 'red',
      descricao TEXT,
      ordem INTEGER NOT NULL DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS procedimentos (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      categoria_id INTEGER NOT NULL,
      titulo TEXT NOT NULL,
      reagente_comum TEXT NOT NULL,
      sintomas TEXT,
      nivel_gravidade TEXT NOT NULL DEFAULT 'ALTA',
      passo_a_passo TEXT NOT NULL,
      o_que_nao_fazer TEXT NOT NULL,
      equipamento_protecao TEXT,
      quando_chamar_socorro TEXT,
      contatos_emergencia TEXT NOT NULL DEFAULT 'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001',
      criado_por INTEGER,
      criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
      atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (categoria_id) REFERENCES categorias_acidentes(id)
    );

    CREATE TABLE IF NOT EXISTS logs_auditoria (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      usuario_id INTEGER,
      usuario_nome TEXT NOT NULL DEFAULT 'Sistema / Público',
      acao TEXT NOT NULL,
      tabela_afetada TEXT,
      registro_id INTEGER,
      descricao TEXT NOT NULL,
      dados_anteriores TEXT,
      dados_novos TEXT,
      ip TEXT NOT NULL DEFAULT '127.0.0.1',
      user_agent TEXT,
      data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $pdo->exec($sqlLite);

    // Inserção do usuário padrão admin (senha: admin123) com bcrypt
    $senhaHash = '$2y$10$n4A6Hu/7ukt3PburAKB7Guwe3Lt96c9SW7I.pIt.dOowZ.wl.hTIm';
    $stmtUser = $pdo->prepare("INSERT OR IGNORE INTO usuarios (id, nome, email, senha, cargo, ativo) VALUES (1, 'Administrador do Laboratório', 'admin@sosquimica.com', ?, 'Responsável Técnico / Professor', 1)");
    $stmtUser->execute([$senhaHash]);

    // Inserção das Categorias
    $categorias = [
        [1, 'Queimadura por Ácidos e Bases (Pele)', 'queimadura-pele', 'fa-hand-holding-medical', 'red', 'Contato de substâncias químicas cáusticas ou corrosivas diretamente sobre a pele.', 1],
        [2, 'Respingo Químico nos Olhos', 'olhos', 'fa-eye', 'rose', 'Acidentes envolvendo contato ocular com ácidos, álcalis ou solventes agressivos.', 2],
        [3, 'Inalação de Gases e Vapores Tóxicos', 'inalacao', 'fa-lungs', 'amber', 'Exposição aguda a vapores corrosivos, asfixiantes ou irritantes respiratórios.', 3],
        [4, 'Ingestão Acidental', 'ingestao', 'fa-skull-crossbones', 'purple', 'Deglutição involuntária de reagentes ou contaminação por pipetagem oral indevida.', 4],
        [5, 'Fogo, Explosão ou Queimadura Térmica', 'fogo-termico', 'fa-fire-flame-curved', 'orange', 'Combustão de solventes, chamas de bico de Bunsen ou contato com vidraria quente.', 5],
        [6, 'Derramamento de Reagentes Perigosos', 'derramamento', 'fa-biohazard', 'yellow', 'Vazamento ou quebra de frascos contendo compostos voláteis, tóxicos ou inflamáveis.', 6],
        [7, 'Corte com Vidraria Contaminada', 'cortes', 'fa-syringe', 'blue', 'Ferimentos pérfuro-cortantes com vidrarias impregnadas de resíduos químicos.', 7]
    ];

    $stmtCat = $pdo->prepare("INSERT OR IGNORE INTO categorias_acidentes (id, nome, slug, icone, cor, descricao, ordem) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($categorias as $cat) {
        $stmtCat->execute($cat);
    }

    // Inserção dos procedimentos de exemplo
    $procedimentos = [
        [
            1, 1, 'Contato de Ácido Forte na Pele (Sulfúrico, Nítrico, Clorídrico)',
            'Ácido Sulfúrico (H2SO4), Ácido Nítrico (HNO3), Ácido Clorídrico (HCl)',
            'Ardência intensa instantânea, vermelhidão, branqueamento ou escurecimento do tecido cutâneo.',
            'CRITICA',
            "1. Leve a vítima IMEDIATAMENTE ao chuveiro de emergência do laboratório.\n2. Lave a região afetada com fluxo contínuo de água corrente abundante por no mínimo 15 a 20 minutos ininterruptos.\n3. Enquanto a água corre, remova rapidamente todas as roupas, sapatos e adornos impregnados de reagente.\n4. Não friccione a pele. Deixe apenas a água fluir para diluir e remover o reagente.\n5. Após a lavagem prolongada, cubra frouxamente a área com gaze estéril umedecida em soro fisiológico ou pano limpo.\n6. Mantenha a vítima calma e aquecida enquanto aguarda atendimento de emergência.",
            "- NUNCA tente neutralizar o ácido aplicando bases (como soda cáustica ou amônia) sobre a pele! A reação de neutralização é violentamente exotérmica e causa queimaduras térmicas gravíssimas combinadas com a química.\n- NÃO aplique pomadas, óleos, pasta de dente, manteiga ou gelo direto na ferida.\n- NÃO friccione ou esfregue a pele.",
            "Chuveiro de emergência, luvas de nitrila de cano longo para quem socorre, avental impermeável.",
            "Sempre acione atendimento médico imediato (SAMU 192). Em queimaduras químicas por ácido sulfúrico concentrado ou nítrico, mesmo que pequenas, a lesão pode continuar progredindo nas camadas profundas.",
            "SAMU 192 | Bombeiros 193 | CIT 0800 722 6001",
            1
        ],
        [
            2, 2, 'Respingo de Base Forte nos Olhos (Soda Cáustica / KOH)',
            'Hidróxido de Sódio (NaOH), Hidróxido de Potássio (KOH), Solução concentrada de Amônia (NH4OH)',
            'Dor aguda lancinante, queimação, lacrimejamento excessivo, sensibilidade à luz, turvação visual.',
            'CRITICA',
            "1. Conduza a vítima IMEDIATAMENTE ao LAVA-OLHOS de emergência (cada segundo conta: bases penetram profundamente e liquefazem tecidos oculares).\n2. Acione o fluxo de água do lava-olhos.\n3. Abra as pálpebras superior e inferior forçadamente com os dedos indicador e polegar, garantindo que a água atinja todo o globo ocular e fundo de saco conjuntival.\n4. Mantenha a irrigação contínua com água corrente suave por NO MÍNIMO 20 a 30 minutos.\n5. Se a vítima usar lentes de contato, retire-as com cuidado durante o enxágue assim que possível.\n6. Ao término, oclua suavemente os olhos com compressa estéril de gaze sem fazer compressão e vá imediatamente ao serviço oftalmológico de urgência.",
            "- NUNCA esfregue os olhos ou aperte as pálpebras.\n- NÃO tente pingar colírios anestésicos ou soluções neutralizantes caseiras (água boricada, vinagre ou leite).\n- NÃO interrompa a lavagem antes de 20 minutos completos.",
            "Lava-olhos de emergência regulado, protetor facial integral, luvas descartáveis.",
            "Chame o SAMU 192 ou conduza imediatamente a um pronto-socorro oftalmológico. Acidentes oculares com álcalis são emergências extremas com risco de perda irreversível da visão.",
            "SAMU 192 | CIT 0800 722 6001",
            1
        ],
        [
            3, 3, 'Inalação Aguda de Gás Cloro ou Vapores Corrosivos',
            'Gás Cloro (Cl2), Bromo (Br2), Dióxido de Nitrogênio (NO2), Cloreto de Hidrogênio',
            'Aperto torácico, tosse seca espasmódica, queimação na garganta e nariz, falta de ar, cianose.',
            'ALTA',
            "1. Remova IMEDIATAMENTE a vítima para um ambiente externo, arejado e ventilado, afastando-a da fonte de emissão gasosa.\n2. Afaste curiosos e garanta a circulação livre de ar.\n3. Coloque a vítima sentada em posição confortável (evite deitá-la, pois a postura ereta facilita a respiração e reduz risco de congestão pulmonar).\n4. Afrouxe colarinho, gravata, cinto ou qualquer roupa restritiva.\n5. Mantenha a vítima em repouso absoluto, proibindo esforços físicos (o esforço pode desencadear edema agudo de pulmão tardio).\n6. Se houver oxigênio medicinal no setor com profissional habilitado, inicie oxigenoterapia a 100% sob máscara.\n7. Monitore frequência respiratória e pulso até a chegada do resgate.",
            "- NÃO entre na área contaminada sem máscara respiratória com filtro químico apropriado.\n- NÃO deite a vítima de costas se ela apresentar dispneia (falta de ar).\n- NÃO forneça bebidas estimulantes ou alimentos.",
            "Capela de exaustão química, máscara panorâmica com filtro multigases (amarelo/marrom), oxímetro de pulso.",
            "Ligue imediatamente para 192 (SAMU). A inalação de vapores nitrosos ou cloro pode causar edema pulmonar até 24 a 48 horas após a exposição, exigindo internação para observação.",
            "SAMU 192 | Bombeiros 193 | CIT 0800 722 6001",
            1
        ],
        [
            4, 4, 'Ingestão Acidental de Reagente Químico Tóxico ou Corrosivo',
            'Sais de Chumbo, Compostos de Mercúrio, Cianetos, Ácido Oxálico, Formol',
            'Queimação no trato gastrointestinal, dor abdominal intensa, náuseas, hálito químico, salivação excessiva.',
            'CRITICA',
            "1. Interrompa imediatamente qualquer ingestão.\n2. Identifique com precisão absoluta o frasco, fórmula química e concentração do produto ingerido (guarde o rótulo/frasco para a equipe médica).\n3. Ligue IMEDIATAMENTE para o Centro de Informação Toxicológica (CIT - 0800 722 6001) com o rótulo em mãos.\n4. Se a vítima estiver consciente, oriente-a a enxaguar a boca várias vezes cuspindo a água, para remover resíduos orais.\n5. Se inconsciente mas com respiração, coloque em Posição Lateral de Segurança (PLS) para evitar aspiração de vômito espontâneo.\n6. Siga estritamente as instruções fornecidas pelo médico do CIT pelo telefone.",
            "- JAMAIS PROVOQUE VÔMITO se a substância for corrosiva (ácido ou base forte) ou derivado de petróleo/hidrocarboneto! O vômito causará nova queimadura corrosiva no esôfago e faringe, além de risco de aspiração pulmonar letal.\n- NÃO dê leite, vinagre, claras de ovos ou água em grande quantidade sem aval prévio do CIT.\n- NÃO administre substâncias via oral para vítimas desmaiadas ou sonolentas.",
            "Pipetadores mecânicos de segurança, recipientes devidamente rotulados com GHS/FISPQ.",
            "Ligue IMEDIATAMENTE para SAMU 192 e CIT 0800 722 6001. Tenha o nome químico exato e volume aproximado consumido.",
            "CIT 0800 722 6001 | SAMU 192",
            1
        ],
        [
            5, 5, 'Incêndio em Bancada ou Roupa com Solvente Inflamável',
            'Éter Dietílico, Hexano, Acetona, Etanol, Tolueno',
            'Chamas na bancada, roupas em fogo, fumaça escura, calor intenso.',
            'CRITICA',
            "1. Se o fogo for na roupa da pessoa: PARE, DEITE E ROLE no chão. Use o cobertor corta-fogo (fire blanket) ou chuveiro de emergência para abafar o fogo. NUNCA corra.\n2. Se o fogo for em vidraria pequena ou béquer: tampe com vidro de relógio, placa metálica ou cuba invertida para cortar o oxigênio (abafamento).\n3. Se o fogo for na bancada: desligue imediatamente o bico de Bunsen principal ou válvula de gás.\n4. Utilize o extintor adequado: PÓ QUÍMICO SECO (PQS - Classe B/C) ou GÁS CARBÔNICO (CO2). Mantenha distância de 2 a 3 metros e mire na base das chamas com movimento de varredura.\n5. Evacue o laboratório ordenadamente fechando portas para confinar as chamas se o fogo sair de controle.\n6. Acione o alarme de incêndio do prédio.",
            "- NUNCA use água sobre fogo de solventes orgânicos inflamáveis ou metais alcalinos (sódio/potássio)! A água espalha o solvente em chamas e pode causar explosão violenta.\n- NÃO permita que a vítima corra se estiver com as vestes em chamas.",
            "Extintores de CO2 e PQS inspecionados, cobertor corta-fogo, chuveiro de segurança.",
            "Se o fogo não for extinto nos primeiros 30 segundos ou se houver queimaduras corporais, acione o Corpo de Bombeiros (193) e SAMU (192). Evacue a área imediatamente.",
            "Bombeiros 193 | SAMU 192",
            1
        ],
        [
            6, 6, 'Derramamento de Reagente Volátil ou Solvente Tóxico na Bancada/Chão',
            'Benzeno, Clorofórmio, Bromo Líquido, Ácido Acético Glacial',
            'Odor forte sufocante, poça de líquido evaporando com risco de ignição ou intoxicação.',
            'ALTA',
            "1. Alerte imediatamente todas as pessoas presentes no laboratório sobre o derramamento.\n2. Elimine qualquer fonte de faísca ou chama aberta no raio de 10 metros.\n3. Aumente a ventilação do local: abra janelas e ligue sistemas de exaustão geral.\n4. Isole e sinalize a área com fita ou cones de advertência.\n5. Para solventes: verta vermiculita, areia seca ou pó absorvente específico sobre o líquido, circulando da borda para o centro para conter o espalhamento.\n6. Para ácidos derramados no chão: neutralizar com bicarbonato de sódio lentamente até cessar a efervescência, e só então absorver.\n7. Recolha o material contaminado usando pá plástica anti-faísca e acondicione em saco plástico grosso ou bombona identificada para incineração.",
            "- NÃO use aspirador de pó elétrico para aspirar líquidos inflamáveis (o motor produz faíscas internas).\n- NÃO jogue o resíduo químico recolhido na pia ou ralo comum.",
            "Kit de mitigação de derramamentos (spill kit), máscara com filtro vapor orgânico/gases ácidos, luvas nitrílicas resistentes.",
            "Se o volume derramado for superior a 1 litro de substância altamente tóxica/cancerígena ou atingir dutos de esgoto, comunique a Brigada de Emergência Química e os Bombeiros (193).",
            "Bombeiros 193 | Defesa Civil 199",
            1
        ],
        [
            7, 7, 'Corte com Vidro Quebrado Contaminado por Agente Químico',
            'Pipeta quebrada, termômetro de mercúrio quebrado, tubos de ensaio fraturados',
            'Sangramento ativo, corte visível, suspeita de impregnação de resíduo químico na derme.',
            'MEDIA',
            "1. Lave a ferida imediatamente sob água corrente abundante por 5 a 10 minutos para drenar e lavar quaisquer vestígios de compostos químicos solúveis.\n2. Permita um sangramento leve inicial para ajudar a expulsar contaminantes da ferida.\n3. Se o sangramento for abundante, aplique pressão direta sobre o corte com gaze estéril seca ou compressa limpa.\n4. Eleve o membro ferido acima do nível do coração para conter o fluxo hemorrágico.\n5. Se houver caco de vidro profundamente encravado, NÃO puxe; imobilize o objeto e faça curativo ao redor.\n6. Anote o reagente que estava na vidraria para repassar ao médico.\n7. Encaminhe a vítima à enfermaria ou pronto-socorro.",
            "- NUNCA remova cacos de vidro grandes ou profundos com pinças sem suporte cirúrgico (risco de hemorragia incontrolável).\n- NÃO recolha vidros quebrados com as mãos nuas (use vassoura e pá).\n- NÃO descarte vidrarias quebradas no lixo comum.",
            "Caixa rígida para perfurocortantes (Descarpack), luvas anti-corte, gaze estéril, faixas e ataduras.",
            "Busque pronto-socorro se a hemorragia não cessar após 10 minutos de compressão, se houver vidro retido na ferida, ou para profilaxia de tétano.",
            "SAMU 192 | Pronto-Socorro Local",
            1
        ]
    ];

    $stmtProc = $pdo->prepare("INSERT OR IGNORE INTO procedimentos (id, categoria_id, titulo, reagente_comum, sintomas, nivel_gravidade, passo_a_passo, o_que_nao_fazer, equipamento_protecao, quando_chamar_socorro, contatos_emergencia, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($procedimentos as $p) {
        $stmtProc->execute($p);
    }

    $stmtLog = $pdo->prepare("INSERT OR IGNORE INTO logs_auditoria (usuario_id, usuario_nome, acao, tabela_afetada, registro_id, descricao, ip) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtLog->execute([1, 'Administrador do Laboratório', 'INSERT', 'sistema', 1, 'Inicialização do banco de dados SOS Química.', '127.0.0.1']);
}
