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
