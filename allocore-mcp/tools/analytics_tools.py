from typing import Dict, Any
from db import query_one, query

def get_platform_metrics() -> Dict[str, Any]:
    """Retrieve platform-wide live metrics (users, audits, pool tools, library catalog)."""
    users_count = query_one("SELECT COUNT(*) as c FROM users")['c']
    audits_count = query_one("SELECT COUNT(*) as c FROM auditpro_audits")['c']
    pool_tools_count = query_one("SELECT COUNT(*) as c FROM modules WHERE in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0")['c']
    books_count = query_one("SELECT COUNT(*) as c FROM books")['c']
    terms_count = query_one("SELECT COUNT(*) as c FROM glossary_terms")['c']
    
    return {
        "registered_users": users_count,
        "completed_audits": audits_count,
        "active_subscription_tools": pool_tools_count,
        "books_in_library": books_count,
        "glossary_terms": terms_count
    }

def get_audit_completion_stats() -> Dict[str, Any]:
    """Analyze completion rates, average scores, and most frequent weak points across all audits."""
    total_audits = query_one("SELECT COUNT(*) as c FROM auditpro_audits")['c']
    total_answers = query_one("SELECT COUNT(*) as c FROM auditpro_answers")['c']
    
    # Top 5 most frequent gap questions
    frequent_gaps = query("""
        SELECT q.id, q.question, p.name as pillar_name, COUNT(*) as gap_count
        FROM auditpro_answers a
        JOIN auditpro_questions q ON a.question_id = q.id
        JOIN auditpro_pillars p ON q.pillar_id = p.id
        WHERE a.value LIKE '%no%' OR a.value LIKE '%0%' OR a.value LIKE '%1%'
        GROUP BY q.id, q.question, p.name
        ORDER BY gap_count DESC
        LIMIT 5
    """)
    
    return {
        "total_audits": total_audits,
        "total_answers_logged": total_answers,
        "top_weak_point_questions": frequent_gaps
    }
