## WP 2FA REST API Usage Guide

The wp-2fa plugin provides a REST API for validating 2FA tokens during the login process. Here's what I found:

### API Endpoint Structure

**Base URL:** `/wp-json/wp-2fa-methods/v1/login/`

**Endpoint Pattern:** `{user_id}/{token}/{provider}/{remember_device}`

### Available Parameters

1. **`user_id`** (required): The WordPress user ID (integer)
2. **`token`** (optional): The 2FA token/code from the authenticator app
3. **`provider`** (optional): The 2FA provider name (e.g., 'totp', 'email', etc.)
4. **`remember_device`** (optional): Boolean to remember the device

### Example API Calls

#### Basic validation:
```
GET /wp-json/wp-2fa-methods/v1/login/123/123456
```

#### With provider specified:
```
GET /wp-json/wp-2fa-methods/v1/login/123/123456/totp
```

#### With remember device:
```
GET /wp-json/wp-2fa-methods/v1/login/123/123456/totp/true
```

### Response Format

The API returns a JSON response with the following structure:

```json
{
  "status": true/false,
  "message": "Success or error message",
  "redirect_to": "URL to redirect after successful authentication"
}
```

### Example Responses

**Successful authentication:**
```json
{
  "status": true,
  "message": "Successfully signed in with WP 2FA.",
  "redirect_to": "https://yoursite.com/wp-admin/"
}
```

**Failed authentication:**
```json
{
  "status": false,
  "message": "Provided details are wrong.",
  "redirect_to": ""
}
```

**Error responses:**
```json
{
  "code": "invalid_request",
  "message": "User ID is required",
  "status": 400
}
```

### Security Considerations

1. **Authentication Required**: The API requires proper authentication through WordPress nonces or user sessions
2. **Rate Limiting**: The plugin includes login attempt tracking to prevent brute force attacks
3. **Session Management**: Successful authentication destroys the current session and creates a new one
4. **User Validation**: The API validates that the user exists and has 2FA enabled

### Implementation Notes

- The API is automatically initialized when the plugin loads via the `rest_api_init` hook
- The endpoint is registered with the namespace `wp-2fa-methods/v1`
- The API supports both GET and POST methods (though the code shows GET as the primary method)
- The plugin includes filters for customizing the validation process: `wp_2fa_validate_login_api`

### Usage in Custom Applications

To integrate this API into your custom application:

1. Ensure the user has 2FA enabled
2. Generate a valid nonce for the user
3. Make a request to the API endpoint with the user's 2FA token
4. Handle the response appropriately (redirect on success, show error on failure)

The API is designed to work seamlessly with WordPress's authentication system and provides a secure way to validate 2FA tokens during the login process.