# Temporary blog importer — delete after use.
$mysql = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
$sqlFile = 'F:\brunch\database\demo-blog.sql'
Get-Content -Path $sqlFile -Raw | & $mysql -u root brunchindetroit
Write-Output "IMPORT_EXIT_CODE: $LASTEXITCODE"