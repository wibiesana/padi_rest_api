# 🌐 Cross-Origin Resource Sharing (CORS)

**Padi REST API Framework v1.0.0**

CORS is a security feature implemented by browsers to restrict how resources on a web page can be requested from another domain.

---

## 📋 Overview

By default, web browsers block AJAX requests (like `fetch` or `axios`) made to a different domain than the one that served the web page. Padi REST API includes a built-in CORS handler to manage these permissions securely.

---

## ⚙️ Configuration

CORS settings are controlled via the `.env` file using the `CORS_ALLOWED_ORIGINS` variable.

### 1. Development Mode (Allow All)

Leaving the variable empty in development will allow requests from any origin. This is convenient for local development but **unsafe for production**.

```env
APP_ENV=development
CORS_ALLOWED_ORIGINS=
```

### 2. Production Mode (Whitelisting)

In production, you should explicitly list the domains that are allowed to access your API. Separate multiple domains with a comma.

```env
APP_ENV=production
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

---

## 🛠️ How it Works

The framework handles CORS automatically within the `Wibiesana\Padi\Core\Application` class during the request lifecycle. It performs the following actions for every request:

1.  **Origin Validation**: Checks the `Origin` header against your `CORS_ALLOWED_ORIGINS` whitelist.
2.  **Dynamic Headers**: If the origin matches, it sends the appropriate `Access-Control-Allow-Origin` and `Access-Control-Allow-Credentials` headers.
3.  **Methods & Headers**: Automatically allows standard REST methods (`GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`, `PATCH`) and common API headers.
4.  **Preflight Handling**: Responds to `OPTIONS` requests (preflight) with a `200 OK` status and the necessary headers, then terminates correctly without executing further logic.

### Allowed Headers

The framework white-lists these headers by default:

- `Content-Type`
- `Authorization`
- `X-Requested-With`
- `X-Response-Format`
- `Accept`
- `Origin`

---

## 🏎️ FrankenPHP Worker Mode Compatibility

Padi REST API's CORS handler is fully compatible with **FrankenPHP Worker Mode**.

Unlike traditional PHP scripts that use `exit;` to handle preflight (`OPTIONS`) requests, this framework uses a `return` statement within the request handler loop. This ensures that the worker process remains alive and ready for the next request.

---

## 🔍 Troubleshooting

### I'm getting a "CORS error" in the browser.

1.  **Check .env**: Ensure the frontend domain (including protocol `https://` and port if non-standard) is in `CORS_ALLOWED_ORIGINS`.
2.  **Trailing Slashes**: Do not include a trailing slash in the origin URL (e.g., use `https://example.com`, not `https://example.com/`).
3.  **Environment**: Ensure `APP_ENV` matches your intention. If set to `production`, whitelisting becomes mandatory.
4.  **Network Tab**: Check the "Network" tab in Browser DevTools. Look for the `OPTIONS` request and see if it returned `200 OK` with the correct headers.

---

**Last Updated:** 2026-02-23  
**Version:** 2.0.1
