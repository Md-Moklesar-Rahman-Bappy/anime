<#
.SYNOPSIS
  Project Manager Agent - manages tasks, sprints, and PR reviews.
.DESCRIPTION
  Creates tasks, assigns to developers, tracks progress, reviews PRs.
#>

param(
    [ValidateSet("create-task", "list-tasks", "assign-task", "review-pr", "status")]
    [string]$Action = "status",
    [string]$Title = "",
    [string]$Description = "",
    [string]$Assignee = "",
    [string]$Priority = "medium",
    [int]$TaskId = 0
)

$tasksDir = "D:\Xammp\htdocs\office-work\agents\tasks"
$logScript = "D:\Xammp\htdocs\office-work\agents\scripts\log.ps1"

function Log($msg, $level = "info") { & $logScript -Agent "PM" -Message $msg -Level $level }

function Get-NextTaskId {
    $files = Get-ChildItem -Path $tasksDir -Filter "task-*.json" -ErrorAction SilentlyContinue
    $max = 0
    foreach ($f in $files) {
        if ($f.BaseName -match 'task-(\d+)') { $max = [Math]::Max($max, [int]$matches[1]) }
    }
    return $max + 1
}

function Create-Task {
    $id = Get-NextTaskId
    $task = @{
        id          = $id
        title       = $Title
        description = $Description
        assignee    = $Assignee
        priority    = $Priority
        status      = "open"
        created     = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
        updated     = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
    }
    $task | ConvertTo-Json | Set-Content -Path "$tasksDir\task-$id.json"
    Log "Created task #${id}: ${Title} (priority: ${Priority}, assigned: ${Assignee})" "success"
    return $task
}

function List-Tasks {
    $files = Get-ChildItem -Path $tasksDir -Filter "task-*.json" -ErrorAction SilentlyContinue | Sort-Object Name
    if (-not $files) { Log "No tasks found"; return }
    foreach ($f in $files) {
        $t = Get-Content $f.FullName | ConvertFrom-Json
        Write-Host "[#$($t.id)] $($t.title) | $($t.status) | $($t.assignee) | $($t.priority)" -ForegroundColor $(if ($t.status -eq "done") {"Green"} elseif ($t.status -eq "open") {"Yellow"} else {"Cyan"})
    }
    Log "Listed $($files.Count) tasks"
}

switch ($Action) {
    "create-task" {
        if (-not $Title) { Log "Error: --Title is required for create-task" "error"; exit 1 }
        Create-Task
    }
    "list-tasks" { List-Tasks }
    "assign-task" {
        if (-not $TaskId -or -not $Assignee) { Log "Error: --TaskId and --Assignee required" "error"; exit 1 }
        $path = "$tasksDir\task-$TaskId.json"
        if (-not (Test-Path $path)) { Log "Task #$TaskId not found" "error"; exit 1 }
        $t = Get-Content $path | ConvertFrom-Json
        $t.assignee = $Assignee
        $t.updated = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
        $t | ConvertTo-Json | Set-Content -Path $path
        Log "Assigned task #$TaskId to $Assignee" "success"
    }
    "review-pr" {
        Write-Host "=== PR Review Checklist ===" -ForegroundColor Magenta
        Write-Host "1. Tests pass:         " -NoNewline; & "D:\Xammp\htdocs\office-work\vendor\bin\phpunit" --no-coverage 2>&1 | Out-Null; if ($?) { Write-Host "PASS" -ForegroundColor Green } else { Write-Host "FAIL" -ForegroundColor Red }
        Write-Host "2. Lint check:         " -NoNewline; & "D:\Xammp\htdocs\office-work\vendor\bin\pint" --test 2>&1 | Out-Null; if ($?) { Write-Host "PASS" -ForegroundColor Green } else { Write-Host "FAIL" -ForegroundColor Red }
        Write-Host "3. No debug code:      CHECK MANUALLY" -ForegroundColor Yellow
        Write-Host "4. Follows MVC pattern: CHECK MANUALLY" -ForegroundColor Yellow
        Log "PR review completed"
    }
    "status" {
        $open = (Get-ChildItem "$tasksDir\task-*.json" -ErrorAction SilentlyContinue | Where-Object { (Get-Content $_.FullName | ConvertFrom-Json).status -eq "open" }).Count
        $done = (Get-ChildItem "$tasksDir\task-*.json" -ErrorAction SilentlyContinue | Where-Object { (Get-Content $_.FullName | ConvertFrom-Json).status -eq "done" }).Count
        $total = (Get-ChildItem "$tasksDir\task-*.json" -ErrorAction SilentlyContinue).Count
        Write-Host "=== Project Status ===" -ForegroundColor Magenta
        Write-Host "Total tasks : $total"
        Write-Host "Open       : $open"
        Write-Host "Completed  : $done"
        if ($total -gt 0) { Write-Host "Progress   : $([math]::Round($done/$total*100, 1))%" }
        Log "Status check: $open open, $done done"
    }
}
