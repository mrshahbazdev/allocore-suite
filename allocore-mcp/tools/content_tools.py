from typing import Optional, Dict, Any, List
from db import query, query_one, execute, execute_last_id

def search_blog_posts(query_str: Optional[str] = None, limit: int = 20) -> List[Dict[str, Any]]:
    """Search published blog and expert articles in the CMS."""
    sql = "SELECT id, title, slug, excerpt, featured_image, is_published, created_at FROM posts WHERE 1=1"
    params = []
    if query_str:
        sql += " AND (title LIKE %s OR body LIKE %s OR excerpt LIKE %s)"
        params.extend([f"%{query_str}%", f"%{query_str}%", f"%{query_str}%"])
    sql += " ORDER BY id DESC LIMIT %s"
    params.append(limit)
    return query(sql, tuple(params))

def get_post_details(post_id_or_slug: str) -> Dict[str, Any]:
    """Retrieve full content and metadata of a blog article."""
    if post_id_or_slug.isdigit():
        p = query_one("SELECT * FROM posts WHERE id = %s", (int(post_id_or_slug),))
    else:
        p = query_one("SELECT * FROM posts WHERE slug = %s", (post_id_or_slug,))
    if not p:
        return {"error": f"Post '{post_id_or_slug}' not found."}
    return p

def create_or_update_post(title: str, slug: str, body: str, post_id: Optional[int] = None, excerpt: Optional[str] = None, is_published: bool = True) -> Dict[str, Any]:
    """Create or update a blog post for content marketing or audit solutions."""
    if post_id:
        execute("""
            UPDATE posts SET title = %s, slug = %s, body = %s, excerpt = %s, is_published = %s, updated_at = NOW()
            WHERE id = %s
        """, (title, slug, body, excerpt, 1 if is_published else 0, post_id))
        return {"status": "updated", "post_id": post_id}
    else:
        new_id = execute_last_id("""
            INSERT INTO posts (title, slug, body, excerpt, is_published, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
        """, (title, slug, body, excerpt, 1 if is_published else 0))
        return {"status": "created", "post_id": new_id}
