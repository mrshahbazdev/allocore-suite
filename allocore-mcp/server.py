import asyncio
import os
import sys

# Ensure server directory is in sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from mcp.server.fastmcp import FastMCP

# Initialize FastMCP Server
mcp = FastMCP("Allocore Enterprise Server", dependencies=["pymysql", "python-dotenv", "pydantic"])

# Import Tools
from tools.audit_tools import (
    list_audit_questions, get_question_details, assign_question_solution,
    batch_auto_match_questions, list_audit_templates, create_or_update_question
)
from tools.module_tools import (
    list_all_modules, manage_tool_pool, deprecate_module,
    update_module_metadata, sync_subscription_plans
)
from tools.coach_tools import (
    diagnose_audit_gaps, calculate_pillar_scores, simulate_audit_recommendations,
    get_question_benchmark, generate_action_plan
)
from tools.book_tools import (
    search_books, get_book_details, create_or_update_book, map_book_to_audit_trigger
)
from tools.glossary_tools import (
    search_glossary_terms, get_glossary_term, create_or_update_glossary_term, list_terms_by_pillar
)
from tools.content_tools import (
    search_blog_posts, get_post_details, create_or_update_post
)
from tools.user_team_tools import (
    search_users_and_teams, get_user_subscription_status, grant_module_access, list_team_members
)
from tools.analytics_tools import (
    get_platform_metrics, get_audit_completion_stats
)
from tools.ops_tools import (
    run_allocore_artisan, get_system_health
)

# Import Resources & Prompts
from resources.dynamic_resources import (
    resource_audit_questions, resource_subscription_pool,
    resource_knowledge_terms, resource_books_catalog
)
from prompts.prompt_templates import (
    prompt_diagnose_audit_gaps, prompt_auto_link_audit_solutions
)

# ----------------------------------------------------
# 1. Register Audit & Solution Tools
# ----------------------------------------------------
mcp.tool()(list_audit_questions)
mcp.tool()(get_question_details)
mcp.tool()(assign_question_solution)
mcp.tool()(batch_auto_match_questions)
mcp.tool()(list_audit_templates)
mcp.tool()(create_or_update_question)

# ----------------------------------------------------
# 2. Register Module & Tool Pool Tools
# ----------------------------------------------------
mcp.tool()(list_all_modules)
mcp.tool()(manage_tool_pool)
mcp.tool()(deprecate_module)
mcp.tool()(update_module_metadata)
mcp.tool()(sync_subscription_plans)

# ----------------------------------------------------
# 3. Register Coach & Gap Analysis Tools
# ----------------------------------------------------
mcp.tool()(diagnose_audit_gaps)
mcp.tool()(calculate_pillar_scores)
mcp.tool()(simulate_audit_recommendations)
mcp.tool()(get_question_benchmark)
mcp.tool()(generate_action_plan)

# ----------------------------------------------------
# 4. Register Book Intelligence Tools
# ----------------------------------------------------
mcp.tool()(search_books)
mcp.tool()(get_book_details)
mcp.tool()(create_or_update_book)
mcp.tool()(map_book_to_audit_trigger)

# ----------------------------------------------------
# 5. Register Knowledge & Glossary Tools
# ----------------------------------------------------
mcp.tool()(search_glossary_terms)
mcp.tool()(get_glossary_term)
mcp.tool()(create_or_update_glossary_term)
mcp.tool()(list_terms_by_pillar)

# ----------------------------------------------------
# 6. Register Content & Blog Tools
# ----------------------------------------------------
mcp.tool()(search_blog_posts)
mcp.tool()(get_post_details)
mcp.tool()(create_or_update_post)

# ----------------------------------------------------
# 7. Register User, Team & Subscription Tools
# ----------------------------------------------------
mcp.tool()(search_users_and_teams)
mcp.tool()(get_user_subscription_status)
mcp.tool()(grant_module_access)
mcp.tool()(list_team_members)

# ----------------------------------------------------
# 8. Register Analytics Tools
# ----------------------------------------------------
mcp.tool()(get_platform_metrics)
mcp.tool()(get_audit_completion_stats)

# ----------------------------------------------------
# 9. Register Ops & DevOps Tools
# ----------------------------------------------------
mcp.tool()(run_allocore_artisan)
mcp.tool()(get_system_health)

# ----------------------------------------------------
# Register Dynamic Resources
# ----------------------------------------------------
@mcp.resource("allocore://audit/questions")
def get_audit_questions_resource() -> str:
    """Live stream of all platform audit questions."""
    return resource_audit_questions()

@mcp.resource("allocore://modules/pool")
def get_subscription_pool_resource() -> str:
    """Live stream of all tools in the customer subscription pool."""
    return resource_subscription_pool()

@mcp.resource("allocore://knowledge/terms")
def get_knowledge_terms_resource() -> str:
    """Live stream of business glossary terms."""
    return resource_knowledge_terms()

@mcp.resource("allocore://books/catalog")
def get_books_catalog_resource() -> str:
    """Live stream of business books catalog."""
    return resource_books_catalog()

# ----------------------------------------------------
# Register Prompts
# ----------------------------------------------------
@mcp.prompt("diagnose_audit_gaps")
def diagnose_audit_prompt(audit_id: int) -> str:
    return prompt_diagnose_audit_gaps(audit_id)

@mcp.prompt("auto_link_audit_solutions")
def auto_link_solutions_prompt() -> str:
    return prompt_auto_link_audit_solutions()

if __name__ == "__main__":
    mcp.run()
