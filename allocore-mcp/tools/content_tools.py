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
    """Retrieve full content, body, featured image, and metadata of a blog article."""
    if str(post_id_or_slug).isdigit():
        p = query_one("SELECT * FROM posts WHERE id = %s", (int(post_id_or_slug),))
    else:
        p = query_one("SELECT * FROM posts WHERE slug = %s", (str(post_id_or_slug),))
    if not p:
        return {"error": f"Post '{post_id_or_slug}' not found."}
    return p

def create_or_update_post(
    post_id: Optional[int] = None,
    title: Optional[str] = None,
    slug: Optional[str] = None,
    body: Optional[str] = None,
    excerpt: Optional[str] = None,
    featured_image: Optional[str] = None,
    meta_title: Optional[str] = None,
    meta_description: Optional[str] = None,
    is_published: bool = True
) -> Dict[str, Any]:
    """Create a new blog post or safely update specific fields of an existing post without overwriting body."""
    if post_id:
        existing = query_one("SELECT * FROM posts WHERE id = %s", (post_id,))
        if not existing:
            return {"error": f"Post ID {post_id} not found."}

        updates = []
        params = []
        if title is not None:
            updates.append("title = %s")
            params.append(title)
        if slug is not None:
            updates.append("slug = %s")
            params.append(slug)
        if body is not None:
            updates.append("body = %s")
            params.append(body)
        if excerpt is not None:
            updates.append("excerpt = %s")
            params.append(excerpt)
        if featured_image is not None:
            updates.append("featured_image = %s")
            params.append(featured_image)
        if meta_title is not None:
            updates.append("meta_title = %s")
            params.append(meta_title)
        if meta_description is not None:
            updates.append("meta_description = %s")
            params.append(meta_description)
        if is_published is not None:
            updates.append("is_published = %s")
            params.append(1 if is_published else 0)

        if updates:
            updates.append("updated_at = NOW()")
            sql = f"UPDATE posts SET {', '.join(updates)} WHERE id = %s"
            params.append(post_id)
            execute(sql, tuple(params))

        return {"status": "updated", "post_id": post_id, "featured_image": featured_image}
    else:
        if not title or not body:
            return {"error": "Both 'title' and 'body' are required when creating a new post."}
        slug_val = slug or title.lower().replace(" ", "-")
        new_id = execute_last_id("""
            INSERT INTO posts (title, slug, body, excerpt, featured_image, meta_title, meta_description, is_published, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """, (title, slug_val, body, excerpt, featured_image, meta_title or title, meta_description or excerpt, 1 if is_published else 0))
        return {"status": "created", "post_id": new_id, "slug": slug_val}

