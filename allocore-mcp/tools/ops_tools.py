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

def admin_list_coupons(only_active: bool = False) -> Dict[str, Any]:
    """(Admin Only) List promotional and discount coupons."""
    from db import query
    sql = "SELECT id, code, type, value, max_uses, used_count, is_active, starts_at, expires_at, description, created_at FROM coupons WHERE 1=1"
    if only_active:
        sql += " AND is_active = 1"
    sql += " ORDER BY created_at DESC"
    coupons = query(sql)
    return {"total": len(coupons), "coupons": coupons}

def admin_create_coupon(code: str, type: str = 'percent', value: float = 10.0, max_uses: int = None, description: str = '', is_active: bool = True) -> Dict[str, Any]:
    """(Admin Only) Create a promotional discount coupon."""
    from db import execute, query_one
    code_clean = code.strip().upper()
    execute("""
        INSERT INTO coupons (code, type, value, max_uses, used_count, is_active, description, starts_at, created_at, updated_at)
        VALUES (%s, %s, %s, %s, 0, %s, %s, NOW(), NOW(), NOW())
    """, (code_clean, type, value, max_uses, 1 if is_active else 0, description))
    created = query_one("SELECT * FROM coupons WHERE code = %s", (code_clean,))
    return {"status": "created", "coupon": created}

def admin_delete_coupon(code: str) -> Dict[str, Any]:
    """(Admin Only) Delete a discount coupon."""
    from db import execute, query_one
    c = query_one("SELECT id, code FROM coupons WHERE code = %s OR id = %s", (code.upper(), int(code) if code.isdigit() else 0))
    if not c:
        return {"error": f"Coupon '{code}' not found."}
    execute("DELETE FROM coupons WHERE id = %s", (c['id'],))
    return {"status": "deleted", "code": c['code']}

def admin_list_backups() -> Dict[str, Any]:
    """(Admin Only) List available database backups."""
    from db import query
    backups = query("SELECT id, name, disk, type, size, completed_at, created_at FROM backups ORDER BY created_at DESC LIMIT 25")
    return {"total": len(backups), "backups": backups}

def admin_read_error_logs(lines: int = 50) -> Dict[str, Any]:
    """(Admin Only) Read recent lines from storage/logs/laravel.log."""
    log_file = BASE_DIR / "storage" / "logs" / "laravel.log"
    if not log_file.exists():
        return {"exists": False, "message": "No laravel.log found."}
    try:
        with open(log_file, "r", encoding="utf-8", errors="ignore") as f:
            all_lines = f.readlines()
        tail = all_lines[-min(150, max(10, lines)):]
        return {"exists": True, "total_lines": len(all_lines), "log_content": "".join(tail)}
    except Exception as e:
        return {"error": str(e)}

def admin_list_announcements() -> Dict[str, Any]:
    """(Admin Only) List site-wide dashboard announcements."""
    from db import query
    announcements = query("SELECT id, title, body, type, is_active, starts_at, ends_at, created_at FROM announcements ORDER BY created_at DESC")
    return {"total": len(announcements), "announcements": announcements}

def admin_create_announcement(title: str, body: str, type: str = 'info', is_active: bool = True) -> Dict[str, Any]:
    """(Admin Only) Create a dashboard announcement banner."""
    from db import execute, query_one
    execute("""
        INSERT INTO announcements (title, body, type, is_active, starts_at, created_at, updated_at)
        VALUES (%s, %s, %s, %s, NOW(), NOW(), NOW())
    """, (title, body, type, 1 if is_active else 0))
    a = query_one("SELECT * FROM announcements ORDER BY id DESC LIMIT 1")
    return {"status": "created", "announcement": a}

def admin_get_settings() -> Dict[str, Any]:
    """(Admin Only) Retrieve system environment and platform counts."""
    from db import query_one
    u = query_one("SELECT COUNT(*) as count FROM users")
    t = query_one("SELECT COUNT(*) as count FROM teams")
    m = query_one("SELECT COUNT(*) as count FROM modules WHERE is_active = 1")
    return {
        "laravel_root": str(BASE_DIR),
        "total_users": u['count'] if u else 0,
        "total_teams": t['count'] if t else 0,
        "active_modules": m['count'] if m else 0,
    }
