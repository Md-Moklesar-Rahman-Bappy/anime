<#
.SYNOPSIS
  DevOps Agent - CI/CD, deploy, rollback, log monitoring.
#>

param(
    [ValidateSet("deploy", "rollback", "status", "logs", "cleanup")]
    [string]$Action = "status"
)

$logScript = "D:\Xammp\htdocs\office-work\agents\scripts\log.ps1"
$projectDir = "D:\Xammp\htdocs\office-work"
$artisan = "php $projectDir\artisan"
$releasesDir = "$projectDir\releases"

function Log($msg, $level = "info") { & $logScript -Agent "DevOps" -Message $msg -Level $level }

function Invoke-Step($name, $script) {
    Write-Host "[$name] Running..." -ForegroundColor Cyan
    $result = Invoke-Expression $script 2>&1
    if ($LASTEXITCODE -eq 0) {
        Log "$name completed" "success"
        return $true
    } else {
        Log "$name failed: $result" "error"
        return $false
    }
}

switch ($Action) {
    "deploy" {
        Log "Starting deployment..."
        $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
        $releaseDir = "$releasesDir\$timestamp"

        if (-not (Test-Path $releasesDir)) { New-Item -ItemType Directory -Path $releasesDir -Force | Out-Null }

        # Step 1: Pull latest
        if (-not (Invoke-Step "Git pull" "git -C $projectDir pull origin main 2>&1")) { exit 1 }

        # Step 2: Composer install
        if (-not (Invoke-Step "Composer install" "cd $projectDir; composer install --no-dev --optimize-autoloader 2>&1")) { exit 1 }

        # Step 3: NPM build
        if (-not (Invoke-Step "NPM build" "cd $projectDir; npm install --production=false 2>&1; npm run build 2>&1")) { exit 1 }

        # Step 4: Migrations
        if (-not (Invoke-Step "Migrations" "$artisan migrate --force 2>&1")) { exit 1 }

        # Step 5: Optimize
        Invoke-Expression "$artisan optimize 2>&1" | Out-Null
        Invoke-Expression "$artisan view:cache 2>&1" | Out-Null
        Invoke-Expression "$artisan route:cache 2>&1" | Out-Null
        Invoke-Expression "$artisan config:cache 2>&1" | Out-Null
        Log "Optimizations cached" "success"

        Log "Deployment completed successfully" "success"
    }
    "rollback" {
        Log "Rolling back deployment..."
        $backup = "$projectDir\storage\backup"
        if (Test-Path $backup) {
            Copy-Item -Recurse -Force "$backup\*" "$projectDir"
            Log "Backup restored from $backup" "success"
        } else {
            $lastCommit = git -C $projectDir log --oneline -2 | Select-Object -Last 1
            if ($lastCommit) {
                git -C $projectDir revert --no-edit HEAD
                Log "Reverted to previous commit: $lastCommit" "success"
            } else {
                Log "No backup or previous commit found" "error"
            }
        }
    }
    "status" {
        Write-Host "=== Environment Status ===" -ForegroundColor Magenta
        $phpV = Invoke-Expression "php -v 2>&1 | Select-String 'PHP '"
        Write-Host "PHP: $phpV"
        $composerV = Invoke-Expression "composer --version 2>&1"
        Write-Host "Composer: $composerV"
        $nodeV = Invoke-Expression "node --version 2>&1"
        Write-Host "Node: $nodeV"
        $appDebug = Select-String -Path "$projectDir\.env" -Pattern "APP_DEBUG=" | ForEach-Object { $_ -replace '.*=','' }
        Write-Host "APP_DEBUG: $appDebug"
        $appEnv = Select-String -Path "$projectDir\.env" -Pattern "APP_ENV=" | ForEach-Object { $_ -replace '.*=','' }
        Write-Host "APP_ENV: $appEnv"

        # Check XAMPP services
        $mysql = Get-Service -Name "MariaDB" -ErrorAction SilentlyContinue
        if ($mysql) { Write-Host "MySQL: $($mysql.Status)" } else { Write-Host "MySQL: Not found" }
        
        Log "Status check complete" "success"
    }
    "logs" {
        Write-Host "=== Recent Laravel Logs ===" -ForegroundColor Magenta
        $laravelLog = "$projectDir\storage\logs\laravel.log"
        if (Test-Path $laravelLog) {
            Get-Content $laravelLog -Tail 20
        } else {
            Write-Host "No Laravel log file found" -ForegroundColor Yellow
        }

        Write-Host "`n=== Agent Activity Logs ===" -ForegroundColor Magenta
        $agentLogs = Get-ChildItem "$projectDir\agents\logs\*.log" -ErrorAction SilentlyContinue
        foreach ($l in $agentLogs) {
            Write-Host "`n--- $($l.Name) (last 10 lines) ---" -ForegroundColor Cyan
            Get-Content $l.FullName -Tail 10
        }
    }
    "cleanup" {
        Log "Cleaning up..."
        Invoke-Expression "$artisan optimize:clear 2>&1" | Out-Null
        Remove-Item -Recurse -Force "$projectDir\storage\framework\cache\data\*" -ErrorAction SilentlyContinue
        Remove-Item -Recurse -Force "$projectDir\storage\framework\views\*" -ErrorAction SilentlyContinue
        Log "Cache and temp files cleaned" "success"
    }
}
