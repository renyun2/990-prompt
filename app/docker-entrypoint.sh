#!/bin/bash
set -e

echo "[Docker 启动脚本] 开始启动..."
echo "[启动 Apache...]"

# 启动Apache
exec apache2-foreground
