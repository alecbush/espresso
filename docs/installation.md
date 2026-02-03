# Installation

## Requirements

- PHP 8.0 or higher

## Preferred (Composer)

```bash
composer require alecbush/espresso
```

## Or download the single-file build (for simple deployments)

Use the command appropriate for your operating system or shell:

### macOS / Linux

```bash
curl -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

### Windows (PowerShell)

```powershell
# Preferred: use PowerShell's cmdlet
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php" -OutFile "espresso.php"

# Or call the real curl executable if you have it installed
curl.exe -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

### Windows (cmd)

```cmd
powershell -Command "Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php' -OutFile 'espresso.php'"
```

### WSL (Windows Subsystem for Linux)

```bash
wsl curl -O https://raw.githubusercontent.com/alecbush/espresso/main/dist/espresso.php
```

Note: PowerShell ships an alias named `curl` that maps to `Invoke-WebRequest` — using `curl -O` in PowerShell can prompt for parameters. Use `curl.exe` or `Invoke-WebRequest` as shown above.