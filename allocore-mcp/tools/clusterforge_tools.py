from typing import Any, Dict, List, Optional
from db import query, query_one, execute, execute_last_id

def clusterforge_list_projects(
    team_id: Optional[int] = None,
    search: Optional[str] = None,
    status: Optional[str] = None,
    limit: int = 25
) -> Dict[str, Any]:
    """List and search SEO keyword and topic cluster projects in ClusterForge."""
    clauses = []
    params = []

    if team_id is not None:
        clauses.append("p.team_id = %s")
        params.append(team_id)

    if search:
        clauses.append("(p.topic LIKE %s OR p.website LIKE %s OR p.pillar_title LIKE %s)")
        term = f"%{search}%"
        params.extend([term, term, term])

    if status:
        clauses.append("p.status = %s")
        params.append(status)

    where_sql = ("WHERE " + " AND ".join(clauses)) if clauses else ""
    limit_val = min(100, max(1, limit))

    sql = f"""
        SELECT p.id, p.team_id, p.topic, p.website, p.language, p.status, p.pillar_title, p.created_at,
               (SELECT COUNT(*) FROM clusterforge_subtopics s WHERE s.project_id = p.id) as subtopics_count,
               (SELECT COUNT(*) FROM clusterforge_questions q JOIN clusterforge_subtopics s ON q.subtopic_id = s.id WHERE s.project_id = p.id) as questions_count
        FROM clusterforge_projects p
        {where_sql}
        ORDER BY p.id DESC
        LIMIT {limit_val}
    """
    projects = query(sql, tuple(params))

    # Stats
    stats_sql = "SELECT status, COUNT(*) as cnt FROM clusterforge_projects"
    if team_id:
        stats_sql += f" WHERE team_id = {int(team_id)}"
    stats_sql += " GROUP BY status"
    raw_stats = query(stats_sql)
    stats_map = {r['status']: r['cnt'] for r in raw_stats}

    return {
        "total": len(projects),
        "stats": {
            "completed": stats_map.get('completed', 0),
            "failed": stats_map.get('failed', 0),
            "pending": stats_map.get('pending', 0),
        },
        "projects": projects,
    }

def clusterforge_get_project(
    project_id: int,
    include_content: bool = False,
    team_id: Optional[int] = None
) -> Dict[str, Any]:
    """Retrieve full details of a specific ClusterForge project with its subtopics and questions count."""
    sql = "SELECT * FROM clusterforge_projects WHERE id = %s"
    params = [project_id]
    if team_id is not None:
        sql += " AND team_id = %s"
        params.append(team_id)

    project = query_one(sql, tuple(params))
    if not project:
        return {"error": f"Project #{project_id} not found."}

    subtopics = query("""
        SELECT s.id, s.title, s.long_tail_keyword, s.search_volume, s.cpc, s.competition, s.competition_index, s.cluster_title,
               (SELECT COUNT(*) FROM clusterforge_questions q WHERE q.subtopic_id = s.id) as questions_count
        FROM clusterforge_subtopics s
        WHERE s.project_id = %s
        ORDER BY s.sort_order ASC, s.id ASC
    """, (project_id,))

    if not include_content and project.get('pillar_content'):
        project['pillar_content'] = project['pillar_content'][:400] + "..."

    project['subtopics'] = subtopics
    project['subtopics_count'] = len(subtopics)
    return {"project": project}

def clusterforge_search_keywords(
    query_term: Optional[str] = None,
    project_id: Optional[int] = None,
    min_volume: Optional[int] = None,
    limit: int = 30,
    team_id: Optional[int] = None
) -> Dict[str, Any]:
    """Search keywords, search volume, CPC, and subtopics across ClusterForge projects."""
    clauses = []
    params = []

    if team_id is not None:
        clauses.append("p.team_id = %s")
        params.append(team_id)

    if project_id:
        clauses.append("s.project_id = %s")
        params.append(project_id)

    if query_term:
        clauses.append("(s.long_tail_keyword LIKE %s OR s.title LIKE %s OR s.description LIKE %s)")
        term = f"%{query_term}%"
        params.extend([term, term, term])

    if min_volume is not None:
        clauses.append("s.search_volume >= %s")
        params.append(min_volume)

    where_sql = ("WHERE " + " AND ".join(clauses)) if clauses else ""
    limit_val = min(100, max(1, limit))

    sql = f"""
        SELECT s.id as subtopic_id, s.project_id, p.topic as project_topic,
               COALESCE(NULLIF(s.long_tail_keyword, ''), s.title) as keyword,
               s.title, s.search_volume, s.cpc, s.competition, s.competition_index, s.cluster_title
        FROM clusterforge_subtopics s
        JOIN clusterforge_projects p ON s.project_id = p.id
        {where_sql}
        ORDER BY s.search_volume DESC, s.id DESC
        LIMIT {limit_val}
    """
    results = query(sql, tuple(params))
    return {"total": len(results), "keywords": results}

def clusterforge_get_subtopic(subtopic_id: int, team_id: Optional[int] = None) -> Dict[str, Any]:
    """Retrieve complete cluster details, article content, and Q&A for a subtopic."""
    sql = """
        SELECT s.*, p.topic as project_topic, p.team_id
        FROM clusterforge_subtopics s
        JOIN clusterforge_projects p ON s.project_id = p.id
        WHERE s.id = %s
    """
    params = [subtopic_id]
    if team_id is not None:
        sql += " AND p.team_id = %s"
        params.append(team_id)

    subtopic = query_one(sql, tuple(params))
    if not subtopic:
        return {"error": f"Subtopic #{subtopic_id} not found."}

    questions = query("""
        SELECT id, question, answer, sort_order
        FROM clusterforge_questions
        WHERE subtopic_id = %s
        ORDER BY sort_order ASC, id ASC
    """, (subtopic_id,))

    subtopic['questions'] = questions
    return {"subtopic": subtopic}

def clusterforge_search_questions(
    query_term: str,
    project_id: Optional[int] = None,
    limit: int = 25
) -> Dict[str, Any]:
    """Search through SEO questions and answers across topic clusters."""
    clauses = ["(q.question LIKE %s OR q.answer LIKE %s)"]
    params = [f"%{query_term}%", f"%{query_term}%"]

    if project_id:
        clauses.append("s.project_id = %s")
        params.append(project_id)

    where_sql = "WHERE " + " AND ".join(clauses)
    limit_val = min(100, max(1, limit))

    sql = f"""
        SELECT q.id, q.subtopic_id, s.title as subtopic_title, s.long_tail_keyword,
               p.id as project_id, p.topic as project_topic, q.question, q.answer
        FROM clusterforge_questions q
        JOIN clusterforge_subtopics s ON q.subtopic_id = s.id
        JOIN clusterforge_projects p ON s.project_id = p.id
        {where_sql}
        ORDER BY q.id DESC
        LIMIT {limit_val}
    """
    results = query(sql, tuple(params))
    return {"total": len(results), "questions": results}

def clusterforge_create_project(
    topic: str,
    website: Optional[str] = None,
    language: str = 'de',
    pillar_title: Optional[str] = None,
    team_id: int = 1
) -> Dict[str, Any]:
    """Create a new SEO keyword cluster project."""
    topic = topic.strip()
    if not topic:
        return {"error": "topic is required."}

    pillar_title = pillar_title or topic
    project_id = execute_last_id("""
        INSERT INTO clusterforge_projects (team_id, user_id, topic, website, language, status, pillar_title, created_at, updated_at)
        VALUES (%s, 1, %s, %s, %s, 'pending', %s, NOW(), NOW())
    """, (team_id, topic, website, language, pillar_title))

    return {
        "status": "created",
        "project": {
            "id": project_id,
            "topic": topic,
            "website": website,
            "language": language,
            "pillar_title": pillar_title,
            "status": "pending",
        },
        "message": f"ClusterForge project '{topic}' created successfully."
    }

def clusterforge_export_content(
    project_id: int,
    type: str = 'pillar',
    subtopic_id: Optional[int] = None
) -> Dict[str, Any]:
    """Export markdown content with frontmatter for a pillar page or cluster subtopic page."""
    p = query_one("SELECT * FROM clusterforge_projects WHERE id = %s", (project_id,))
    if not p:
        return {"error": f"Project #{project_id} not found."}

    if type == 'cluster':
        if not subtopic_id:
            return {"error": "subtopic_id is required when type is cluster."}
        s = query_one("SELECT * FROM clusterforge_subtopics WHERE id = %s AND project_id = %s", (subtopic_id, project_id))
        if not s:
            return {"error": f"Subtopic #{subtopic_id} not found in project #{project_id}."}

        body = f"<!--\nTitle: {s.get('cluster_title') or ''}\nMeta Description: {s.get('cluster_meta_description') or ''}\nLong-tail keyword: {s.get('long_tail_keyword') or ''}\n-->\n\n{s.get('cluster_content') or ''}\n"
        return {
            "type": "cluster",
            "project_id": project_id,
            "subtopic_id": subtopic_id,
            "title": s.get('cluster_title'),
            "meta_description": s.get('cluster_meta_description'),
            "long_tail_keyword": s.get('long_tail_keyword'),
            "content": body
        }

    body = f"<!--\nTitle: {p.get('pillar_title') or ''}\nMeta Description: {p.get('pillar_meta_description') or ''}\n-->\n\n{p.get('pillar_content') or ''}\n"
    return {
        "type": "pillar",
        "project_id": project_id,
        "title": p.get('pillar_title'),
        "meta_description": p.get('pillar_meta_description'),
        "content": body
    }

def clusterforge_delete_project(project_id: int) -> Dict[str, Any]:
    """Delete an SEO topic cluster project."""
    p = query_one("SELECT id, topic FROM clusterforge_projects WHERE id = %s", (project_id,))
    if not p:
        return {"error": f"Project #{project_id} not found."}

    execute("DELETE FROM clusterforge_projects WHERE id = %s", (project_id,))
    return {"status": "deleted", "project_id": project_id, "topic": p['topic']}
