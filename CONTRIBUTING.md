# Katkıda Bulunma

Katkılar pull request üzerinden kabul edilir.

## Geliştirme kuralları

- XenForo çekirdek dosyaları değiştirilmemelidir.
- Kod belirli bir domain, tema veya üçüncü parti eklentiye bağımlı olmamalıdır.
- Yeni özellikler XenForo entity, repository, service, controller, route, permission, option ve template sistemleriyle uygulanmalıdır.
- Kullanıcı girdileri doğrulanmalı ve yetkili işlemler sunucu tarafında izin kontrolünden geçmelidir.
- Davet ilişkileri için veritabanı bütünlüğü korunmalıdır.
- Ham IP adresi eklenti tablolarında saklanmamalıdır.
- Kaynak kod içinde açıklama veya yorum satırı kullanılmamalıdır.
- PHP 8.1, 8.2, 8.3 ve 8.4 sözdizimi kontrolünden geçmelidir.
- Arayüzde sabit tema renkleri yerine XenForo stil değişkenleri tercih edilmelidir.
- Yeni kullanıcıya veya mevcut foruma özel isimler kaynak koda eklenmemelidir.

## Pull request öncesi

1. Değişikliğin XenForo 2.3.x ile uyumlu olduğunu kontrol edin.
2. `find upload -type f -name '*.php' -print0 | xargs -0 -n1 php -l` komutuyla PHP dosyalarını doğrulayın.
3. Yetki gerektiren yeni endpointlerin hem controller hem service katmanında korunduğunu kontrol edin.
4. Yeni tablo veya kolon gerekiyorsa upgrade adımı ekleyin.
5. CHANGELOG.md dosyasını güncelleyin.
