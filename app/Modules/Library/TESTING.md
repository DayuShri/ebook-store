# Library Module - Admin API Testing Guide

## Prerequisites

1. Start Laravel server: `php artisan serve`
2. Import Postman collection: `postman/Library-Admin.postman_collection.json`
3. Have admin and user tokens ready

## Environment Variables

Set these in Postman environment:

```
base_url: http://localhost:8000
admin_token: <your_admin_bearer_token>
user_token: <your_user_bearer_token>
book_id: <uuid_of_book>
user_id: <uuid_of_user>
library_item_id: <uuid_of_library_item>
```

## Testing Flow

### 1. Admin Uploads Book Files

**Upload PDF:**
```
POST /api/v1/library/files
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

book_id: {book_uuid}
file: {your_file.pdf}
```

**Upload EPUB:**
```
POST /api/v1/library/files
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

book_id: {book_uuid}
file: {your_file.epub}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "id": "file-uuid",
        "file_path": "book/{book_id}/{hash}.pdf",
        "file_format": "pdf",
        "file_size_mb": "2.45"
    }
}
```

### 2. Admin Grants Access to User

**Option A - Grant via API:**
```
POST /api/v1/library
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "user_id": "{user_uuid}",
    "book_id": "{book_uuid}",
    "order_id": "{order_uuid}" // optional
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Book added to library successfully",
    "data": {
        "id": "library-item-uuid",
        "user_id": "user-uuid",
        "book_id": "book-uuid",
        "order_id": "order-uuid",
        "status": "ACTIVE",
        "granted_at": "2025-12-18T10:30:00.000000Z",
        "revoked_at": null
    }
}
```

### 3. User Views Their Library

```
GET /api/v1/library
Authorization: Bearer {user_token}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Library retrieved",
    "data": [
        {
            "id": "library-item-uuid",
            "user_id": "user-uuid",
            "book_id": "book-uuid",
            "status": "ACTIVE",
            "granted_at": "2025-12-18T10:30:00.000000Z",
            "revoked_at": null
        }
    ]
}
```

### 4. User Creates Viewer Session

```
POST /api/v1/library/viewer
Authorization: Bearer {user_token}
Content-Type: application/json

{
    "book_id": "{book_uuid}",
    "format": "pdf"
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Viewer session created",
    "data": {
        "token": "session-token-uuid",
        "expires_at": "2025-12-18T12:30:00.000000Z",
        "stream_url": "http://localhost:8000/api/v1/library/stream/{token}"
    }
}
```

### 5. User Accesses Stream URL

```
GET /api/v1/library/stream/{token}
No Authorization Required
```

**Expected Behavior:**
- Redirects to Supabase signed URL
- File downloads/streams in browser
- URL expires after 1 hour

### 6. Admin Revokes Access

**Option A - Revoke by Library Item ID:**
```
DELETE /api/v1/library/{library_item_id}
Authorization: Bearer {admin_token}
```

**Option B - Revoke by User & Book:**
```
POST /api/v1/library/revoke
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "user_id": "{user_uuid}",
    "book_id": "{book_uuid}"
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Book access revoked successfully",
    "data": {
        "id": "library-item-uuid",
        "user_id": "user-uuid",
        "book_id": "book-uuid",
        "status": "REVOKED",
        "granted_at": "2025-12-18T10:30:00.000000Z",
        "revoked_at": "2025-12-18T14:30:00.000000Z"
    }
}
```

## Admin-Only Endpoints

All these endpoints require `role: admin` in user JWT:

1. `POST /api/v1/library/files` - Upload book files
2. `DELETE /api/v1/library/{id}` - Revoke by ID
3. `POST /api/v1/library/revoke` - Revoke by user & book

**Non-Admin Error Response:**
```json
{
    "success": false,
    "message": "Forbidden: only admin can revoke access"
}
```

## Testing Scenarios

### Scenario 1: Complete Flow
1. Admin uploads PDF for book
2. Admin grants access to user
3. User views library (sees book)
4. User creates viewer session
5. User accesses stream URL (downloads file)
6. Admin revokes access
7. User tries to create new session (should fail)

### Scenario 2: Multiple Formats
1. Admin uploads PDF for book
2. Admin uploads EPUB for same book
3. User creates session with format="pdf"
4. Verify correct format is served
5. User creates session with format="epub"
6. Verify correct format is served

### Scenario 3: Re-upload
1. Admin uploads PDF v1
2. Admin uploads PDF v2 (same book_id)
3. Verify file is replaced, not duplicated
4. Check file path is updated

### Scenario 4: Revoke Methods
1. Grant access to user A for book X
2. Method A: Revoke by library_item_id
3. Grant again
4. Method B: Revoke by user_id + book_id
5. Both should work identically

## Error Cases to Test

### Upload Errors:
- Non-admin user tries to upload
- File size > 50MB
- Invalid file format (not PDF/EPUB)
- Missing book_id

### Grant Errors:
- Invalid book_id (UUID format)
- Invalid user_id (UUID format)

### Viewer Errors:
- User requests book they don't own
- Expired token access
- Invalid token format

### Revoke Errors:
- Non-admin tries to revoke
- Invalid library_item_id
- User/book combination doesn't exist

## Validation Rules

### Upload File:
- `book_id`: required, uuid
- `file`: required, file, mimes:pdf,epub, max:51200 (50MB)

### Revoke by User & Book:
- `user_id`: required, uuid
- `book_id`: required, uuid

### Create Viewer Session:
- `book_id`: required, uuid
- `format`: nullable, string (pdf/epub)

## Database Check

After testing, verify in database:

```sql
-- Check library items
SELECT * FROM library_items WHERE user_id = 'your-user-uuid';

-- Check book files
SELECT * FROM book_files WHERE book_id = 'your-book-uuid';

-- Check viewer sessions
SELECT * FROM viewer_sessions WHERE user_id = 'your-user-uuid';

-- Check revoked items
SELECT * FROM library_items WHERE status = 'REVOKED';
```

## Postman Collection Features

The collection includes:
- ✅ All admin endpoints
- ✅ User library viewing
- ✅ Viewer session creation
- ✅ File streaming
- ✅ Both revoke methods
- ✅ Pre-configured environment variables
- ✅ Request descriptions

## Quick Test Commands

```bash
# Start server
php artisan serve

# Check routes
php artisan route:list --path=library

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check logs
tail -f storage/logs/laravel.log
```

## Tips

1. **Use Postman Variables**: Set tokens and IDs in environment for easy reuse
2. **Check Response Status**: 200/201 = success, 403 = forbidden, 404 = not found
3. **Monitor Logs**: Watch `storage/logs/laravel.log` for detailed error info
4. **Test Both Formats**: Always test PDF and EPUB separately
5. **Verify Supabase**: Check Supabase dashboard for uploaded files

## Support

For issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify .env configuration
3. Ensure Supabase bucket exists and is accessible
4. Confirm user role is set correctly in JWT token
