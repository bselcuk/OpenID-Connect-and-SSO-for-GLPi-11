# GLPi OpenID Connect & SSO Eklentisi

Bu eklenti, GLPi 11.0 için modern, güvenli ve esnek bir OpenID Connect (OIDC) Single Sign-On (SSO) entegrasyonu sunar. Keycloak, Google Workspace, Azure AD gibi protokolleri destekler.

## Özellikler
- **Çoklu Sağlayıcı Desteği (Multi-Provider):** Birden fazla OpenID sağlayıcısını yönetme.
- **Mix Mode (Zorunlu SSO):** Standart GLPi giriş ekranını gizleme (Acil durumlar için `?local_login=1`).
- **Auto-Provisioning:** SSO ile giriş yapan kullanıcıyı otomatik oluşturma.
- **Dinamik JSON Profil Eşleştirme:** IdP'den gelen (claim) verileri GLPi ile eşleştirme.
- **Single Provider Auto-Redirect:** Tek sağlayıcı varsa GLPi login formunu atlayarak doğrudan SSO sayfasına yönlendirme.
- **Gerçek SSO Çıkışı (Single Sign-Out):** Çıkış yapıldığında IdP oturumunu da sonlandırma.

## Geliştirici ve Lisans
- **Geliştirici:** B.Selçuk ÖKSÜZ
- **Firma:** MacSoft
- **Lisans:** GPLv3+
