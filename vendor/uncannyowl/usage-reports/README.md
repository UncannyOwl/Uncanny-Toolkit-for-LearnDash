# 📊 Usage Reports

---

## ⚡️ Pre-requisite to Working with Usage Reports

### ✅ MongoDB Driver Setup

◆ **Install the MongoDB PHP Driver:**
  ```bash
  sudo pecl install mongodb
  ```
◆ **Add the extension to your `php.ini`:**
  ```ini
  extension=mongodb.so
  ```
> 💡 **Pro Tip:** For more details, see the [MongoDB PHP Driver documentation](https://www.mongodb.com/docs/drivers/php-drivers/#connect-to-a-compatible-mongodb-deployment).

---

## 🏠 Local Development Environment Tips

> 💡 **Note:** Installing the MongoDB PHP driver can be different depending on your local stack. Below are some tips for common environments.

### XAMPP (Windows/macOS)

◆ **Install the MongoDB driver using PECL for PHP 8.2**
  - Open a terminal/command prompt.
  - Run:
    ```bash
    pecl install mongodb
    ```
    > ⚠️ **Warning:** By default, PECL may install the latest extension (e.g., for PHP 8.4). If you are using PHP 8.2, specify the version:
    ```bash
    pecl install mongodb-1.16.2
    ```
    *(Replace `1.16.2` with the latest compatible version for PHP 8.2)*

◆ **Enable the extension in your `php.ini`**
  - Add:
    ```
    extension=mongodb.so
    ```
    (On Windows, use `extension=php_mongodb.dll` and place the DLL in your `ext` directory.)

◆ **Restart Apache**
  - Use the XAMPP control panel to restart Apache after making changes.

> ⚠️ **Warning:** Make sure the driver version matches your PHP version (e.g., PHP 8.2 needs a compatible DLL or .so file).

---

### LocalWP (formerly Local by Flywheel)

◆ **LocalWP does not support PECL out of the box.**
  - You may need to manually download the correct `mongodb.so` for your PHP version (e.g., 8.2) and place it in the site’s `php/ext` directory.
  - Edit the site’s `php.ini.hbs` to add:
    ```
    extension=mongodb.so
    ```
  - Restart the site in LocalWP.

> 💡 **Pro Tip:** Check LocalWP’s community forums for precompiled extensions or troubleshooting tips.

---

### MAMP Pro (macOS/Windows)

◆ **Use the built-in PECL or download the extension manually for PHP 8.2:**
  - Open Terminal and run:
    ```bash
    /Applications/MAMP/bin/php/php8.2.0/bin/pecl install mongodb
    ```
    *(Replace `php8.2.0` with your exact PHP 8.2 version directory)*

◆ **Edit the correct `php.ini`**
  - MAMP has multiple PHP versions; make sure you’re editing the one you’re using.
  - Add:
    ```
    extension=mongodb.so
    ```

◆ **Restart MAMP servers** after making changes.

> ⚠️ **Warning:** If you switch PHP versions in MAMP, you’ll need to install the driver for each version.

---

### General Troubleshooting

◆ Run `php -m | grep mongodb` to check if the extension is loaded.
◆ Check your PHP error logs for startup errors related to the extension.
◆ On Windows, ensure you use the correct thread safety (TS/NTS) and architecture (x86/x64) for your PHP build.

---

### 🔗 Add Endpoint on Automator-API

◆ **Copy/Paste an existing block** in [`routes.php`](https://github.com/UncannyOwl/automator-api/blob/pre-release/app/routes.php#L112-L118)
◆ **Update for the new plugin** (e.g., `tincanny`)

> 📝 **Note:** Consistent naming helps with maintainability.

---

### 📁 Add a New Folder in Automator-API

◆ **Create a new folder** in [`src/Application/Actions`](https://github.com/UncannyOwl/automator-api/tree/pre-release/src/Application/Actions)
◆ **Name it after your plugin** (e.g., `tincanny`)
◆ **Copy contents from `codes`**

---

### 🧪 Add a New Postman Collection

◆ **Copy an existing collection** (e.g., `codes`) for the new plugin (e.g., `tincanny`)
◆ **Update endpoints** from `codes` to `tincanny`

---

### 🛠️ Configure Local Environment for Automator-API

◆ **Add the following `.env` file in your `automator-api` project:**
  ```env
  ENV=local
  URL=http://localhost:9000/
  MONGO=mongodb+srv://api-staging:~~~~~~~~~~@uncannydb-staging.vlpv6.mongodb.net/api # Use correct password
  HOST=local
  AP_URL=https://staging2.automatorplugin.com/
  ENCRYPTION_KEY=~~~~~~~~~~~~~~ # Use correct encryption key
  ```

> ⚠️ **Warning:** Never commit sensitive credentials to version control!

◆ **Connect to MongoDB of the staging API:**
  - Go to Config
  - Add a new config by copying an existing one (e.g., `codes`)
  - Update the signature with any new random key

◆ **Start the local server:**
  ```bash
  composer start
  ```

◆ **Test the endpoint:**
  - Send a request from Postman to the new endpoint
  - It should return information about the endpoint you added above in automator-api

---

## 📦 Adding and Using the Usage Report Module

### 1️⃣ Add Usage Reports as a Dependency (Recommended: Composer)

#### **Using Composer with UncannyOwl Composer Repository**

1. **Add the UncannyOwl Composer repository to your plugin's `composer.json`:**
   ```json
   "repositories": [
     {
       "type": "composer",
       "url": "https://composer.uncannyowl.com/"
     }
   ]
   ```

2. **Require the Usage Reports package:**
   ```sh
   composer require uncannyowl/usage-reports:dev-master
   ```
   - If you use only dev branches, add to your `composer.json`:
     ```json
     "minimum-stability": "dev",
     "prefer-stable": true
     ```

3. **Autoloading:**
   - Make sure your plugin loads Composer's autoloader:
     ```php
     require_once __DIR__ . '/vendor/autoload.php';
     ```
   - Now you can use the Usage Reports classes directly:
     ```php
     use UncannyOwl\UsageReports\Reporting_Schedule;
     use UncannyOwl\UsageReports\Report;
     ```

---

### 2️⃣ Include the Usage Module

◆ **Add `loader.php` in `src/usage-reports/loader.php`:**
  ```php
  <?php
  
  namespace uncanny_learndash_codes; // Update namespace
  
  use UncannyOwl\UsageReports\Reporting_Schedule;
  use UncannyOwl\UsageReports\Report;
  
  require_once __DIR__ . '/codes-report.php'; // Create plugin-report.php
  
  // Initialize the reporting scheduler after all plugins are fully loaded
  add_action(
  	'plugins_loaded',
  	function () {
  
  		$reporting_enabled = true;
  
  		// Optionally disable reporting by setting UNCANNY_LEARNDASH_CODES_REPORTING to false
  		if ( defined( 'UNCANNY_LEARNDASH_CODES_REPORTING' ) ) { // Define constant
  			$reporting_enabled = UNCANNY_LEARNDASH_CODES_REPORTING; // Usage constant
  		}
  
  		new Reporting_Schedule(
  			'Uncanny Codes', // Use the plugin name
  			$reporting_enabled,
  			new CodesReport()  // Replace with the plugin-report.php class name 
  		);
  	}
  );
  ```
> 💡 **Pro Tip:** Update the namespace and class names as needed for your plugin.

---

### 3️⃣ Add Signature to Plugin Root File

◆ **Add the following to your plugin's root file:**
  ```php
  if ( ! defined( 'UNCANNY_API_URL' ) ) {
  	/**
  	 *
  	 */
  	define( 'UNCANNY_API_URL', 'https://api.uncannyowl.com/codes/' );
  }
  
  if ( ! defined( 'UNCANNY_API_KEY' ) ) {
  	/**
  	 *
  	 */
  	define( 'UNCANNY_API_KEY', 'kc)5zbblqxz' ); // Update the signature that was added in MongoDB
  }
  ```
> 📝 **Note:** Update the API key to match the signature you added in MongoDB.

---