#!/bin/bash
# Manual environment file upload script for BIOSPEX
# Allows manual triggering of environment file upload without git push

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 BIOSPEX Manual Environment File Upload${NC}"
echo -e "${BLUE}===============================================${NC}"

# Get current branch
CURRENT_BRANCH=$(git branch --show-current 2>/dev/null)

if [[ -z "$CURRENT_BRANCH" ]]; then
    echo -e "${RED}❌ Error: Not in a Git repository or no current branch${NC}"
    exit 1
fi

echo -e "${YELLOW}Current branch: ${GREEN}$CURRENT_BRANCH${NC}"

# Determine which environment file will be uploaded
case "$CURRENT_BRANCH" in
    "main")
        ENV_FILE=".env.aws.production"
        ENVIRONMENT="PRODUCTION"
        TARGET="production server (/data/web/biospex/shared/.env)"
        ;;
    "development")
        ENV_FILE=".env.aws.development"
        ENVIRONMENT="DEVELOPMENT"
        TARGET="development server (/data/web/dev.biospex/shared/.env)"
        ;;
    *)
        echo -e "${YELLOW}⚠️  Branch '$CURRENT_BRANCH' is not configured for environment file upload${NC}"
        echo -e "${BLUE}ℹ️  Only 'main' and 'development' branches upload environment files${NC}"
        exit 0
        ;;
esac

echo -e "${BLUE}Environment: ${GREEN}$ENVIRONMENT${NC}"
echo -e "${BLUE}Source file: ${GREEN}$ENV_FILE${NC}"
echo -e "${BLUE}Target: ${GREEN}$TARGET${NC}"
echo ""

# Check if environment file exists
if [[ ! -f "$ENV_FILE" ]]; then
    echo -e "${RED}❌ Error: Environment file not found: $ENV_FILE${NC}"
    echo -e "${YELLOW}Please ensure the environment file exists in the project root${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Environment file found: $ENV_FILE${NC}"
echo ""

# Confirmation prompt
echo -e "${YELLOW}This will upload $ENV_FILE to the $ENVIRONMENT server.${NC}"
echo -e "${YELLOW}A backup of the current server .env file will be created.${NC}"
echo ""
read -p "Continue with upload? (y/N): " confirm

if [[ ! $confirm =~ ^[Yy]$ ]]; then
    echo -e "${BLUE}ℹ️  Upload cancelled by user${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}🚀 Starting manual environment file upload...${NC}"
echo ""

# Get current commit SHA for the fake push data
CURRENT_SHA=$(git rev-parse HEAD 2>/dev/null)
if [[ -z "$CURRENT_SHA" ]]; then
    CURRENT_SHA="0000000000000000000000000000000000000000"
fi

# Trigger the pre-push hook with fake push data
# Format: local_ref local_sha remote_ref remote_sha
FAKE_PUSH_DATA="refs/heads/$CURRENT_BRANCH $CURRENT_SHA refs/heads/$CURRENT_BRANCH 0000000000000000000000000000000000000000"

echo "$FAKE_PUSH_DATA" | .git/hooks/pre-push

if [[ $? -eq 0 ]]; then
    echo ""
    echo -e "${GREEN}🎉 Manual environment file upload completed successfully!${NC}"
    echo -e "${BLUE}ℹ️  Your $ENVIRONMENT environment is now updated with the latest configuration${NC}"
else
    echo ""
    echo -e "${RED}❌ Manual environment file upload failed${NC}"
    echo -e "${YELLOW}Please check the error messages above and try again${NC}"
    exit 1
fi