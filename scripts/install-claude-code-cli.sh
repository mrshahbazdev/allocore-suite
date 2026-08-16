#!/bin/bash
set -e

# Installs the official Claude Code CLI from Anthropic.
# After installation, run `claude --version` and then `claude` to start a session.

if command -v claude >/dev/null 2>&1; then
    echo "Claude Code CLI already installed:"
    claude --version
    exit 0
fi

echo "Installing Claude Code CLI..."
curl -fsSL https://claude.ai/install.sh | bash

echo "Claude Code CLI installed. Run 'claude --help' to get started."
