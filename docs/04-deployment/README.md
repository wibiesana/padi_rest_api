# Deployment Documentation

Complete documentation for deploying and configuring Padi REST API in production.

## 📚 Documentation List

### Docker & Redis

- **[DOCKER_MODE_SELECTION.md](DOCKER_MODE_SELECTION.md)** ✨ - Choose Docker deployment mode (Standard/Worker/Nginx)
- **[DOCKER_DEPLOY.md](DOCKER_DEPLOY.md)** ✨ - Complete Docker deployment guide with Redis
- **[REDIS_SETUP.md](REDIS_SETUP.md)** ✨ - Redis cache configuration and setup
- **[DOCKER.md](DOCKER.md)** - Docker deployment guide

### FrankenPHP & Performance

- **[MODE_SWITCHING.md](MODE_SWITCHING.md)** - How to switch between Worker Mode and Standard Mode
- **[PERFORMANCE.md](PERFORMANCE.md)** - Benchmark and performance comparison of both modes
- **[FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md)** - Complete FrankenPHP setup guide
- **[FRANKENPHP_IMPLEMENTATION.md](FRANKENPHP_IMPLEMENTATION.md)** - FrankenPHP implementation details

### Deployment

- **[PRODUCTION.md](PRODUCTION.md)** - Production best practices
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Troubleshooting guide

## 🚀 Quick Start

### Docker Deployment (Recommended)

```bash
# Standard Mode - Development
docker compose -f docker-compose.standard.yml up -d

# Worker Mode - Production (FASTEST)
docker compose -f docker-compose.worker.yml up -d

# Full Stack with Nginx - Production + SSL
docker compose -f docker-compose.nginx.yml up -d
```

See [DOCKER_MODE_SELECTION.md](DOCKER_MODE_SELECTION.md) for a detailed guide.

### Switch FrankenPHP Mode

```powershell
# Worker Mode (Production - High Performance)
.\quick-switch.ps1 worker

# Standard Mode (Development - Easy Debugging)
.\quick-switch.ps1 standard
```

### Performance Comparison

| Mode     | Cold Start | Warm Requests | Best For    |
| -------- | ---------- | ------------- | ----------- |
| Worker   | ~40ms      | ~28ms         | Production  |
| Standard | ~110ms     | ~30ms         | Development |

## 📖 Related Documentation

- [Getting Started](../01-getting-started/) - Initial application setup
- [Core Concepts](../02-core-concepts/) - Basic framework concepts
- [Advanced](../03-advanced/) - Advanced features
- [Examples](../05-examples/) - Implementation examples

## 🔗 Quick Links

- [Main README](../../README.md)
- [Documentation Index](../INDEX.md)
- [Docker Compose](../../docker-compose.yml)
- [Caddyfile](../../Caddyfile)
