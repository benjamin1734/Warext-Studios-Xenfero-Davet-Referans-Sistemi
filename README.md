# Warext Studios | XenForo Davet Referans Sistemi

XenForo 2.3.x için açık kaynak davet, referans, ilerleme ve ödül sistemi.

## Amaç

Eklenti her kullanıcıya tek ve kalıcı bir davet kodu ile bu koda bağlı kişisel davet bağlantısı sağlar. Kullanıcılar kendi davet sayılarını, bekleyen ve geçerli davetlerini ve ödül ilerlemelerini XenForo arayüzü içinde görüntüleyebilir.

## Temel özellikler

- Her kullanıcı için otomatik, benzersiz ve kalıcı davet kodu
- Kişisel davet bağlantısı
- Kullanıcının kendi kodunu değiştirememesi
- Yetkili kullanıcı grupları için kod değiştirme, askıya alma ve yeniden etkinleştirme altyapısı
- Davet bağlantısı ve manuel davet kodu desteği
- Kayıt sırasında referans ilişkilendirmesi
- Bir hesabın yalnızca tek davetçiye bağlanabilmesi
- Bekleyen, incelemede, geçerli ve reddedilmiş davet durumları
- Davet sayısı ve ilerleme çubuğu
- Ödül kilometre taşları
- XenForo permission sistemiyle yetkilendirme
- XenForo route, controller, entity, repository, service ve template sistemleriyle native entegrasyon
- Tema bağımsız tasarım
- Harici servis veya üçüncü parti eklenti zorunluluğu yok
- Kurulum sırasında gerekli veritabanı tablolarının otomatik oluşturulması
- Ham IP saklamadan kötüye kullanım kontrolü için hash tabanlı kayıt güvenliği

## Güvenlik yaklaşımı

Referanslar kayıt anında doğrudan ödüllendirilmez. Yeni kayıtlar önce bekleyen veya inceleme durumunda tutulur. Aynı hesabın birden fazla davetçiye yazılması veritabanı seviyesinde engellenir. Askıya alınmış kodlar anında geçersiz hale gelir. Kod yönetimi normal kullanıcılara kapalıdır ve XenForo izin sistemiyle sınırlandırılır.

## Gereksinimler

- XenForo 2.3.0 veya üzeri
- PHP 8.1 veya üzeri
- PHP 8.4 ile uyum hedefi

## Kurulum

`upload` klasörünün içeriğini XenForo kök dizinine yükleyin. Ardından Admin CP > Add-ons bölümünden `Warext Studios - Davet Referans Sistemi` eklentisini kurun.

Eklenti kurulurken gerekli tablolar otomatik oluşturulur. Manuel SQL import gerekmez.

## Add-on ID

`WarextStudios/ReferralSystem`

## Lisans

MIT
