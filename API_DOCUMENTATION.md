# Authentication and User REST API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Role-Based Access Control

The API implements two user roles:
- **user** - Regular user with access to their own profile and data
- **admin** - Administrator with full access to user management

## Authentication Endpoints

### 1. Register User
**Endpoint:** `POST /auth/register`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecureP@ss123",
  "full_name": "John Doe",
  "date_of_birth": "1990-01-15",
  "phone_number": "+1234567890"
}
```

**Response (201):**
```json
{
  "message": "Registration successful",
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "role": "user",
      "is_active": true,
      "last_login_at": null,
      "created_at": "2025-12-11T10:00:00.000000Z",
      "updated_at": "2025-12-11T10:00:00.000000Z",
      "profile": {
        "id": "uuid",
        "user_id": "uuid",
        "full_name": "John Doe",
        "profile_picture_url": null,
        "date_of_birth": "1990-01-15",
        "phone_number": "+1234567890",
        "created_at": "2025-12-11T10:00:00.000000Z",
        "updated_at": "2025-12-11T10:00:00.000000Z"
      }
    },
    "access_token": "token_string",
    "refresh_token": "refresh_token_string",
    "token_type": "Bearer"
  }
}
```

### 2. Login
**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecureP@ss123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "data": {
    "user": { ... },
    "access_token": "token_string",
    "refresh_token": "refresh_token_string",
    "token_type": "Bearer"
  }
}
```

### 3. Refresh Token
**Endpoint:** `POST /auth/refresh`

**Request Body:**
```json
{
  "refresh_token": "refresh_token_string"
}
```

**Response (200):**
```json
{
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "new_token_string",
    "token_type": "Bearer"
  }
}
```

### 4. Logout
**Endpoint:** `POST /auth/logout`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "message": "Logout successful"
}
```

### 5. Get Current User
**Endpoint:** `GET /auth/me`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "is_active": true,
    "last_login_at": "2025-12-11T10:00:00.000000Z",
    "created_at": "2025-12-11T10:00:00.000000Z",
    "updated_at": "2025-12-11T10:00:00.000000Z",
    "profile": { ... }
  }
}
```

## User Profile Endpoints

### 6. Get User Information
**Endpoint:** `GET /user`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "is_active": true,
    "profile": { ... }
  }
}
```

### 7. Get User Profile
**Endpoint:** `GET /user/profile`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "data": {
    "id": "uuid",
    "user_id": "uuid",
    "full_name": "John Doe",
    "profile_picture_url": "https://example.com/avatar.jpg",
    "date_of_birth": "1990-01-15",
    "phone_number": "+1234567890",
    "created_at": "2025-12-11T10:00:00.000000Z",
    "updated_at": "2025-12-11T10:00:00.000000Z"
  }
}
```

### 8. Update User Profile
**Endpoint:** `PUT /user/profile` or `PATCH /user/profile`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Request Body:**
```json
{
  "full_name": "Jane Doe",
  "profile_picture_url": "https://example.com/new-avatar.jpg",
  "date_of_birth": "1990-01-15",
  "phone_number": "+9876543210"
}
```

**Response (200):**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": "uuid",
    "user_id": "uuid",
    "full_name": "Jane Doe",
    "profile_picture_url": "https://example.com/new-avatar.jpg",
    "date_of_birth": "1990-01-15",
    "phone_number": "+9876543210",
    "created_at": "2025-12-11T10:00:00.000000Z",
    "updated_at": "2025-12-11T10:00:00.000000Z"
  }
}
```

### 9. Deactivate Account
**Endpoint:** `POST /user/deactivate`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "message": "Account deactivated successfully"
}
```

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation error message",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Account is inactive"
}
```

### 404 Not Found
```json
{
  "message": "Profile not found"
}
```

### 500 Internal Server Error
```json
{
  "message": "Operation failed",
  "error": "Error details"
}
```

## Security Features

1. **Password Requirements:**
   - Minimum 8 characters
   - Mixed case (uppercase and lowercase)
   - Contains numbers
   - Contains symbols

2. **Token Management:**
   - Access tokens use Laravel Sanctum
   - Refresh tokens stored in database
   - Refresh tokens expire after 30 days
   - Tokens can be revoked on logout

3. **Account Security:**
   - Inactive accounts cannot login
   - Active account status checked on each request
   - All tokens revoked on account deactivation

## Database Models

### User Model
- UUID primary key
- Email (unique)
- Password hash
- **Role (user/admin)** - Controls access level
- Active status
- Last login timestamp

### UserProfile Model
- UUID primary key
- Belongs to User (1-to-1)
- Full name
- Profile picture URL
- Date of birth
- Phone number

### RefreshToken Model
- UUID primary key
- Belongs to User
- Token string (unique)
- Expiration date
- Revocation status

---

## Admin Endpoints (Requires Admin Role)

### 10. Get All Users (Paginated)
**Endpoint:** `GET /admin/users`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Query Parameters:**
- `per_page` (optional) - Number of users per page (default: 15)

**Response (200):**
```json
{
  "data": [
    {
      "id": "uuid",
      "email": "user@example.com",
      "role": "user",
      "is_active": true,
      "last_login_at": "2025-12-11T10:00:00.000000Z",
      "created_at": "2025-12-11T10:00:00.000000Z",
      "updated_at": "2025-12-11T10:00:00.000000Z",
      "profile": { ... }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### 11. Get User Statistics
**Endpoint:** `GET /admin/users/statistics`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "data": {
    "total_users": 100,
    "active_users": 85,
    "inactive_users": 15,
    "admin_users": 5,
    "regular_users": 95,
    "recent_registrations": 12
  }
}
```

### 12. Get Specific User
**Endpoint:** `GET /admin/users/{id}`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "data": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "is_active": true,
    "profile": { ... }
  }
}
```

### 13. Activate User Account
**Endpoint:** `POST /admin/users/{id}/activate`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "message": "User activated successfully",
  "data": { ... }
}
```

### 14. Deactivate User Account
**Endpoint:** `POST /admin/users/{id}/deactivate`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "message": "User deactivated successfully",
  "data": { ... }
}
```

**Note:** Admin cannot deactivate their own account.

### 15. Promote User to Admin
**Endpoint:** `POST /admin/users/{id}/promote`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "message": "User promoted to admin successfully",
  "data": { ... }
}
```

### 16. Demote Admin to User
**Endpoint:** `POST /admin/users/{id}/demote`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "message": "Admin demoted to user successfully",
  "data": { ... }
}
```

**Note:** Admin cannot demote themselves.

### 17. Delete User Account
**Endpoint:** `DELETE /admin/users/{id}`

**Headers:**
```
Authorization: Bearer {admin_access_token}
```

**Response (200):**
```json
{
  "message": "User deleted successfully"
}
```

**Note:** 
- Admin cannot delete their own account
- This performs a soft delete by deactivating the account
- All user tokens are revoked
