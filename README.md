# 🏖️ Sistema di Gestione Ferie e Permessi Aziendali

Un sistema completo e moderno per la gestione delle richieste di ferie e permessi aziendali, sviluppato con **Laravel 11** e **Vue.js 3** utilizzando **Inertia.js**.

## 🎯 Panoramica

Il **Sistema di Gestione Ferie e Permessi** è una soluzione enterprise-ready progettata per automatizzare e semplificare il processo di richiesta, approvazione e gestione delle assenze aziendali. Il sistema supporta workflow complessi con diversi ruoli utente e include funzionalità avanzate come notifiche email, validazione delle sovrapposizioni e dashboard analitiche.

### Caratteristiche Distintive

- **🔐 Autenticazione Sicura**: Login tradizionale
- **👥 Gestione Ruoli**: Sistema granulare con 4 ruoli (Employee, Manager, HR, Admin IT)
- **📧 Notifiche Email**: Sistema automatico di notifiche per tutte le azioni
- **🎨 UI/UX Moderna**: Interfaccia responsive con supporto tema scuro/chiaro
- **⚡ Performance**: Architettura SPA con Inertia.js per prestazioni ottimali
- **🛡️ Sicurezza**: Validazione lato server e client, prevenzione sovrapposizioni
- **📊 Analytics**: Dashboard con statistiche e filtri avanzati

## 🚀 Funzionalità Principali

### 👤 Gestione Utenti e Autenticazione

- **Registrazione e Login**: Sistema completo con email/password
- **Gestione Profili**: Aggiornamento dati personali e password
- **Struttura Organizzativa**: Assegnazione a unità organizzative e manager

### 📝 Gestione Richieste di Ferie

#### Per i Dipendenti:
- **Creazione Richieste**: Form intuitivo per ferie e permessi
- **Validazione Date**: Controllo automatico sovrapposizioni e date passate
- **Tracking Status**: Visualizzazione stato richieste (Pending, Approved, Rejected)
- **Storico Completo**: Accesso a tutte le richieste passate con dettagli

#### Per i Manager:
- **Dashboard Dedicata**: Interfaccia specifica per approvazioni
- **Notifiche Real-time**: Badge con conteggio richieste pending
- **Filtri Avanzati**: Ricerca per utente, tipo, date
- **Azioni Rapide**: Approvazione/rifiuto con note personalizzate

#### Per HR:
- **Vista Globale**: Accesso a tutte le richieste aziendali
- **Gestione Avanzata**: Creazione richieste per conto di altri utenti
- **Analytics**: Statistiche e report dettagliati
- **Controllo Completo**: Possibilità di cancellare richieste approvate

### 📧 Sistema di Notifiche

- **Email Automatiche**: Invio notifiche per ogni cambio di stato
- **Notifiche Manager**: Alert automatici per nuove richieste
- **Feedback Utente**: Banner di conferma per ogni azione

### 🎨 Interfaccia Utente

- **Design Moderno**: UI basata su Tailwind CSS e Shadcn/ui
- **Responsive**: Ottimizzata per desktop, tablet e mobile
- **Tema Scuro/Chiaro**: Supporto completo per entrambi i temi
- **Accessibilità**: Contrasti WCAG AA compliant
- **Animazioni**: Transizioni fluide e feedback visivo

## 🏗️ Architettura del Sistema

### Backend (Laravel 11)

```
app/
├── Http/Controllers/
│   ├── LeaveRequestController.php    # Gestione richieste ferie
│   └── ManagerController.php         # Dashboard manager
├── Models/
│   ├── User.php                      # Modello utente con ruoli
│   ├── LeaveRequest.php              # Modello richieste
│   └── OrganizationalUnit.php       # Unità organizzative
├── Services/
│   └── LeaveRequestService.php      # Business logic centralizzata
└── Mail/
    ├── LeaveRequestStatusUpdated.php # Email cambio stato
    └── NewLeaveRequestNotification.php # Email nuove richieste
```

### Frontend (Vue.js 3 + Inertia.js)

```
resources/js/
├── pages/
│   ├── Dashboard.vue                 # Dashboard principale
│   ├── Manager/Dashboard.vue         # Dashboard manager
│   └── auth/                        # Pagine autenticazione
├── components/
│   ├── NotificationBanner.vue       # Sistema notifiche
│   ├── AppSidebar.vue              # Navigazione laterale
│   └── ui/                         # Componenti UI riutilizzabili
└── layouts/
    └── AppLayout.vue               # Layout principale
```

### Database Schema

```sql
-- Tabelle principali
users                    # Utenti del sistema
organizational_units     # Struttura organizzativa
leave_requests          # Richieste di ferie/permessi

-- Relazioni
users.manager_id → users.id
users.organizational_unit_id → organizational_units.id
leave_requests.user_id → users.id
leave_requests.manager_id → users.id
leave_requests.approver_id → users.id
```

## 💻 Tecnologie Utilizzate

### Backend
- **Laravel 11.31**: Framework PHP moderno
- **PHP 8.2+**: Linguaggio backend
- **MySQL**: Database relazionale
- **Inertia.js**: Bridge SPA senza API
- **Laravel Socialite**: Autenticazione OAuth

### Frontend
- **Vue.js 3.5**: Framework JavaScript reattivo
- **TypeScript**: Type safety e developer experience
- **Tailwind CSS 4.1**: Utility-first CSS framework
- **Shadcn/ui**: Componenti UI professionali
- **Vite 6.0**: Build tool veloce
- **Lucide Icons**: Libreria icone moderne

### DevOps & Tools
- **Composer**: Dependency manager PHP
- **npm**: Dependency manager JavaScript
- **ESLint + Prettier**: Code quality e formatting
- **PHPUnit**: Testing framework PHP
- **Laravel Pint**: Code style fixer

## 📋 Requisiti di Sistema

### Requisiti Minimi
- **PHP**: 8.2 o superiore
- **Node.js**: 18.0 o superiore
- **Composer**: 2.0 o superiore
- **Database**: MySQL 8.0+ o MariaDB 10.3+
- **Web Server**: Apache 2.4+ o Nginx 1.18+

## 🚀 Installazione e Setup

### 1. Clonazione del Repository

```bash
git clone <repository-url>
cd gestionale-ferie
```

### 2. Installazione Dipendenze Backend

```bash
# Installa dipendenze PHP
composer install

# Copia file di configurazione
cp .env.example .env

# Genera chiave applicazione
php artisan key:generate
```

### 3. Installazione Dipendenze Frontend

```bash
# Installa dipendenze Node.js
npm install

# Build assets per produzione (opzionale)
npm run build
```

### 4. Configurazione Database

```bash
# Crea database MySQL
mysql -u root -p
CREATE DATABASE gestionale_ferie;
EXIT;

# Esegui migrazioni
php artisan migrate

# Popola database con dati di esempio
php artisan db:seed
```

### 5. Avvio Ambiente di Sviluppo

```bash
# Metodo 1: Comando unificato (raccomandato)
composer run dev

# Metodo 2: Comandi separati
# Terminal 1: Server Laravel
php artisan serve

# Terminal 2: Build assets
npm run dev

# Terminal 3: Queue worker (per email)
php artisan queue:work
```

L'applicazione sarà disponibile su `http://localhost:8000`

## ⚙️ Configurazione

### File .env Principale

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestionale_ferie
DB_USERNAME=root
DB_PASSWORD=

# Email (per notifiche)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Gestionale Ferie"
```

### Configurazione Email

Per abilitare le notifiche email:

1. **Gmail**: Genera una password per app nelle impostazioni Google
2. **SMTP Personalizzato**: Configura il tuo server SMTP
3. **Servizi Cloud**: Usa Mailgun, SendGrid, etc.

## 📖 Utilizzo

### Ruoli Utente

#### 👤 Employee (Dipendente)
- Crea richieste di ferie e permessi
- Visualizza storico delle proprie richieste
- Riceve notifiche email per approvazioni/rifiuti

#### 👨‍💼 Manager (Responsabile)
- Approva/rifiuta richieste dei propri subordinati
- Accede alla Manager Dashboard
- Riceve notifiche per nuove richieste

#### 👩‍💼 HR (Risorse Umane)
- Vista globale di tutte le richieste aziendali
- Crea richieste per conto di altri utenti
- Gestisce approvazioni per richieste HR
- Accesso a filtri e analytics avanzati

#### 🔧 Admin IT
- Accesso completo al sistema
- Gestione utenti e configurazioni
- Vista amministrativa con tutti i dati

### Workflow Tipico

1. **Dipendente** crea richiesta ferie
2. **Sistema** valida date e sovrapposizioni
3. **Manager** riceve notifica email
4. **Manager** approva/rifiuta con note
5. **Dipendente** riceve conferma email
6. **HR** monitora statistiche globali

### Funzionalità Avanzate

#### Validazione Sovrapposizioni
Il sistema previene automaticamente:
- Richieste con date sovrapposte
- Richieste per date passate
- Conflitti con richieste già approvate

#### Filtri e Ricerca
- **Per tipo**: Ferie vs Permessi
- **Per stato**: Pending, Approved, Rejected
- **Per utente**: Nome dipendente
- **Per date**: Range temporale
- **Per unità**: Reparto/divisione

## 🗄️ Struttura del Database

### Tabella `users`
```sql
id, name, email, password, manager_id, is_manager, 
role, organizational_unit_id, created_at, updated_at
```

### Tabella `leave_requests`
```sql
id, user_id, manager_id, approver_id, type, start_date, 
end_date, days_count, reason, status, manager_notes, 
approved_at, rejected_at, cancelled_at, created_at, updated_at
```

### Tabella `organizational_units`
```sql
id, name, description, created_at, updated_at
```

## 🔗 API e Endpoints

### Autenticazione
- `GET /login` - Pagina di login
- `POST /login` - Autenticazione utente
- `GET /register` - Pagina registrazione
- `POST /register` - Registrazione nuovo utente

### Dashboard
- `GET /dashboard` - Dashboard principale con filtri

### Richieste Ferie
- `POST /leave-requests` - Crea nuova richiesta
- `POST /leave-requests/{id}/approve` - Approva richiesta
- `POST /leave-requests/{id}/reject` - Rifiuta richiesta
- `POST /leave-requests/{id}/cancel` - Cancella richiesta
- `DELETE /leave-requests/{id}` - Elimina richiesta

### Manager
- `GET /manager/dashboard` - Dashboard manager
- `POST /manager/approve/{id}` - Approvazione rapida
- `POST /manager/reject/{id}` - Rifiuto rapido

### API Utilities
- `GET /api/users` - Lista utenti (per HR)