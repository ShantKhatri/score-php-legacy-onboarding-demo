#!/bin/bash
set -o errexit

echo "Cleaning up score-compose deployment..."
if [ -f "compose.yaml" ]; then
    docker compose down -v || true
fi

echo "Removing score-compose generated files..."
rm -rf .score-compose/
rm -f compose.yaml

echo "Removing built image..."
docker rmi legacy-guestbook:latest 2>/dev/null || true

echo "Cleaning up score-k8s deployment..."
if sudo kind get clusters 2>/dev/null | grep -q "^kind$"; then
    sudo kind delete cluster || true
else
    echo "Kind cluster 'kind' not found or already deleted. Skipping."
fi

echo "Removing score-k8s generated files..."
rm -rf .score-k8s/
rm -f manifests.yaml

echo "Cleanup complete!"
