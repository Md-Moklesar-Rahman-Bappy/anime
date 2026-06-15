# Agent Collaboration System

An automated multi-agent system for the AniWaves project using PowerShell scripts on a local XAMPP environment.

## Agent Roles

| Agent | Script | Role |
|-------|--------|------|
| **Orchestrator** | `agent-orchestrator.ps1` | Coordinates full pipeline |
| **PM** | `agent-pm.ps1` | Task management, PR reviews, sprint tracking |
| **Frontend** | `agent-frontend.ps1` | Vite builds, UI linting, asset checks |
| **Backend** | `agent-backend.ps1` | Migrations, optimizations, route checks |
| **QA** | `agent-qa.ps1` | PHPUnit tests, Pint linting, bug reports, build approval |
| **DevOps** | `agent-devops.ps1` | Deploy, rollback, environment status, log monitoring |

## Quick Start

```powershell
# View overall status
.\agents\scripts\agent-orchestrator.ps1 -Action status

# Create a task and run full pipeline
.\agents\scripts\agent-orchestrator.ps1 -Action full-pipeline -TaskTitle "Add search autocomplete" -TaskDescription "Implement live search with Alpine.js" -Assignee frontend

# Run individual cycles
.\agents\scripts\agent-orchestrator.ps1 -Action dev-cycle    # migrate + build
.\agents\scripts\agent-orchestrator.ps1 -Action qa-cycle     # test + lint + approve
.\agents\scripts\agent-orchestrator.ps1 -Action deploy-cycle # deploy + optimize
```

## Agent Commands

### PM (Project Manager)
```powershell
.\agents\scripts\agent-pm.ps1 -Action create-task -Title "Fix login bug" -Assignee backend -Priority high
.\agents\scripts\agent-pm.ps1 -Action list-tasks
.\agents\scripts\agent-pm.ps1 -Action assign-task -TaskId 1 -Assignee frontend
.\agents\scripts\agent-pm.ps1 -Action review-pr
.\agents\scripts\agent-pm.ps1 -Action status
```

### Frontend Developer
```powershell
.\agents\scripts\agent-frontend.ps1 -Action build    # npm run build
.\agents\scripts\agent-frontend.ps1 -Action dev      # vite dev server
.\agents\scripts\agent-frontend.ps1 -Action lint     # check for console.log etc
.\agents\scripts\agent-frontend.ps1 -Action check    # verify build manifest
```

### Backend Developer
```powershell
.\agents\scripts\agent-backend.ps1 -Action migrate
.\agents\scripts\agent-backend.ps1 -Action rollback -Steps 1
.\agents\scripts\agent-backend.ps1 -Action seed
.\agents\scripts\agent-backend.ps1 -Action optimize
.\agents\scripts\agent-backend.ps1 -Action routes
.\agents\scripts\agent-backend.ps1 -Action check
```

### QA (Quality Assurance)
```powershell
.\agents\scripts\agent-qa.ps1 -Action test           # full test suite
.\agents\scripts\agent-qa.ps1 -Action test-feature -Filter "ExampleTest"
.\agents\scripts\agent-qa.ps1 -Action test-unit
.\agents\scripts\agent-qa.ps1 -Action lint
.\agents\scripts\agent-qa.ps1 -Action report -BugTitle "404 on /random" -BugDescription "Route returns 404 when no anime exists"
.\agents\scripts\agent-qa.ps1 -Action approve         # gate before deploy
```

### DevOps
```powershell
.\agents\scripts\agent-devops.ps1 -Action deploy
.\agents\scripts\agent-devops.ps1 -Action rollback
.\agents\scripts\agent-devops.ps1 -Action status      # PHP, Node, DB, APP_DEBUG
.\agents\scripts\agent-devops.ps1 -Action logs        # Laravel + agent logs
.\agents\scripts\agent-devops.ps1 -Action cleanup     # clear caches
```

## Workflow (Agile)

```
PM Creates Task
    ↓
Backend: Migrate + Develop
    ↓
Frontend: Build Assets
    ↓
QA: Run Tests + Lint + Approve
    ↓
PM: Review PR
    ↓
DevOps: Deploy to XAMPP
    ↓
All: Log Results
```

## Task Tracking

Tasks are stored as JSON files in `agents/tasks/task-{id}.json`:
```json
{
  "id": 1,
  "title": "Fix login bug",
  "assignee": "backend",
  "priority": "high",
  "status": "open"
}
```

Bugs are tracked in `agents/tasks/bugs.json`.

## Logs

All agent activity is logged to `agents/logs/{Agent}.log` with timestamps.

View logs:
```powershell
.\agents\scripts\agent-devops.ps1 -Action logs
Get-Content .\agents\logs\QA.log -Tail 20
```

## CI/CD Integration

The system mirrors the existing GitHub Actions workflows:
- `ci.yml` → PHPUnit + Pint (what QA Agent runs)
- `phpunit.yml` → Full test suite with MySQL service
- `deploy.yml` → SSH deployment (what DevOps Agent does locally)

For local testing without GitHub, run:
```powershell
.\agents\scripts\agent-orchestrator.ps1 -Action qa-cycle
```
