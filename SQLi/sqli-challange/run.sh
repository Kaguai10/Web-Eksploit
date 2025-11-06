#!/bin/bash
set -e
docker-compose build --no-cache --pull
docker-compose up -d
echo "Application should be available at http://localhost:8080 (or your Docker host)"
