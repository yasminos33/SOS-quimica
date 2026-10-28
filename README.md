# SOS Química 🚨🧪

> **Sistema de Resposta Imediata e Primeiros Socorros para Acidentes em Laboratórios de Química**  
> Desenvolvido de acordo com as especificações e regras de negócio contidas em [`requisitos.md`](file:///c:/Users/Aluno/Desktop/sos-quimica/requisitos.md).

---

## 🎯 Objetivo do Sistema

Oferecer auxílio rápido, eficiente e intuitivo a estudantes, monitores e professores em caso de ocorrência de acidentes em laboratórios de química (queimaduras por ácidos/bases, respingos oculares, inalação de gases tóxicos, ingestão acidental, incêndios com solventes, cortes com vidraria contaminada).

Além da resposta emergencial, o sistema disponibiliza uma **Central de Pesquisa e Suporte Laboratorial** com informações preventivas sobre reagentes, pictogramas GHS e fichas de segurança (FISPQ).

---

## ⚡ Conformidade com os Requisitos (`requisitos.md`)

| Requisito do Projeto | Como Foi Implementado | Arquivos Relacionados |
| :--- | :--- | :--- |
| **Sem necessidade de login para o usuário comum** | Acesso 100% público e imediato para consulta de emergências na tela principal. O aluno pode **digitar** o reagente/sintoma ou **selecionar** a categoria em cards rápidos. | [`index.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/index.php), [`pesquisa.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/pesquisa.php), [`detalhes.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/detalhes.php) |
| **Senhas tratadas com Hash Bcrypt** | As senhas de administradores e monitores são obrigatoriamente protegidas com `password_hash($senha, PASSWORD_BCRYPT)` e autenticadas via `password_verify()`. | [`actions/login.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/actions/login.php), [`actions/salvar_usuario.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/actions/salvar_usuario.php), [`admin/usuarios.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/admin/usuarios.php) |
| **Página de Históricos e Logs de Auditoria** | Tabela dedicada `logs_auditoria` gravando usuário, ação (INSERT, UPDATE, DELETE, SEARCH, LOGIN), IP, timestamp e diff de dados anteriores/novos em JSON. | [`historico.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/historico.php), [`includes/funcoes.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/includes/funcoes.php) |
| **Uso estrito de PDO contra SQL Injections** | Todas as consultas utilizam PDO com *prepared statements* nativos (`ATTR_EMULATE_PREPARES => false`), eliminando vulnerabilidades de injeção SQL. | [`config/bd.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/config/bd.php), [`actions/`](file:///c:/Users/Aluno/Desktop/sos-quimica/actions/) |
| **Separação Lógica (Sem Monólito)** | Conexão isolada em `config/bd.php`, scripts de regras/processamento em `actions/`, componentes reutilizáveis em `includes/` e views limpas. | Toda a estrutura modular do projeto |
| **Estilização Tailwind CSS (Mobile-First)** | Layout 100% responsivo com botões de toque rápido para smartphones, barra de ligação direta para SAMU 192 e Bombeiros 193, estilizado na elegante paleta **Verde Musgo**. | [`includes/header.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/includes/header.php), [`includes/footer.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/includes/footer.php) |
| **Mensagens de Erro/Sucesso via TOAST** | Sistema de notificações flutuantes (Toasts) automáticos via sessão PHP e acionáveis via JavaScript (`mostrarToast()`). | [`includes/toast.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/includes/toast.php) |
| **Substituição de caixas nativas por MODAL** | Todos os diálogos, avisos e confirmações de exclusão substituem o `alert()` e `confirm()` nativos por um componente de Modal elegante e moderno. | [`includes/modal.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/includes/modal.php), [`assets/js/app.js`](file:///c:/Users/Aluno/Desktop/sos-quimica/assets/js/app.js) |

---

## 📁 Estrutura de Arquivos e Diretórios

```text
sos-quimica/
├── actions/                         # Scripts de backend isolados (Regras de negócio)
│   ├── buscar.php                   # Endpoint AJAX para pesquisa dinâmica
│   ├── excluir_procedimento.php     # Exclusão com auditoria (snapshot no log)
│   ├── login.php                    # Autenticação com password_verify (Bcrypt)
│   ├── logout.php                   # Destruição segura de sessão e log de saída
│   ├── salvar_procedimento.php      # Criação/Edição de protocolos com auditoria
│   └── salvar_usuario.php           # Gestão de monitores com hash Bcrypt
├── admin/                           # Painel de gestão restrita (Docentes/Monitores)
│   ├── form_procedimento.php        # Formulário de protocolo de emergência
│   ├── gerenciar.php                # Listagem e gestão de protocolos (com Modal)
│   ├── index.php                    # Dashboard com métricas e histórico recente
│   ├── login.php                    # Tela de autenticação administrativa
│   └── usuarios.php                 # Controle de usuários e senhas seguras
├── assets/                          # Recursos estáticos de front-end
│   ├── css/
│   │   └── style.css                # Estilos adicionais e modo de impressão
│   └── js/
│       └── app.js                   # Live-search, gatilhos de Modal e Toasts
├── config/
│   └── bd.php                       # Conexão PDO segura (MySQL / Fallback resiliente)
├── database/
│   └── schema.sql                   # Esquema completo do MySQL com seeds
├── includes/                        # Componentes reutilizáveis
│   ├── footer.php                   # Rodapé com telefones 192/193 e barra mobile
│   ├── funcoes.php                  # Helper de auditoria, badges e toasts
│   ├── header.php                   # Topo com Tailwind CSS e navegação
│   ├── modal.php                    # Componente Modal customizado
│   └── toast.php                    # Componente Toast flutuante
├── detalhes.php                     # Ficha completa do protocolo (passo a passo + proibidos)
├── historico.php                    # Página oficial de auditoria e logs de alterações
├── index.php                        # PÁGINA PRINCIPAL SOS (Sem login / Busca rápida)
├── pesquisa.php                     # Central de pesquisa de reagentes e pictogramas GHS
├── README.md                        # Documentação técnica do sistema
└── requisitos.md                    # Especificação original de requisitos
```

---

## 🚀 Como Executar o Projeto

### Opção 1: Servidor Embutido do PHP (Rápido e Direto)
Abra o terminal na pasta do projeto e execute:
```bash
php -S localhost:8080
```
Acesse no seu navegador: **`http://localhost:8080`**

### Opção 2: Com XAMPP, WampServer ou Laragon (MySQL)
1. Coloque a pasta do projeto dentro de `htdocs` (XAMPP) ou `www` (WampServer).
2. Abra o **phpMyAdmin** e crie a base de dados `sos_quimica`.
3. Importe o script [`database/schema.sql`](file:///c:/Users/Aluno/Desktop/sos-quimica/database/schema.sql).
4. As configurações de banco estão centralizadas em [`config/bd.php`](file:///c:/Users/Aluno/Desktop/sos-quimica/config/bd.php) (padrão: `localhost`, usuário: `root`, sem senha).
5. Abra no navegador: **`http://localhost/sos-quimica`**

---

## 🔐 Credenciais de Acesso Administrativo (Demonstração)

- **URL de Login:** `admin/login.php`
- **E-mail:** `admin@sosquimica.com`
- **Senha:** `admin123`
- *(A senha foi protegida utilizando o algoritmo de hashing nativo `PASSWORD_BCRYPT`).*

---

## 📞 Telefones Úteis de Emergência Química e Médica
- **SAMU:** `192`
- **Corpo de Bombeiros:** `193`
- **Centro de Informação Toxicológica (CIT - Nacional):** `0800 722 6001`
- **Defesa Civil:** `199`
