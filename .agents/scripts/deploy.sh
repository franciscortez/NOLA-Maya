#!/bin/bash

# Configuration
PROJECT_ID="nola-maya"
REGION="asia-southeast1"
REPO_NAME="maya-repo"
IMAGE_NAME="maya-app"
SERVICE_NAME="maya-app"
JOB_NAME="migrate-db"

echo "🚀 Starting deployment for ${SERVICE_NAME} to ${REGION}..."

# 1. Build & Push Image
echo "📦 Building and pushing Docker image..."
gcloud builds submit \
  --tag ${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/${IMAGE_NAME} \
  --project=${PROJECT_ID}

# 2. Update Migration Job
echo "🔄 Updating migration job image..."
gcloud run jobs update ${JOB_NAME} \
  --image ${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/${IMAGE_NAME} \
  --region ${REGION} \
  --project=${PROJECT_ID}

# 3. Deploy Cloud Run Service
echo "📡 Deploying to Cloud Run..."
gcloud run deploy ${SERVICE_NAME} \
  --image ${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/${IMAGE_NAME} \
  --region ${REGION} \
  --project=${PROJECT_ID} \
  --allow-unauthenticated

echo "✅ Deployment complete!"
