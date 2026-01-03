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
1. **PHP 8.3 Environment**: The deployment now runs on PHP 8.3.
2. **Downloads CI artifacts** using GitHub API: This replaces server-side building, so `npm` or `yarn` are no longer required on the server.
3. **Dynamic SQS Queues**: Queue names are now environment-prefixed (`prod-`, `dev-`, or `loc-`) as defined in `config/services.php`.
4. **Filament & OpCache**: Automated tasks now handle Filament asset optimization and OpCache resets during deployment.
5. **Checks queue status** before restarting to prevent job interruption
6. **Cleans up** temporary files and node_modules automatically

## Key Features

### ✅ CI/CD Artifact Strategy
- Assets built once in GitHub Actions
- Deployed as artifacts, no server-side building
- Faster deployments with reduced server resource usage

### ✅ Queue-Safe Deployments
- `queue:count` command checks status before restarting.
- **Dynamic Prefixing**: SQS queues are dynamically named based on the environment (e.g., `prod-batch-trigger`).

## Queue Names Configuration
The deployment checks these queues by default. Note that names are now derived from the `APP_ENV` prefix:
- `{{prefix}}-export`
- `{{prefix}}-geolocate`
- `{{prefix}}-pusher_classification`

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