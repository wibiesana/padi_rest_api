# 📁 File Upload Guide

**Padi REST API Framework v1.0.0**

The `Core\File` class provides a simple and secure way to handle file uploads, deletions, and URL generation.

---

## 📋 Table of Contents

- [Configuration](#configuration)
- [Basic Upload](#basic-upload)
- [Validation (Types & Size)](#validation-types--size)
- [Organizing with Subdirectories](#organizing-with-subdirectories)
- [Complete Controller Example](#complete-controller-example)
- [Deleting Files](#deleting-files)
- [Generating File URLs](#generating-file-urls)

---

## ⚙️ Configuration

By default, files are uploaded to the `uploads/` directory in the project root.

> **Note**: For security and public access, it is recommended to ensure your web server can serve this directory or use a symbolic link if your web root is `public/`.

---

## 📍 Basic Upload

To upload a file from an HTTP request, use the `upload` method and pass the `$_FILES` array element.

```php
use Core\File;

try {
    // Basic upload to default folder
    $path = File::upload($_FILES['avatar']);

    // Returns something like: "65c3a1...jpg"
} catch (\Exception $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

---

## 🛠️ Validation (Types & Size)

You can restrict file types and set a maximum file size (in bytes).

```php
use Core\File;

$allowed = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSize = 2 * 1024 * 1024; // 2MB

$path = File::upload($_FILES['document'], 'documents', $allowed, $maxSize);
```

---

## 📂 Organizing with Subdirectories

Pass a string as the second parameter to group uploads into folders.

```php
// Uploads to: uploads/profiles/avatars/65c3a1...png
$path = File::upload($_FILES['avatar'], 'profiles/avatars');
```

---

## 🚀 Complete Controller Example

Here is how you would typically use it in a REST API controller.

```php
namespace App\Controllers;

use Core\Controller;
use Core\File;
use App\Models\User;

class ProfileController extends Controller
{
    public function uploadAvatar()
    {
        // 1. Validate if file exists in request
        $file = $this->request->file('avatar'); // Helper in Request class

        if (!$file) {
            throw new \Exception("No file uploaded", 400);
        }

        // 2. Perform Upload
        $path = File::upload($file, 'avatars', ['jpg', 'png'], 1024 * 1024);

        // 3. Save to Database
        $user = new User();
        $user->update(auth()->id(), [
            'avatar_path' => $path
        ]);

        return $this->success([
            'path' => $path,
            'url' => File::url($path)
        ], "Avatar uploaded successfully");
    }
}
```

---

## 🗑️ Deleting Files

When a record is deleted or an image is replaced, you should remove the old file from disk.

```php
use Core\File;

$oldPath = $user['avatar_path'];
File::delete($oldPath);
```

---

## 🔗 Generating File URLs

To return a full URL to the frontend, use the `url()` method.

```php
use Core\File;

$fullUrl = File::url($user['avatar_path']);
// Result: http://localhost:8085/uploads/avatars/65c3a1...png
```

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
