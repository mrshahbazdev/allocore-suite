from typing import Optional, Dict, Any, List
from db import query, query_one, execute

def search_users_and_teams(query_str: Optional[str] = None, limit: int = 20) -> Dict[str, Any]:
    """Search registered users, accounts, and teams."""
    users_sql = "SELECT id, name, email, current_team_id, created_at FROM users WHERE 1=1"
    teams_sql = "SELECT id, name, user_id as owner_id, personal_team, created_at FROM teams WHERE 1=1"
    params = []
    if query_str:
        users_sql += " AND (name LIKE %s OR email LIKE %s)"
        teams_sql += " AND name LIKE %s"
        params = [f"%{query_str}%", f"%{query_str}%"]
        teams_params = [f"%{query_str}%"]
    else:
        teams_params = []

    users_sql += " ORDER BY id DESC LIMIT %s"
    teams_sql += " ORDER BY id DESC LIMIT %s"
    
    users = query(users_sql, tuple(params + [limit] if params else [limit]))
    teams = query(teams_sql, tuple(teams_params + [limit] if teams_params else [limit]))
    return {"users": users, "teams": teams}

def get_user_subscription_status(user_id: int) -> Dict[str, Any]:
    """Inspect active plans, subscription status, and module permissions for a user."""
    user = query_one("SELECT id, name, email FROM users WHERE id = %s", (user_id,))
    if not user:
        return {"error": f"User #{user_id} not found."}
        
    subs = query("SELECT * FROM subscriptions WHERE user_id = %s ORDER BY id DESC", (user_id,))
    pool_tools = query("SELECT `key`, name FROM modules WHERE in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0")
    
    return {
        "user": user,
        "subscriptions": subs,
        "automatic_pool_access_granted": len(subs) > 0,
        "accessible_pool_tools": pool_tools
    }

def grant_module_access(user_id: int, module_key: str) -> Dict[str, Any]:
    """Directly grant a specific module access override to a user."""
    module = query_one("SELECT id FROM modules WHERE `key` = %s", (module_key,))
    if not module:
        return {"error": f"Module '{module_key}' not found."}
    execute("INSERT IGNORE INTO module_user (user_id, module_id, created_at, updated_at) VALUES (%s, %s, NOW(), NOW())", (user_id, module['id']))
    return {"status": "granted", "user_id": user_id, "module_key": module_key}

def list_team_members(team_id: int) -> List[Dict[str, Any]]:
    """List all members and their roles within a specific team workspace."""
    return query("""
        SELECT u.id, u.name, u.email, tu.role
        FROM team_user tu
        JOIN users u ON tu.user_id = u.id
        WHERE tu.team_id = %s
    """, (team_id,))
