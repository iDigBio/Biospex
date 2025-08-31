# CI/CD Deployment Setup - Option 1 Implementation

## Overview
This implementation uses GitHub Actions to build assets and create deployment artifacts, eliminating server-side building and reducing deployment time and resource usage.

## GitHub Token Configuration

### For GitHub Actions (Easiest - Recommended)
The `GITHUB_TOKEN` is automatically provided by GitHub Actions and is already configured in the workflow. No additional setup required.

### For Local Development/Testing
If you need to test deployments locally:

1. **Create a Personal Access Token:**
   - Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
   - Generate new token with `repo` and `actions:read` permissions
   - Copy the token

2. **Set Environment Variable:**
   ```bash
   # Windows PowerShell
   $env:GITHUB_TOKEN="your_token_here"
   
   # Linux/Mac
   export GITHUB_TOKEN="your_token_here"
   ```

## How It Works

### GitHub Actions Workflow (`.github/workflows/deploy.yml`)
1. **Builds assets** using `npm run production`
2. **Creates deployment package** excluding unnecessary files (node_modules, .git, tests)
3. **Uploads artifacts** to GitHub with SHA-based naming
4. **Deploys using deployphp** with environment variables for token access

### Deployment Process (`deploy.php` + `deploy/custom.php`)
1. **Downloads CI artifacts** using GitHub API
2. **Extracts and deploys** built assets without server-side building
3. **Checks queue status** before restarting to prevent job interruption
4. **Cleans up** temporary files and node_modules automatically

## Key Features

### ✅ CI/CD Artifact Strategy
- Assets built once in GitHub Actions
- Deployed as artifacts, no server-side building
- Faster deployments with reduced server resource usage

### ✅ Queue-Safe Deployments
- `queue:count` command checks Beanstalkd queue status
- Deployment pauses queue restart if jobs are running
- Prevents interruption of long-running tasks (e.g., CSV processing)

### ✅ Automatic Cleanup
- `node_modules` removed automatically via `clear_paths`
- Temporary artifact files cleaned up after deployment
- No manual intervention required

## Queue Names Configuration
The deployment checks these queues by default:
- `export`
- `geolocate`
- `pusher_classification`

To modify queue names, edit the `$queues` array in `deploy/custom.php`:
```php
$queues = ['your_queue_1', 'your_queue_2']; // Update as needed
```

## Testing the Queue Command
Test the queue counting functionality:
```bash
# Check default queue
php artisan queue:count

# Check specific queue
php artisan queue:count export
```

## Deployment Commands
Deploy to different environments:
```bash
# Production (main branch)
php deployer.phar deploy production

# Development (development branch)  
php deployer.phar deploy development
```

## Benefits
- **Faster deployments**: No server-side asset building
- **Reduced server load**: No yarn/npm installation on server
- **Job protection**: Queue-aware restart prevention
- **Consistent builds**: Assets built in controlled CI environment
- **Automatic cleanup**: No manual file management required

## Troubleshooting
- Ensure `GITHUB_TOKEN` has proper permissions
- Verify queue names match your `.env` configuration
- Check artifact retention (set to 30 days)
- Monitor deployment logs for any API rate limiting