# WP 2FA Integration Technical Documentation

This document explains how the Uncanny Toolkit's frontend login integrates with the WP 2FA plugin to provide seamless two-factor authentication across all form types.

## Overview

The integration intercepts login attempts, validates credentials, then redirects users to a unified 2FA challenge page using secure cookies instead of URL parameters. It works with both AJAX and traditional forms.

## 1. How the Integration Works

### WordPress Hooks We Use

**Primary Hooks:**
- `wp_authenticate_user` (priority 10) - Intercepts traditional form logins
- `uo-login-action-response` (priority 99) - Intercepts AJAX form logins  
- `init` - Handles 2FA form submissions

**Hooks We Remove:**
- `wp_login` from `WP2FA\Authenticator\Login::wp_login` - Prevents WP 2FA's default behavior

### Why These Hooks

1. **`wp_authenticate_user`** - Catches login attempts before WordPress completes authentication, allowing us to redirect to 2FA
2. **`uo-login-action-response`** - Modifies AJAX responses to redirect to 2FA instead of completing login
3. **`init`** - Processes 2FA form submissions early in WordPress lifecycle

## 2. Inner Workings

### Authentication Flow

```
User Login Attempt
       ↓
Check if 2FA enabled
       ↓
Destroy user session
       ↓
Create login nonce (WP 2FA)
       ↓
Send email OTP (if email 2FA)
       ↓
Create secure cookie
       ↓
Redirect to ?2fa_challenge=1
       ↓
Validate cookie & render 2FA form
       ↓
Process 2FA code via REST API
       ↓
Complete login & redirect
```

### Cookie Security

Instead of exposing sensitive data in URLs, we use HMAC-signed cookies:

```php
// Cookie contains:
{
    "user_id": 123,
    "nonce": "abc123...",
    "timestamp": 1640995200,
    "user_agent_hash": "sha256_hash"
}

// Signed with WordPress salt
$signature = hash_hmac('sha256', $payload, wp_salt('auth'));
```

### REST API Integration

We use WP 2FA's built-in REST endpoints:
- Primary codes: `wp-2fa-methods/v1/login/{user_id}/{code}/{method}`
- Backup codes: `wp-2fa-methods/v1/login/{user_id}/{code}/backup_codes`

## 3. Architecture (Junior-Friendly)

Think of it like a restaurant with multiple entrances but one kitchen:

### The Components

**`Integration` (The Manager)**
- Coordinates all other components
- Sets up WordPress hooks
- Entry point for the system

**`Authentication_Handler` (The Bouncer)**
- Intercepts login attempts
- Decides if 2FA is needed
- Handles successful 2FA completion

**`Cookie_Manager` (The Security Guard)**
- Creates tamper-proof cookies
- Validates cookies are legitimate
- Clears cookies when done

**`Form_Renderer` (The Waiter)**
- Shows the 2FA form to users
- Handles different 2FA methods (TOTP, Email, Backup codes)

**`Form_Submission_Handler` (The Cashier)**
- Processes when users submit 2FA codes
- Validates the submission is legitimate

**`Redirect_Manager` (The Host)**
- Builds URLs for redirects
- Handles success/error redirections

**`Hook_Manager` (The Coordinator)**
- Registers all WordPress hooks
- Removes conflicting WP 2FA hooks

### File Structure

```
wp-2fa/
├── class-integration.php          # Main orchestrator
├── class-authentication-handler.php  # Login interception
├── class-cookie-manager.php       # Secure cookie handling
├── class-form-renderer.php        # 2FA form display
├── class-form-submission-handler.php # Form processing
├── class-redirect-manager.php     # URL management
├── class-hook-manager.php         # WordPress hooks
├── class-helper.php              # WP 2FA utilities
└── html/
    ├── 2fa-form.php              # Centralized template
    └── class-html-helper.php     # Template utilities
```

## Important Technical Details

### PHP 7.0 Compatibility
- No null coalescing operator (`??`)
- Use `15 * 60` instead of `MINUTE_IN_SECONDS` constant
- All code tested on PHP 7.0

### Security Measures
1. **HMAC Cookie Signing** - Prevents tampering
2. **Browser Fingerprinting** - Prevents cookie theft
3. **Time-based Expiry** - 15-minute cookie lifetime
4. **Nonce Validation** - WordPress security tokens
5. **No URL Parameters** - Sensitive data never in URLs

### Error Handling
- Invalid cookies redirect to login with error message
- Failed 2FA codes show user-friendly errors
- Expired sessions clear automatically
- "Go Back" button always available

### Form Types Supported
- **Traditional Forms** - Standard HTML form submission
- **AJAX Forms** - JavaScript-powered forms
- **All Layouts** - Works with any frontend login layout

## Debugging Tips

### Common Issues

**"Invalid or expired 2FA challenge"**
- Check cookie creation in browser dev tools
- Verify nonce matches in user meta
- Ensure cookie hasn't expired (15 min limit)

**White screen after code submission**
- Check PHP error logs
- Verify WP 2FA REST API is accessible
- Confirm user has 2FA method enabled

**AJAX forms not redirecting**
- Check browser console for JavaScript errors
- Verify `UltimateLogin2FARedirect` is loaded
- Ensure response has `requires_redirect: true`

### Useful Debug Code

```php
// Check if user has 2FA enabled
$method = Helper::get_user_2fa_method($user_id);
error_log("User $user_id 2FA method: " . $method);

// Validate cookie contents
$cookie_data = $cookie_manager->validate_secure_2fa_cookie();
error_log("Cookie data: " . print_r($cookie_data, true));

// Test REST API directly
$result = Helper::validate_2fa_token($user_id, $code);
error_log("2FA API result: " . print_r($result, true));
```

## Configuration

### Required Settings
- Frontend Login Plus module must be enabled
- Login page must be selected in settings
- WP 2FA plugin must be active and configured

### Template Integration
The system checks for `?2fa_challenge=1` parameter and renders the 2FA form instead of the login form. Template integration uses:

```php
// In your login template
if (uo_toolkit_2fa_form_exists()) {
    echo uo_toolkit_2fa_render_authentication_form();
} else {
    // Show regular login form
}
```

## Legacy Code

The `legacy/` folder contains old implementations for different WP 2FA versions. These are kept for reference but should not be modified. The current system is version-agnostic and uses the WP 2FA REST API.

## Maintenance Notes

### When WP 2FA Updates
- Test the REST API endpoints still work
- Verify nonce creation/validation methods
- Check user helper methods for changes

### When WordPress Updates
- Test hook priorities haven't changed
- Verify cookie security functions work
- Check session management compatibility

### Code Quality
- All files follow WordPress Coding Standards (WPCS)
- Use `vendor/bin/phpcs` to check compliance
- Use `vendor/bin/phpcbf` to auto-fix issues

## Support

If 2FA breaks after updates:
1. Check WordPress and WP 2FA plugin versions
2. Test with a simple TOTP setup first
3. Enable WordPress debug logging
4. Check if REST API endpoints are accessible
5. Verify frontend login page is still configured correctly

The integration is designed to fail gracefully - if 2FA can't be displayed, users can still access the regular login form.
