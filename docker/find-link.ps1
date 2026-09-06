# Waits for cloudflared to publish the quick-tunnel address, then prints it.
# Used by share-link.bat; prints nothing if the address never appears.

$ErrorActionPreference = 'SilentlyContinue'
$pattern = 'https://[a-z0-9-]+\.trycloudflare\.com'

for ($i = 0; $i -lt 40; $i++) {
    $logs = docker compose logs --no-color quick-link 2>$null | Out-String
    $match = [regex]::Matches($logs, $pattern) | Select-Object -Last 1

    if ($match) {
        Write-Output $match.Value
        exit 0
    }

    Start-Sleep -Seconds 2
}

exit 1
