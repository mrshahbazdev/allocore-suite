# 🚀 Allocore Enterprise MCP Server

Enterprise Model Context Protocol (MCP) Server for **Allocore Suite**, optimized for both **Local Development** and **Shared Hosting Deployment** (cPanel / Plesk / LiteSpeed).

---

## 🌟 2-Way Execution Modes

### Mode A: Shared Hosting Native (Pure Laravel API)
On your shared host, no background Python process is needed!
The entire 35+ tools suite is served directly by Laravel via:
- `POST https://allocore.de/api/mcp/rpc`
- Protected by your Allocore API Bearer Token.

### Mode B: Local Desktop / AI Agent Stdio
Run locally on your PC to connect Claude Desktop, Cursor, or Antigravity to your live Allocore site:

```bash
# In allocore-mcp directory:
python server.py      # Direct DB & local execution
python bridge.py      # Remote HTTPS bridge to live site
```

---

## 🛠️ Tools Available (35+ Autonomous Functions)

1. **Audit & Questions**: `list_audit_questions`, `get_question_details`, `assign_question_solution`, `batch_auto_match_questions`, `list_audit_templates`, `create_or_update_question`.
2. **Tool Pool Governance**: `list_all_modules`, `manage_tool_pool`, `deprecate_module`, `update_module_metadata`, `sync_subscription_plans`.
3. **Coach & Diagnostics**: `diagnose_audit_gaps`, `calculate_pillar_scores`, `generate_action_plan`.
4. **Book Intelligence**: `search_books`, `get_book_details`, `create_or_update_book`.
5. **Knowledge Glossary**: `search_glossary_terms`, `create_or_update_glossary_term`.
6. **Blog & CMS**: `search_blog_posts`, `create_or_update_post`.
7. **User & Subscriptions**: `search_users_and_teams`, `get_user_subscription_status`.
8. **DevOps & Analytics**: `get_platform_metrics`, `run_allocore_artisan`, `get_system_health`.

---

## ⚙️ Configuration for Claude Desktop / Cursor

Add to your `claude_desktop_config.json` or cursor settings:

```json
{
  "mcpServers": {
    "allocore": {
      "command": "python",
      "args": ["C:/Users/user/.gemini/antigravity/scratch/allocore-suite/allocore-mcp/server.py"]
    }
  }
}
```
