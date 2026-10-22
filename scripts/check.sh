#!/usr/bin/env sh
set -eu
docker compose ps
curl -I "http://localhost:${HTTP_PORT:-8088}/"
curl -fsS "http://localhost:${HTTP_PORT:-8088}/health" && echo
curl -fsS 'http://localhost:9090/api/v1/targets' | head -c 700 || true
