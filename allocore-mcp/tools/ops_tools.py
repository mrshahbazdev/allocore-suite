import subprocess
import os
from pathlib import Path
from typing import Dict, Any
from db import query_one

BASE_DIR = Path(__file__).resolve().parent.parent.parent

def run_allocore_artisan(command: str) -> Dict[str, Any]:
    """Execute safe maintenance artisan commands (e.g. cache:clear, view:clear, route:clear, migrate --status)."""
    allowed_commands = [
        "cache:clear", "view:clear", "route:clear", "config:clear",
        "migrate:status", "optimize:clear", "module:list"
    ]
    
    cmd_clean = command.strip()
    if not any(cmd_clean.startswith(allowed) for allowed in allowed_commands):
        return {
            "error": f"Command '{command}' is not in the allowed safe operations whitelist.",
            "allowed_whitelist": allowed_commands
        }
        
    try:
        res = subprocess.run(
            ["php", "artisan"] + cmd_clean.split(),
            cwd=str(BASE_DIR),
            capture_output=True,
            text=True,
            timeout=30
        )
        return {
            "command": f"php artisan {command}",
            "exit_code": res.returncode,
            "stdout": res.stdout,
            "stderr": res.stderr
        }
    except Exception as e:
        return {"error": f"Execution failed: {str(e)}"}

def get_system_health() -> Dict[str, Any]:
    """Check database health, Laravel environment, and module directory status."""
    db_ok = False
    try:
        res = query_one("SELECT 1 as healthy")
        db_ok = res and res.get('healthy') == 1
    except:
        db_ok = False
        
    modules_dir = BASE_DIR / "Modules"
    modules_count = len([d for d in modules_dir.iterdir() if d.is_dir()]) if modules_dir.exists() else 0
    
    return {
        "database_connected": db_ok,
        "laravel_root": str(BASE_DIR),
        "disk_modules_installed": modules_count,
        "php_cli_available": True
    }
