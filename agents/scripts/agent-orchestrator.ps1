<#
.SYNOPSIS
  Agent Orchestrator - coordinates all agents in the workflow pipeline.
.DESCRIPTION
  Runs the full pipeline: PM task check → Dev work → QA validation → DevOps deploy.
#>

param(
    [ValidateSet("full-pipeline", "dev-cycle", "qa-cycle", "deploy-cycle", "status")]
    [string]$Action = "status",
    [string]$TaskTitle = "",
    [string]$TaskDescription = "",
    [string]$Assignee = "backend",
    [string]$Priority = "medium"
)

$scriptsDir = "D:\Xammp\htdocs\office-work\agents\scripts"
$logScript = "$scriptsDir\log.ps1"

function Log($msg, $level = "info") { & $logScript -Agent "Orchestrator" -Message $msg -Level $level }

function Step($name, $cmd) {
    Write-Host "`n═══════════════════════════════════════════" -ForegroundColor Magenta
    Write-Host "STEP: $name" -ForegroundColor Magenta
    Write-Host "═══════════════════════════════════════════" -ForegroundColor Magenta
    Invoke-Expression $cmd
    return $LASTEXITCODE
}

switch ($Action) {
    "full-pipeline" {
        Log "Starting full pipeline..."
        
        # 1. PM: Create task
        if ($TaskTitle) {
            Step "PM: Create task" "& '$scriptsDir\agent-pm.ps1' -Action create-task -Title '$TaskTitle' -Description '$TaskDescription' -Assignee '$Assignee' -Priority '$Priority'"
        }
        
        # 2. Backend: Check & migrate
        Step "Backend: Migrations" "& '$scriptsDir\agent-backend.ps1' -Action migrate"
        
        # 3. Frontend: Build
        Step "Frontend: Build" "& '$scriptsDir\agent-frontend.ps1' -Action build"
        
        # 4. QA: Test & lint
        Step "QA: Full test suite" "& '$scriptsDir\agent-qa.ps1' -Action test"
        Step "QA: Lint check" "& '$scriptsDir\agent-qa.ps1' -Action lint"
        
        # 5. QA: Approve
        Step "QA: Approval" "& '$scriptsDir\agent-qa.ps1' -Action approve"
        
        # 6. DevOps: Deploy
        if ($?) {
            Step "DevOps: Deploy" "& '$scriptsDir\agent-devops.ps1' -Action deploy"
            Log "Full pipeline completed" "success"
        } else {
            Log "Pipeline aborted due to failures" "error"
        }
    }
    "dev-cycle" {
        Step "Backend: Migrations" "& '$scriptsDir\agent-backend.ps1' -Action migrate"
        Step "Frontend: Build" "& '$scriptsDir\agent-frontend.ps1' -Action build"
        Log "Dev cycle complete" "success"
    }
    "qa-cycle" {
        Step "QA: Full test suite" "& '$scriptsDir\agent-qa.ps1' -Action test"
        Step "QA: Lint check" "& '$scriptsDir\agent-qa.ps1' -Action lint"
        Step "QA: Approval" "& '$scriptsDir\agent-qa.ps1' -Action approve"
        Log "QA cycle complete" "success"
    }
    "deploy-cycle" {
        Step "DevOps: Deploy" "& '$scriptsDir\agent-devops.ps1' -Action deploy"
        Step "DevOps: Optimize" "& '$scriptsDir\agent-backend.ps1' -Action optimize"
        Log "Deploy cycle complete" "success"
    }
    "status" {
        Write-Host "`n═══════════════════════════════════════════" -ForegroundColor Magenta
        Write-Host "   ANIWAVES - Agent Orchestrator Dashboard" -ForegroundColor Magenta
        Write-Host "═══════════════════════════════════════════" -ForegroundColor Magenta
        
        & "$scriptsDir\agent-pm.ps1" -Action status
        & "$scriptsDir\agent-devops.ps1" -Action status
        
        Write-Host "`nUsage examples:" -ForegroundColor Cyan
        Write-Host "  agents\scripts\agent-orchestrator.ps1 -Action full-pipeline -TaskTitle 'Feature X'" -ForegroundColor Gray
        Write-Host "  agents\scripts\agent-orchestrator.ps1 -Action dev-cycle" -ForegroundColor Gray
        Write-Host "  agents\scripts\agent-orchestrator.ps1 -Action qa-cycle" -ForegroundColor Gray
        Write-Host "  agents\scripts\agent-orchestrator.ps1 -Action deploy-cycle" -ForegroundColor Gray
    }
}
