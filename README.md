<p align="center">
  <img src="logo.png" width="200" alt="OpenID Connect SSO Plugin for GLPi">
</p>

<h1 align="center">🛡️ OpenID Connect & SSO for GLPi 11</h1>

<p align="center">
  <em>Modern, Secure, and Enterprise-Ready Single Sign-On Plugin for GLPi 11.0+</em>
</p>

---

## 🌟 Overview

The **OpenID Connect & SSO** plugin provides a seamless authentication experience for your GLPi Helpdesk platform. By integrating with industry-standard Identity Providers (IdP) like **Keycloak, Azure AD, Google Workspace, Okta, and Auth0**, it eliminates password fatigue, centralizes access control, and automates user provisioning.

Built strictly for **GLPi 11.0+**, this plugin is robust, CSRF-secured, and follows the latest GLPi strict-types architecture.

## 🚀 Key Features

- 🌍 **Multi-Provider Support:** Add and manage multiple OpenID providers simultaneously. Let your users choose between "Login with Microsoft", "Login with Google", or your corporate Keycloak.
- 🔄 **Auto-Provisioning (JIT):** Users are automatically created in GLPi on their first successful login. No need to pre-import CSVs or sync LDAP manually.
- 🗺️ **Dynamic Field Mapping:** Synchronize OpenID claims (e.g., `preferred_username`, `email`, `given_name`) directly to GLPi user fields on every login.
- 🌗 **Mix Mode Authentication:** 
    - **Enabled:** Shows both standard GLPi login and OpenID buttons.
    - **Disabled:** Completely hides standard GLPi login. Only SSO is allowed!
    - *Emergency Backdoor:* If Mix Mode is disabled, administrators can still access local login by appending `?local_login=1` to the URL.
- 🚪 **Global Logout:** When a user logs out of GLPi, they are securely signed out from the central Identity Provider as well (if a Logout URL is configured).
- 🎨 **Native GLPi 11 UI:** Seamlessly integrates into GLPi's new configuration layout, side-menus, and marketplace.

## 📸 Provider Configuration & Mapping

Easily add new OpenID Providers by navigating to **Setup > OpenID SSO > Providers**. You will need:
- `Client ID` & `Client Secret`
- `Provider URL` (e.g., `https://keycloak.example.com/realms/myrealm`)
- `Logout URL`

### Field Mapping Example
To automatically sync data from the provider to the GLPi user account, provide a JSON map in the **Sync Field Mapping** setting:
```json
{
  "email": "email",
  "preferred_username": "name",
  "phone_number": "phone"
}
```

## 🛠️ Installation

1. Download the latest release (e.g. `openid-v1.2.0.zip`).
2. Extract the archive into your GLPi plugins directory:
   ```bash
   unzip openid-v1.2.0.zip -d /var/www/glpi/plugins/
   ```
   *(Ensure the resulting folder is named exactly `openid`)*
3. Navigate to **Setup > Plugins** in your GLPi dashboard.
4. Click the **Install** folder icon next to "OpenID Connect & SSO".
5. Click the **Enable** green button.

## 🔐 Requirements
- **GLPi:** `>= 11.0.0`
- **PHP:** `>= 8.1` (requires `curl` extension)

---
<p align="center">
  <b>Developed by B.Selçuk ÖKSÜZ - MacSoft</b>
</p>
