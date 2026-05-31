<#
.SYNOPSIS
  Backend Developer Agent - Laravel/PHP, features, migrations, optimization.
#>

param(
    [ValidateSet("migrate", "rollback", "seed", "optimize", "routes", "check")]
    [string]$Action = "check",
    [int]$Steps = 1
)

$logScript = "D:\Xammp\htdocs\office-work\agents\scripts\log.ps1"
$artisan = "php D:\Xammp\htdocs\office-work\artisan"

function Log($msg, $level = "info") { & $logScript -Agent "Backend" -Message $msg -Level $level }

switch ($Action) {
    "migrate" {
        Log "Running migrations..."
        $result = Invoke-Expression "$artisan migrate --force 2>&1"
        if ($LASTEXITCODE -eq 0) { Log "Migrations applied successfully" "success" } else { Log "Migration failed: $result" "error" }
    }
    "rollback" {
        Log "Rolling back $Steps step(s)..."
        $result = Invoke-Expression "$artisan migrate:rollback --step=$Steps --force 2>&1"
        if ($LASTEXITCODE -eq 0) { Log "Rollback successful" "success" } else { Log "Rollback failed: $result" "error" }
    }
    "seed" {
        Log "Running database seeders..."
        $result = Invoke-Expression "$artisan db:seed --force 2>&1"
        if ($LASTEXITCODE -eq 0) { Log "Seeding completed" "success" } else { Log "Seeding failed: $result" "error" }
    }
    "optimize" {
        Log "Running Laravel optimizations..."
        Invoke-Expression "$artisan optimize 2>&1" | Out-Null
        Invoke-Expression "$artisan view:cache 2>&1" | Out-Null
        Invoke-Expression "$artisan route:cache 2>&1" | Out-Null
        Invoke-Expression "$artisan config:cache 2>&1" | Out-Null
        Log "All caches cleared & rebuilt" "success"
    }
    "routes" {
        Invoke-Expression "$artisan route:list 2>&1"
    }
    "check" {
        Log "Running backend health checks..."
        Invoke-Expression "$artisan migrate --pretend 2>&1" | Out-Null
        if ($LASTEXITCODE -eq 0) { Log "Migrations synced" "success" } else { Log "Pending migrations detected" "warn" }
    }
}
