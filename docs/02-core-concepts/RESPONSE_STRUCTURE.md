# Response Structure Guide

This document explains the standardized response structure used throughout the Padi REST API Framework.

## Overview

All API responses follow a consistent structure to ensure predictable frontend integration and easy error handling. The framework provides semantic methods to return different types of responses.

## Response Methods

### 🔹 `collection()` - Multiple Items

Use for returning arrays of items (with or without pagination):

```php
// Without pagination
$this->collection($items);

// With pagination
$this->collection($items, $meta);

// With custom message
$this->collection($items, [], 'Items retrieved successfully');
```

**Use Cases:**

- `index()` methods with pagination
- `all()` methods without pagination
- Search results
- List endpoints

### 🔹 `single()` - Single Item

Use for returning individual objects:

```php
// Basic usage
$this->single($item);

// With custom message
$this->single($item, 'Item created successfully');

// With status code
$this->single($item, 'Item created successfully', 201);
```

**Use Cases:**

- `show()` methods
- `store()` methods (after creation)
- `update()` methods (after update)

### 🔹 `success()` - Generic Success

Use for responses without specific data:

```php
// Basic usage
$this->success();

// With message only
$this->success(null, 'Operation completed successfully');

// Legacy usage (still supported)
$this->success($data, 'Custom message');
```

**Use Cases:**

- `destroy()` methods
- Operations without return data
- Legacy compatibility

## Response Structure

### Collection Response (With Pagination)

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": [
    {
      "id": 1,
      "title": "Sample Title",
      "content": "Sample content..."
    },
    {
      "id": 2,
      "title": "Another Title",
      "content": "More content..."
    }
  ],
  "meta": {
    "total": 25,
    "per_page": 10,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 10
  }
}
```

### Collection Response (Without Pagination)

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": [
    {
      "id": 1,
      "title": "Sample Title",
      "content": "Sample content..."
    },
    {
      "id": 2,
      "title": "Another Title",
      "content": "More content..."
    }
  ]
}
```

### Single Item Response

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": {
    "id": 1,
    "title": "Sample Title",
    "content": "Sample content...",
    "created_at": "2026-01-25 10:30:00"
  }
}
```

### Success Response (No Data)

```json
{
  "success": true,
  "message": "Item deleted successfully",
  "message_code": "SUCCESS"
}
```

## Migration from Legacy Structure

### Before (Legacy)

```php
// Old nested structure
$this->success([
    'data' => $items
]);

// Response:
{
    "data": {
        "success": true,
        "data": [...]
    },
    "debug": {...}
}
```

### After (Current)

```php
// New flat structure
$this->collection($items);

// Response:
{
    "success": true,
    "message_code": "SUCCESS",
    "item": [...],
    "debug": {...}
}
```

## Best Practices

### ✅ DO

```php
// Use semantic methods
$this->collection($posts);           // For lists
$this->single($post);               // For single items
$this->success(null, 'Deleted');   // For operations without data

// Handle pagination properly
$result = $this->model->paginate($page, $perPage);
$this->collection($result['data'], $result['meta'] ?? []);

// Use appropriate status codes
$this->single($post, 'Post created', 201);
```

### ❌ DON'T

```php
// Don't use generic success for everything
$this->success(['data' => $posts]);  // ❌ Use collection() instead
$this->success($post);               // ❌ Use single() instead

// Don't wrap data unnecessarily
$this->collection(['data' => $items]); // ❌ Pass items directly

// Don't ignore status codes
$this->single($post);  // ❌ Should be 201 for creation
$this->single($post, 'Created', 201); // ✅ Correct
```

## Controller Examples

### Complete CRUD Controller

```php
class PostController extends Controller
{
    // GET /posts (with pagination)
    public function index(): void
    {
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per_page', 10);

        $result = $this->model->paginate($page, $perPage);
        $this->collection($result['data'], $result['meta'] ?? []);
    }

    // GET /posts/all (without pagination)
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->collection($data);
    }

    // GET /posts/{id}
    public function show(): void
    {
        $post = $this->model->find($this->request->param('id'));

        if (!$post) {
            $this->notFound('Post not found');
        }

        $this->single($post);
    }

    // POST /posts
    public function store(): void
    {
        $validated = $this->validate([...]);
        $id = $this->model->create($validated);

        $post = $this->model->find($id);
        $this->single($post, 'Post created successfully', 201);
    }

    // PUT /posts/{id}
    public function update(): void
    {
        $id = $this->request->param('id');
        $post = $this->model->find($id);

        if (!$post) {
            $this->notFound('Post not found');
        }

        $validated = $this->validate([...]);
        $this->model->update($id, $validated);

        $updatedPost = $this->model->find($id);
        $this->single($updatedPost, 'Post updated successfully');
    }

    // DELETE /posts/{id}
    public function destroy(): void
    {
        $post = $this->model->find($this->request->param('id'));

        if (!$post) {
            $this->notFound('Post not found');
        }

        $this->model->delete($post['id']);
        $this->success(null, 'Post deleted successfully');
    }
}
```

## Frontend Integration

### JavaScript/TypeScript Pagination

```typescript
interface PaginationMeta {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from: number;
  to: number;
}

interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  message_code: string;
  item?: T;
  meta?: PaginationMeta;
}

// Fetch posts with pagination
async function fetchPosts(page: number = 1, perPage: number = 10) {
  const response: ApiResponse<Post[]> = await api.get(
    `/posts?page=${page}&per_page=${perPage}`,
  );

  if (response.success) {
    const posts = response.item || [];
    const pagination = response.meta;

    if (pagination) {
      console.log(
        `Showing ${pagination.from}-${pagination.to} of ${pagination.total} items`,
      );
      console.log(`Page ${pagination.current_page} of ${pagination.last_page}`);
    }

    return { posts, pagination };
  }

  throw new Error(response.message);
}

// Usage example
const { posts, pagination } = await fetchPosts(1, 20);
```

### Vue 3 Pagination Component

```vue
<template>
  <div>
    <!-- Posts list -->
    <div v-for="post in posts" :key="post.id" class="post-item">
      <h3>{{ post.title }}</h3>
      <p>{{ post.content }}</p>
    </div>

    <!-- Pagination controls -->
    <div v-if="pagination" class="pagination">
      <button
        @click="fetchPage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
      >
        Previous
      </button>

      <span class="pagination-info">
        Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{
          pagination.from
        }}-{{ pagination.to }} of {{ pagination.total }})
      </span>

      <button
        @click="fetchPage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const posts = ref([]);
const pagination = ref(null);
const loading = ref(false);

async function fetchPage(page = 1, perPage = 10) {
  loading.value = true;
  try {
    const response = await $api.get(`/posts?page=${page}&per_page=${perPage}`);
    if (response.success) {
      posts.value = response.item || [];
      pagination.value = response.meta || null;
    }
  } catch (error) {
    console.error("Failed to fetch posts:", error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchPage(1, 20); // Load first page with 20 items
});
</script>
```

### React Pagination Hook

```typescript
import { useState, useEffect } from 'react';

interface UsePaginationOptions {
  initialPage?: number;
  perPage?: number;
  endpoint: string;
}

function usePagination<T>({ initialPage = 1, perPage = 10, endpoint }: UsePaginationOptions) {
  const [data, setData] = useState<T[]>([]);
  const [pagination, setPagination] = useState<PaginationMeta | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchPage = async (page: number) => {
    setLoading(true);
    setError(null);

    try {
      const response: ApiResponse<T[]> = await api.get(`${endpoint}?page=${page}&per_page=${perPage}`);

      if (response.success) {
        setData(response.item || []);
        setPagination(response.meta || null);
      } else {
        setError(response.message);
      }
    } catch (err) {
      setError('Failed to fetch data');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPage(initialPage);
  }, []);

  return {
    data,
    pagination,
    loading,
    error,
    fetchPage,
    nextPage: () => pagination && fetchPage(pagination.current_page + 1),
    prevPage: () => pagination && fetchPage(pagination.current_page - 1),
    canGoNext: pagination ? pagination.current_page < pagination.last_page : false,
    canGoPrev: pagination ? pagination.current_page > 1 : false
  };
}

// Usage in component
function PostsList() {
  const {
    data: posts,
    pagination,
    loading,
    nextPage,
    prevPage,
    canGoNext,
    canGoPrev
  } = usePagination<Post>({ endpoint: '/posts', perPage: 20 });

  return (
    <div>
      {posts.map(post => <PostItem key={post.id} post={post} />)}

      {pagination && (
        <div className="pagination">
          <button onClick={prevPage} disabled={!canGoPrev}>Previous</button>
          <span>Page {pagination.current_page} of {pagination.last_page}</span>
          <button onClick={nextPage} disabled={!canGoNext}>Next</button>
        </div>
      )}
    </div>
  );
}
```

## Debug Information

In development mode (`APP_DEBUG=true`), responses include debug information:

```json
{
    "success": true,
    "message": "Success",
    "message_code": "SUCCESS",
    "item": [...],
    "debug": {
        "execution_time": "45.23ms",
        "memory_usage": "2.1MB",
        "query_count": 3,
        "queries": [...]
    }
}
```

The debug information is automatically added and does not affect the main response structure.

## See Also

- [Error Handling Guide](ERROR_HANDLING.md)
- [Pagination Guide](../02-core-concepts/PAGINATION.md)
- [Controller Guide](../02-core-concepts/CONTROLLERS.md)
