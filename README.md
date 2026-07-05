# score-php-legacy-onboarding-demo

A reference example for the Score community: onboarding an **existing, unmodified** PHP application onto Score via a build-time config shim.

A Developer authors and maintains their files in the [`app/src`](./app/src/) folder, their [`Dockerfile`](docker/Dockerfile) and the [`score.yaml`](score.yaml) file.

Then, they can deploy their `score.yaml` file with three options:
- [Deploy with `score-compose`](#deploy-with-score-compose)
- [Deploy with `score-k8s`](#deploy-with-score-k8s)

## Deploy with `score-compose`

Build the image:
```bash
docker build -t legacy-guestbook:latest -f docker/Dockerfile .
```

Initialize the local `score-compose` workspace:
```bash
score-compose init --no-sample
```

Generate the Docker Compose files:
```bash
score-compose generate score.yaml
```

Deploy the Docker Compose files:
```bash
docker compose up -d --remove-orphans
```

Test the deployed Workload:
```bash
docker compose exec legacy-guestbook-app curl -s -o /dev/null -w "%{http_code}" localhost/index.php
```

## Deploy with `score-k8s`

Prepare the cluster:
```bash
./scripts/setup-kind-cluster.sh

CONTAINER_IMAGE=legacy-guestbook:v1
docker build -t ${CONTAINER_IMAGE} -f docker/Dockerfile .
kind load docker-image ${CONTAINER_IMAGE}

NAMESPACE=default
kubectl create ns $NAMESPACE || true
```

Initialize the local `score-k8s` workspace:
```bash
score-k8s init --no-sample
```

Generate the Kubernetes manifests:
```bash
score-k8s generate score.yaml --override-property containers.app.image=${CONTAINER_IMAGE}
```

Deploy the Kubernetes manifests:
```bash
kubectl apply -n $NAMESPACE -f manifests.yaml
```

Test the deployed Workload (wait for pods to be ready first):
```bash
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=legacy-guestbook -n $NAMESPACE --timeout=60s
kubectl exec -it -n $NAMESPACE deploy/legacy-guestbook -- curl -s -o /dev/null -w "%{http_code}" localhost/index.php
```