<#
.SYNOPSIS
  QA Agent - automated testing, validation, bug reporting.
#>

param(
    [ValidateSet("test", "test-feature", "test-unit", "lint", "report", "approve")]
    [string]$Action = "test",
    [string]$Filter = "",
    [string]$BugTitle = "",
    [string]$BugDescription = ""
)

$logScript = "D:\Xammp\htdocs\office-work\agents\scripts\log.ps1"
$bugFile = "D:\Xammp\htdocs\office-work\agents\tasks\bugs.json"

function Log($msg, $level = "info") { & $logScript -Agent "QA" -Message $msg -Level $level }

function Invoke-Batch($cmd, $args) {
    $pinfo = New-Object System.Diagnostics.ProcessStartInfo
    $pinfo.FileName = "cmd.exe"
    $pinfo.RedirectStandardError = $true
    $pinfo.RedirectStandardOutput = $true
    $pinfo.UseShellExecute = $false
    $pinfo.Arguments = "/c `"$cmd $args 2>&1`""
    $p = New-Object System.Diagnostics.Process
    $p.StartInfo = $pinfo
    $p.Start() | Out-Null
    $stdout = $p.StandardOutput.ReadToEnd()
    $p.WaitForExit()
    return $stdout, $p.ExitCode
}

function Run-Tests {
    param([string]$Suite = "", [string]$Name = "")
    $phpunit = "D:\Xammp\htdocs\office-work\vendor\bin\phpunit.bat"

    if ($Suite -eq "unit") {
        Log "Running unit tests..."
        $result, $ec = Invoke-Batch $phpunit "--testsuite=Unit --no-coverage"
    } elseif ($Suite -eq "feature" -and $Name) {
        Log "Running feature test: $Name..."
        $result, $ec = Invoke-Batch $phpunit "--filter=`"$Name`" --no-coverage"
    } else {
        Log "Running full test suite..."
        $result, $ec = Invoke-Batch $phpunit "--no-coverage"
    }

    Write-Host $result
    if ($ec -eq 0) {
        Log "All tests passed" "success"
    } else {
        Log "Tests failed!" "error"
    }
    return ($ec -eq 0)
}

function Lint-Check {
    Log "Running Laravel Pint lint check..."
    $pint = "D:\Xammp\htdocs\office-work\vendor\bin\pint.bat"
    $result, $ec = Invoke-Batch $pint "--test"
    Write-Host $result
    if ($ec -eq 0) {
        Log "Lint check passed" "success"
    } else {
        Log "Lint issues found" "error"
    }
    return ($ec -eq 0)
}

function Report-Bug {
    if (-not $BugTitle) { Log "Bug title required" "error"; return }
    $bug = @{
        id          = (Get-Date -Format "yyyyMMdd-HHmmss")
        title       = $BugTitle
        description = $BugDescription
        reported_by = "QA Agent"
        status      = "open"
        created     = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
    }
    if (Test-Path $bugFile) { $bugs = Get-Content $bugFile | ConvertFrom-Json } else { $bugs = @() }
    $bugs += $bug
    $bugs | ConvertTo-Json | Set-Content -Path $bugFile
    Log "Bug reported: $BugTitle" "warn"
}

switch ($Action) {
    "test" { $global:passed = Run-Tests }
    "test-feature" { $global:passed = Run-Tests -Suite "feature" -Name $Filter }
    "test-unit" { $global:passed = Run-Tests -Suite "unit" }
    "lint" { $global:passed = Lint-Check }
    "report" { Report-Bug }
    "approve" {
        $testsOk = Run-Tests
        $lintOk = Lint-Check
        if ($testsOk -and $lintOk) {
            Log "QA approval granted - all checks passed" "success"
            Write-Host "BUILD APPROVED" -ForegroundColor Green
        } else {
            Log "QA approval denied - checks failed" "error"
            Write-Host "BUILD REJECTED" -ForegroundColor Red
        }
    }
}
