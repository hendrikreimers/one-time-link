
# URL Shortener with One-Time Use Feature

This project provides a simple URL shortening service with a one-time use feature. The URL will be deleted after its first access. 

## Project Structure

- `create.php`: Handles the creation of short URLs.
- `redirect.php`: Redirects the user based on the short URL and deletes the mapping after use.
- `_custom.inc.php`: Optional file for custom transformations of the short and target URLs.
- `.htaccess`: Configures URL rewriting and enhances security with various HTTP headers.
- `Classes`: Folder for Classes to handle Security, Creating and anything else.
- `Ressources`: Folder for static files like Templates, Styles and JavaScript.
- `data`: Folder for storing the created ShortURL and Targets.

## Security Features

- **HTTPS Enforcement**: The application forces HTTPS on all requests.
- **Security Headers**: Various security headers are set to prevent XSS, clickjacking, and other attacks.
- **Crawler Prevention**: Prevents crawlers and bots from accessing the one-time URLs, preserving their one-time nature.
- **Custom Nonce Handling**: Uses CSP with nonce to protect against script injection attacks.
- **Obfuscation**: The target URL will be obfuscated, to prevent it from being saved in the browsers history.
- **Notifications**: Optional E-Mail notifications if the ShortURL is hit.
- **Dot Env File**: Support for .env file for configuration, like notifications.
- **Automatic Cleanup**: If set in .env file, it automatically cleans unused shortURLs after X number of days.
- **Encryption (Optional)**: Optional encryption of the shortURL Data. See `cli-initialize-encryption.php` for more.

## Usage

1. **Create a Short URL**: 
   - Access `create.php` via a POST request with a valid URL.
   - The script will generate a short URL and provide it to you.

2. **Redirect to the Target URL**:
   - Access the generated short URL.
   - You will be redirected to the original URL, and the short URL will be deleted.
   - The target URL will be obfuscated, to prevent it from being saved in the browsers history.

3. **Custom Transformations**:
   - If needed, you can define custom transformations for the short and target URLs in `_custom.inc.php`.

## Requirements

- PHP 8.3 with strict typing.
- A web server configured with `.htaccess` support (Apache recommended).
- Properly configured `.htpasswd` file for basic authentication on `create.php`.

## Installation

1. Place the project files on your web server.
2. Ensure the `data/` directory is writable by the web server user, as this is where URL mappings are stored.
3. Configure your `.htpasswd` for securing the `create.php` file: `htpasswd -c ./data/.htpasswd YOUR_USERNAME`
5. Modify `.htacces` in project folder on web server to set the **absolute path** to your .htpasswd file
5. OPTIONAL: Initialize encryption mode: Run on command line `php ./cli-initialize-encryption.php`
6. Access `create.php` to start creating short URLs.

## DotEnv File

If you like to modify or extend the configuration, or just set the notification mechanism.
Here's an example .env file you need to place in the same folder of this project files on your web server.

    TRANSFORM_TARGET_EXPR="=\/\/www.only-on-this-domain.com\/=i"
    TRANSFORM_TARGET_SEARCH="/your-folder/"
    TRANSFORM_TARGET_REPLACE="/your-folder/subfolder/"
    
    TRANSFORM_SHORT_EXPR="=\/\/your-folder\/=i"
    TRANSFORM_SHORT_SEARCH="/your-folder/"
    TRANSFORM_SHORT_REPLACE="/your-folder/sub-folder/"
        
    NOTIFICATION_EMAIL="webmaster@your-domain.com"
    NOTIFICATION_SUBJECT="One Time Link - Event"
    NOTIFICATION_HIDE_TARGET=1

    DELETE_UNUSED_SHORTURLS_AFTER_DAYS=5

    SHORTURL_FILENAME_MAXBYTES=4

    ENCRYPTION_SECRET=YOUR_16_BYTES_SECRET___SEE_SCRIPT___cli-initialize-encryption
    ENCRYPTION_RAND_PASS_MAXBYTES=4

## License

This project is licensed under the MIT License.
