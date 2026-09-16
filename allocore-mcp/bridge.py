"""
Allocore MCP Remote HTTPS Bridge
Enables AI Agents (Claude Desktop, Cursor, Antigravity) on local machines
to control Allocore Suite hosted on Shared Hosting via secure HTTPS JSON-RPC.
"""

import sys
import os
import json
import httpx
from dotenv import load_dotenv

load_dotenv()

ALLOCORE_API_URL = os.getenv("ALLOCORE_API_URL", "https://allocore.de/api/mcp/rpc")
ALLOCORE_API_TOKEN = os.getenv("ALLOCORE_API_TOKEN", "")

def forward_rpc_request(payload: dict) -> dict:
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    if ALLOCORE_API_TOKEN:
        headers["Authorization"] = f"Bearer {ALLOCORE_API_TOKEN}"
        headers["X-Api-Token"] = ALLOCORE_API_TOKEN

    try:
        with httpx.Client(timeout=60.0, verify=False) as client:
            resp = client.post(ALLOCORE_API_URL, json=payload, headers=headers)
            return resp.json()
    except Exception as e:
        return {
            "jsonrpc": "2.0",
            "id": payload.get("id", 1),
            "error": {
                "code": -32603,
                "message": f"Bridge HTTP error: {str(e)}"
            }
        }

def main():
    """Stdio JSON-RPC loop for Claude Desktop / Cursor / Antigravity MCP."""
    for line in sys.stdin:
        if not line.strip():
            continue
        try:
            req = json.loads(line)
            res = forward_rpc_request(req)
            sys.stdout.write(json.dumps(res) + "\n")
            sys.stdout.flush()
        except Exception as e:
            err = {
                "jsonrpc": "2.0",
                "id": None,
                "error": {"code": -32700, "message": str(e)}
            }
            sys.stdout.write(json.dumps(err) + "\n")
            sys.stdout.flush()

if __name__ == "__main__":
    main()
