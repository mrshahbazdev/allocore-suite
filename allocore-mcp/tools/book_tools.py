from typing import Optional, Dict, Any, List
from db import query, query_one, execute, execute_last_id

def search_books(query_str: Optional[str] = None, limit: int = 20) -> List[Dict[str, Any]]:
    """Search the BookIntelligence business book library by title, author, or description."""
    sql = """
        SELECT b.id, b.title, b.isbn, b.cover_url, b.description, b.affiliate_link, b.status, a.name as author_name
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.id
        WHERE 1=1
    """
    params = []
    if query_str:
        sql += " AND (b.title LIKE %s OR b.description LIKE %s OR a.name LIKE %s)"
        params.extend([f"%{query_str}%", f"%{query_str}%", f"%{query_str}%"])
    sql += " ORDER BY b.id DESC LIMIT %s"
    params.append(limit)
    return query(sql, tuple(params))

def get_book_details(book_id: int) -> Dict[str, Any]:
    """Get full information for a business book including key takeaways and author."""
    b = query_one("""
        SELECT b.*, a.name as author_name, a.bio as author_bio
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.id
        WHERE b.id = %s
    """, (book_id,))
    if not b:
        return {"error": f"Book #{book_id} not found."}
    
    # Check for question mappings
    mappings = query("""
        SELECT qm.*, q.question
        FROM book_question_mappings qm
        LEFT JOIN auditpro_questions q ON qm.audit_question_id = q.id
        WHERE qm.book_id = %s
    """, (book_id,))
    b['question_mappings'] = mappings
    return b

def create_or_update_book(title: str, book_id: Optional[int] = None, author_name: Optional[str] = None, description: Optional[str] = None, cover_url: Optional[str] = None, affiliate_link: Optional[str] = None, status: str = "active") -> Dict[str, Any]:
    """Add a new business book or update an existing one in the BookIntelligence catalog."""
    author_id = None
    if author_name:
        a = query_one("SELECT id FROM authors WHERE name = %s", (author_name,))
        if a:
            author_id = a['id']
        else:
            author_id = execute_last_id("INSERT INTO authors (name, created_at, updated_at) VALUES (%s, NOW(), NOW())", (author_name,))

    if book_id:
        execute("""
            UPDATE books
            SET title = %s, author_id = COALESCE(%s, author_id), description = %s, cover_url = %s, affiliate_link = %s, status = %s, updated_at = NOW()
            WHERE id = %s
        """, (title, author_id, description, cover_url, affiliate_link, status, book_id))
        return {"status": "updated", "book_id": book_id}
    else:
        new_id = execute_last_id("""
            INSERT INTO books (title, author_id, description, cover_url, affiliate_link, status, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
        """, (title, author_id, description, cover_url, affiliate_link, status))
        return {"status": "created", "book_id": new_id}

def map_book_to_audit_trigger(book_id: int, audit_question_id: Optional[int] = None, module_key: Optional[str] = None, when_to_read_trigger: Optional[str] = None, problem_statement: Optional[str] = None) -> Dict[str, Any]:
    """Link a book to an audit question or tool as a high-impact solution trigger."""
    map_id = execute_last_id("""
        INSERT INTO book_question_mappings (book_id, audit_question_id, module_key, when_to_read_trigger, problem_statement, is_active, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, 1, NOW(), NOW())
    """, (book_id, audit_question_id, module_key, when_to_read_trigger, problem_statement))
    return {"status": "mapped", "mapping_id": map_id, "book_id": book_id}
