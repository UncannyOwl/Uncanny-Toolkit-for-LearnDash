# WP 2FA Integration Technical Documentation

This document explains how the Uncanny Toolkit's frontend login integrates with the WP 2FA plugin to provide seamless two-factor authentication across all form types.

## Overview

The integration intercepts login attempts, validates credentials, then redirects users to a unified 2FA challenge page that carries the pending login in a signed token (not a cookie, see "Challenge Token" below). It works with both AJAX and traditional forms.

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
Build signed challenge token
       ↓
Redirect to ?2fa_challenge=1&2fa_request={token}
       ↓
Validate token & render 2FA form
       ↓
Process 2FA code via REST API
       ↓
Complete login & redirect
```

### Challenge Token

The challenge page is a front-end page viewed by a logged-out visitor. Page caches and reverse proxies
(Varnish, host-level caches) routinely strip the `Cookie` header from exactly those requests, so a cookie
cannot carry the hand-off from the AJAX login to the challenge page. The token travels in the URL
(`2fa_request`) and in a hidden field of every challenge form instead:

```php
// <user id>.<WP 2FA login nonce>.<signature>
$payload   = $user_id . '.' . $nonce;
$signature = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
```

- The token is the WP 2FA login transaction. WP 2FA stores the nonce, expires it (15 minutes), revokes it
  on a password change, deletes it when the second factor passes, and locks the second factor after
  repeated failures.
- Holding the token does not log anyone in; a valid second factor is still required.
- The signature is verified before the nonce reaches WP 2FA. WP 2FA releases before 4.2.0 delete the stored
  nonce when a submitted one does not match, so an unsigned token would let anyone cancel another user's
  pending login.
- The challenge response sends `nocache_headers()` and `Referrer-Policy: no-referrer` so the URL is neither
  cached nor leaked to other sites.

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

**`Challenge_Token` (The Security Guard)**
- Builds the signed token that carries a pending login
- Resolves and verifies the token on every challenge request (GET) and form submission (POST)

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
├── class-challenge-token.php      # Signed token carrying the pending login
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
1. **HMAC Token Signing** - Prevents tampering and forged tokens
2. **WP 2FA Login Nonce** - Expiry, revocation and single use are WP 2FA's own
3. **Second Factor Required** - The token alone never logs anyone in
4. **No Cache, No Referer** - The challenge URL is never cached or leaked

### Error Handling
- Invalid or expired tokens show "Invalid or expired 2FA challenge" with a link back to login
- Failed 2FA codes show user-friendly errors
- Expired login nonces are rejected by WP 2FA
- "Go Back" button always available

### Form Types Supported
- **Traditional Forms** - Standard HTML form submission
- **AJAX Forms** - JavaScript-powered forms
- **All Layouts** - Works with any frontend login layout

## Debugging Tips

### Common Issues

**"Invalid or expired 2FA challenge"**
- Check the URL still carries `2fa_request` (a redirect or cache rule may be dropping the query string)
- Verify the login nonce is still stored in user meta (WP 2FA expires it after 15 minutes)
- Confirm the token's signature key (`wp_salt( 'auth' )`) did not change between the login and the challenge

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

// Resolve the challenge token of the current request
$challenge = ( new Challenge_Token() )->resolve();
error_log("Challenge: " . print_r($challenge, true));

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
- Verify `wp_salt( 'auth' )` is stable (token signing depends on it)
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
