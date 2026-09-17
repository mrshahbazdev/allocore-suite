import json
from db import query

def resource_audit_questions() -> str:
    """Resource provider for allocore://audit/questions"""
    questions = query("""
        SELECT q.id, p.name as pillar, q.question, q.recommended_module_key, q.recommended_book_id
        FROM auditpro_questions q
        LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id
        WHERE q.is_active = 1
        ORDER BY q.id ASC
    """)
    return json.dumps(questions, indent=2, ensure_ascii=False)

def resource_subscription_pool() -> str:
    """Resource provider for allocore://modules/pool"""
    pool = query("""
        SELECT `key`, name, category, icon, route_prefix, description
        FROM modules
        WHERE in_subscription_pool = 1 AND is_active = 1 AND is_deprecated = 0
        ORDER BY sort_order ASC
    """)
    return json.dumps(pool, indent=2, ensure_ascii=False)

def resource_knowledge_terms() -> str:
    """Resource provider for allocore://knowledge/terms"""
    terms = query("""
        SELECT term, slug, pillar, simple_definition
        FROM glossary_terms
        WHERE is_published = 1
        ORDER BY term ASC
    """)
    return json.dumps(terms, indent=2, ensure_ascii=False)

def resource_books_catalog() -> str:
    """Resource provider for allocore://books/catalog"""
    books = query("""
        SELECT b.id, b.title, a.name as author, b.cover_url, b.affiliate_link
        FROM books b
        LEFT JOIN authors a ON b.author_id = a.id
        ORDER BY b.id DESC
    """)
    return json.dumps(books, indent=2, ensure_ascii=False)

def resource_recent_audits() -> str:
    """Resource provider for allocore://recent-audits"""
    audits = query("""
        SELECT id, company_name, industry, status, created_at
        FROM auditpro_audits
        ORDER BY id DESC LIMIT 15
    """)
    return json.dumps(audits, indent=2, ensure_ascii=False)

def resource_blog_posts() -> str:
    """Resource provider for allocore://blog/posts"""
    posts = query("""
        SELECT id, title, slug, featured_image, created_at
        FROM posts
        WHERE is_published = 1
        ORDER BY id DESC
    """)
    return json.dumps(posts, indent=2, ensure_ascii=False)

def resource_case_studies() -> str:
    """Resource provider for allocore://case-studies"""
    cases = query("""
        SELECT id, title, slug, company, industry
        FROM case_studies
        WHERE is_published = 1
        ORDER BY sort_order ASC, id DESC
    """)
    return json.dumps(cases, indent=2, ensure_ascii=False)

def resource_crm_funnel() -> str:
    """Resource provider for allocore://crm/funnel"""
    leads = query("""
        SELECT pipeline_stage, count(*) as count, coalesce(sum(budget), 0) as total_budget, coalesce(avg(score), 0) as avg_score
        FROM leadquality_contacts
        GROUP BY pipeline_stage
    """)
    return json.dumps(leads, indent=2, ensure_ascii=False)

def resource_support_tickets() -> str:
    """Resource provider for allocore://support/tickets"""
    tickets = query("""
        SELECT id, subject, category, priority, status, created_at
        FROM support_tickets
        ORDER BY id DESC LIMIT 25
    """)
    return json.dumps(tickets, indent=2, ensure_ascii=False)

