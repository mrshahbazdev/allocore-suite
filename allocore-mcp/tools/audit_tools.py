import json
import re
from typing import Optional, Dict, Any, List
from db import query, query_one, execute, execute_last_id

def list_audit_questions(pillar: Optional[str] = None, search: Optional[str] = None, missing_solution_only: bool = False, limit: int = 50, offset: int = 0) -> List[Dict[str, Any]]:
    """List and filter audit questions across templates and pillars."""
    sql = """
        SELECT q.id, q.pillar_id, p.name as pillar_name, q.question, q.description, q.question_type,
               q.recommended_module_key, q.recommended_book_id, q.recommended_post_id, q.knowledge_slug,
               q.failure_recommendation, q.is_active
        FROM auditpro_questions q
        LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id
        WHERE 1=1
    """
    params = []
    if pillar:
        sql += " AND (p.name LIKE %s OR p.name = %s)"
        params.extend([f"%{pillar}%", pillar])
    if search:
        sql += " AND (q.question LIKE %s OR q.description LIKE %s OR q.failure_recommendation LIKE %s)"
        params.extend([f"%{search}%", f"%{search}%", f"%{search}%"])
    if missing_solution_only:
        sql += " AND (q.recommended_module_key IS NULL OR q.recommended_module_key = '') AND (q.recommended_book_id IS NULL OR q.recommended_book_id = 0)"
    
    sql += " ORDER BY q.pillar_id ASC, q.sort_order ASC, q.id ASC LIMIT %s OFFSET %s"
    params.extend([limit, offset])
    
    return query(sql, tuple(params))

def get_question_details(question_id: int) -> Dict[str, Any]:
    """Get full details of a specific audit question including assigned tool, book, and article."""
    q = query_one("""
        SELECT q.*, p.name as pillar_name, t.name as template_name
        FROM auditpro_questions q
        LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id
        LEFT JOIN auditpro_templates t ON p.template_id = t.id
        WHERE q.id = %s
    """, (question_id,))
    if not q:
        return {"error": f"Question #{question_id} not found."}
    
    # Fetch assigned book details
    book = None
    if q.get('recommended_book_id'):
        book = query_one("SELECT id, title, cover_url, affiliate_link, description FROM books WHERE id = %s", (q['recommended_book_id'],))
    
    # Fetch assigned post details
    post = None
    if q.get('recommended_post_id'):
        post = query_one("SELECT id, title, slug, excerpt, featured_image FROM posts WHERE id = %s", (q['recommended_post_id'],))
        
    # Fetch assigned glossary term
    term = None
    if q.get('knowledge_slug'):
        term = query_one("SELECT id, term, slug, simple_definition, definition FROM glossary_terms WHERE slug = %s", (q['knowledge_slug'],))
        
    # Fetch module details
    module = None
    if q.get('recommended_module_key'):
        module = query_one("SELECT id, `key`, name, description, category, icon, route_prefix, in_subscription_pool FROM modules WHERE `key` = %s", (q['recommended_module_key'],))
        
    return {
        "question": q,
        "assigned_tool": module,
        "assigned_book": book,
        "assigned_article": post,
        "assigned_glossary_term": term
    }

def assign_question_solution(question_id: int, module_key: Optional[str] = None, book_id: Optional[int] = None, post_id: Optional[int] = None, knowledge_slug: Optional[str] = None, failure_recommendation: Optional[str] = None) -> Dict[str, Any]:
    """Assign or update a recommended platform tool, book, article, or glossary term for an audit question."""
    updates = []
    params = []
    
    if module_key is not None:
        updates.append("recommended_module_key = %s")
        params.append(module_key or None)
    if book_id is not None:
        updates.append("recommended_book_id = %s")
        params.append(book_id or None)
    if post_id is not None:
        updates.append("recommended_post_id = %s")
        params.append(post_id or None)
    if knowledge_slug is not None:
        updates.append("knowledge_slug = %s")
        params.append(knowledge_slug or None)
    if failure_recommendation is not None:
        updates.append("failure_recommendation = %s")
        params.append(failure_recommendation or None)
        
    if not updates:
        return {"status": "no_changes", "message": "No solution fields provided for update."}
        
    params.append(question_id)
    sql = f"UPDATE auditpro_questions SET {', '.join(updates)} WHERE id = %s"
    affected = execute(sql, tuple(params))
    
    return {
        "status": "success",
        "question_id": question_id,
        "affected_rows": affected,
        "updated_solution": {
            "module_key": module_key,
            "book_id": book_id,
            "post_id": post_id,
            "knowledge_slug": knowledge_slug,
            "failure_recommendation": failure_recommendation
        }
    }

def batch_auto_match_questions(dry_run: bool = False) -> Dict[str, Any]:
    """AI heuristic engine: Automatically matches all unassigned audit questions with the best platform tools, books, and glossary terms."""
    questions = query("""
        SELECT q.id, q.question, q.description, p.name as pillar_name, q.recommended_module_key, q.recommended_book_id, q.knowledge_slug
        FROM auditpro_questions q
        LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id
        WHERE q.recommended_module_key IS NULL OR q.recommended_module_key = '' OR q.recommended_book_id IS NULL OR q.knowledge_slug IS NULL
    """)
    
    matched = []
    for q in questions:
        text = (q['question'] or "") + " " + (q['description'] or "")
        pillar = q['pillar_name'] or "Revenue"
        
        # Tool deduction
        guessed_tool = q['recommended_module_key']
        if not guessed_tool:
            if re.search(r'(umsatz|monatlich|turnover|revenue|mindestumsatz|zielumsatz|financial plan)', text, re.I):
                guessed_tool = 'revenue-planner'
            elif re.search(r'(cash|liquidit|kontostand|bank|profit first|finanzpolster)', text, re.I):
                guessed_tool = 'cash-core'
            elif re.search(r'(rechnung|mahnung|forderung|invoice|debitoren)', text, re.I):
                guessed_tool = 'invoice-maker'
            elif re.search(r'(lead|vertrieb|akquise|kunde|interessent|pipeline|b2b)', text, re.I):
                guessed_tool = 'lead-quality'
            elif re.search(r'(aufgabe|projekt|deadline|deleg|team|plan)', text, re.I):
                guessed_tool = 'plan-hive'
            elif re.search(r'(sop|prozess|checkliste|qualit|arbeitsanweisung)', text, re.I):
                guessed_tool = 'loop-engine'
            elif re.search(r'(vision|strategie|leitbild|werte|positionierung)', text, re.I):
                guessed_tool = 'vision-flow'
            elif re.search(r'(zeit|stundenerfassung|urlaub|arbeitszeit)', text, re.I):
                guessed_tool = 'time-butler'
            elif re.search(r'(kpi|kennzahl|soll-ist|cockpit)', text, re.I):
                guessed_tool = 'kpi-tool'
            else:
                guessed_tool = 'audit'

        # Book deduction
        guessed_book_id = q['recommended_book_id']
        if not guessed_book_id:
            if re.search(r'(umsatz|businessplan|plan|revenue)', text, re.I):
                b = query_one("SELECT id FROM books WHERE title LIKE '%Business%' OR title LIKE '%Plan%' OR title LIKE '%Profit%' LIMIT 1")
                if b: guessed_book_id = b['id']
            elif re.search(r'(cash|liquidit|profit)', text, re.I):
                b = query_one("SELECT id FROM books WHERE title LIKE '%Profit First%' OR title LIKE '%Cash%' LIMIT 1")
                if b: guessed_book_id = b['id']
            elif re.search(r'(sop|prozess|system)', text, re.I):
                b = query_one("SELECT id FROM books WHERE title LIKE '%System%' OR title LIKE '%Myth%' OR title LIKE '%Prozess%' LIMIT 1")
                if b: guessed_book_id = b['id']
                
        # Glossary slug deduction
        guessed_slug = q['knowledge_slug']
        if not guessed_slug:
            if re.search(r'(fixkosten|fixed cost|kosten)', text, re.I):
                guessed_slug = 'fixkosten'
            elif re.search(r'(cash|liquidit)', text, re.I):
                guessed_slug = 'liquiditaet'
            elif re.search(r'(sop|prozess)', text, re.I):
                guessed_slug = 'standard-operating-procedure'
            elif re.search(r'(kpi|kennzahl)', text, re.I):
                guessed_slug = 'key-performance-indicator'
            else:
                guessed_slug = 'allocore-score'

        matched.append({
            "question_id": q['id'],
            "question": q['question'],
            "pillar": pillar,
            "tool": guessed_tool,
            "book_id": guessed_book_id,
            "glossary_slug": guessed_slug
        })
        
        if not dry_run:
            execute("""
                UPDATE auditpro_questions
                SET recommended_module_key = COALESCE(recommended_module_key, %s),
                    recommended_book_id = COALESCE(recommended_book_id, %s),
                    knowledge_slug = COALESCE(knowledge_slug, %s)
                WHERE id = %s
            """, (guessed_tool, guessed_book_id, guessed_slug, q['id']))

    return {
        "total_analyzed": len(questions),
        "total_matched": len(matched),
        "dry_run": dry_run,
        "sample_matches": matched[:10]
    }

def list_audit_templates() -> List[Dict[str, Any]]:
    """List all audit templates along with their pillars and question counts."""
    templates = query("SELECT id, name, slug, description, is_active FROM auditpro_templates ORDER BY id ASC")
    for t in templates:
        pillars = query("""
            SELECT p.id, p.name, p.sort_order, COUNT(q.id) as question_count
            FROM auditpro_pillars p
            LEFT JOIN auditpro_questions q ON p.id = q.pillar_id
            WHERE p.template_id = %s
            GROUP BY p.id, p.name, p.sort_order
            ORDER BY p.sort_order ASC
        """, (t['id'],))
        t['pillars'] = pillars
    return templates

def create_or_update_question(question: str, pillar_id: int, question_id: Optional[int] = None, description: Optional[str] = None, question_type: str = "yes_no", options: Optional[List[str]] = None, failure_recommendation: Optional[str] = None) -> Dict[str, Any]:
    """Create a new audit question or update an existing one."""
    options_json = json.dumps(options) if options else None
    if question_id:
        execute("""
            UPDATE auditpro_questions
            SET question = %s, pillar_id = %s, description = %s, question_type = %s, options = %s, failure_recommendation = %s
            WHERE id = %s
        """, (question, pillar_id, description, question_type, options_json, failure_recommendation, question_id))
        return {"status": "updated", "question_id": question_id}
    else:
        new_id = execute_last_id("""
            INSERT INTO auditpro_questions (question, pillar_id, description, question_type, options, failure_recommendation, is_active, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, 1, NOW(), NOW())
        """, (question, pillar_id, description, question_type, options_json, failure_recommendation))
        return {"status": "created", "question_id": new_id}
