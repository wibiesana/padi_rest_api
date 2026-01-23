# Password Reset Feature

## Overview

Fitur forgot password dan reset password memungkinkan user untuk mereset password mereka melalui email verification.

## Alur Kerja

### 1. Forgot Password (Request Reset)

User meminta reset password dengan mengirimkan email mereka:

```bash
POST /auth/forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}
```

**Response:**

```json
{
  "success": true,
  "message": "If the email exists, a password reset link has been sent.",
  "data": null
}
```

**Catatan:** Response selalu success untuk mencegah email enumeration attack.

### 2. Email Dikirim

System akan:

- Generate token unik (64 karakter hex)
- Simpan token di database dengan expiry 1 jam
- Kirim email berisi link reset password
- Link format: `{FRONTEND_URL}/reset-password?token={TOKEN}&email={EMAIL}`

### 3. Reset Password

User mengakses link dari email dan submit form reset password:

```bash
POST /auth/reset-password
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "your-reset-token-from-email",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
```

**Response Success:**

```json
{
  "success": true,
  "message": "Password has been reset successfully. You can now login with your new password.",
  "data": null
}
```

**Response Error (Invalid Token):**

```json
{
  "success": false,
  "message": "Invalid or expired reset token",
  "data": null
}
```

## Password Requirements

Password harus memenuhi kriteria:

- ✅ Minimal 8 karakter
- ✅ Minimal 1 huruf besar (A-Z)
- ✅ Minimal 1 huruf kecil (a-z)
- ✅ Minimal 1 angka (0-9)
- ✅ Minimal 1 karakter special (@$!%\*?&#)

## Database Schema

### Table: password_resets

```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);
```

## Security Features

### 1. Token Expiration

- Token berlaku selama 1 jam
- Setelah expired, token tidak bisa digunakan

### 2. One-Time Use

- Token dihapus setelah digunakan
- Tidak bisa digunakan lebih dari sekali

### 3. Email Enumeration Prevention

- Response selalu success meskipun email tidak terdaftar
- Mencegah attacker mengetahui email yang terdaftar

### 4. Token Security

- Token 64 karakter random hex (32 bytes)
- Cryptographically secure random

### 5. Old Token Cleanup

- Token lama dihapus saat request reset baru
- Hanya 1 active token per email

## Configuration

### Environment Variables

```env
# Frontend URL untuk reset password page
FRONTEND_URL=http://localhost:3000

# Email Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Your App Name"
```

## Migration

Jalankan migration untuk membuat table `password_resets`:

```bash
php scripts/migrate.php migrate
```

Ini akan membuat table berdasarkan file:

```
database/migrations/006_create_password_resets_table.php
```

## Frontend Integration

### React/Next.js Example

**Forgot Password Page:**

```jsx
import { useState } from "react";
import axios from "axios";

export default function ForgotPassword() {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await axios.post(
        "http://localhost:8000/auth/forgot-password",
        {
          email,
        },
      );
      setMessage(response.data.message);
    } catch (error) {
      setMessage(error.response?.data?.message || "An error occurred");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="email"
        placeholder="Enter your email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
      />
      <button type="submit" disabled={loading}>
        {loading ? "Sending..." : "Send Reset Link"}
      </button>
      {message && <p>{message}</p>}
    </form>
  );
}
```

**Reset Password Page:**

```jsx
import { useState } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import axios from "axios";

export default function ResetPassword() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get("token");
  const email = searchParams.get("email");

  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await axios.post(
        "http://localhost:8000/auth/reset-password",
        {
          email,
          token,
          password,
          password_confirmation: passwordConfirmation,
        },
      );
      setMessage(response.data.message);

      // Redirect to login after 2 seconds
      setTimeout(() => {
        router.push("/login");
      }, 2000);
    } catch (error) {
      setMessage(error.response?.data?.message || "An error occurred");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="password"
        placeholder="New Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        required
      />
      <input
        type="password"
        placeholder="Confirm Password"
        value={passwordConfirmation}
        onChange={(e) => setPasswordConfirmation(e.target.value)}
        required
      />
      <button type="submit" disabled={loading}>
        {loading ? "Resetting..." : "Reset Password"}
      </button>
      {message && <p>{message}</p>}
    </form>
  );
}
```

### Vue 3 Example

**ForgotPassword.vue:**

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <input
      v-model="email"
      type="email"
      placeholder="Enter your email"
      required
    />
    <button type="submit" :disabled="loading">
      {{ loading ? "Sending..." : "Send Reset Link" }}
    </button>
    <p v-if="message">{{ message }}</p>
  </form>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const email = ref("");
const message = ref("");
const loading = ref(false);

const handleSubmit = async () => {
  loading.value = true;

  try {
    const response = await axios.post(
      "http://localhost:8000/auth/forgot-password",
      {
        email: email.value,
      },
    );
    message.value = response.data.message;
  } catch (error) {
    message.value = error.response?.data?.message || "An error occurred";
  } finally {
    loading.value = false;
  }
};
</script>
```

**ResetPassword.vue:**

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <input
      v-model="password"
      type="password"
      placeholder="New Password"
      required
    />
    <input
      v-model="passwordConfirmation"
      type="password"
      placeholder="Confirm Password"
      required
    />
    <button type="submit" :disabled="loading">
      {{ loading ? "Resetting..." : "Reset Password" }}
    </button>
    <p v-if="message">{{ message }}</p>
  </form>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const password = ref("");
const passwordConfirmation = ref("");
const message = ref("");
const loading = ref(false);

const token = ref("");
const email = ref("");

onMounted(() => {
  token.value = route.query.token;
  email.value = route.query.email;
});

const handleSubmit = async () => {
  loading.value = true;

  try {
    const response = await axios.post(
      "http://localhost:8000/auth/reset-password",
      {
        email: email.value,
        token: token.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      },
    );
    message.value = response.data.message;

    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push("/login");
    }, 2000);
  } catch (error) {
    message.value = error.response?.data?.message || "An error occurred";
  } finally {
    loading.value = false;
  }
};
</script>
```

## Testing

### Using Postman

1. **Import Collection:**
   - Import `postman/auth_api_collection.json`

2. **Test Forgot Password:**
   - Request: `POST /auth/forgot-password`
   - Body: `{ "email": "user@example.com" }`

3. **Check Email:**
   - Check your email inbox (or Mailtrap if testing)
   - Copy token from email URL

4. **Test Reset Password:**
   - Request: `POST /auth/reset-password`
   - Body: Include email, token, and new password

### Using cURL

**Request Reset:**

```bash
curl -X POST http://localhost:8000/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'
```

**Reset Password:**

```bash
curl -X POST http://localhost:8000/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email":"user@example.com",
    "token":"your-token-here",
    "password":"NewPassword123!",
    "password_confirmation":"NewPassword123!"
  }'
```

## Email Templates

Email template bisa di-customize di `AuthController`:

### Forgot Password Email Template

```html
<h2>Password Reset Request</h2>
<p>Hello,</p>
<p>You requested to reset your password. Click the link below:</p>
<p><a href="{reset_url}">{reset_url}</a></p>
<p>This link will expire in 1 hour.</p>
<p>If you didn't request this, please ignore this email.</p>
```

### Password Reset Success Email Template

```html
<h2>Password Reset Successful</h2>
<p>Hello,</p>
<p>Your password has been successfully reset.</p>
<p>If you didn't make this change, please contact us immediately.</p>
```

## Troubleshooting

### Email Not Received

1. Check email configuration in `.env`
2. Check queue worker is running: `php scripts/worker.php`
3. Check spam/junk folder
4. Verify SMTP credentials

### Token Expired

- Token hanya valid 1 jam
- Request reset password baru

### Token Invalid

- Token hanya bisa digunakan sekali
- Pastikan token dari URL email benar
- Request reset password baru

### Password Validation Failed

- Check password requirements
- Minimal 8 karakter dengan uppercase, lowercase, angka, dan special char

## API Reference

### POST /auth/forgot-password

Request password reset link.

**Request:**

```json
{
  "email": "user@example.com"
}
```

**Response: 200 OK**

```json
{
  "success": true,
  "message": "If the email exists, a password reset link has been sent.",
  "data": null
}
```

### POST /auth/reset-password

Reset password using token.

**Request:**

```json
{
  "email": "user@example.com",
  "token": "64-character-hex-token",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Response: 200 OK**

```json
{
  "success": true,
  "message": "Password has been reset successfully. You can now login with your new password.",
  "data": null
}
```

**Response: 400 Bad Request**

```json
{
  "success": false,
  "message": "Invalid or expired reset token",
  "data": null
}
```

**Response: 422 Unprocessable Entity**

```json
{
  "success": false,
  "message": "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character",
  "data": null
}
```

---

**Happy Coding! 🎉**
