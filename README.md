# PW Panel 1.6.5

Admin panel for **Perfect World private server** (version 1.7.2 / 1.7.4) built with Laravel 12.

---

## ✨ Features

### Account Management
- List all accounts with search & pagination
- View account detail (ID, username, role, status)
- **Add Cubi Gold** — add in-game currency (Gold) to any account via socket
- **Add Cubi Coin** — add in-game currency (Coin) to any account via socket
- Reset account password

### Character Management
- List characters per account
- View character detail:
  - Basic info (name, class, level, realm, map, coordinates)
  - Stats (HP, MP, vitality, strength, agility, energy, defense, armor)
  - 3-column layout: info / stats / equipment paperdoll
- Equipment paperdoll — click item cell to view item details (name, description, color, sharpening, skills)
- **XML Editor** — full raw character data viewer & editor:
  - Syntax highlighting (dark theme, atom-one-dark / dracula)
  - Line numbers
  - Search with match navigation (▲▼, Enter/Shift+Enter, Escape)
  - Edit mode with **CodeMirror 5** (XML syntax highlighting, dracula theme)
  - Line wrap toggle (default: wrap on)
  - Search works in both view mode and edit mode
  - Save edited XML back to game database via socket
  - Copy to clipboard

### Server / Game Connection
- All data read/write via **TCP socket** to game backend (gamedbd / gdeliveryd)
- Binary protocol: `Stream` class for read/write, `GRoleReader` for character decode/encode
- Supports opcode-based communication (read=4, write=3, forward=6)

---

## 🛠 Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| MariaDB / MySQL | 10.x+ |
| PW Server | 1.7.2 or 1.7.4 |
| Node.js (frontend build) | 18+ |
| Composer | 2.x |

### PHP Extensions required
- `sockets` — TCP socket to game server
- `pdo_mysql` — database connection
- `mbstring`, `xml`, `dom` — XML processing
- `bcmath`, `ctype`, `fileinfo`, `openssl`, `tokenizer`

---

## ⚙️ Installation

```bash
git clone https://github.com/hirely2-cyber/pw-panel-1.6.5-.git
cd pw-panel-1.6.5-

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_pw_database
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# PW game server socket settings
PW_SOCKET_HOST=127.0.0.1
PW_SOCKET_PORT=65000     # socket proxy
PW_DB_PORT=29400         # gamedbd port
```

```bash
php artisan migrate
php artisan serve
```

---

## 🗂 Project Structure

```
app/
  Http/Controllers/Panel/   # AccountController, CharacterController, ...
  Services/PW/              # CashService (Cubi Gold/Coin)
  Models/                   # Account, Character models
  Libs/                     # Stream, GRoleReader, GRole (binary protocol)
resources/views/panel/      # Blade templates
  account/                  # Account list, detail
  character/                # Character detail, XML editor
routes/
  panel.php                 # All panel routes
```

---

## 🤝 Contributing

Pull requests are welcome! Here are areas that need help:

- **Character struct** — decode more binary fields (Spirit, Soulforce, Accuracy, Evasion)
- **extraprop** — decode the `extraprop` binary blob structure
- **Version support** — test/adapt for PW 1.5.x / 1.3.x server versions
- **Skill viewer** — display character skills from binary
- **Inventory viewer** — full bag/equipment inventory grid
- **Logs viewer** — game activity logs per character
- **Access control** — role-based admin permissions (super admin / GM / viewer)
- **i18n** — multi-language support (EN/CN/ID)

### How to contribute
1. Fork this repo
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Commit your changes: `git commit -m "feat: description"`
4. Push and open a Pull Request

---

## 📋 Known Limitations

- Spirit / Soulforce / Accuracy / Evasion stats are **computed server-side** — not stored in binary, cannot be read/edited directly
- `extraprop` binary format is not fully decoded yet
- Tested only on PW server version **1.7.2** and **1.7.4**

---

## 📄 License

MIT License — free to use, modify, and distribute.
