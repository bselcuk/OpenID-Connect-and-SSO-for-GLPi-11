# GLPi 11.0 OpenID Plugin Development Context (Blueprint)

## 1. Project Scope & Architecture Rules
We are creating an "OpenID Login" plugin for GLPi version >= 11.0. 
*   **Plugin Key:** `openid`
*   **Namespace:** `GlpiPlugin\Openid`
*   **Absolute Rule:** NEVER modify GLPi core files or core database tables directly. The plugin must be entirely self-contained inside the `glpi/plugins/openid/` directory.

## 2. Directory Structure
*   `/`: Contains `setup.php` and `hook.php` (Mandatory files).
*   `/src/`: Contains core plugin classes (PSR-4 compliant with namespace `GlpiPlugin\Openid`).
*   `/src/Controller/`: Contains routing controllers (Auto-discovered in GLPi 11.0).
*   `/templates/`: Contains Twig templates (e.g., config page).

## 3. Mandatory Initialization Files
### `setup.php`
Must contain three core functions:
1.  `plugin_version_openid()`: Returns an array with name, version, author, and `requirements` (glpi => min 11.0.0, php => ext-curl required).
2.  `plugin_openid_check_prerequisites()`: Checks if PHP environment is suitable.
3.  `plugin_init_openid()`: Registers hooks. We need:
    *   `$PLUGIN_HOOKS['csrf_compliant']['openid'] = true;`
    *   `$PLUGIN_HOOKS['config_page']['openid'] = 'front/config.php';`
    *   `$PLUGIN_HOOKS['display_login']['openid'] = 'plugin_openid_display_login';`

### `hook.php`
Must contain install and uninstall functions:
1.  `plugin_openid_install()`: Must use GLPi's `Config::getConfigurationValue` and `setConfigurationValues` to store standard OpenID settings (`client_id`, `client_secret`, `provider_url`, `user_field`) in the existing `glpi_configs` table under the `plugin_openid` context. Do not create a new DB table.
2.  `plugin_openid_uninstall()`: Must delete from `glpi_configs` where context is `plugin_openid`.
3.  `plugin_openid_display_login()`: A hook function that echoes an HTML button ("Login with OpenID") on the GLPi login screen. The button must link to `/plugins/openid/login`.

## 4. Configuration Page (Adım 2)
*   Create `/src/Config.php` extending `CommonGLPI` or simply handle logic to update the `glpi_configs` settings.
*   Create a Twig template `/templates/config.html.twig` to display the form. 
*   GLPi UI will wrap it automatically if we hook it properly.

## 5. GLPi 11.0 Controller & Routing (OpenID Callback)
*   GLPi 11.0 requires Plugin controllers to be in `src/Controller/` extending `Glpi\Controller\AbstractController` and defining a route using the PHP `#[Route]` attribute.
*   We need an endpoint that catches the OpenID provider's redirect (callback).
*   Since the user is NOT logged in yet during the callback, the route MUST use GLPi's "Unauthenticated access" mechanism (Session based: No auth check) by registering the path pattern in `plugin_openid_init()` inside `setup.php` using `$PLUGIN_HOOKS['public_pages']['openid'] = ['callback'];` (or proper unauthenticated route syntax for 11.0).
*   Route example: `#[Route('/callback', name: 'plugin_openid_callback', methods: ['GET'])]`

## 6. Core Authentication Logic (How to log user in)
Once the Controller receives the valid token and identifies the User's email, the plugin must find the GLPi user in the database and force a session start.
*   GLPi initiates a session via `Session::init(Auth $auth);`
*   In the controller, we must:
    1. Find the User by email: `$user = new User(); $user->getFromDBByCrit(['email' => $email_from_openid]);`
    2. Instantiate Auth: `$auth = new Auth();`
    3. Assign user and set flags: `$auth->user = $user; $auth->auth_succeded = true; $auth->extauth = 1;`
    4. Call `Session::init($auth);`
    5. Redirect to GLPi central page.