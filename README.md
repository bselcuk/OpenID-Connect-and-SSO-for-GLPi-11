# GLPi OpenID Connect & SSO Eklentisi

Bu eklenti, GLPi 11.0 için modern, güvenli ve esnek bir OpenID Connect (OIDC) Single Sign-On (SSO) entegrasyonu sunar. 

Keycloak, Google Workspace, Azure AD gibi standart OpenID Connect protokolünü destekleyen tüm kimlik sağlayıcılarıyla (IdP) sorunsuz çalışacak şekilde tasarlanmıştır.

## Özellikler

- **Çoklu Sağlayıcı Desteği (Multi-Provider):** Birden fazla OpenID sağlayıcısını aynı anda ekleyebilir ve yönetebilirsiniz.
- **Mix Mode (Zorunlu SSO):** Standart GLPi giriş ekranını gizleyerek kullanıcıları sadece SSO üzerinden giriş yapmaya zorlayabilirsiniz. (Acil durumlarda `?local_login=1` arka kapısı mevcuttur).
- **Auto-Provisioning (Otomatik Kullanıcı Oluşturma):** SSO ile giriş yapan kullanıcı GLPi veritabanında yoksa otomatik olarak oluşturulur.
- **Dinamik JSON Profil Eşleştirme:** Sağlayıcıdan gelen yetki (claim) verilerini, GLPi veritabanındaki Ad, Soyad, Telefon gibi alanlarla tek bir JSON konfigürasyonu üzerinden anında eşleştirebilirsiniz.
- **Single Provider Auto-Redirect:** Eğer sistemde aktif sadece 1 adet sağlayıcı varsa ve Mix Mode kapalıysa, kullanıcı GLPi giriş ekranını hiç görmeden doğrudan SSO login sayfasına şimşek hızında yönlendirilir.
- **Gerçek SSO Çıkışı (Single Sign-Out):** GLPi'den çıkış yapıldığında, bağlanan OpenID sağlayıcısının oturumu da otomatik olarak sonlandırılır.

## Kurulum ve Gereksinimler

- GLPi Sürümü: **11.0 ve üzeri**
- PHP Sürümü: **8.2 ve üzeri** (GLPi 11.0 standardı)

### Kurulum Adımları
1. Bu dizini GLPi'nin `plugins/` klasörü altına `openid` adıyla yükleyin.
2. Terminal üzerinden eklentiyi yükleyin ve aktifleştirin:
   ```bash
   php bin/console plugin:install openid
   php bin/console plugin:activate openid
   ```
3. GLPi arayüzünde **Yapılandırma > OpenID SSO** menüsüne giderek ayarlarınızı yapılandırın.

## Örnek Keycloak Konfigürasyonu
Yeni bir sağlayıcı eklerken aşağıdaki değerleri referans alabilirsiniz:
- **Provider URL:** `http://IP:8080/realms/glpi`
- **Icon Class:** `ti ti-key`
- **Scopes:** `openid email profile`
- **Match OpenID Claim:** `preferred_username`
- **Match GLPI Field:** `Username`
- **Sync Field Mapping:** `{"given_name": "firstname", "family_name": "realname", "email": "email"}`

## Geliştirici ve Lisans
- **Geliştirici:** B.Selçuk ÖKSÜZ
- **Firma:** MacSoft
- **Lisans:** GPLv3+
