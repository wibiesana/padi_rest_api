# 🌾 Padi REST API Framework - Documentation

> **Version 2.0.5** | Production Ready | Last Updated: 2026-03-04

Welcome to the official documentation for **Padi REST API Framework** - a high-performance, lightweight PHP framework designed for building professional REST APIs with maximum speed and minimal overhead.

> **"PADI: The Staple Layer for Modern Apps."**

> _"Stateless enough for workers, but connected enough for performance."_

## ✨ P.A.D.I. Philosophy

- **P**erformant: Fast and efficient.
- **A**daptable: Easy to adapt to various databases and frontends.
- **D**istributed: Built for distributed systems and microservices architecture.
- **I**nterface: A standard, reliable data connector for modern applications.

---

## 📖 Quick Navigation

| Section                                    | Description                                        | Best For          |
| ------------------------------------------ | -------------------------------------------------- | ----------------- |
| [🚀 Getting Started](#-getting-started)    | Installation, setup, and first steps               | New users         |
| [📚 Core Concepts](#-core-concepts)        | Models, controllers, resources, and query building | All developers    |
| [🎯 Advanced Topics](#-advanced-topics)    | Multi-database, frontend integration               | Experienced users |
| [🚀 Deployment](#-deployment--performance) | Production deployment and optimization             | DevOps teams      |
| [💡 Examples](#-code-examples)             | Ready-to-use code samples                          | Quick reference   |

---

## 🚀 Getting Started

Perfect for developers new to Padi REST API or setting up a new project.

### Quick Start (5 minutes)

**[📄 QUICK_START.md](01-getting-started/QUICK_START.md)**

- Installation in 3 commands
- First API endpoint
- Testing your setup

### Installation Guide

**[📄 INSTALLATION.md](01-getting-started/INSTALLATION.md)** ✨ NEW

- Detailed installation steps
- Server requirements
- PHP extensions
- Automated vs manual setup

### Configuration Guide

**[📄 CONFIGURATION.md](01-getting-started/CONFIGURATION.md)** ✨ NEW

- Environment variables
- Development vs production
- Security settings
- CORS configuration

### First Steps

**[📄 FIRST_STEPS.md](01-getting-started/FIRST_STEPS.md)** ✨ NEW

- Register first user
- Test authentication
- Generate CRUD
- Common tasks

### Complete Setup Guide

**[📄 INIT_APP_GUIDE.md](01-getting-started/INIT_APP_GUIDE.md)**

- Automated setup process
- Database configuration
- User management
- CRUD generation

### Setup Methods Comparison

**[📄 SETUP_METHODS.md](01-getting-started/SETUP_METHODS.md)**

- `init.php` vs `init_app.bat`
- Cross-platform compatibility
- Choosing the right method

### Troubleshooting

**[📄 INIT_APP_TROUBLESHOOTING.md](01-getting-started/INIT_APP_TROUBLESHOOTING.md)**

- Common installation issues
- Platform-specific problems
- Solutions and workarounds

---

## 📚 Core Concepts

Essential knowledge for building with Padi REST API.

### Padi CLI (Console)

**[📄 CLI_INTERFACE.md](02-core-concepts/CLI_INTERFACE.md)** ✨ NEW

- Overview & sentralisasi perintah
- Daftar perintah (App, Make, Migrate, Generate)
- Opsi & parameter global (`--write`, `--overwrite`, dll)
- Contoh alur kerja pengembangan

### Authentication & Security

**[📄 AUTHENTICATION.md](02-core-concepts/AUTHENTICATION.md)** ✨ NEW

- JWT authentication flow
- Password requirements
- Security features
- Rate limiting
- CORS configuration

### Role-Based Access Control (RBAC)

**[📄 RBAC.md](02-core-concepts/RBAC.md)** ✨ UPDATED

- RoleMiddleware usage
- Controller helper methods
- Real-world examples (student, teacher, admin)
- Best practices
- Testing authorization

### Models Guide

**[📄 MODELS.md](02-core-concepts/MODELS.md)** ✨ NEW

- Model structure (Base/Concrete)
- CRUD operations
- Relationships
- Validation
- Best practices

### Controllers Guide

**[📄 CONTROLLERS.md](02-core-concepts/CONTROLLERS.md)** ✨ UPDATED

- Controller structure
- Request handling
- Custom endpoints
- Response methods
- Error handling

### Routing Guide

**[📄 ROUTING.md](02-core-concepts/ROUTING.md)** ✨ NEW

- Route mapping
- Route parameters
- API Versioning
- Route groups
- Best practices

### Middleware Guide

**[📄 MIDDLEWARE.md](02-core-concepts/MIDDLEWARE.md)** ✨ NEW

- Understanding middleware
- Defining middleware
- Registering middleware
- Built-in middleware
- FrankenPHP compatibility

### Response Structure & Flexible Formats

**[📄 RESPONSE_STRUCTURE.md](02-core-concepts/RESPONSE_STRUCTURE.md)** ✨ UPDATED

- Flexible response formats (Full, Simple, Raw)
- Header-based format switching
- Automatic data transformation
- Direct Return pattern (No more `success()` calls)
- FrankenPHP Worker mode compatibility

- Workflow examples

### API Resources Guide

**[📄 RESOURCES.md](02-core-concepts/RESOURCES.md)** ✨ NEW

- Transformation layer
- Formatting API responses
- Conditional relationships
- Collection handling

### Email Guide

**[📄 EMAIL.md](02-core-concepts/EMAIL.md)** ✨ NEW

- SMTP configuration
- Sending HTML emails
- Sending attachments
- Queued emails

### Queue System Guide

**[📄 QUEUE.md](02-core-concepts/QUEUE.md)** ✨ NEW

- Background job processing
- Defining job classes
- Pushing to queue
- Running queue worker

### Caching System Guide

**[📄 CACHE.md](02-core-concepts/CACHE.md)** ✨ NEW

- File & Redis drivers
- Basic operations (Get/Set)
- The "Remember" pattern
- Cache clearing

### File Upload Guide

**[📄 FILE_UPLOAD.md](02-core-concepts/FILE_UPLOAD.md)** ✨ NEW

- Safe file uploads
- Size & type validation
- Directory organization
- URL generation & deletion

### Database Setup

**[📄 DATABASE_SETUP.md](02-core-concepts/DATABASE_SETUP.md)**

- Database configuration
- Schema management
- Connection setup
- Best practices

### Query Builder

**[📄 QUERY_BUILDER.md](02-core-concepts/QUERY_BUILDER.md)**

- Fluent query interface
- Complex queries
- Joins and aggregations
- Security features

### Database Transactions

**[📄 DATABASE_TRANSACTIONS.md](02-core-concepts/DATABASE_TRANSACTIONS.md)**

- Automatic transactions
- Manual transaction control
- Rollback strategies
- Error handling

### Active Record

**[📄 ACTIVE_RECORD.md](02-core-concepts/ACTIVE_RECORD.md)** ✨ UPDATED

- CRUD operations
- Eager loading (`with`)
- Audit fields
- Composite keys
- Lifecycle hooks (`beforeSave`, `afterLoad`)

### User Model Guide

**[📄 USER_MODEL.md](02-core-concepts/USER_MODEL.md)**

- Enhanced user model
- Authentication fields
- Role-based access
- Best practices

---

## 🎯 Advanced Topics

Take your API to the next level with advanced features.

### Error Handling & Message Codes

**[📄 ERROR_HANDLING.md](03-advanced/ERROR_HANDLING.md)** ✨ UPDATED

- Complete message code reference
- Standardized error response structure
- **Database Error Handling & Debugging**
- Frontend integration examples (Vue/React)
- Custom error codes & best practices

### Security Best Practices

**[📄 SECURITY.md](03-advanced/SECURITY.md)** ✨ NEW

- Security checklist
- SQL injection protection
- Password security
- JWT best practices
- HTTPS enforcement

### Multi-Database Support

**[📄 MULTI_DATABASE.md](03-advanced/MULTI_DATABASE.md)**

- Multiple database connections
- MySQL, PostgreSQL, SQLite
- Connection switching
- Real-world use cases

### Frontend Integration

**[📄 FRONTEND_INTEGRATION.md](03-advanced/FRONTEND_INTEGRATION.md)**

- Vue.js integration
- React integration
- Angular integration
- Next.js integration

### Cross-Origin Resource Sharing (CORS)

**[📄 CORS.md](03-advanced/CORS.md)** ✨ NEW

- Understanding CORS
- Whitelisting origins
- Preflight handling
- FrankenPHP compatibility
- Vanilla JavaScript

### API Testing

**[📄 API_TESTING.md](03-advanced/API_TESTING.md)**

- cURL examples
- Testing workflows
- Automated testing
- Best practices

### API Collection Guide

**[📄 API_COLLECTION_GUIDE.md](03-advanced/API_COLLECTION_GUIDE.md)**

- API collection setup
- Environment variables
- Testing workflows
- Import/export

---

## 🚀 Deployment & Performance

Production-ready deployment and performance optimization.

### Production Deployment

**[📄 PRODUCTION.md](04-deployment/PRODUCTION.md)** ✨ UPDATED

- Pre-deployment checklist (Detailed)
- Server configuration (Apache/NGINX/FrankenPHP)
- SSL/TLS security hardening
- Database setup & automated backups
- Monitoring, logging & health checks
- Deployment workflow & maintenance

### Troubleshooting Guide

**[📄 TROUBLESHOOTING.md](04-deployment/TROUBLESHOOTING.md)** ✨ NEW

- Common issues
- Database problems
- Authentication errors
- CORS issues
- Performance problems
- Debug tools

### Docker Deployment

**[📄 DOCKER.md](04-deployment/DOCKER.md)** ✨ UPDATED

- Comprehensive Docker setup guide
- 3 deployment modes (Standard/Worker/Nginx)
- Redis cache & persistent storage
- SSL/TLS & Caddyfile configuration
- Commands for backup, restore, and scaling
- Troubleshooting & FAQ

**[📄 REDIS_SETUP.md](04-deployment/REDIS_SETUP.md)** ✨ Redis Configuration

- Redis cache setup
- File vs Redis comparison
- Testing guide
- Performance tips

### FrankenPHP Worker Mode (3-10x Faster!)

**[📄 WORKER_SCRIPTS.md](04-deployment/WORKER_SCRIPTS.md)** ✨ NEW

- Queue worker vs FrankenPHP worker
- Clear naming conventions
- Usage examples
- Migration from old naming

**[📄 FRANKENPHP_SETUP.md](04-deployment/FRANKENPHP_SETUP.md)** ✨ UPDATED

- Installation & setup guide
- Performance benchmarks
- Technical implementation details
- Docker & Caddyfile configuration
- Troubleshooting & FAQ

**[📄 MODE_SWITCHING.md](04-deployment/MODE_SWITCHING.md)** ✨ NEW

- Switch between Worker and Standard mode
- Quick switch script
- Mode comparison
- Testing performance

**[📄 PERFORMANCE.md](04-deployment/PERFORMANCE.md)** ✨ NEW

- Performance benchmarks
- Worker vs Standard mode
- Cold start comparison
- Best practices

---

## 💡 Code Examples

Ready-to-use code samples and collections.

### API Reference

**[📄 API_REFERENCE.md](05-examples/API_REFERENCE.md)** ✨ NEW

- Standard response format
- HTTP status codes
- Authentication endpoints
- CRUD endpoints
- Validation rules
- cURL examples

### Frontend API Client

**[📄 frontend-examples.js](05-examples/frontend-examples.js)**

- Axios setup
- Authentication flow
- CRUD operations
- Error handling

### API Collection

**[📄 postman_collection.json](05-examples/postman_collection.json)**

- Complete API collection
- Pre-configured requests
- Environment templates
- Import ready

---

## 📋 Complete Reference

### Main Documentation

**[📄 README.md](README.md)** ✨ UPDATED

Concise overview with links to:

- Quick start guide
- Documentation index
- Learning paths
- Quick reference
- System requirements

---

## 🎓 Learning Paths

### Path 1: Beginner (First-time users)

1. [QUICK_START.md](01-getting-started/QUICK_START.md) - 5 min
2. [FIRST_STEPS.md](01-getting-started/FIRST_STEPS.md) - 15 min
3. [AUTHENTICATION.md](02-core-concepts/AUTHENTICATION.md) - 20 min
4. [MODELS.md](02-core-concepts/MODELS.md) - 20 min

**Total: ~1 hour**

### Path 2: Intermediate (Building production apps)

1. [INSTALLATION.md](01-getting-started/INSTALLATION.md) - 15 min
2. [CONFIGURATION.md](01-getting-started/CONFIGURATION.md) - 10 min
3. [CODE_GENERATOR.md](02-core-concepts/CODE_GENERATOR.md) - 15 min
4. [CONTROLLERS.md](02-core-concepts/CONTROLLERS.md) - 20 min
5. [ROUTING.md](02-core-concepts/ROUTING.md) - 10 min
6. [MIDDLEWARE.md](02-core-concepts/MIDDLEWARE.md) - 15 min
7. [RESOURCES.md](02-core-concepts/RESOURCES.md) - 15 min
8. [CACHE.md](02-core-concepts/CACHE.md) - 10 min
9. [FILE_UPLOAD.md](02-core-concepts/FILE_UPLOAD.md) - 10 min
10. [RBAC.md](02-core-concepts/RBAC.md) - 20 min
11. [FRONTEND_INTEGRATION.md](03-advanced/FRONTEND_INTEGRATION.md) - 30 min

**Total: ~2.7 hours**

### Path 3: Advanced (Performance & scaling)

1. [QUERY_BUILDER.md](02-core-concepts/QUERY_BUILDER.md) - 20 min
2. [MULTI_DATABASE.md](03-advanced/MULTI_DATABASE.md) - 25 min
3. [SECURITY.md](03-advanced/SECURITY.md) - 30 min
4. [FRANKENPHP_SETUP.md](04-deployment/FRANKENPHP_SETUP.md) - 20 min
5. [PRODUCTION.md](04-deployment/PRODUCTION.md) - 30 min

**Total: ~2 hours**

---

## 🔍 Quick Reference

### Common Tasks

| Topic                                                    | Description                                                    |
| -------------------------------------------------------- | -------------------------------------------------------------- |
| **[Padi CLI](02-core-concepts/CLI_INTERFACE.md)**        | Pusat kendali & command interface                              |
| **[Authentication](02-core-concepts/AUTHENTICATION.md)** | JWT authentication & security                                  |
| Install framework                                        | [INSTALLATION.md](01-getting-started/INSTALLATION.md)          |
| Configure environment                                    | [CONFIGURATION.md](01-getting-started/CONFIGURATION.md)        |
| First API calls                                          | [FIRST_STEPS.md](01-getting-started/FIRST_STEPS.md)            |
| Setup database                                           | [DATABASE_SETUP.md](02-core-concepts/DATABASE_SETUP.md)        |
| Generate CRUD                                            | [CODE_GENERATOR.md](02-core-concepts/CODE_GENERATOR.md)        |
| Create models                                            | [MODELS.md](02-core-concepts/MODELS.md)                        |
| Build controllers                                        | [CONTROLLERS.md](02-core-concepts/CONTROLLERS.md)              |
| Connect frontend                                         | [FRONTEND_INTEGRATION.md](03-advanced/FRONTEND_INTEGRATION.md) |
| Security hardening                                       | [SECURITY.md](03-advanced/SECURITY.md)                         |
| Deploy to production                                     | [PRODUCTION.md](04-deployment/PRODUCTION.md)                   |
| Deploy with Docker                                       | [DOCKER.md](04-deployment/DOCKER.md)                           |
| Troubleshoot issues                                      | [TROUBLESHOOTING.md](04-deployment/TROUBLESHOOTING.md)         |
| API reference                                            | [API_REFERENCE.md](05-examples/API_REFERENCE.md)               |

---

## 📁 Documentation Structure

```
docs/
├── INDEX.md                          # This file - Complete navigation
├── README.md                         # Quick overview & getting started
│
├── 01-getting-started/               # Installation & Setup
│   ├── QUICK_START.md                # 5-minute quick start
│   ├── INSTALLATION.md               # ✨ Detailed installation
│   ├── CONFIGURATION.md              # ✨ Environment configuration
│   ├── FIRST_STEPS.md                # ✨ First API calls
│   ├── INIT_APP_GUIDE.md             # Automated setup guide
│   ├── SETUP_METHODS.md              # Setup methods comparison
│   └── INIT_APP_TROUBLESHOOTING.md   # Installation troubleshooting
│
├── 02-core-concepts/                 # Core Features
│   ├── AUTHENTICATION.md             # ✨ JWT authentication & security
│   ├── MODELS.md                     # ✨ Database models guide
│   ├── CONTROLLERS.md                # ✨ Controllers guide
│   ├── ROUTING.md                    # ✨ Routing guide
│   ├── CODE_GENERATOR.md             # ✨ Auto CRUD generator
│   ├── RESOURCES.md                  # ✨ API resources guide
│   ├── EMAIL.md                      # ✨ Email guide
│   ├── QUEUE.md                      # ✨ Queue system guide
│   ├── CACHE.md                      # ✨ Caching system guide
│   ├── FILE_UPLOAD.md                # ✨ File upload guide
│   ├── DATABASE_SETUP.md             # Database configuration
│   ├── QUERY_BUILDER.md              # Query builder
│   ├── DATABASE_TRANSACTIONS.md      # Transactions
│   ├── ACTIVE_RECORD.md              # ✨ ActiveRecord guide
│   ├── RESPONSE_STRUCTURE.md         # ✨ Response structure & formats
│   └── USER_MODEL.md                 # User model guide
│
├── 03-advanced/                      # Advanced Topics
│   ├── SECURITY.md                   # ✨ Security best practices
│   ├── MULTI_DATABASE.md             # Multi-database support
│   ├── FRONTEND_INTEGRATION.md       # Frontend integration
│   ├── CORS.md                       # ✨ CORS guide
│   ├── ERROR_HANDLING.md             # ✨ Error handling & DB debugging
│   ├── API_TESTING.md                # API testing
│   └── API_COLLECTION_GUIDE.md       # API collection guide
│
├── 04-deployment/                    # Production & Performance
│   ├── PRODUCTION.md                 # ✨ Production deployment
│   ├── DOCKER.md                     # ✨ Docker deployment
│   ├── FRANKENPHP_SETUP.md           # ✨ FrankenPHP setup & implementation
│   └── TROUBLESHOOTING.md            # ✨ Troubleshooting guide
│
└── 05-examples/                      # Code Samples
    ├── API_REFERENCE.md              # ✨ Complete API reference
    ├── frontend-examples.js          # Frontend examples
    └── postman_collection.json       # API collection
```

**✨ NEW** = Newly created in reorganization

---

## 🌟 Key Features

- ⚡ **Auto CRUD Generator** - Generate models, controllers, and routes automatically
- 🚀 **Padi CLI** - Powerful command-line interface for development and automation
- 🔐 **JWT Authentication** - Secure token-based authentication built-in
- 🗄️ **Multi-Database** - MySQL, MariaDB, PostgreSQL, SQLite support
- 🚀 **FrankenPHP Ready** - 3-10x performance boost with worker mode
- 🛡️ **Security First** - SQL injection protection, rate limiting, CORS
- 📦 **Zero Dependencies** - Pure PHP, no heavy frameworks
- 🎯 **Frontend Ready** - Works with Vue, React, Angular, Next.js

---

## 💬 Need Help?

1. **Check the docs** - Most answers are in this documentation
2. **Quick Start** - [QUICK_START.md](01-getting-started/QUICK_START.md)
3. **Troubleshooting** - [TROUBLESHOOTING.md](04-deployment/TROUBLESHOOTING.md)
4. **Examples** - [05-examples/](05-examples/) for working code
5. **API Reference** - [API_REFERENCE.md](05-examples/API_REFERENCE.md)

---

## 🚀 Quick Links

- **[Get Started in 5 Minutes →](01-getting-started/QUICK_START.md)**
- **[Installation Guide →](01-getting-started/INSTALLATION.md)**
- **[First Steps →](01-getting-started/FIRST_STEPS.md)**
- **[Frontend Integration →](03-advanced/FRONTEND_INTEGRATION.md)**
- **[Production Deployment →](04-deployment/PRODUCTION.md)**
- **[API Reference →](05-examples/API_REFERENCE.md)**

---

**Framework:** Padi REST API v2.0.5  
**Status:** Production Ready ✅  
**Security Score:** 9.0/10 🛡️  
**Performance Score:** 8.5/10 ⚡  
**License:** MIT

**Happy Coding!** 🌾
