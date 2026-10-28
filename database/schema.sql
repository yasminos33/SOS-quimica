-- ==============================================================
-- SOS QUÍMICA - ESQUEMA DO BANCO DE DADOS (MySQL / MariaDB)
-- Compatível com PDO MySQL e boas práticas de segurança
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `sos_quimica` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sos_quimica`;

-- Desativar verificação de chaves temporariamente para criação limpa
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- 1. TABELA DE USUÁRIOS ADMINISTRATIVOS / MONITORES
-- Senhas obrigatoriamente armazenadas com hash BCRYPT
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt gerado por password_hash()',
  `cargo` VARCHAR(80) NOT NULL DEFAULT 'Monitor de Laboratório',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. TABELA DE CATEGORIAS DE ACIDENTES
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `categorias_acidentes`;
CREATE TABLE `categorias_acidentes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icone` VARCHAR(60) NOT NULL DEFAULT 'fa-exclamation-triangle',
  `cor` VARCHAR(50) NOT NULL DEFAULT 'red',
  `descricao` TEXT NULL,
  `ordem` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. TABELA DE PROCEDIMENTOS DE EMERGÊNCIA
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `procedimentos`;
CREATE TABLE `procedimentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `categoria_id` INT NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `reagente_comum` VARCHAR(255) NOT NULL COMMENT 'Exemplos: Ácido Sulfúrico, Soda Cáustica, Bromo',
  `sintomas` VARCHAR(255) NULL COMMENT 'Sintomas visíveis rápidos para identificação',
  `nivel_gravidade` ENUM('BAIXA', 'MEDIA', 'ALTA', 'CRITICA') NOT NULL DEFAULT 'ALTA',
  `passo_a_passo` TEXT NOT NULL COMMENT 'Instruções sequenciais imediatas',
  `o_que_nao_fazer` TEXT NOT NULL COMMENT 'Avisos críticos de condutas perigosas proibidas',
  `equipamento_protecao` TEXT NULL COMMENT 'EPIs e equipamentos recomendados no socorro',
  `quando_chamar_socorro` TEXT NULL COMMENT 'Critérios para acionar SAMU/Bombeiros imediatamente',
  `contatos_emergencia` VARCHAR(150) NOT NULL DEFAULT 'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001',
  `criado_por` INT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_procedimentos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_acidentes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_procedimentos_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. TABELA DE HISTÓRICO E LOGS DE AUDITORIA
-- Guarda qualquer alteração de cadastro, atualização ou exclusão
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `logs_auditoria`;
CREATE TABLE `logs_auditoria` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NULL,
  `usuario_nome` VARCHAR(100) NOT NULL DEFAULT 'Sistema / Público',
  `acao` VARCHAR(50) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, SEARCH, LOGIN, LOGOUT',
  `tabela_afetada` VARCHAR(60) NULL,
  `registro_id` INT NULL,
  `descricao` TEXT NOT NULL,
  `dados_anteriores` LONGTEXT NULL COMMENT 'Estado anterior em JSON',
  `dados_novos` LONGTEXT NULL COMMENT 'Novo estado em JSON',
  `ip` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
  `user_agent` VARCHAR(255) NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_acao` (`acao`),
  INDEX `idx_logs_data` (`data_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
-- DADOS INICIAIS (SEEDS)
-- Senha padrão do admin: admin123 (hash bcrypt)
-- -------------------------------------------------------------

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `cargo`, `ativo`) VALUES
(1, 'Administrador do Laboratório', 'admin@sosquimica.com', '$2y$10$n4A6Hu/7ukt3PburAKB7Guwe3Lt96c9SW7I.pIt.dOowZ.wl.hTIm', 'Responsável Técnico / Professor', 1);

INSERT INTO `categorias_acidentes` (`id`, `nome`, `slug`, `icone`, `cor`, `descricao`, `ordem`) VALUES
(1, 'Queimadura por Ácidos e Bases (Pele)', 'queimadura-pele', 'fa-hand-holding-medical', 'red', 'Contato de substâncias químicas cáusticas ou corrosivas diretamente sobre a pele.', 1),
(2, 'Respingo Químico nos Olhos', 'olhos', 'fa-eye', 'rose', 'Acidentes envolvendo contato ocular com ácidos, álcalis ou solventes agressivos.', 2),
(3, 'Inalação de Gases e Vapores Tóxicos', 'inalacao', 'fa-lungs', 'amber', 'Exposição aguda a vapores corrosivos, asfixiantes ou irritantes respiratórios.', 3),
(4, 'Ingestão Acidental', 'ingestao', 'fa-skull-crossbones', 'purple', 'Deglutição involuntária de reagentes ou contaminação por pipetagem oral indevida.', 4),
(5, 'Fogo, Explosão ou Queimadura Térmica', 'fogo-termico', 'fa-fire-flame-curved', 'orange', 'Combustão de solventes, chamas de bico de Bunsen ou contato com vidraria em alta temperatura.', 5),
(6, 'Derramamento de Reagentes Perigosos', 'derramamento', 'fa-biohazard', 'yellow', 'Vazamento ou quebra de frascos contendo compostos voláteis, tóxicos ou inflamáveis.', 6),
(7, 'Corte com Vidraria Contaminada', 'cortes', 'fa-syringe', 'blue', 'Ferimentos pérfuro-cortantes ocorridos durante manuseio de vidrarias com resíduos químicos.', 7);

INSERT INTO `procedimentos` (`id`, `categoria_id`, `titulo`, `reagente_comum`, `sintomas`, `nivel_gravidade`, `passo_a_passo`, `o_que_nao_fazer`, `equipamento_protecao`, `quando_chamar_socorro`, `contatos_emergencia`, `criado_por`) VALUES
(1, 1, 'Contato de Ácido Forte na Pele (Sulfúrico, Nítrico, Clorídrico)', 'Ácido Sulfúrico (H2SO4), Ácido Nítrico (HNO3), Ácido Clorídrico (HCl)', 'Ardência intensa instantânea, vermelhidão, branqueamento ou escurecimento do tecido.', 'CRITICA', 
'1. Leve a vítima IMEDIATAMENTE ao chuveiro de emergência do laboratório.\n2. Lave a região afetada com fluxo contínuo de água corrente abundante por no mínimo 15 a 20 minutos ininterruptos.\n3. Enquanto a água corre, remova rapidamente todas as roupas, sapatos e adornos impregnados de reagente (evitando espalhar para áreas sãs).\n4. Não friccione a pele. Deixe apenas a água fluir para diluir e remover o reagente.\n5. Após a lavagem prolongada, cubra frouxamente a área com gaze estéril umedecida em soro fisiológico ou pano limpo.\n6. Mantenha a vítima calma e aquecida enquanto aguarda atendimento de emergência.', 
'- NUNCA tente neutralizar o ácido aplicando bases (como soda cáustica ou amônia) sobre a pele! A reação de neutralização é violentamente exotérmica e causa queimaduras térmicas gravíssimas combinadas com a química.\n- NÃO aplique pomadas, óleos, pasta de dente, manteiga ou gelo direto na ferida.\n- NÃO friccione ou esfregue a pele.', 
'Chuveiro de emergência, luvas de nitrila de cano longo para quem socorre, avental impermeável.', 
'Sempre acione atendimento médico imediato (SAMU 192). Em queimaduras químicas por ácido sulfúrico concentrado ou nítrico, mesmo que pequenas, a lesão pode continuar progredindo nas camadas profundas.', 
'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001', 1),

(2, 2, 'Respingo de Base Forte nos Olhos (Soda Cáustica / KOH)', 'Hidróxido de Sódio (NaOH), Hidróxido de Potássio (KOH), Solução concentrada de Amônia (NH4OH)', 'Dor aguda lancinante, queimação, lacrimejamento excessivo, sensibilidade à luz, turvação visual.', 'CRITICA', 
'1. Conduza a vítima IMEDIATAMENTE ao LAVA-OLHOS de emergência (cada segundo conta: bases penetram profundamente e liquefazem tecidos oculares).\n2. Acione o fluxo de água do lava-olhos.\n3. Abra as pálpebras superior e inferior forçadamente com os dedos indicador e polegar, garantindo que a água atinja todo o globo ocular e fundo de saco conjuntival.\n4. Mantenha a irrigação contínua com água corrente suave por NO MÍNIMO 20 a 30 minutos.\n5. Se a vítima usar lentes de contato, retire-as com cuidado durante o enxágue assim que possível.\n6. Ao término, oclua suavemente os olhos com compressa estéril de gaze sem fazer compressão e vá imediatamente ao serviço oftalmológico de urgência.', 
'- NUNCA esfregue os olhos ou aperte as pálpebras.\n- NÃO tente pingar colírios anestésicos ou soluções neutralizantes caseiras (água boricada, vinagre ou leite).\n- NÃO interrompa a lavagem antes de 20 minutos completos.', 
'Lava-olhos de emergência regulado, protetor facial integral, luvas descartáveis.', 
'Chame o SAMU 192 ou conduza imediatamente a um pronto-socorro oftalmológico. Acidentes oculares com álcalis são emergências extremas com risco de perda irreversível da visão.', 
'SAMU 192 | CIT 0800 722 6001', 1),

(3, 3, 'Inalação Aguda de Gás Cloro ou Vapores Corrosivos', 'Gás Cloro (Cl2), Bromo (Br2), Dióxido de Nitrogênio (NO2), Cloreto de Hidrogênio', 'Aperto torácico, tosse seca espasmódica, queimação na garganta e nariz, falta de ar, cianose.', 'ALTA', 
'1. Remova IMEDIATAMENTE a vítima para um ambiente externo, arejado e ventilado, afastando-a da fonte de emissão gasosa.\n2. Afaste curiosos e garanta a circulação livre de ar.\n3. Coloque a vítima sentada em posição confortável (evite deitá-la, pois a postura ereta facilita a respiração e reduz risco de congestão pulmonar).\n4. Afrouxe colarinho, gravata, cinto ou qualquer roupa restritiva.\n5. Mantenha a vítima em repouso absoluto, proibindo esforços físicos (o esforço pode desencadear edema agudo de pulmão tardio).\n6. Se houver oxigênio medicinal no setor com profissional habilitado, inicie oxigenoterapia a 100% sob máscara.\n7. Monitore frequência respiratória e pulso até a chegada do resgate.', 
'- NÃO entre na área contaminada sem máscara respiratória com filtro químico apropriado.\n- NÃO deite a vítima de costas se ela apresentar dispneia (falta de ar).\n- NÃO forneça bebidas estimulantes ou alimentos.', 
'Capela de exaustão química, máscara panorâmica com filtro multigases (amarelo/marrom), oxímetro de pulso.', 
'Ligue imediatamente para 192 (SAMU). A inalação de vapores nitrosos ou cloro pode causar edema pulmonar até 24 a 48 horas após a exposição, exigindo internação para observação.', 
'SAMU 192 | Bombeiros 193 | CIT 0800 722 6001', 1),

(4, 4, 'Ingestão Acidental de Reagente Químico Tóxico ou Corrosivo', 'Sais de Chumbo, Compostos de Mercúrio, Cianetos, Ácido Oxálico, Formol', 'Queimação no trato gastrointestinal, dor abdominal intensa, náuseas, hálito químico, salivação excessiva.', 'CRITICA', 
'1. Interrompa imediatamente qualquer ingestão.\n2. Identifique com precisão absoluta o frasco, fórmula química e concentração do produto ingerido (guarde o rótulo/frasco para a equipe médica).\n3. Ligue IMEDIATAMENTE para o Centro de Informação Toxicológica (CIT - 0800 722 6001) com o rótulo em mãos.\n4. Se a vítima estiver consciente, oriente-a a enxaguar a boca várias vezes cuspindo a água, para remover resíduos orais.\n5. Se inconsciente mas com respiração, coloque em Posição Lateral de Segurança (PLS) para evitar aspiração de vômito espontâneo.\n6. Siga estritamente as instruções fornecidas pelo médico do CIT pelo telefone.', 
'- JAMAIS PROVOQUE VÔMITO se a substância for corrosiva (ácido ou base forte) ou derivado de petróleo/hidrocarboneto! O vômito causará nova queimadura corrosiva no esôfago e faringe, além de risco de aspiração pulmonar letal.\n- NÃO dê leite, vinagre, claras de ovos ou água em grande quantidade sem aval prévio do CIT.\n- NÃO administre substâncias via oral para vítimas desmaiadas ou sonolentas.', 
'Pipetadores mecânicos de segurança, recipientes devidamente rotulados com GHS/FISPQ.', 
'Ligue IMEDIATAMENTE para SAMU 192 e CIT 0800 722 6001. Tenha o nome químico exato e volume aproximado consumido.', 
'CIT 0800 722 6001 | SAMU 192', 1),

(5, 5, 'Incêndio em Bancada ou Roupa com Solvente Inflamável', 'Éter Dietílico, Hexano, Acetona, Etanol, Tolueno', 'Chamas na bancada, roupas em fogo, fumaça escura, calor intenso.', 'CRITICA', 
'1. Se o fogo for na roupa da pessoa: PARE, DEITE E ROLE no chão. Use o cobertor corta-fogo (fire blanket) ou chuveiro de emergência para abafar o fogo. NUNCA corra.\n2. Se o fogo for em vidraria pequena ou béquer: tampe com vidro de relógio, placa metálica ou cuba invertida para cortar o oxigênio (abafamento).\n3. Se o fogo for na bancada: desligue imediatamente o bico de Bunsen principal ou válvula de gás.\n4. Utilize o extintor adequado: PÓ QUÍMICO SECO (PQS - Classe B/C) ou GÁS CARBÔNICO (CO2). Mantenha distância de 2 a 3 metros e mire na base das chamas com movimento de varredura.\n5. Evacue o laboratório ordenadamente fechando portas para confinar as chamas se o fogo sair de controle.\n6. Acione o alarme de incêndio do prédio.', 
'- NUNCA use água sobre fogo de solventes orgânicos inflamáveis ou metais alcalinos (sódio/potássio)! A água espalha o solvente em chamas e pode causar explosão violenta.\n- NÃO permita que a vítima corra se estiver com as vestes em chamas.', 
'Extintores de CO2 e PQS inspecionados, cobertor corta-fogo, chuveiro de segurança.', 
'Se o fogo não for extinto nos primeiros 30 segundos ou se houver queimaduras corporais, acione o Corpo de Bombeiros (193) e SAMU (192). Evacue a área imediatamente.', 
'Bombeiros 193 | SAMU 192', 1),

(6, 6, 'Derramamento de Reagente Volátil ou Solvente Tóxico na Bancada/Chão', 'Benzeno, Clorofórmio, Bromo Líquido, Ácido Acético Glacial', 'Odor forte sufocante, poça de líquido evaporando com risco de ignição ou intoxicação.', 'ALTA', 
'1. Alerte imediatamente todas as pessoas presentes no laboratório sobre o derramamento.\n2. Elimine qualquer fonte de faísca ou chama aberta no raio de 10 metros.\n3. Aumente a ventilação do local: abra janelas e ligue sistemas de exaustão geral.\n4. Isole e sinalize a área com fita ou cones de advertência.\n5. Para solventes: verta vermiculita, areia seca ou pó absorvente específico sobre o líquido, circulando da borda para o centro para conter o espalhamento.\n6. Para ácidos derramados no chão: neutralizar com bicarbonato de sódio lentamente até cessar a efervescência, e só então absorver.\n7. Recolha o material contaminado usando pá plástica anti-faísca e acondicione em saco plástico grosso ou bombona identificada para incineração.', 
'- NÃO use aspirador de pó elétrico para aspirar líquidos inflamáveis (o motor produz faíscas internas).\n- NÃO jogue o resíduo químico recolhido na pia ou ralo comum.', 
'Kit de mitigação de derramamentos (spill kit), máscara com filtro vapor orgânico/gases ácidos, luvas nitrílicas resistentes.', 
'Se o volume derramado for superior a 1 litro de substância altamente tóxica/cancerígena ou atingir dutos de esgoto, comunique a Brigada de Emergência Química e os Bombeiros (193).', 
'Bombeiros 193 | Defesa Civil 199', 1),

(7, 7, 'Corte com Vidro Quebrado Contaminado por Agente Químico', 'Pipeta quebrada, termômetro de mercúrio quebrado, tubos de ensaio fraturados', 'Sangramento ativo, corte visível, suspeita de impregnação de resíduo químico na derme.', 'MEDIA', 
'1. Lave a ferida imediatamente sob água corrente abundante por 5 a 10 minutos para drenar e lavar quaisquer vestígios de compostos químicos solúveis.\n2. Permita um sangramento leve inicial para ajudar a expulsar contaminantes da ferida.\n3. Se o sangramento for abundante, aplique pressão direta sobre o corte com gaze estéril seca ou compressa limpa.\n4. Eleve o membro ferido acima do nível do coração para conter o fluxo hemorrágico.\n5. Se houver caco de vidro profundamente encravado, NÃO puxe; imobilize o objeto e faça curativo ao redor.\n6. Anote o reagente que estava na vidraria para repassar ao médico.\n7. Encaminhe a vítima à enfermaria ou pronto-socorro.', 
'- NUNCA remova cacos de vidro grandes ou profundos com pinças sem suporte cirúrgico (risco de hemorragia incontrolável).\n- NÃO recolha vidros quebrados com as mãos nuas (use vassoura e pá).\n- NÃO descarte vidrarias quebradas no lixo comum.', 
'Caixa rígida para perfurocortantes (Descarpack), luvas anti-corte, gaze estéril, faixas e ataduras.', 
'Busque pronto-socorro se a hemorragia não cessar após 10 minutos de compressão, se houver vidro retido na ferida, ou para profilaxia de tétano.', 
'SAMU 192 | Pronto-Socorro Local', 1);

-- Registro inicial de auditoria
INSERT INTO `logs_auditoria` (`usuario_id`, `usuario_nome`, `acao`, `tabela_afetada`, `registro_id`, `descricao`, `ip`) VALUES
(1, 'Administrador do Laboratório', 'INSERT', 'sistema', 1, 'Inicialização do banco de dados SOS Química com procedimentos padrão de laboratório.', '127.0.0.1');
