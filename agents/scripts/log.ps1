param(
    [Parameter(Mandatory = $true)]
    [string]$Agent,
    [Parameter(Mandatory = $true)]
    [string]$Message,
    [ValidateSet("info", "warn", "error", "success")]
    [string]$Level = "info"
)

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$logFile = "D:\Xammp\htdocs\office-work\agents\logs\$Agent.log"
$label = @{info = " [INFO]"; warn = " [WARN]"; error = "[ERROR]"; success = "  [OK]"}
$line = "$timestamp $($label[$Level]) [$Agent] $Message"

Add-Content -Path $logFile -Value $line

$colors = @{error = "Red"; warn = "Yellow"; success = "Green"; info = "Cyan"}
if ($colors.ContainsKey($Level)) { Write-Host $line -ForegroundColor $colors[$Level] } else { Write-Host $line }
