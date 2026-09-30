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

def admin_create_user(name: str, email: str, password: Optional[str] = None, role: Optional[str] = None, is_active: bool = True, locale: str = 'de') -> Dict[str, Any]:
    """(Admin Only) Create a new user account with role and active status."""
    import secrets
    existing = query_one("SELECT id FROM users WHERE email = %s", (email,))
    if existing:
        return {"error": f"A user with email '{email}' already exists."}
    plain_password = password if password else secrets.token_urlsafe(10)
    import hashlib
    # In Laravel bcrypt/argon is used, or handled via API
    execute("""
        INSERT INTO users (name, email, password, is_active, locale, email_verified_at, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, NOW(), NOW(), NOW())
    """, (name, email, plain_password, 1 if is_active else 0, locale))
    new_user = query_one("SELECT id, name, email, is_active, created_at FROM users WHERE email = %s", (email,))
    return {"status": "created", "user": new_user, "generated_password": plain_password if not password else "(provided)"}

def admin_update_user(user_id: int, name: Optional[str] = None, email: Optional[str] = None, is_active: Optional[bool] = None, locale: Optional[str] = None) -> Dict[str, Any]:
    """(Admin Only) Update an existing user account."""
    user = query_one("SELECT id, name, email FROM users WHERE id = %s", (user_id,))
    if not user:
        return {"error": f"User #{user_id} not found."}
    if name:
        execute("UPDATE users SET name = %s, updated_at = NOW() WHERE id = %s", (name, user_id))
    if email:
        execute("UPDATE users SET email = %s, updated_at = NOW() WHERE id = %s", (email, user_id))
    if is_active is not None:
        execute("UPDATE users SET is_active = %s, updated_at = NOW() WHERE id = %s", (1 if is_active else 0, user_id))
    if locale:
        execute("UPDATE users SET locale = %s, updated_at = NOW() WHERE id = %s", (locale, user_id))
    updated = query_one("SELECT id, name, email, is_active, locale, updated_at FROM users WHERE id = %s", (user_id,))
    return {"status": "updated", "user": updated}

def admin_delete_user(user_id: int) -> Dict[str, Any]:
    """(Admin Only) Delete a user account."""
    user = query_one("SELECT id, name, email FROM users WHERE id = %s", (user_id,))
    if not user:
        return {"error": f"User #{user_id} not found."}
    execute("DELETE FROM users WHERE id = %s", (user_id,))
    return {"status": "deleted", "user_id": user_id, "name": user['name'], "email": user['email']}

def admin_assign_subscription(user_id: int, plan_id: Optional[int] = None, plan_slug: Optional[str] = None, billing_interval: str = 'monthly', status: str = 'active', admin_note: Optional[str] = None) -> Dict[str, Any]:
    """(Admin Only) Assign or create a subscription plan for a user."""
    user = query_one("SELECT id, name, email FROM users WHERE id = %s", (user_id,))
    if not user:
        return {"error": f"User #{user_id} not found."}
    if plan_id:
        plan = query_one("SELECT id, name, slug FROM plans WHERE id = %s", (plan_id,))
    elif plan_slug:
        plan = query_one("SELECT id, name, slug FROM plans WHERE slug = %s", (plan_slug,))
    else:
        plan = query_one("SELECT id, name, slug FROM plans WHERE slug = 'all-tools' OR is_active = 1 LIMIT 1")
    if not plan:
        return {"error": "Plan not found."}
    execute("""
        INSERT INTO tool_subscriptions (billable_type, billable_id, plan_id, billing_interval, status, starts_at, ends_at, admin_note, created_at, updated_at)
        VALUES ('App\\\\Models\\\\User', %s, %s, %s, %s, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), %s, NOW(), NOW())
    """, (user_id, plan['id'], billing_interval, status, admin_note or 'Assigned via MCP'))
    return {"status": "assigned", "user_id": user_id, "plan": plan, "billing_interval": billing_interval, "status": status}

def admin_cancel_subscription(subscription_id: int, admin_note: Optional[str] = None) -> Dict[str, Any]:
    """(Admin Only) Cancel an active subscription."""
    sub = query_one("SELECT id, status FROM tool_subscriptions WHERE id = %s", (subscription_id,))
    if not sub:
        return {"error": f"Subscription #{subscription_id} not found."}
    execute("UPDATE tool_subscriptions SET status = 'cancelled', ends_at = NOW(), updated_at = NOW() WHERE id = %s", (subscription_id,))
    return {"status": "cancelled", "subscription_id": subscription_id}

def admin_list_plans() -> Dict[str, Any]:
    """(Admin Only) List all subscription plans available in the system."""
    plans = query("SELECT id, name, slug, price_monthly, price_yearly, currency, is_active FROM plans ORDER BY id ASC")
    return {"total": len(plans), "plans": plans}

