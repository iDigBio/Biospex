# Biospex

## Overview
Biospex is a Laravel-based web application for managing biodiversity specimen data and citizen science projects. The platform integrates with Zooniverse for crowdsourced transcription, supports automated OCR processing, and provides comprehensive data export capabilities.

## Features
- **Citizen Science Integration**: Seamlessly connects with Zooniverse platform for specimen transcription
- **Automated OCR Processing**: AWS Lambda-based OCR for specimen label extraction  
- **Data Management**: Import, export, and reconciliation of biodiversity specimen data
- **Queue Processing**: Background job processing for large-scale operations
- **Multi-Environment Support**: Separate development and production configurations

## Deployment

This project uses **automated CI/CD deployment** with GitHub Actions and deployphp for streamlined, queue-safe deployments.

### Quick Deployment Commands

**Automatic deployment (recommended):**
```bash
# Deploy to production
git push origin main

# Deploy to development  
git push origin development

# Skip deployment (push code without deploying)
git commit -m "Update documentation [skip deploy]"
git push origin main
```

**Manual deployment:**
```bash
# Deploy to production
dep deploy production

# Deploy to development
dep deploy development
```

### Key Features
- ✅ **CI/CD Artifacts**: Assets built in GitHub Actions, no server-side building
- ✅ **Queue Safety**: Automatic job checking prevents interruption of active tasks
- ✅ **Environment Isolation**: Separate queue names for development vs production
- ✅ **Skip Deployment**: Use `[skip deploy]` in commit messages to push without deploying

### Detailed Setup & Configuration

**For complete deployment setup, troubleshooting, and advanced configuration:**  
👉 **[See DEPLOYMENT_SETUP.md](DEPLOYMENT_SETUP.md)**

The detailed guide covers:
- GitHub token configuration
- CI/CD workflow explanation  
- Queue configuration and environment variables
- Troubleshooting common deployment issues
- Testing procedures and best practices

## License
Biospex is open-sourced software licensed under GNU General Public License v3.0.

## Translation
Translation by https://translation.io