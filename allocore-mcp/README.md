# 🚀 Allocore Enterprise MCP Server

Enterprise Model Context Protocol (MCP) Server for **Allocore Suite**, optimized for both **Local Development** and **Shared Hosting Deployment** (cPanel / Plesk / LiteSpeed / SiteGround).

---

## 🌟 2-Way Execution Modes

### Mode A: Shared Hosting Native (Pure Laravel API)
On your shared host, no background Python process is needed!
The entire **40 autonomous tools** suite is served directly by Laravel via:
- `POST https://allocore.de/api/mcp/rpc` (JSON-RPC 2.0 Protocol Handler)
- `GET/POST https://allocore.de/api/mcp` (SSE Stream for Claude / MCP Remote Connectors)
- `GET https://allocore.de/api/mcp/tools` (Dedicated HTTP Tool Discovery with CORS)
- `GET https://allocore.de/api/mcp/resources` (Dedicated HTTP Resource Discovery with CORS)
- `GET https://allocore.de/api/mcp/prompts` (Dedicated HTTP Prompt Discovery with CORS)
- Protected by your Allocore API Bearer Token, `X-Api-Key`, or `?token=...`.

### Mode B: Local Desktop / AI Agent Stdio
Run locally on your PC to connect Claude Desktop, Cursor, or Antigravity to your live Allocore site:

```bash
# In allocore-mcp directory:
python bridge.py      # Remote HTTPS bridge to live site (Zero pip dependencies)
python server.py      # Direct DB & local execution
```

---

## 🛠️ Complete 40 Tool Catalog

1. **Audit Pro & Assessment**: `list_audit_questions`, `get_question_details`, `assign_question_solution`, `batch_auto_match_questions`, `list_audit_templates`, `create_or_update_question`, `create_client_audit`, `submit_audit_answers`, `list_recent_audits`, `get_audit_full_answers`.
2. **Diagnostics & 5-Säulen Coach**: `diagnose_audit_gaps`, `calculate_pillar_scores`, `generate_action_plan`, `generate_audit_executive_summary`, `benchmark_audit_performance`.
3. **CRM, Funnel & Leads (LeadQuality)**: `search_leads`, `get_lead_details`, `create_or_update_lead`, `score_lead_with_ai`, `get_pipeline_funnel_analytics`.
4. **Customer Support & Ticketing**: `list_support_tickets`, `create_or_reply_support_ticket`.
5. **BookIntelligence Catalog**: `search_books`, `get_book_details`, `create_or_update_book`, `repurpose_book_to_blog`.
6. **Blog CMS & Thought Leadership**: `search_blog_posts`, `get_post_details`, `create_or_update_post`, `batch_relink_glossary_in_posts`.
7. **Case Studies & Success Stories**: `search_case_studies`, `get_case_study_details`, `create_or_update_case_study`.
8. **Knowledge & Glossary (Lexikon)**: `search_glossary_terms`, `create_or_update_glossary_term`.
9. **Tool Pool & Subscription Governance**: `list_all_modules`, `manage_tool_pool`, `deprecate_module`, `update_module_metadata`, `sync_subscription_plans`, `validate_tool_pool_integrity`.
10. **Financial & User Metrics**: `search_users_and_teams`, `get_user_subscription_status`, `get_financial_summary`, `get_platform_metrics`.
11. **DevOps, Data Export & Audit Trails**: `run_allocore_artisan`, `get_system_health`, `list_activity_logs`, `export_platform_dataset`.

---

## 📦 Dynamic MCP Resources (`resources/list`)

- `allocore://platform-overview`: Live aggregate counters and health status.
- `allocore://audit/questions`: Master question library.
- `allocore://audit/templates`: 5-pillar assessment blueprints.
- `allocore://recent-audits`: Live client audit completions.
- `allocore://modules/pool`: Active subscription tools.
- `allocore://knowledge/terms`: 80+ business glossary terms.
- `allocore://books/catalog`: 250+ BookIntelligence catalog.
- `allocore://blog/posts`: Published thought leadership articles.
- `allocore://case-studies`: Verified B2B transformation case studies.
- `allocore://leads/summary`: CRM leads and contact distribution.
- `allocore://crm/funnel`: Live pipeline conversion stages and opportunity value.
- `allocore://support/tickets`: Customer support inquiries and status.
- `allocore://financial/summary`: MRR and subscriber breakdown.

---

## 🧠 Built-in AI Prompts (`prompts/list`)

- `audit_consultant`: 5-Säulen executive diagnosis & 90-day action plan.
- `executive_audit_briefing`: High-level C-Suite board presentation and risk briefing.
- `seo_content_creator`: 8-section German thought leadership blog post with book CTA widget.
- `internal_seo_optimizer`: Automatic cross-linking of glossary terms in articles.
- `lead_nurture_strategy`: Personalized high-probability conversion roadmap.
- `customer_support_resolver`: Empathetic, accurate customer inquiry resolution.
- `kpi_cockpit_analyzer`: 5-pillar metric health check and tool matcher.
- `sop_generator`: Standard Operating Procedure documentation.
- `case_study_writer`: B2B client transformation case study drafter.
- `auto_link_audit_solutions`: Heuristic unassigned question solver.

---

## ⚙️ Configuration for Claude Desktop / Cursor

Add to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "allocore": {
      "command": "python",
      "args": [
        "C:\\Users\\user\\.gemini\\antigravity\\scratch\\allocore-suite\\allocore-mcp\\bridge.py"
      ],
      "env": {
        "ALLOCORE_BASE_URL": "https://allocore.de",
        "ALLOCORE_API_TOKEN": "DeQwE56KePEvaQfGDmqwkHHcZDePKacpdBQ7z4T2"
      }
    }
  }
}
```

