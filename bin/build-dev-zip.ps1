param(
	[string]$OutputDir = 'dist',
	[switch]$AllowDirty
)

$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath( ( Join-Path $PSScriptRoot '..' ) )

Push-Location $repoRoot

try {
	$status = (@( git status --porcelain ) -join [Environment]::NewLine).Trim()

	if ( -not $AllowDirty -and $status -ne '' ) {
		throw 'Working tree is not clean. Commit your changes before building a dev ZIP, or rerun with -AllowDirty if you intentionally want a ZIP from HEAD only.'
	}

	$shortSha   = (@( git rev-parse --short HEAD ) -join [Environment]::NewLine).Trim()
	$outputPath = Join-Path $OutputDir "mac-core-dev-$shortSha.zip"

	New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null

	$existingDevZips = Get-ChildItem -LiteralPath $OutputDir -Filter 'mac-core-dev-*.zip' -File -ErrorAction SilentlyContinue

	foreach ( $existingDevZip in $existingDevZips ) {
		Remove-Item -LiteralPath $existingDevZip.FullName -Force
	}

	git archive --format=zip "--output=$outputPath" --prefix=mac-core/ HEAD

	Write-Host "Created $outputPath"
}
finally {
	Pop-Location
}
