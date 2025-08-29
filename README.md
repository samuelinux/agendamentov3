# Sistema de Agendamento Multi-Empresa

Um sistema completo de agendamento desenvolvido em Laravel 11 com PHP 8.1, projetado para múltiplas empresas com funcionalidades avançadas de gestão de horários e disponibilidade.

## 🚀 Características Principais

### ✅ **Sistema Multi-Empresa**
- Cada empresa possui sua própria URL (ex: `/barbearia-do-joao`)
- Isolamento completo de dados entre empresas
- Painel administrativo específico para cada empresa

### ✅ **Gestão Inteligente de Horários**
- Geração dinâmica de horários baseada em jornadas de trabalho
- Suporte a agendamentos "colados" (sem intervalos obrigatórios)
- Validação em tempo real de disponibilidade
- Sistema de exceções (feriados, férias, saidinhas)

### ✅ **Interface Mobile-First**
- Design responsivo otimizado para dispositivos móveis
- Fluxo de agendamento em 3 passos simples
- Interface moderna com gradientes e animações

### ✅ **Autenticação Simplificada**
- Login de clientes apenas com telefone
- Cadastro automático para novos usuários
- Sistema de autenticação por níveis para administradores

## 🛠️ Tecnologias Utilizadas

- **Backend:** Laravel 11 + PHP 8.1
- **Frontend:** Livewire 3 + Blade Templates
- **Banco de Dados:** SQLite (facilmente alterável para MySQL/PostgreSQL)
- **Estilização:** CSS customizado com gradientes e animações
- **Arquitetura:** MVC com Services e Repository Pattern

## 📦 Instalação

### Pré-requisitos
- PHP 8.1 ou superior
- Composer
- SQLite (ou MySQL/PostgreSQL)

### Passos de Instalação

1. **Clone ou extraia o projeto:**
   ```bash
   cd sistema-agendamento
   ```

2. **Instale as dependências:**
   ```bash
   composer install
   ```

3. **Configure o ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Execute as migrations e seeders:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Inicie o servidor:**
   ```bash
   php artisan serve
   ```

6. **Acesse o sistema:**
   - Site principal: `http://localhost:8000`
   - Painel administrativo: `http://localhost:8000/admin/login`

## 👥 Contas de Demonstração

### Super Administrador
- **Email:** admin@sistema.com
- **Senha:** 123456
- **Função:** Gerencia todas as empresas do sistema

### Administrador da Barbearia
- **Email:** joao@barbearia.com
- **Senha:** 123456
- **Função:** Gerencia apenas a Barbearia do João

### Administrador do Salão
- **Email:** maria@salao.com
- **Senha:** 123456
- **Função:** Gerencia apenas o Salão da Maria

## 🏢 Empresas de Demonstração

### Barbearia do João
- **URL:** `/barbearia-do-joao`
- **Serviços:** Corte de Cabelo (30min), Barba (20min), Corte + Barba (45min)
- **Horário:** Segunda a Sexta: 8h-12h e 14h-18h | Sábado: 8h-12h

### Salão da Maria
- **URL:** `/salao-da-maria`
- **Serviços:** Corte Feminino (45min), Escova (30min), Manicure (40min)
- **Horário:** Terça a Sábado: 9h-12h e 14h-19h

## 🎯 Funcionalidades Implementadas

### Para Clientes
- [x] Seleção de empresa e serviço
- [x] Visualização de horários disponíveis
- [x] Agendamento com dados pessoais
- [x] Login simplificado por telefone
- [x] Cancelamento de agendamentos

### Para Administradores da Empresa
- [x] Gestão de serviços (CRUD completo)
- [x] Configuração de jornadas de trabalho
- [x] Gestão de exceções (feriados, férias, saidinhas)
- [x] Configuração de antecedência mínima
- [x] Visualização de agendamentos

### Para Super Administrador
- [x] Gestão de empresas (CRUD completo)
- [x] Habilitação/desabilitação de empresas
- [x] Controle total do sistema

## 📊 Estrutura do Banco de Dados

### Tabelas Principais
- **empresas:** Dados das empresas
- **usuarios:** Clientes e administradores
- **servicos:** Serviços oferecidos por cada empresa
- **jornadas_trabalho:** Horários de funcionamento
- **agendamentos:** Agendamentos realizados
- **excecoes_agenda:** Feriados, férias e saidinhas

### Relacionamentos
- Empresa → Serviços (1:N)
- Empresa → Jornadas de Trabalho (1:N)
- Empresa → Agendamentos (1:N)
- Empresa → Exceções de Agenda (1:N)
- Usuário → Agendamentos (1:N)
- Serviço → Agendamentos (1:N)

## 🔧 Configurações Avançadas

### Personalização de Slots
Cada empresa pode configurar o tamanho dos slots de agendamento editando o campo `tamanho_slot_minutos` na tabela `empresas`.

### Antecedência Mínima
Configure o tempo mínimo de antecedência para agendamentos no campo `antecedencia_minima_horas` da tabela `empresas`.

### Jornadas de Trabalho
As jornadas são configuráveis por dia da semana (0=Domingo, 1=Segunda, etc.) com horários de início e fim.

## 🚀 Próximas Funcionalidades (Roadmap)

- [ ] Integração com API do WhatsApp para notificações
- [ ] Relatórios de agendamentos e faturamento
- [ ] Sistema de avaliações e comentários
- [ ] Integração com sistemas de pagamento
- [ ] App mobile nativo
- [ ] Sistema de lembretes automáticos

## 🐛 Solução de Problemas

### Erro de Permissões
```bash
sudo chown samuel:samuel .gitignore
sudo chown samuel:samuel .env
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:$USER storage bootstrap/cache
sudo chmod -R u+rwX storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

```

### Erro de Banco de Dados
```bash
php artisan migrate:fresh --seed
```

### Erro de Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear

php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear
```

### Faça backup das mudanças
```bash
git add . && git commit -m "Pequenas mudanças" && git push
```

## 📝 Licença

Este projeto foi desenvolvido como um sistema de agendamento completo e funcional. Todos os direitos reservados.

## 🤝 Suporte

Para dúvidas ou suporte técnico, consulte a documentação ou entre em contato com a equipe de desenvolvimento.

---

**Desenvolvido com ❤️ usando Laravel 11**

