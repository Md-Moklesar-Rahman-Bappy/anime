# 🏗️ System Architecture

This document describes the architecture of the **Anime Project** 🎌 — a scalable Laravel-based anime streaming platform.

---

## 📌 Overview

The application follows a **layered MVC architecture** with clear separation of concerns:

User (Browser)
↓
Frontend (Blade + Vite + Tailwind + Alpine.js)
↓
Controllers (HTTP Layer)
↓
Services (Business Logic)
↓
Models (Eloquent ORM)
↓
Database (MySQL / SQLite)

---

## 🏗️ System Architecture Diagram

```
mermaid
flowchart TD
    User[👤 User Browser]
    Frontend[🎨 Frontend\n(Blade + Tailwind + Alpine)]
    Controller[⚙️ Controller]
    Service[🧠 Service Layer]
    Model[🗄️ Model (Eloquent)]
    DB[(🗃️ Database)]
    
    User --> Frontend
    Frontend --> Controller
    Controller --> Service
    Service --> Model
    Model --> DB
    DB --> Model
    Model --> Service
    Service --> Controller
    Controller --> Frontend
```

## 🧱 Core Layers
# 🎨 Frontend Layer

- Blade templates
- Tailwind CSS
- Alpine.js
- Vite build tool
 
---

# Responsibilities:

- UI rendering
- Interaction handling
- Video player integration (Plyr)

---

# 🧠 Service Layer
Location: app/Services
Responsibilities:

- Business logic
- Reusable logic
- Clean separation from controllers

---

# 🗄️ Model Layer
Location: app/Models
Responsibilities:

- Database interaction
- Relationships
- Query scopes

---

# 🗃️ Database Layer
Supported:

- MySQL (Production)
- SQLite (Testing)

Main Entities:

- Users
- Anime
- Episodes
- Genres
- Comments
- Watch History

---
#🔄 Request Lifecycle

```
sequenceDiagram
    participant User
    participant Route
    participant Controller
    participant Service
    participant Model
    participant DB

    User->>Route: Request
    Route->>Controller: Route match
    Controller->>Service: Process request
    Service->>Model: Query
    Model->>DB: Fetch data
    DB-->>Model: Result
    Model-->>Service: Data
    Service-->>Controller: Processed data
    Controller-->>User: Response
```

---

# 📂 Project Structure

```
app/
  ├── Models/
  ├── Services/
  ├── Http/
  │   └── Controllers/

resources/
  ├── views/
  ├── js/
  ├── css/

routes/
  └── web.php

database/
  ├── migrations/

tests/
```

---

# 🔌 External Integrations

- Jikan API → Anime metadata
- External scrapers → Episode sources
- YouTube → Video import

---

# 🔐 Security Architecture

- Laravel authentication (Breeze)
- CSRF protection
- Input validation
- Role-based access
- .env configuration

---

# ⚡ Performance Strategy

- Eager loading relationships
- Cache optimization (config, routes, views)
- Lazy loading when needed
- Optimized database indexing

---

# 🎮 Video Player Architecture

- Plyr.js integration
- Multiple video sources
- Skip intro/outro
- Auto-next functionality

---

# 🚀 CI/CD Pipeline

```
flowchart LR
    Dev[👨‍💻 Push Code]
    GitHub[📦 GitHub]
    CI[✅ Tests]
    Security[🔐 Scan]
    Deploy[🚀 Deploy]
    Server[🖥️ Server]

    Dev --> GitHub
    GitHub --> CI
    CI --> Security
    Security --> Deploy
    Deploy --> Server
```

---

# 🔐 DevOps Workflow

```
flowchart TD
    Code[💻 Code]
    Test[🧪 Tests]
    Scan[🔍 Security]
    Merge[🔀 Merge]
    Release[📦 Release]
    Deploy[🚀 Deploy]

    Code --> Test
    Test --> Scan
    Scan --> Merge
    Merge --> Release
    Release --> Deploy
```

---

# 🗄️ Database Diagram

```
erDiagram
    USERS ||--o{ WATCH_HISTORY : has
    USERS ||--o{ COMMENTS : writes

    ANIME ||--o{ EPISODES : contains
    ANIME ||--o{ GENRES : belongs_to

    EPISODES ||--o{ COMMENTS : has

    USERS {
        id int
        name string
    }

    ANIME {
        id int
        title string
    }

    EPISODES {
        id int
        anime_id int
    }
```

---

# ⚠️ Scalability Plan
To scale:

- Redis caching
- Queue workers
- CDN for media
- Load balancer
- Horizontal scaling

---

# 🧠 Design Principles

- Separation of Concerns
- DRY (Don't Repeat Yourself)
- SOLID principles
- Clean architecture

---

# ✅ Example Flow

```
User clicks "Watch Episode"
→ Route triggered
→ Controller handles request
→ Service processes logic
→ Model fetches data
→ View renders video
```

---

# 🎯 Future Improvements

- GraphQL API
- WebSockets (real-time)
- Microservices architecture
- Advanced caching

---

# 📌 Final Notes

- Keep controllers thin
- Move logic into services
- Optimize queries
- Maintain modular architecture
