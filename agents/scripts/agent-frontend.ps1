<#
.SYNOPSIS
  Frontend Developer Agent - UI/UX, CSS, JS, responsive, API integration.
#>

param(
    [ValidateSet("build", "dev", "lint", "check")]
    [string]$Action = "check"
)

$logScript = "D:\Xammp\htdocs\office-work\agents\scripts\log.ps1"
function Log($msg, $level = "info") { & $logScript -Agent "Frontend" -Message $msg -Level $level }

switch ($Action) {
    "build" {
        Log "Running Vite production build..."
        $result = & "D:\Xammp\htdocs\office-work\node_modules\.bin\vite" build 2>&1
        if ($LASTEXITCODE -eq 0) { Log "Vite build successful" "success" } else { Log "Vite build failed: $result" "error" }
    }
    "dev" {
        Log "Starting Vite dev server..."
        & "D:\Xammp\htdocs\office-work\node_modules\.bin\vite"
    }
    "lint" {
        Log "Checking for common frontend issues..."
        $files = Get-ChildItem -Path "D:\Xammp\htdocs\office-work\resources" -Recurse -Include "*.blade.php" -ErrorAction SilentlyContinue
        $issues = 0
        foreach ($f in $files) {
            $content = Get-Content $f.FullName -Raw
            if ($content -match 'console\.(log|warn|error)\(') { Write-Host "⚠ $($f.Name) has console.log" -ForegroundColor Yellow; $issues++ }
        }
        if ($issues -eq 0) { Log "No frontend issues found" "success" } else { Log "Found $issues frontend issues" "warn" }
    }
    "check" {
        Log "Checking frontend build..."
        if (Test-Path "D:\Xammp\htdocs\office-work\public\build\manifest.json") {
            Log "Build manifest exists (last build OK)" "success"
        } else {
            Log "No build manifest found. Run 'build' action first." "warn"
        }
    }
}
