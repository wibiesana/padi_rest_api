# FrankenPHP Mode Configuration

This application supports two FrankenPHP operational modes:

## Available Modes

### 1. **Standard Mode** (Default)

- PHP runs for every request
- Suitable for development and debugging
- Lower memory usage
- Performance: ~50-70ms per request

### 2. **Worker Mode** (Production)

- PHP worker stays in memory
- Application bootstrap happens only once
- High performance for production
- Performance: ~25-35ms per request after warm-up

## How to Use

### Method 1: Quick Switch (No Rebuild)

Fast, no need to rebuild the image:

```powershell
# Switch to worker mode
.\quick-switch.ps1 worker

# Switch to standard mode
.\quick-switch.ps1 standard
```

### Method 2: Full Rebuild

Rebuild the image from scratch (safer):

```powershell
# Switch to worker mode
.\switch-mode.ps1 worker

# Switch to standard mode
.\switch-mode.ps1 standard
```

### Method 3: Manual

```powershell
# 1. Copy the desired Caddyfile
Copy-Item Caddyfile.worker Caddyfile
# or
Copy-Item Caddyfile.standard Caddyfile

# 2. Restart container
docker-compose restart app
```

## Configuration Files

- `Caddyfile.standard` - Standard mode configuration
- `Caddyfile.worker` - Worker mode configuration
- `Caddyfile` - Active configuration file (copied from one of the above)

## Performance Testing

```powershell
# Single request
curl http://localhost:8085/

# Multiple requests for performance testing
1..10 | ForEach-Object {
    Measure-Command { curl.exe -s http://localhost:8085/ | Out-Null } |
    Select-Object -ExpandProperty TotalMilliseconds
}
```

## Recommendations

- **Development**: Use **standard mode** for easier debugging
- **Production**: Use **worker mode** for maximum performance
- **Testing**: Use **standard mode** for more consistent tests

## Troubleshooting

If you encounter issues after switching modes:

```powershell
# Reset and rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```
