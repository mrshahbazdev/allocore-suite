"""
Allocore MCP Remote HTTPS Bridge
Enables AI Clients (Claude Desktop, Cursor, Antigravity) on local machines
to control Allocore Suite hosted on Shared Hosting via secure HTTPS JSON-RPC.
Zero external pip dependencies required (uses standard library).
"""

import io
import json
import os
import sys
import urllib.error
import urllib.request

# Ensure UTF-8 I/O on Windows
if sys.platform == "win32":
    try:
        sys.stdin.reconfigure(encoding="utf-8")
        sys.stdout.reconfigure(encoding="utf-8")
    except Exception:
        pass

ALLOCORE_BASE_URL = os.getenv("ALLOCORE_BASE_URL", "https://allocore.de").rstrip("/")
ALLOCORE_API_TOKEN = os.getenv("ALLOCORE_API_TOKEN", "DeQwE56KePEvaQfGDmqwkHHcZDePKacpdBQ7z4T2")

RPC_URL = f"{ALLOCORE_BASE_URL}/api/mcp/rpc"
if ALLOCORE_API_TOKEN and "?" not in RPC_URL:
    RPC_URL = f"{RPC_URL}?token={ALLOCORE_API_TOKEN}"


def forward_rpc_request(payload: dict) -> dict | None:
    req_id = payload.get("id")
    method = payload.get("method", "")

    # MCP Notifications (e.g. notifications/initialized) require no RPC response
    if req_id is None and (method.startswith("notifications/") or method == "initialized"):
        return None

    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 AllocoreBridge/2.0",
    }
    if ALLOCORE_API_TOKEN:
        headers["Authorization"] = f"Bearer {ALLOCORE_API_TOKEN}"
        headers["X-Api-Key"] = ALLOCORE_API_TOKEN

    try:
        data = json.dumps(payload).encode("utf-8")
        req = urllib.request.Request(RPC_URL, data=data, headers=headers, method="POST")
        with urllib.request.urlopen(req, timeout=30) as resp:
            resp_bytes = resp.read()
            return json.loads(resp_bytes.decode("utf-8"))
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8", errors="ignore")
        return {
            "jsonrpc": "2.0",
            "id": req_id if req_id is not None else 1,
            "error": {
                "code": -32603,
                "message": f"Allocore Server HTTP {e.code}: {err_body[:200]}",
            },
        }
    except Exception as e:
        return {
            "jsonrpc": "2.0",
            "id": req_id if req_id is not None else 1,
            "error": {
                "code": -32603,
                "message": f"Bridge Connection Error: {str(e)}",
            },
        }


def main():
    """Stdio JSON-RPC loop for Claude Desktop."""
    for line in sys.stdin:
        line = line.strip()
        if not line:
            continue
        try:
            req = json.loads(line)
            res = forward_rpc_request(req)
            if res is not None:
                sys.stdout.write(json.dumps(res) + "\n")
                sys.stdout.flush()
        except Exception as e:
            err = {
                "jsonrpc": "2.0",
                "id": None,
                "error": {"code": -32700, "message": str(e)},
            }
            sys.stdout.write(json.dumps(err) + "\n")
            sys.stdout.flush()


if __name__ == "__main__":
    main()

