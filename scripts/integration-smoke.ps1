$ErrorActionPreference = 'Stop'

$base = 'http://localhost/https-github.com-antonio-valero-daw2personal/Proyecto/spotMap/backend/public/index.php'

function Assert-True($condition, $message) {
    if (-not $condition) {
        throw "FAIL: $message"
    }
}

Write-Host "[Smoke] Base: $base"

# 1) API status
$status = Invoke-RestMethod -Uri "$base/api/status" -Method Get
Assert-True ($status.status -eq 'healthy') 'api/status should return status=healthy'
Write-Host "[Smoke] api/status OK (healthy)"

# 2) DB ping (warn if not ok)
try {
    $ping = Invoke-RestMethod -Uri "http://localhost/https-github.com-antonio-valero-daw2personal/Proyecto/spotMap/backend/public/index.php/ping-db" -Method Get
    if ($ping.ok -ne $true) {
        Write-Warning "[Smoke] ping-db returned ok=false"
    } else {
        Write-Host "[Smoke] ping-db OK"
    }
} catch {
    Write-Warning "[Smoke] ping-db failed: $($_.Exception.Message)"
}

# 3) List spots
$spotsResp = Invoke-RestMethod -Uri "$base/spots" -Method Get
Assert-True ($spotsResp.success -eq $true) 'GET /spots should return success=true'
Assert-True ($spotsResp.data.spots -is [System.Collections.IEnumerable]) 'GET /spots should return spots array'
Write-Host ("[Smoke] spots count: {0}" -f $spotsResp.data.spots.Count)

# 4) Get first spot details if any
if ($spotsResp.data.spots.Count -gt 0) {
    $firstId = $spotsResp.data.spots[0].id
    $spot = Invoke-RestMethod -Uri "$base/spots/$firstId" -Method Get
    Assert-True ($spot.success -eq $true) 'GET /spots/{id} should return success=true'
    Assert-True ($spot.data.id -eq $firstId) 'GET /spots/{id} should return the requested id'
    Write-Host "[Smoke] spot details OK (id=$firstId)"
} else {
    Write-Warning '[Smoke] No spots found to test /spots/{id}'
}

Write-Host '[Smoke] Completed'
