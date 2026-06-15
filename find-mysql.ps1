# Temporary locator for mysql.exe — delete after use.
$paths = @(
  'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
  'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe',
  'C:\Program Files\MySQL\MySQL Server 9.0\bin\mysql.exe',
  'C:\xampp\mysql\bin\mysql.exe',
  'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe',
  'C:\wamp\bin\mysql\mysql8.0.31\bin\mysql.exe',
  'C:\phpstudy_pro\Extensions\MySQL8.0.12\bin\mysql.exe',
  'C:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe',
  'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe',
  'C:\laragon\bin\mysql\mysql-5.7.33-winx64\bin\mysql.exe'
)
$found = $false
foreach ($p in $paths) {
  if (Test-Path $p) {
    Write-Output "FOUND: $p"
    $found = $true
  }
}
if (-not $found) {
  Write-Output 'NO_KNOWN_PATH'
  # Also search common roots broadly
  Get-ChildItem -Path 'C:\Program Files','C:\' -Filter 'mysql.exe' -Recurse -ErrorAction SilentlyContinue |
    Select-Object -First 5 -ExpandProperty FullName
}