# 2FA Integration QA Checklist

## Pre-Testing Setup
- [ ] WP 2FA plugin is active and configured
- [ ] Frontend Login Plus module is enabled
- [ ] Login page is selected in Frontend Login Plus settings
- [ ] Test user has 2FA enabled (TOTP, Email, or both)
- [ ] Test user has backup codes generated

## Core Functionality Tests

### Traditional Form Login
- [ ] Login with 2FA user redirects to `?2fa_challenge=1` page
- [ ] 2FA form displays with correct method instructions
- [ ] Valid TOTP code authenticates successfully
- [ ] Valid email OTP code authenticates successfully  
- [ ] Invalid codes show error message and stay on 2FA page
- [ ] User is logged in and redirected after successful 2FA

### AJAX Form Login
- [ ] Login with 2FA user shows "Redirecting to two-factor authentication..." message
- [ ] Page redirects to `?2fa_challenge=1` automatically
- [ ] 2FA form displays correctly (same as traditional)
- [ ] Valid codes authenticate successfully
- [ ] User is logged in and redirected after successful 2FA

### Backup Code Functionality
- [ ] "Use Backup Code" button appears when user has backup codes
- [ ] Clicking button toggles to backup code form
- [ ] "Use Primary Method" button appears and toggles back
- [ ] Valid backup code authenticates successfully
- [ ] Invalid backup code shows error message
- [ ] Focus moves to appropriate input field when toggling

### Security & Edge Cases
- [ ] Direct access to `?2fa_challenge=1` without a valid `2fa_request` token shows error
- [ ] A tampered `2fa_request` token (changed user ID or nonce) shows error
- [ ] Challenge expires after 15 minutes (shows "Invalid or expired" error)
- [ ] Challenge works on a host whose page cache strips the Cookie header from front-end requests
- [ ] "Go Back" button returns to login and the abandoned challenge URL stops working
- [ ] Multiple browser tabs don't interfere with each other
- [ ] Incognito/private browsing works correctly
- [ ] The challenge response sends `Referrer-Policy: no-referrer` and no-cache headers

### Error Handling
- [ ] Invalid/expired challenge shows "Invalid or expired 2FA challenge" with "Back to Login" link
- [ ] Network errors during 2FA validation show appropriate message
- [ ] Users without 2FA enabled login normally (no 2FA challenge)
- [ ] WP 2FA plugin disabled/broken fails gracefully

## Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (iOS Safari, Android Chrome)

## Form Layout Tests
Test with different Frontend Login Plus layouts:
- [ ] Default layout
- [ ] Custom layout with different CSS classes
- [ ] AJAX enabled layout
- [ ] Non-AJAX layout

## Performance & Code Quality
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug log
- [ ] Page load times reasonable
- [ ] PHPCS compliance (main files only, ignore legacy)

## Regression Testing
- [ ] Users without 2FA still login normally
- [ ] Other login functionality unaffected (password reset, registration, etc.)
- [ ] Admin login (wp-admin) unaffected
- [ ] Third-party login plugins still work

## Documentation
- [ ] README.md explains the integration clearly
- [ ] Code is well-commented
- [ ] Architecture makes sense to junior developers

---

## Known Acceptable Issues
- Legacy files in `legacy/` folder have PHPCS violations (don't fix)
- `$password` parameter warning in Authentication_Handler (required for WordPress hook)
- `$form_data` parameter warning in Form_Renderer (required for template inclusion)

## Quick Debug Commands
```bash
# Check PHPCS compliance (main files)
vendor/bin/phpcs src/includes/two-factor/providers/wp-2fa/class-*.php src/includes/two-factor/providers/wp-2fa/html/ --standard=WordPress

# Check PHP syntax
find src/includes/two-factor/providers/wp-2fa/ -name "*.php" -not -path "*/legacy/*" -exec php -l {} \;

# Enable WordPress debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Test User Setup
1. Create test user with 2FA enabled
2. Generate backup codes
3. Test with both TOTP app and email methods
4. Use different login page layouts
5. Test in multiple browsers
