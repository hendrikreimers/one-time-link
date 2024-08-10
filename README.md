
# URL Shortener with One-Time Use Feature

This project provides a simple URL shortening service with a one-time use feature. The URL will be deleted after its first access. 

## Project Structure

- `create.php`: Handles the creation of short URLs.
- `redirect.php`: Redirects the user based on the short URL and deletes the mapping after use.
- `_functions.inc.php`: Contains utility functions for URL creation, redirection, security, and more.
- `_custom.inc.php`: Optional file for custom transformations of the short and target URLs.
- `.htaccess`: Configures URL rewriting and enhances security with various HTTP headers.
- `script.js`: Contains client-side logic for handling redirection and user interaction.
- `style.css`: Placeholder for styling.

## Security Features

- **HTTPS Enforcement**: The application forces HTTPS on all requests.
- **Security Headers**: Various security headers are set to prevent XSS, clickjacking, and other attacks.
- **Crawler Prevention**: Prevents crawlers and bots from accessing the one-time URLs, preserving their one-time nature.
- **Custom Nonce Handling**: Uses CSP with nonce to protect against script injection attacks.
- **Obfuscation**: The target URL will be obfuscated, to prevent it from being saved in the browsers history.

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
4. Rename `_htaccess` to `.htaccess` in this project directory and your `data` directory.
5. Access `create.php` to start creating short URLs.

## License

This project is licensed under the MIT License.
