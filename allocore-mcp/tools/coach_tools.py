import json
from typing import Optional, Dict, Any, List
from db import query, query_one

def diagnose_audit_gaps(audit_id: int) -> Dict[str, Any]:
    """Analyze a completed customer audit and return prioritized gaps based on the 5-pillar pyramid."""
    audit = query_one("SELECT a.*, u.name as user_name, u.email as user_email FROM auditpro_audits a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = %s", (audit_id,))
    if not audit:
        return {"error": f"Audit #{audit_id} not found."}
        
    answers = query("SELECT question_id, value FROM auditpro_answers WHERE audit_id = %s", (audit_id,))
    answers_map = {}
    for a in answers:
        try:
            val = json.loads(a['value']) if isinstance(a['value'], str) else a['value']
            answers_map[a['question_id']] = val.get('answer') if isinstance(val, dict) else val
        except:
            answers_map[a['question_id']] = a['value']
            
    pyramid_order = ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy']
    gaps = []
    
    questions = query("""
        SELECT q.id, q.question, q.description, p.name as pillar_name, q.recommended_module_key, q.recommended_book_id, q.recommended_post_id, q.knowledge_slug, q.failure_recommendation
        FROM auditpro_questions q
        LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id
        WHERE q.is_active = 1
        ORDER BY FIELD(p.name, 'Revenue', 'Profit', 'Order', 'Influence', 'Legacy'), q.sort_order ASC
    """)
    
    for q in questions:
        user_ans = answers_map.get(q['id'])
        is_gap = False
        if user_ans is None:
            is_gap = True
        elif isinstance(user_ans, (int, float)) and user_ans < 3.5:
            is_gap = True
        elif user_ans in ['no', '0', 0, False]:
            is_gap = True
            
        if is_gap:
            # Resolve tool & book details
            tool = query_one("SELECT `key`, name, route_prefix FROM modules WHERE `key` = %s", (q['recommended_module_key'],)) if q['recommended_module_key'] else None
            book = query_one("SELECT id, title, affiliate_link FROM books WHERE id = %s", (q['recommended_book_id'],)) if q['recommended_book_id'] else None
            gaps.append({
                "pillar": q['pillar_name'],
                "question_id": q['id'],
                "question": q['question'],
                "user_answer": user_ans,
                "recommended_tool": tool,
                "recommended_book": book,
                "action_recommendation": q['failure_recommendation'] or f"Nutzen Sie {tool['name'] if tool else 'das passende Allocore Tool'} zur Optimierung."
            })
            
    return {
        "audit_id": audit_id,
        "customer": {"name": audit.get('user_name'), "email": audit.get('user_email')},
        "total_gaps_identified": len(gaps),
        "primary_focus_gap": gaps[0] if gaps else None,
        "all_gaps": gaps
    }

def calculate_pillar_scores(audit_id: int) -> Dict[str, Any]:
    """Calculate percentage score breakdown for Revenue, Profit, Order, Influence, and Legacy."""
    answers = query("SELECT question_id, value FROM auditpro_answers WHERE audit_id = %s", (audit_id,))
    ans_dict = {a['question_id']: a['value'] for a in answers}
    
    pillars = query("SELECT id, name FROM auditpro_pillars ORDER BY sort_order ASC")
    results = {}
    
    for p in pillars:
        p_questions = query("SELECT id, question_type FROM auditpro_questions WHERE pillar_id = %s AND is_active = 1", (p['id'],))
        total_possible = len(p_questions) * 4
        actual_score = 0
        
        for q in p_questions:
            val = ans_dict.get(q['id'])
            if val is not None:
                try:
                    parsed = json.loads(val) if isinstance(val, str) else val
                    ans = parsed.get('answer', 0) if isinstance(parsed, dict) else parsed
                    if ans in ['yes', 1, '1', True]: actual_score += 4
                    elif isinstance(ans, (int, float)): actual_score += min(4, float(ans))
                except:
                    pass
                    
        pct = round((actual_score / total_possible * 100), 1) if total_possible > 0 else 0
        results[p['name']] = {
            "score_percentage": pct,
            "actual_points": actual_score,
            "max_points": total_possible,
            "status": "Stark" if pct >= 75 else ("Ausbaufähig" if pct >= 40 else "Kritische Schwachstelle")
        }
        
    return {"audit_id": audit_id, "pillar_breakdown": results}

def simulate_audit_recommendations(sample_answers: Dict[int, Any]) -> List[Dict[str, Any]]:
    """Simulate what tools, books, and articles will be recommended based on hypothetical audit answers."""
    recommendations = []
    for q_id, ans in sample_answers.items():
        if ans in [0, '0', 'no', False] or (isinstance(ans, (int, float)) and ans < 3.5):
            q = query_one("SELECT q.*, p.name as pillar_name FROM auditpro_questions q LEFT JOIN auditpro_pillars p ON q.pillar_id = p.id WHERE q.id = %s", (q_id,))
            if q:
                tool = query_one("SELECT name, `key` FROM modules WHERE `key` = %s", (q['recommended_module_key'],)) if q['recommended_module_key'] else None
                book = query_one("SELECT title FROM books WHERE id = %s", (q['recommended_book_id'],)) if q['recommended_book_id'] else None
                recommendations.append({
                    "question_id": q_id,
                    "question": q['question'],
                    "pillar": q['pillar_name'],
                    "recommended_tool": tool['name'] if tool else "Umsatzplaner",
                    "recommended_book": book['title'] if book else "Businessplan-Praxis"
                })
    return recommendations

def get_question_benchmark(question_id: int) -> Dict[str, Any]:
    """Get anonymized benchmark average score and response distribution for an audit question."""
    answers = query("SELECT value FROM auditpro_answers WHERE question_id = %s", (question_id,))
    total = len(answers)
    yes_count = 0
    for a in answers:
        try:
            val = json.loads(a['value']) if isinstance(a['value'], str) else a['value']
            ans = val.get('answer') if isinstance(val, dict) else val
            if ans in ['yes', 1, '1', True] or (isinstance(ans, (int, float)) and ans >= 3):
                yes_count += 1
        except:
            pass
            
    pct_positive = round((yes_count / total * 100), 1) if total > 0 else 50.0
    return {
        "question_id": question_id,
        "total_responses": total,
        "positive_fulfillment_rate": f"{pct_positive}%",
        "benchmark_difficulty": "Schwer" if pct_positive < 30 else ("Mittel" if pct_positive < 70 else "Leicht")
    }

def generate_action_plan(audit_id: int) -> Dict[str, Any]:
    """Generate a structured step-by-step action plan for a client based on their audit results."""
    gaps_data = diagnose_audit_gaps(audit_id)
    if "error" in gaps_data:
        return gaps_data
        
    steps = []
    for idx, gap in enumerate(gaps_data.get('all_gaps', [])[:5], start=1):
        steps.append({
            "step": idx,
            "title": f"Säule {gap['pillar']}: {gap['question'][:45]}...",
            "primary_tool": gap['recommended_tool']['name'] if gap['recommended_tool'] else "Plattform-Tool",
            "recommended_reading": gap['recommended_book']['title'] if gap['recommended_book'] else "Fachbuch aus der Bibliothek",
            "action_instructions": gap['action_recommendation']
        })
        
    return {
        "audit_id": audit_id,
        "action_plan_title": "Allocore 90-Tage Umsetzungs-Masterplan",
        "total_steps": len(steps),
        "steps": steps
    }
