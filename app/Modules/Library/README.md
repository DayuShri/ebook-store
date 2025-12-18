# Library Module - Documentation

## Overview
The Library module manages e-book file storage, user library access, and secure file streaming using Supabase Storage.

## Architecture

### Directory Structure
```
app/Modules/Library/
├── Controllers/
│   ├── Http/
│   │   ├── LibraryController.php      # User library management
│   │   ├── BookFileController.php     # File upload/management
│   │   └── ViewerController.php       # Viewer session & streaming
│   └── Internal/
│       └── LibraryInternalController.php # HMVC endpoints
├── Services/
│   ├── LibraryService.php             # Library business logic
│   ├── ViewerService.php              # Viewer session management
│   └── SupabaseService.php            # Supabase storage operations
├── Models/
│   ├── LibraryItem.php                # User's library items
│   ├── BookFile.php                   # Book file metadata
│   └── ViewerSession.php              # Temporary access sessions
├── Routes/
│   ├── api.php                        # Public API routes
│   └── hmvc.php                       # Internal HMVC routes
├── Requests/
│   ├── GrantLibraryRequest.php
│   └── CreateViewerRequest.php
├── Resources/
│   └── LibraryItemResource.php
└── Traits/
    └── ApiResponse.php
```

## Core Components

### 1. LibraryController
Manages user's personal library.

**Endpoints:**
- `GET /api/v1/library` - List user's books
- `POST /api/v1/library` - Add book to library (requires authentication)
- `DELETE /api/v1/library/{id}` - Revoke book access

### 2. BookFileController
Handles file uploads and metadata management (Admin only).

**Endpoints:**
- `GET /api/v1/library/files/{bookId}/{format}` - Get file metadata
- `POST /api/v1/library/files` - Upload book file (PDF/EPUB)

**Upload Features:**
- Supports PDF and EPUB formats
- Maximum file size: 50MB
- Auto-replaces existing files of same format
- Path structure: `book/{book_id}/{random_hash}.{ext}`
- Uses Supabase service_role key for upload permissions

### 3. ViewerController
Creates temporary access sessions and streams files.

**Endpoints:**
- `POST /api/v1/library/viewer` - Create viewer session
- `GET /api/v1/library/stream/{token}` - Stream file (token-based, no auth required)

**Flow:**
1. User requests viewer session with `book_id` and optional `format`
2. System creates session with unique token (expires in 2 hours)
3. User accesses stream URL with token
4. System validates token and redirects to Supabase signed URL

### 4. LibraryInternalController (HMVC)
Internal endpoint for inter-module communication.

**Endpoints:**
- `POST /api/v1/hmvc/library/grant` - Grant book access from Payment module

## Services

### LibraryService
- `listUserLibrary($userId)` - Get user's library items
- `grant($userId, $bookId, $orderId)` - Grant book access to user
- `revoke($id)` - Revoke book access

### ViewerService
- `createSession($userId, $bookId, $format, $deviceInfo, $ip)` - Create viewer session
- `validateTokenAndGetFile($token)` - Validate token and get file path

### SupabaseService
Handles all Supabase Storage operations.

**Methods:**
- `uploadFile($file, $folder, $filename)` - Upload file (uses service_role key)
- `uploadFromUrl($fileUrl, $objectPath, $bucket)` - Upload from remote URL
- `fileExists($storagePath)` - Check if file exists
- `signedUrl($storagePath, $expiresIn)` - Generate temporary signed URL

**Configuration:**
```env
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_BUCKET=bukuku
```

## Models

### LibraryItem
User's library ownership records.

**Fields:**
- `id` (UUID)
- `user_id` (UUID)
- `book_id` (UUID)
- `order_id` (UUID, nullable)
- `status` (ACTIVE/REVOKED)
- `granted_at` (datetime)
- `revoked_at` (datetime, nullable)

### BookFile
Physical file storage records.

**Fields:**
- `id` (UUID)
- `book_id` (UUID)
- `file_path` (string) - e.g., "book/{uuid}/{hash}.pdf"
- `file_format` (pdf/epub)
- `file_size_mb` (decimal)
- `encryption_key` (string)
- `checksum` (string)

### ViewerSession
Temporary access sessions for streaming.

**Fields:**
- `id` (UUID)
- `user_id` (UUID)
- `book_id` (UUID)
- `file_id` (UUID) - Tracks specific file version
- `file_format` (string) - User's requested format
- `token` (UUID)
- `expires_at` (datetime)
- `created_at` (datetime)
- `last_activity_at` (datetime)
- `device_info` (string)
- `ip_address` (string)

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

## Security Features

1. **Token-based Streaming**: Temporary UUID tokens for file access
2. **Session Expiration**: 2-hour expiry on viewer sessions
3. **Signed URLs**: Supabase signed URLs with 1-hour expiry
4. **Admin-only Upload**: File uploads restricted to admin role
5. **File Format Tracking**: Ensures correct format is served
6. **IP & Device Tracking**: Logs access attempts for audit

## Integration with Other Modules

### Payment Module → Library Module
After successful payment, Payment module calls:
```
POST /api/v1/hmvc/library/grant
{
  "user_id": "uuid",
  "book_id": "uuid",
  "order_id": "uuid"
}
```

## Supabase Storage Structure

**Bucket:** `bukuku` (public read access)

**Path Structure:**
```
bukuku/
└── book/
    └── {book_id}/
        ├── {random_hash}.pdf
        └── {random_hash}.epub
```

## Common Issues & Solutions

### Issue: "requested path is invalid"
**Solution:** Ensure path format is `book/{book_id}/{filename}` without double prefixes

### Issue: RLS policy error on upload
**Solution:** Use `SUPABASE_SERVICE_ROLE_KEY` for uploads (bypasses RLS)

### Issue: Wrong file format being served
**Solution:** Ensure `file_id` is stored in viewer_sessions table

### Issue: SSL certificate errors
**Solution:** `withoutVerifying()` is used in Http client calls

## Testing

### Upload File (Admin)
```bash
POST /api/v1/library/files
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

book_id: {uuid}
file: {pdf_or_epub_file}
```

### Create Viewer Session (User)
```bash
POST /api/v1/library/viewer
Authorization: Bearer {user_token}
Content-Type: application/json

{
  "book_id": "{uuid}",
  "format": "pdf"
}
```

### Stream File
```bash
GET /api/v1/library/stream/{token}
# No authentication required - redirects to Supabase signed URL
```

## Migration History

1. Initial tables: library_items, book_files, viewer_sessions
2. `2025_12_18_normalize_book_file_paths.php` - Fixed path structure
3. `2025_12_18_125553_add_file_info_to_viewer_sessions_table.php` - Added file_id and file_format tracking

## Code Quality Standards

✅ **Clean Code:**
- Removed all Indonesian comments
- Consistent indentation and formatting
- Proper type hints on all methods
- Clear variable naming

✅ **Error Handling:**
- Try-catch blocks on critical operations
- Detailed error messages with context
- Proper HTTP status codes

✅ **Logging:**
- Strategic logging at key points
- Includes context (file paths, tokens, etc.)
- Error logs include full exception details

✅ **Validation:**
- Request validation via Validator
- UUID format validation
- File size and type validation

## Future Enhancements

- [ ] Add file encryption at rest
- [ ] Implement download speed limiting
- [ ] Add analytics for reading patterns
- [ ] Support for more file formats (MOBI, AZW)
- [ ] Batch file upload for admins
- [ ] CDN integration for better performance

## Contact

For questions or issues, contact the development team.
