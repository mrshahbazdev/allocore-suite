from typing import Optional, Dict, Any, List
from db import query, query_one, execute

def list_all_modules(category: Optional[str] = None, only_pool: bool = False, only_active: bool = False) -> List[Dict[str, Any]]:
    """List all platform tools and modules with their subscription pool status, category, icon, and route prefix."""
    sql = "SELECT id, `key`, name, description, category, icon, route_prefix, in_subscription_pool, is_active, is_deprecated, badge_text, sort_order FROM modules WHERE 1=1"
    params = []
    if category:
        sql += " AND category = %s"
        params.append(category)
    if only_pool:
        sql += " AND in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0"
    if only_active:
        sql += " AND is_active = 1"
    sql += " ORDER BY sort_order ASC, id ASC"
    return query(sql, tuple(params))

def manage_tool_pool(module_key: str, in_pool: bool) -> Dict[str, Any]:
    """Add or remove a module from the automatic customer subscription pool."""
    execute("UPDATE modules SET in_subscription_pool = %s WHERE `key` = %s", (1 if in_pool else 0, module_key))
    sync_subscription_plans()
    return {"status": "success", "module_key": module_key, "in_subscription_pool": in_pool}

def deprecate_module(module_key: str, is_deprecated: bool = True) -> Dict[str, Any]:
    """Mark a module as deprecated/archived or restore it to active status."""
    execute("UPDATE modules SET is_deprecated = %s WHERE `key` = %s", (1 if is_deprecated else 0, module_key))
    sync_subscription_plans()
    return {"status": "success", "module_key": module_key, "is_deprecated": is_deprecated}

def update_module_metadata(module_key: str, name: Optional[str] = None, description: Optional[str] = None, category: Optional[str] = None, icon: Optional[str] = None, badge_text: Optional[str] = None, sort_order: Optional[int] = None, route_prefix: Optional[str] = None) -> Dict[str, Any]:
    """Update display metadata, modern icon, category, and route prefix for a tool."""
    updates = []
    params = []
    if name is not None:
        updates.append("name = %s")
        params.append(name)
    if description is not None:
        updates.append("description = %s")
        params.append(description)
    if category is not None:
        updates.append("category = %s")
        params.append(category)
    if icon is not None:
        updates.append("icon = %s")
        params.append(icon)
    if badge_text is not None:
        updates.append("badge_text = %s")
        params.append(badge_text or None)
    if sort_order is not None:
        updates.append("sort_order = %s")
        params.append(sort_order)
    if route_prefix is not None:
        updates.append("route_prefix = %s")
        params.append(route_prefix)

    if not updates:
        return {"status": "no_changes"}

    params.append(module_key)
    sql = f"UPDATE modules SET {', '.join(updates)} WHERE `key` = %s"
    execute(sql, tuple(params))
    return {"status": "success", "module_key": module_key, "updated": True}

def sync_subscription_plans() -> Dict[str, Any]:
    """Sync all active in-pool modules to the main 'All Tools Bundle' plan."""
    plan = query_one("SELECT id FROM plans WHERE slug = 'all-tools' OR slug = 'bundle' LIMIT 1")
    if not plan:
        return {"status": "skipped", "message": "Bundle plan not found."}
    
    pool_modules = query("SELECT id FROM modules WHERE in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0")
    module_ids = [m['id'] for m in pool_modules]
    
    execute("DELETE FROM module_plan WHERE plan_id = %s", (plan['id'],))
    for mid in module_ids:
        execute("INSERT INTO module_plan (plan_id, module_id, created_at, updated_at) VALUES (%s, %s, NOW(), NOW())", (plan['id'], mid))
        
    return {"status": "synced", "plan_id": plan['id'], "total_modules_synced": len(module_ids)}

def admin_list_modules(category: Optional[str] = None, only_pool: bool = False, only_active: bool = False) -> Dict[str, Any]:
    """(Admin Only) List all platform tools and modules with full admin attributes."""
    sql = "SELECT id, `key`, name, description, category, icon, route_prefix, in_subscription_pool, is_active, is_deprecated, badge_text, sort_order, allowed_roles FROM modules WHERE 1=1"
    params = []
    if category:
        sql += " AND category = %s"
        params.append(category)
    if only_pool:
        sql += " AND in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0"
    if only_active:
        sql += " AND is_active = 1"
    sql += " ORDER BY sort_order ASC, id ASC"
    mods = query(sql, tuple(params))
    return {
        "total": len(mods),
        "modules": mods
    }

def admin_get_module_details(module_key: str) -> Dict[str, Any]:
    """(Admin Only) Get complete configuration, allowed roles, and linked plans for a tool."""
    mod = query_one("SELECT * FROM modules WHERE `key` = %s OR id = %s", (module_key, int(module_key) if module_key.isdigit() else 0))
    if not mod:
        return {"error": f"Module '{module_key}' not found."}
    
    plans = query("SELECT p.id, p.name, p.slug FROM plans p INNER JOIN module_plan mp ON mp.plan_id = p.id WHERE mp.module_id = %s", (mod['id'],))
    return {"module": mod, "linked_plans": plans}

def admin_install_module(name: str, category: Optional[str] = 'Produktivität & Prozesse', icon: Optional[str] = 'sparkles', route_prefix: Optional[str] = None, in_subscription_pool: bool = True) -> Dict[str, Any]:
    """(Admin Only) Register or activate a module in the platform database."""
    key = name.lower().replace(' ', '-').replace('_', '-')
    existing = query_one("SELECT id, `key`, name FROM modules WHERE `key` = %s", (key,))
    if existing:
        execute("UPDATE modules SET is_active = 1, updated_at = NOW() WHERE id = %s", (existing['id'],))
        sync_subscription_plans()
        return {"status": "activated", "module_id": existing['id'], "key": key}
    
    execute("""
        INSERT INTO modules (`key`, name, description, category, icon, route_prefix, in_subscription_pool, is_active, is_deprecated, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, 1, 0, NOW(), NOW())
    """, (key, name, f"{name} Module", category, icon, route_prefix or key, 1 if in_subscription_pool else 0))
    new_mod = query_one("SELECT * FROM modules WHERE `key` = %s", (key,))
    sync_subscription_plans()
    return {"status": "installed", "module": new_mod}

def admin_update_module(module_key: str, name: Optional[str] = None, description: Optional[str] = None, category: Optional[str] = None, icon: Optional[str] = None, badge_text: Optional[str] = None, sort_order: Optional[int] = None, route_prefix: Optional[str] = None, is_active: Optional[bool] = None, in_subscription_pool: Optional[bool] = None, is_deprecated: Optional[bool] = None) -> Dict[str, Any]:
    """(Admin Only) Update full tool metadata, status, pool inclusion, or deprecation."""
    updates = []
    params = []
    if name is not None:
        updates.append("name = %s")
        params.append(name)
    if description is not None:
        updates.append("description = %s")
        params.append(description)
    if category is not None:
        updates.append("category = %s")
        params.append(category)
    if icon is not None:
        updates.append("icon = %s")
        params.append(icon)
    if badge_text is not None:
        updates.append("badge_text = %s")
        params.append(badge_text or None)
    if sort_order is not None:
        updates.append("sort_order = %s")
        params.append(sort_order)
    if route_prefix is not None:
        updates.append("route_prefix = %s")
        params.append(route_prefix)
    if is_active is not None:
        updates.append("is_active = %s")
        params.append(1 if is_active else 0)
    if in_subscription_pool is not None:
        updates.append("in_subscription_pool = %s")
        params.append(1 if in_subscription_pool else 0)
    if is_deprecated is not None:
        updates.append("is_deprecated = %s")
        params.append(1 if is_deprecated else 0)

    if not updates:
        return {"status": "no_changes"}

    params.append(module_key)
    sql = f"UPDATE modules SET {', '.join(updates)}, updated_at = NOW() WHERE `key` = %s"
    execute(sql, tuple(params))
    sync_subscription_plans()
    mod = query_one("SELECT * FROM modules WHERE `key` = %s", (module_key,))
    return {"status": "success", "module": mod}

def admin_toggle_module(module_key: str, is_active: Optional[bool] = None) -> Dict[str, Any]:
    """(Admin Only) Toggle or explicitly set active/inactive status for a tool/module."""
    mod = query_one("SELECT id, `key`, is_active FROM modules WHERE `key` = %s", (module_key,))
    if not mod:
        return {"error": f"Module '{module_key}' not found."}
    new_state = is_active if is_active is not None else (not bool(mod['is_active']))
    execute("UPDATE modules SET is_active = %s, updated_at = NOW() WHERE id = %s", (1 if new_state else 0, mod['id']))
    sync_subscription_plans()
    return {"status": "success", "module_key": module_key, "is_active": new_state}

def admin_toggle_module_pool(module_key: str, in_pool: Optional[bool] = None) -> Dict[str, Any]:
    """(Admin Only) Toggle or set subscription pool status."""
    mod = query_one("SELECT id, `key`, in_subscription_pool FROM modules WHERE `key` = %s", (module_key,))
    if not mod:
        return {"error": f"Module '{module_key}' not found."}
    new_pool = in_pool if in_pool is not None else (not bool(mod['in_subscription_pool']))
    execute("UPDATE modules SET in_subscription_pool = %s, updated_at = NOW() WHERE id = %s", (1 if new_pool else 0, mod['id']))
    sync_subscription_plans()
    return {"status": "success", "module_key": module_key, "in_subscription_pool": new_pool}

def admin_deprecate_module(module_key: str, is_deprecated: bool = True) -> Dict[str, Any]:
    """(Admin Only) Deprecate or un-deprecate a module."""
    return deprecate_module(module_key, is_deprecated)

def admin_delete_module(module_key: str) -> Dict[str, Any]:
    """(Admin Only) Detach plans and remove module registration from database."""
    mod = query_one("SELECT id, `key`, name FROM modules WHERE `key` = %s", (module_key,))
    if not mod:
        return {"error": f"Module '{module_key}' not found."}
    execute("DELETE FROM module_plan WHERE module_id = %s", (mod['id'],))
    execute("DELETE FROM modules WHERE id = %s", (mod['id'],))
    sync_subscription_plans()
    return {"status": "deleted", "module_key": module_key, "name": mod['name']}

def admin_sync_module_plans() -> Dict[str, Any]:
    """(Admin Only) Synchronize active in-pool modules with the All Tools Bundle plan."""
    return sync_subscription_plans()
