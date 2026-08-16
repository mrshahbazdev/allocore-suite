#!/bin/bash
set -e

# Installs the official VS Code `code` CLI into ~/.local/bin
# so it can be used for `code tunnel`, `code serve-web`, etc.

DEST_DIR="${HOME}/.local/bin"
mkdir -p "$DEST_DIR"

if [ -x "$DEST_DIR/code" ]; then
    echo "code CLI already installed at $DEST_DIR/code"
    "$DEST_DIR/code" --version
    exit 0
fi

ARCH=$(uname -m)
case "$ARCH" in
    x86_64) OS="alpine-x64" ;;
    aarch64|arm64) OS="alpine-arm64" ;;
    *) echo "Unsupported architecture: $ARCH"; exit 1 ;;
esac

URL="https://code.visualstudio.com/sha/download?build=stable&os=cli-${OS}"
TMPDIR=$(mktemp -d)

echo "Downloading code CLI for $OS..."
curl -fsSL "$URL" -o "$TMPDIR/vscode_cli.tar.gz"
tar -xzf "$TMPDIR/vscode_cli.tar.gz" -C "$TMPDIR"
mv "$TMPDIR/code" "$DEST_DIR/code"
chmod +x "$DEST_DIR/code"
rm -rf "$TMPDIR"

echo "code CLI installed at $DEST_DIR/code"
"$DEST_DIR/code" --version
