from typing import Optional, Dict, Any, List
from db import query, query_one, execute, execute_last_id

def search_glossary_terms(query_str: Optional[str] = None, pillar: Optional[str] = None, category: Optional[str] = None, limit: int = 30) -> List[Dict[str, Any]]:
    """Search corporate business terms and definitions in the Allocore Knowledge Glossary."""
    sql = "SELECT id, term, slug, simple_definition, definition, category, is_beginner_friendly, is_published FROM glossary_terms WHERE 1=1"
    params = []
    if query_str:
        sql += " AND (term LIKE %s OR definition LIKE %s OR simple_definition LIKE %s)"
        params.extend([f"%{query_str}%", f"%{query_str}%", f"%{query_str}%"])
    cat = category or pillar
    if cat:
        sql += " AND (category = %s OR category LIKE %s)"
        params.extend([cat, f"%{cat}%"])
    sql += " ORDER BY term ASC LIMIT %s"
    params.append(limit)
    rows = query(sql, tuple(params))
    for r in rows:
        r['pillar'] = r.get('category')
    return rows

def get_glossary_term(slug_or_id: str) -> Dict[str, Any]:
    """Get full definition, plain-language explanation, and pillar details for a glossary term."""
    if str(slug_or_id).isdigit():
        t = query_one("SELECT * FROM glossary_terms WHERE id = %s", (int(slug_or_id),))
    else:
        t = query_one("SELECT * FROM glossary_terms WHERE slug = %s", (str(slug_or_id),))
    if not t:
        return {"error": f"Glossary term '{slug_or_id}' not found."}
    t['pillar'] = t.get('category')
    return t

def create_or_update_glossary_term(term: str, slug: str, definition: str, simple_definition: Optional[str] = None, pillar: Optional[str] = None, category: Optional[str] = None, is_published: bool = True, is_beginner_friendly: bool = True) -> Dict[str, Any]:
    """Create a new business glossary term or update an existing definition."""
    cat = category or pillar or "Revenue"
    existing = query_one("SELECT id FROM glossary_terms WHERE slug = %s", (slug,))
    if existing:
        execute("""
            UPDATE glossary_terms
            SET term = %s, definition = %s, simple_definition = %s, category = %s, is_published = %s, is_beginner_friendly = %s, updated_at = NOW()
            WHERE id = %s
        """, (term, definition, simple_definition, cat, 1 if is_published else 0, 1 if is_beginner_friendly else 0, existing['id']))
        return {"status": "updated", "term_id": existing['id'], "slug": slug, "category": cat, "pillar": cat, "is_published": is_published}
    else:
        new_id = execute_last_id("""
            INSERT INTO glossary_terms (term, slug, definition, simple_definition, category, is_beginner_friendly, is_published, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """, (term, slug, definition, simple_definition, cat, 1 if is_beginner_friendly else 0, 1 if is_published else 0))
        return {"status": "created", "term_id": new_id, "slug": slug, "category": cat, "pillar": cat, "is_published": is_published}

def delete_glossary_term(term_id: Optional[int] = None, slug: Optional[str] = None) -> Dict[str, Any]:
    """Delete a duplicate or deprecated glossary term by ID or slug."""
    if term_id:
        existing = query_one("SELECT id, term, slug FROM glossary_terms WHERE id = %s", (term_id,))
    elif slug:
        existing = query_one("SELECT id, term, slug FROM glossary_terms WHERE slug = %s", (slug,))
    else:
        return {"error": "Either 'term_id' or 'slug' is required."}

    if not existing:
        return {"error": "Glossary term not found."}

    execute("DELETE FROM glossary_terms WHERE id = %s", (existing['id'],))
    return {"status": "deleted", "deleted_term": existing}

def list_terms_by_pillar(pillar: str) -> List[Dict[str, Any]]:
    """List all business terms associated with a specific pillar (Revenue, Profit, Order, Influence, Legacy)."""
    rows = query("SELECT id, term, slug, category, simple_definition FROM glossary_terms WHERE (category = %s OR category LIKE %s) AND is_published = 1 ORDER BY term ASC", (pillar, f"%{pillar}%"))
    for r in rows:
        r['pillar'] = r.get('category')
    return rows
