# 📁 File Upload Guide

**Padi REST API Framework v2.0.2**

The `Core\File` class provides a simple and **secure** way to handle file uploads, deletions, and URL generation.

---

## 📋 Table of Contents

- [Configuration](#configuration)
- [Basic Upload](#basic-upload)
- [Validation (Types & Size)](#validation-types--size)
- [Security Features (v2.0.2)](#security-features-v202)
- [Organizing with Subdirectories](#organizing-with-subdirectories)
- [Complete Controller Example](#complete-controller-example)
- [Deleting Files](#deleting-files)
- [Generating File URLs](#generating-file-urls)

---

## ⚙️ Configuration

By default, files are uploaded to the `uploads/` directory in the project root with **0750 permissions**.

> **Note**: For security and public access, it is recommended to ensure your web server can serve this directory or use a symbolic link if your web root is `public/`.

---

## 📍 Basic Upload

To upload a file from an HTTP request, use the `upload` method and pass the `$_FILES` array element.

```php
use Wibiesana\Padi\Core\File;

try {
    // Basic upload to default folder
    $path = File::upload($_FILES['avatar']);

    // Returns something like: "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4.jpg"
} catch (\Exception $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

---

## 🛠️ Validation (Types & Size)

You can restrict file types and set a maximum file size (in bytes).

```php
use Wibiesana\Padi\Core\File;

$allowed = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSize = 2 * 1024 * 1024; // 2MB

$path = File::upload($_FILES['document'], 'documents', $allowed, $maxSize);
```

---

## 🔒 Security Features (v2.0.2)

### Multi-Layer Protection

The `File` class now includes **6 layers** of upload security:

| Layer | Protection              | Description                                                               |
| ----- | ----------------------- | ------------------------------------------------------------------------- |
| 1     | **Extension Blacklist** | Always blocks dangerous extensions (`.php`, `.phar`, `.exe`, `.sh`, etc.) |
| 2     | **Extension Whitelist** | Only allows specified types when `$allowedTypes` is provided              |
| 3     | **MIME Verification**   | Uses `finfo` to verify file content matches its extension                 |
| 4     | **Path Traversal**      | `sanitizePath()` removes `..`, null bytes, normalizes separators          |
| 5     | **Secure Filenames**    | `bin2hex(random_bytes(16))` — 32-char hex filename                        |
| 6     | **Delete Verification** | `realpath()` ensures deletion stays within uploads directory              |

### Blocked Extensions

The following extensions are **always blocked**, even if listed in `$allowedTypes`:

```
php, phtml, phar, php3, php4, php5, php7, php8, phps,
cgi, pl, asp, aspx, shtml, htaccess,
sh, bat, cmd, com, exe, dll, msi,
py, rb, js, jsp, war
```

### Path Traversal Prevention

```php
// ❌ BLOCKED - Path traversal attempt
File::delete('../../etc/passwd');
// sanitizePath() removes ".." components
// realpath() verifies path is within uploads/

// ✅ SAFE - Normal deletion
File::delete('avatars/a1b2c3d4.jpg');
```

---

## 📂 Organizing with Subdirectories

Pass a string as the second parameter to group uploads into folders.

```php
// Uploads to: uploads/profiles/avatars/a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4.png
$path = File::upload($_FILES['avatar'], 'profiles/avatars');
```

---

## 🚀 Complete Controller Example

Here is how you would typically use it in a REST API controller.

```php
namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\File;
use App\Models\User;

class ProfileController extends Controller
{
    public function uploadAvatar()
    {
        // 1. Validate if file exists in request
        $file = $this->request->file('avatar');

        if (!$file) {
            throw new \Exception("No file uploaded", 400);
        }

        // 2. Perform Upload (with extension whitelist)
        $path = File::upload($file, 'avatars', ['jpg', 'png'], 1024 * 1024);

        // 3. Save to Database
        $user = new User();
        $user->update($this->request->user->user_id, [
            'avatar_path' => $path
        ]);

        return $this->created([
            'path' => $path,
            'url' => File::url($path)
        ]);
    }
}
```

---

## 🗑️ Deleting Files

When a record is deleted or an image is replaced, you should remove the old file from disk.

```php
use Wibiesana\Padi\Core\File;

$oldPath = $user['avatar_path'];
$deleted = File::delete($oldPath);
// Returns true on success, false if file doesn't exist or path traversal detected
```

> **Security**: The `delete()` method uses `realpath()` to verify the resolved path is still within the `uploads/` directory before unlinking.

---

## 🔗 Generating File URLs

To return a full URL to the frontend, use the `url()` method.

```php
use Wibiesana\Padi\Core\File;

$fullUrl = File::url($user['avatar_path']);
// Result: http://localhost:8085/uploads/avatars/a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4.png
```

---

**Last Updated:** 2026-02-26
**Version:** 2.0.2
