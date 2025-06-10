# Frontend Web - Carona Feliz

Frontend web desenvolvido em PHP para o sistema de compartilhamento de caronas Carona Feliz.

## 🌟 Características

- **Design Responsivo**: Interface moderna e responsiva usando Bootstrap 5
- **Tema Verde**: Cores harmoniosas em tons de verde (#2E7D32, #4CAF50, #C8E6C9)
- **Autenticação Completa**: Sistema de login/registro integrado com a API Laravel
- **Gestão de Caronas**: Interface completa para motoristas gerenciarem suas ofertas
- **Busca Avançada**: Sistema de filtros para passageiros encontrarem caronas
- **Imagens de Veículos**: Exibição de fotos dos veículos com suporte a CORS
- **Notificações**: Sistema de mensagens flash para feedback ao usuário

## 🚀 Funcionalidades

### Para Motoristas
- ✅ Criar novas caronas
- ✅ Visualizar e gerenciar ofertas criadas
- ✅ Aprovar/rejeitar solicitações de passageiros
- ✅ Cancelar caronas
- ✅ Upload e visualização de fotos do veículo

### Para Passageiros
- ✅ Buscar caronas com filtros avançados
- ✅ Solicitar participação em caronas
- ✅ Acompanhar status das solicitações
- ✅ Cancelar solicitações pendentes
- ✅ Visualizar detalhes completos das caronas

### Para Ambos (Motorista + Passageiro)
- ✅ Dashboard personalizado baseado no tipo de usuário
- ✅ Navegação intuitiva entre funcionalidades
- ✅ Perfil de usuário integrado

## 📋 Pré-requisitos

- **PHP 8.0+** com extensões:
  - `curl`
  - `json`
  - `mbstring`
  - `session`
- **Servidor Web** (Apache, Nginx ou servidor embutido do PHP)
- **Laravel Backend** rodando em `http://localhost:8000`

## 🛠️ Instalação e Configuração

### 1. Clonar o Repositório
```bash
cd /caminho/para/carona-main/frontend
```

### 2. Configurar Servidor Web

#### Opção A: Servidor Embutido do PHP (Desenvolvimento)
```bash
cd frontend
php -S localhost:8080
```

#### Opção B: Apache/Nginx
Configure o documento root para apontar para a pasta `frontend/`

### 3. Configurar Conexão com Backend

Edite o arquivo `includes/config.php` se necessário:

```php
// Configurações gerais
define('SITE_TITLE', 'Carona Feliz');
define('BASE_URL', 'http://localhost:8080'); // URL do frontend
define('STORAGE_URL', 'http://localhost:8000/storage'); // URL do storage do Laravel
```

### 4. Verificar Backend Laravel

Certifique-se que o backend está rodando:
```bash
cd ../backend
php artisan serve
```

O backend deve estar acessível em `http://localhost:8000`

## 🗂️ Estrutura do Projeto

```
frontend/
├── includes/
│   ├── config.php         # Configurações e funções auxiliares
│   ├── api_client.php     # Cliente para comunicação com a API Laravel
│   ├── header.php         # Template do cabeçalho
│   └── footer.php         # Template do rodapé
├── assets/
│   ├── css/
│   │   └── style.css      # Estilos customizados
│   └── js/
│       └── main.js        # JavaScript auxiliar
├── pages/                 # Páginas adicionais (se necessário)
├── index.php             # Página inicial com redirecionamento inteligente
├── login.php             # Página de autenticação
├── register.php          # Página de cadastro
├── logout.php            # Script de logout
├── dashboard.php         # Dashboard principal
├── search_rides.php      # Busca de caronas
├── create_ride.php       # Criação de caronas
├── my_rides.php          # Caronas do motorista
├── my_requests.php       # Solicitações do passageiro
├── ride_details.php      # Detalhes de uma carona
├── ride_requests.php     # Gerenciar solicitações (motorista)
└── README.md             # Este arquivo
```

## 🔧 Configuração de Desenvolvimento

### Configurações de Debug

Para ativar o modo debug, adicione ao `config.php`:

```php
// Debug (apenas desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### URLs de Desenvolvimento Padrão

- **Frontend**: `http://localhost:8080`
- **Backend API**: `http://localhost:8000`
- **Imagens**: `http://localhost:8000/storage-image/`

## 🎨 Customização de Tema

### Cores Principais

O sistema usa um esquema de cores verde definido em `assets/css/style.css`:

```css
:root {
    --primary-green: #2E7D32;    /* Verde escuro */
    --medium-green: #4CAF50;     /* Verde médio */
    --light-green: #C8E6C9;      /* Verde claro */
    --success-green: #4CAF50;    /* Verde de sucesso */
}
```

### Modificar Cores

Para alterar as cores do tema, edite as variáveis CSS no arquivo `assets/css/style.css`.

## 🔐 Autenticação

O sistema usa sessões PHP para manter o estado de autenticação:

- **Token**: Armazenado em `$_SESSION['token']`
- **Dados do Usuário**: Cache em `$_SESSION['user']`
- **Validade**: Validada a cada requisição via API

### Fluxo de Autenticação

1. Usuário faz login via `login.php`
2. Credenciais enviadas para API Laravel
3. Token JWT retornado e armazenado na sessão
4. Requisições subsequentes incluem o token
5. Logout limpa a sessão

## 🖼️ Imagens de Veículos

### Configuração CORS

As imagens dos veículos são servidas pelo Laravel com headers CORS personalizados através da rota:

```
GET /storage-image/{path}
```

### Fallback de Imagens

O sistema inclui JavaScript para fallback automático em caso de erro no carregamento:

```javascript
// Em assets/js/main.js
document.querySelectorAll('img[data-fallback="true"]').forEach(img => {
    img.onerror = function() {
        this.style.display = 'none';
    };
});
```

## 📱 Responsividade

O frontend é totalmente responsivo usando Bootstrap 5:

- **Desktop**: Layout completo com sidebar
- **Tablet**: Layout adaptado para telas médias
- **Mobile**: Layout otimizado para touch

### Breakpoints

- `xs`: < 576px (celulares)
- `sm`: ≥ 576px (celulares grandes)
- `md`: ≥ 768px (tablets)
- `lg`: ≥ 992px (desktops)
- `xl`: ≥ 1200px (telas grandes)

## 🔍 Sistema de Busca

### Filtros Disponíveis

- **Origem**: Cidade/estado de partida
- **Destino**: Cidade/estado de chegada
- **Data**: Data da viagem (apenas futuras)
- **Preço Máximo**: Valor limite por passageiro

### Ordenação

Por padrão, os resultados são ordenados por:
1. Data de partida (mais próximas primeiro)
2. Preço (menores primeiro)
3. Data de criação (mais recentes primeiro)

## 🚨 Tratamento de Erros

### Tipos de Erro

1. **Erros de Validação**: Campos obrigatórios, formatos inválidos
2. **Erros de API**: Problemas de comunicação com o backend
3. **Erros de Autorização**: Acesso negado, token inválido
4. **Erros de Sistema**: Problemas internos do servidor

### Sistema de Flash Messages

```php
// Definir mensagem
setFlash('success', 'Operação realizada com sucesso!');
setFlash('error', 'Ocorreu um erro ao processar a solicitação.');
setFlash('warning', 'Atenção: verifique os dados informados.');
setFlash('info', 'Informação importante sobre a operação.');

// Exibir mensagem (automático no header.php)
$flash = getFlash();
if ($flash) {
    echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
}
```

## 📞 Suporte

### Problemas Comuns

#### 1. Erro 500 - Internal Server Error
- Verificar se o PHP tem as extensões necessárias
- Verificar permissões de arquivo
- Verificar logs do servidor web

#### 2. Erro de Conexão com API
- Verificar se o Laravel está rodando em `localhost:8000`
- Verificar configurações de CORS no backend
- Verificar se as rotas da API estão funcionando

#### 3. Imagens não Carregam
- Verificar se a rota `/storage-image/{path}` está configurada no Laravel
- Verificar permissões da pasta `storage/`
- Verificar configurações de CORS

#### 4. Sessão Não Mantém Login
- Verificar se as sessões PHP estão habilitadas
- Verificar permissões da pasta de sessões
- Verificar se o `session_start()` é chamado corretamente

### Logs

#### Logs PHP
```bash
# Ubuntu/Debian
tail -f /var/log/apache2/error.log

# CentOS/RHEL
tail -f /var/log/httpd/error_log

# Servidor embutido PHP
# Os erros aparecem no terminal onde o servidor foi iniciado
```

#### Logs Laravel (Backend)
```bash
cd ../backend
tail -f storage/logs/laravel.log
```

## 🔄 Atualizações

### Backup Antes de Atualizar

```bash
# Backup do frontend
cp -r frontend/ frontend_backup_$(date +%Y%m%d)/
```

### Verificar Compatibilidade

Sempre verificar:
1. Versão do PHP
2. Compatibilidade com API do Laravel
3. Dependências JavaScript (Bootstrap, FontAwesome)

## 📈 Performance

### Otimizações Implementadas

- **CSS/JS Minificado**: Arquivos otimizados para produção
- **Lazy Loading**: Imagens carregadas conforme necessário
- **Cache de Sessão**: Dados do usuário em cache local
- **Compressão**: Headers de compressão quando disponível

### Monitoramento

Para monitorar performance:

```bash
# Verificar uso de CPU/memória
top -p $(pgrep php)

# Verificar logs de acesso
tail -f /var/log/apache2/access.log | grep "frontend"
```

## 🌐 Deploy em Produção

### Checklist Pré-Deploy

- [ ] Desabilitar debug (`display_errors = 0`)
- [ ] Configurar HTTPS
- [ ] Configurar URLs de produção em `config.php`
- [ ] Configurar headers de segurança
- [ ] Testar todas as funcionalidades
- [ ] Verificar backup do banco de dados

### Configurações de Produção

```php
// config.php - Produção
define('BASE_URL', 'https://seudominio.com');
define('STORAGE_URL', 'https://api.seudominio.com/storage');

// Desabilitar debug
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

---

**Desenvolvido com ❤️ para facilitar o compartilhamento de caronas**

Para dúvidas ou sugestões, consulte a documentação da API Laravel ou entre em contato com a equipe de desenvolvimento. 