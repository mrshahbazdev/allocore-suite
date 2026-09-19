# 🚀 Allocore Enterprise MCP Server

Enterprise Model Context Protocol (MCP) Server for **Allocore Suite**, optimized for both **Local Development** and **Shared Hosting Deployment** (cPanel / Plesk / LiteSpeed / SiteGround).

---

## 🌟 2-Way Execution Modes

### Mode A: Shared Hosting Native (Pure Laravel API)
On your shared host, no background Python process is needed!
The entire **70 autonomous tools** suite is served directly by Laravel via:
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

## 🛠️ Complete 70 Tool Catalog

1. **Audit Pro & Assessment**: `list_audit_questions`, `get_question_details`, `assign_question_solution`, `batch_auto_match_questions`, `list_audit_templates`, `create_or_update_question`, `create_client_audit`, `submit_audit_answers`, `list_recent_audits`, `get_audit_full_answers`.
2. **Diagnostics & 5-Säulen Coach**: `diagnose_audit_gaps`, `calculate_pillar_scores`, `generate_action_plan`, `generate_audit_executive_summary`, `benchmark_audit_performance`.
3. **Debt Snowball & Avalanche (DebtSnowballTracker)**: `list_snowball_debts`, `create_or_update_snowball_debt`, `log_snowball_payment`, `calculate_snowball_payoff_plan`, `get_snowball_financial_summary`.
4. **Cash & Financial Intelligence**: `simulate_cashflow_runway`, `calculate_unit_economics`, `get_financial_summary`.
5. **Billing & Invoicing (InvoiceMaker)**: `create_or_preview_invoice`, `list_invoices_and_receivables`.
6. **Organizational Architecture (OrgMatrix)**: `get_organization_chart`, `create_or_update_org_role`.
7. **Vision & Strategic OKRs (VisionFlow)**: `get_strategic_vision_and_goals`, `create_strategic_goal`.
8. **Task & Priority Matrix (FocusMatrix)**: `analyze_eisenhower_tasks`, `create_delegation_task`.
9. **Workforce & Time Tracking (TimeButler)**: `log_work_time_entry`, `get_team_workforce_summary`.
10. **Client Matrix & ICP Scoring (SweetSpot)**: `score_customer_sweet_spot`, `list_sweet_spot_rankings`.
11. **Project Portfolio & Goal Milestones (PlanHive)**: `create_project_milestone`, `get_project_portfolio_overview`.
12. **Production Tracking & Barcode Scans (DentalTrack)**: `track_production_order_status`, `log_workstation_scan_event`.
13. **CRM, Funnel & Leads (LeadQuality)**: `search_leads`, `get_lead_details`, `create_or_update_lead`, `score_lead_with_ai`, `get_pipeline_funnel_analytics`, `score_account_health`.
14. **Process & SOP Engineering (SopBuilder)**: `generate_and_store_sop`, `list_stored_sops`.
15. **KPI Engine & Smart Monitoring (SmartKpi)**: `evaluate_kpi_health`.
16. **Customer Support & Ticketing**: `list_support_tickets`, `create_or_reply_support_ticket`.
17. **BookIntelligence Catalog**: `search_books`, `get_book_details`, `create_or_update_book`, `repurpose_book_to_blog`.
18. **Blog CMS & Thought Leadership**: `search_blog_posts`, `get_post_details`, `create_or_update_post`, `upload_post_image`, `batch_relink_glossary_in_posts`.
19. **Case Studies & Success Stories**: `search_case_studies`, `get_case_study_details`, `create_or_update_case_study`.
20. **Knowledge & Glossary (Lexikon)**: `search_glossary_terms`, `create_or_update_glossary_term`, `delete_glossary_term`.
21. **Tool Pool & Subscription Governance**: `list_all_modules`, `manage_tool_pool`, `deprecate_module`, `update_module_metadata`, `sync_subscription_plans`, `validate_tool_pool_integrity`.
22. **User & Account Metrics**: `search_users_and_teams`, `get_user_subscription_status`, `get_platform_metrics`.
23. **DevOps, Automation & Webhooks**: `run_allocore_artisan`, `get_system_health`, `list_activity_logs`, `export_platform_dataset`, `list_webhooks_and_integrations`.

---

## 📦 Dynamic MCP Resources (`resources/list`)

- `allocore://platform-overview`: Live aggregate counters and health status.
- `allocore://audit/questions`: Master question library.
- `allocore://debts/snowball`: Debt Snowball & Avalanche Payoff Overview & Cashflow Analysis.
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
- `allocore://sops/library`: Corporate SOP & process playbook library.
- `allocore://kpis/benchmarks`: Mittelstand standard KPI reference benchmarks.
- `allocore://invoices/summary`: Open receivables and invoice payment breakdown.
- `allocore://org/chart`: Company roles, departments, and personnel chart.
- `allocore://strategy/goals`: Long-term vision and 1-3 year strategic OKR goals.
- `allocore://tasks/focus-matrix`: Active tasks mapped across Eisenhower quadrants.
- `allocore://workforce/times`: TimeButler workforce hours and recent time tracking entries.
- `allocore://customers/sweet-spot`: SweetSpot customer profitability matrix & rankings.
- `allocore://projects/portfolio`: PlanHive project portfolio overview and goal progress.
- `allocore://production/orders`: DentalTrack production orders and workstation statuses.
- `allocore://integrations/status`: Webhooks & third-party integrations.
- `allocore://financial/summary`: MRR and subscriber breakdown.

---

## 🧠 Built-in AI Prompts (`prompts/list`)

- `audit_consultant`: 5-Säulen executive diagnosis & 90-day action plan.
- `executive_audit_briefing`: High-level C-Suite board presentation and risk briefing.
- `okr_strategy_planner`: High-leverage quarterly OKR formulation.
- `eisenhower_task_triager`: Task backlog triage and delegation mapping.
- `invoice_dunning_generator`: Professional German B2B payment reminder notices.
- `job_description_architect`: Full German B2B job description and competency profiler.
- `cashflow_runway_optimizer`: Defensive liquidity and cost reduction strategy.
- `unit_economics_advisor`: Unit economics (CAC, CLV, Gross Margin) optimization.
- `account_retention_strategist`: Proactive retention roadmap for at-risk accounts.
- `icp_customer_analyzer`: Sweet Spot customer matrix analysis to isolate Ideal Customer Profiles.
- `project_risk_assessor`: PlanHive project bottleneck, milestone risk, and delay diagnostic.
- `workforce_capacity_planner`: TimeButler workforce capacity, logged hours, and overtime analysis.
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

