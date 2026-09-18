# Warext Studios | XenForo Referral System

## English

Warext Studios XenForo Invitation & Referral System is an open-source invitation, referral, and reward add-on for XenForo 2.3.x.

The add-on creates a permanent and unique invitation code and personal invitation link for every user. Members can invite friends using either the link or code, track invitation status, and earn rewards as they reach configured valid-invitation milestones.

## Current version

**V1.1 — 1.1.0**

V1.1 is the stable release that adds manual approval, return-to-pending, rejection, and permanent deletion tools for invitation records in Admin CP.

## Main features

### Personal invitation code and link

- A unique invitation code is generated automatically for every user.
- Every invitation code has a personal invitation URL.
- A user's code normally remains unchanged.
- Normal users cannot change their own invitation code.
- Authorized user groups can change codes when permission is granted.
- Staff can suspend or reactivate an invitation code.
- A code that previously belonged to one user can never be assigned to another user.

## My Invitations page

Users have a dedicated page for monitoring their own referral system.

The page can display:

- Total invitations
- Valid invitations
- Pending invitations
- Invitations awaiting review
- Personal invitation link
- Personal invitation code
- One-click copy buttons
- WhatsApp sharing
- Telegram sharing
- Invitation QR code generated with XenForo's bundled QR library
- Invitation progress bar
- Reward milestones for configured invitation counts
- Earned rewards
- Recently invited users and their statuses
- Link to the full paginated invitation history

The invitation link and code fields use a XenForo-compatible combined input/copy layout that avoids overflow on mobile devices.

### Full invitation history

Users can view all invitation records on a separate paginated screen and filter the history by valid, pending, under-review, and rejected status.

## XenForo alerts

Users receive native XenForo alerts when:

- An invited account becomes a valid invitation
- An invitation reward is earned
- A reward is revoked because its requirements are no longer satisfied

No external notification service is used.

## Invitation progress and reward system

Administrators can create any number of invitation milestones.

Examples:

- 1 valid invitation → First Invitation reward
- 5 valid invitations → Inviter reward
- 10 valid invitations → Active Inviter reward
- 25 valid invitations → Community Ambassador reward

Each milestone can define:

- Required valid invitation count
- Reward name
- Description
- Font Awesome icon
- Custom reward image
- Reward type
- User-group reward
- Whether the reward should be revoked when the milestone is no longer valid
- Display order
- Active/inactive status

### Supported reward types

**Visual reward:** shown as an earned reward in the user's invitation history.

**Additional user-group reward:** when the required valid invitation count is reached, the user is automatically added to the selected XenForo secondary user group.

User-group operations use XenForo's own user-group change service. Existing user groups are not directly overwritten.

Each reward is created only once per user. Database-level checks prevent the same reward from being granted multiple times.

Failed reward deliveries can be retried individually or in bulk from Admin CP.

## Registration system

Invitations can be used in two ways:

### Invitation link

A user shares their personal link. When a visitor opens it, the referral information is carried into the registration screen.

### Invitation code

The personal code can be entered manually into the Invitation Code field during registration.

Invalid, suspended, or unavailable codes are rejected during registration.

An account can be linked to only one inviter. After registration is completed, the inviter cannot be changed later.

## Valid invitation requirements

A newly registered account does not become a valid invitation immediately.

Administrators can configure:

- Minimum account age
- Minimum post count

Default values:

- Minimum account age: 3 days
- Minimum post count: 3

Accounts that do not yet meet the conditions remain pending and are automatically marked valid after satisfying them.

An administrator can manually approve a pending or under-review invitation in Admin CP. Manual approval can override the account-age and post-count thresholds, while fundamental integrity checks such as deleted accounts, bans, or an invalid inviter remain enforced.

## Invitation statuses

- **Pending:** the user registered but has not yet met the required conditions.
- **Under review:** possible abuse was detected and staff review is required.
- **Valid:** the invitation meets the requirements or was manually approved by an administrator.
- **Rejected:** the invitation was considered invalid or manually rejected by staff.

An invitation under review never becomes valid automatically.

## Abuse protection

The system uses multiple controls to make artificial referral-count inflation more difficult.

- Users cannot invite themselves.
- An account can be linked to only one inviter.
- Invitation codes are not generated from predictable user IDs.
- Secure random values are used to generate codes.
- Invitation codes are unique at database level.
- Previously used codes cannot be reassigned to other users.
- Suspended codes cannot be used for new registrations.
- Repeated registrations through the same network for one inviter can be placed under review.
- Accounts created from the same network as the inviter can be placed under review.
- Raw IP addresses are not stored in add-on tables.
- One-way HMAC-SHA256 values are used for network comparison.
- Network-control data is automatically deleted after the configured retention period.
- Critical code, review, and reward operations use database locking and transactions.
- Manual Admin CP invitation operations use row locking and database transactions.

## Staff operations

Two management permissions are available through XenForo user-group permissions:

- Manage invitation codes
- Review suspicious invitations

Users with invitation-code management permission can:

- Search for users.
- View invitation codes.
- Change invitation codes.
- Suspend codes.
- Reactivate codes.
- Enter a suspension reason.

Authorized code changes are stored in the operation history.

Users with suspicious-invitation review permission can approve or reject records in the review queue. A staff member cannot approve an invitation record they created themselves.

## Admin CP

Admin CP contains a dedicated Invitation & Referral System section.

Administrators can view and manage:

- General system statistics
- Total and active invitation codes
- Reserved historical invitation codes
- Total, valid, pending, under-review, and rejected invitations
- Reward records and delivery status
- Invitation milestones
- Recent invitation-code changes
- Reward-delivery errors
- Bulk rechecking of failed rewards
- Retrying a single failed reward
- Per-user invitation detail screens
- A user's code, rewards, recent invitations, and code-operation history in one screen
- Filtering by inviter
- Filtering by invited user
- Filtering by invitation status
- Filtering by date range
- Manual approval of pending, under-review, or rejected invitations
- Returning an invitation to pending state
- Manual rejection
- Permanent deletion with confirmation

When a valid invitation is manually returned to pending, rejected, or deleted, the inviter's reward eligibility is recalculated. Manual approval and rejection store the administrator and operation time in the existing review fields.

The entire system can also be enabled or disabled from Admin CP. Disabling it does not delete existing data, and management/maintenance operations continue to work.

## Automatic maintenance

To avoid processing every user in one request on large forums, the add-on uses XenForo's background job system.

Automatically:

- Pending invitations are checked.
- Previously valid invitations are revalidated.
- Codes are created for older users who do not have one.
- Reward eligibility is checked.
- Expired network-control data is cleaned up.

Manually approved invitations are not forced to satisfy account-age and post-count thresholds again, but user and inviter account integrity continues to be checked during daily revalidation.

Operations are processed in chunks to reduce unnecessary load on large user tables.

## Database

Required tables are created automatically during installation. No manual SQL import is required.

Tables used:

- `xf_wrxt_referral_code`
- `xf_wrxt_referral_code_reservation`
- `xf_wrxt_referral`
- `xf_wrxt_referral_milestone`
- `xf_wrxt_referral_reward`
- `xf_wrxt_referral_code_log`

Required table and field changes are applied automatically by XenForo's add-on upgrade system.

After the V1 upgrade completes, missing user codes and reward eligibility are rechecked in the background.

## Privacy

- Raw IP addresses are not stored.
- One-way hashes are used for network checks.
- Network-control values in invitation records are retained for 90 days by default.
- The invitation-code owner's network-control value follows the same retention policy.
- Retention duration can be changed from Admin CP.
- QR generation runs client-side with XenForo's bundled library; invitation links are not sent to an external service for QR generation.

## XenForo compatibility

- XenForo 2.3.0+
- PHP 8.1+
- Syntax validation for PHP 8.1, 8.2, 8.3, and 8.4

The add-on does not modify XenForo core files.

It does not require a custom domain, custom theme, credits add-on, or any other third-party add-on.

## Installation

1. Upload the contents of the `upload` directory into the XenForo installation directory.
2. Open Admin CP > Add-ons.
3. Install `Warext Studios - Davet Referans Sistemi`.
4. Configure the referral settings from Admin CP > Options.
5. Grant the required invitation-management permissions to the appropriate user groups.
6. Create reward milestones from the Invitation & Referral System section.

Missing invitation codes for existing XenForo users are generated automatically after installation.

## Upgrade

Upgrading to 1.1.0 is supported from 0.1.0, 0.2.0, 0.3.0, 0.4.0, 0.4.1, 1.0.0, and 1.0.1. After updating the files, run the XenForo add-on upgrade from Admin CP. No manual SQL operation is required.

## Source-code rules

- XenForo core files are never modified.
- Source PHP files do not use explanatory comments, inline comments, or PHPDoc.
- PHP-tokenizer-based automated validation prevents source comments.
- User input is validated server-side.
- Staff operations are enforced at service level, not only in the interface.
- PHP, JSON, and XML files are validated through GitHub Actions.

## Project documentation

- `CHANGELOG.md` — release changes
- `SECURITY.md` — security policy
- `CONTRIBUTING.md` — contribution guidelines
- `docs/ARCHITECTURE.md` — technical architecture

## Add-on ID

`WarextStudios/ReferralSystem`

## License

MIT License

## Support

For questions, bug reports, installation support, and help with Warext Studios XenForo add-ons, you can join our support Discord server:

**Discord:** https://discord.gg/tgsV5XMcFS

---

## Türkçe

XenForo 2.3.x için açık kaynak davet, referans ve ödül eklentisidir.

Eklenti her kullanıcıya kendine özel, kalıcı ve benzersiz bir davet kodu ile davet bağlantısı oluşturur. Kullanıcılar arkadaşlarını bağlantı veya kod ile davet edebilir, davet durumlarını takip edebilir ve belirlenen davet sayılarına ulaştıkça ödül kazanabilir.

## Güncel sürüm

**V1.1 — 1.1.0**

V1.1, Admin CP davet kayıtlarına manuel onaylama, beklemeye alma, reddetme ve kalıcı silme araçlarını ekleyen kararlı sürümdür.

## Temel özellikler

### Kişisel davet kodu ve bağlantısı

- Her kullanıcıya otomatik olarak benzersiz bir davet kodu oluşturulur.
- Her davet koduna bağlı kişisel davet bağlantısı bulunur.
- Kullanıcının kodu normal şartlarda değişmez.
- Normal kullanıcı kendi davet kodunu değiştiremez.
- Yetkili kullanıcı grupları izin verilmesi halinde kod değiştirebilir.
- Yetkililer bir davet kodunu askıya alabilir veya tekrar etkinleştirebilir.
- Daha önce bir kullanıcıya ait olmuş davet kodu başka bir kullanıcıya tekrar verilemez.

## Davetlerim sayfası

Kullanıcıların kendi davet sistemlerini takip edebildiği özel bir sayfa bulunur.

Bu sayfada:

- Toplam davet sayısı
- Geçerli davet sayısı
- Bekleyen davet sayısı
- İnceleme bekleyen davet sayısı
- Kişisel davet bağlantısı
- Kişisel davet kodu
- Tek tıkla kopyalama butonları
- WhatsApp paylaşımı
- Telegram paylaşımı
- XenForo'nun kendi QR kütüphanesiyle üretilen davet QR kodu
- Davet ilerleme çubuğu
- Belirlenen davet sayılarına ait ödül noktaları
- Kazanılmış ödüller
- Son davet edilen kullanıcılar ve durumları
- Sayfalı tam davet geçmişine geçiş

görüntülenebilir.

Davet bağlantısı ve davet kodu alanları XenForo form yapısına uyumlu, mobilde taşma yapmayacak birleşik giriş ve kopyalama düzeni kullanır.

### Tam davet geçmişi

Kullanıcılar bütün davetlerini ayrı ekranda sayfalı olarak görüntüleyebilir. Geçmiş ekranı geçerli, bekleyen, incelemede ve reddedilmiş durumlarına göre filtrelenebilir.

## XenForo bildirimleri

Kullanıcıya aşağıdaki durumlarda XenForo'nun kendi bildirim sistemi üzerinden bildirim gönderilir:

- Davet edilen hesap geçerli davet haline geldiğinde
- Bir davet ödülü kazanıldığında
- Şartlar kaybedildiği için bir ödül geri alındığında

Bildirim sistemi harici bir servis kullanmaz.

## Davet ilerleme ve ödül sistemi

Yönetici panelinden istenildiği kadar davet hedefi oluşturulabilir.

Örneğin:

- 1 geçerli davet → İlk Davet ödülü
- 5 geçerli davet → Davetçi ödülü
- 10 geçerli davet → Aktif Davetçi ödülü
- 25 geçerli davet → Topluluk Elçisi ödülü

Her hedef için aşağıdaki bilgiler ayarlanabilir:

- Gerekli geçerli davet sayısı
- Ödül adı
- Açıklama
- Font Awesome ikonu
- Özel ödül görseli
- Ödül türü
- Kullanıcı grubu ödülü
- Hedef geçerliliğini kaybederse ödülün geri alınıp alınmayacağı
- Görüntüleme sırası
- Aktif veya pasif durumu

### Desteklenen ödül türleri

**Görsel ödül:** Kullanıcının davet geçmişinde kazanılmış ödül olarak gösterilir.

**Ek kullanıcı grubu ödülü:** Kullanıcı belirlenen davet sayısına ulaştığında seçilen XenForo ek kullanıcı grubuna otomatik olarak dahil edilir.

Kullanıcı grubu işlemleri XenForo'nun kendi kullanıcı grubu değişiklik servisi üzerinden gerçekleştirilir. Kullanıcının mevcut grupları doğrudan değiştirilmez.

Her ödül kullanıcı başına yalnızca bir kez oluşturulur. Aynı ödülün birden fazla kez verilmesini engelleyen veritabanı kontrolleri bulunur.

Başarısız ödüller Admin CP üzerinden topluca veya tekil olarak yeniden denenebilir.

## Kayıt sistemi

Davet iki şekilde kullanılabilir:

### Davet bağlantısı

Kullanıcı kişisel bağlantısını paylaşır. Bağlantıyı açan ziyaretçinin davet bilgisi kayıt ekranına aktarılır.

### Davet kodu

Kayıt ekranında bulunan Davet kodu alanına kişisel kod manuel olarak girilebilir.

Geçersiz, askıya alınmış veya kullanılamayan bir kod kayıt sırasında kabul edilmez.

Bir hesap yalnızca bir davetçiye bağlanabilir. Kayıt tamamlandıktan sonra davet sahibi sonradan değiştirilemez.

## Geçerli davet şartları

Yeni bir hesap kayıt olduğu anda otomatik olarak geçerli davet sayılmaz.

Yönetici panelinden şu şartlar belirlenebilir:

- Minimum hesap yaşı
- Minimum mesaj sayısı

Varsayılan değerler:

- Minimum hesap yaşı: 3 gün
- Minimum mesaj sayısı: 3

Şartları tamamlamayan kayıtlar bekleyen durumda tutulur. Şartları tamamladıktan sonra sistem tarafından otomatik olarak geçerli hale getirilir.

Yönetici, Admin CP üzerinden gerekli gördüğü bir bekleyen veya incelemedeki daveti manuel olarak geçerli hale getirebilir. Manuel onay hesap yaşı ve mesaj sayısı eşiklerini yönetici kararıyla geçersiz kılar; hesap silinmesi, ban veya davetçinin geçersiz hale gelmesi gibi temel bütünlük kontrolleri korunur.

## Davet durumları

- **Bekleyen:** Kullanıcı kayıt olmuş ancak gerekli şartları henüz tamamlamamış.
- **İncelemede:** Kayıtta kötüye kullanım ihtimali tespit edilmiş ve yetkili kontrolü gerekiyor.
- **Geçerli:** Davet gerekli şartları karşılıyor veya yönetici tarafından manuel onaylanmış.
- **Reddedildi:** Davet geçersiz sayılmış veya yetkili tarafından reddedilmiş.

İnceleme durumundaki bir kayıt otomatik olarak geçerli hale getirilmez.

## Kötüye kullanım koruması

Sistem sahte hesaplarla davet sayısı yükseltilmesini zorlaştırmak için birden fazla kontrol uygular.

- Aynı kullanıcının kendisini davet etmesi engellenir.
- Bir hesap yalnızca bir davetçiye bağlanabilir.
- Davet kodları tahmin edilebilir kullanıcı numaralarından oluşturulmaz.
- Kod üretiminde güvenli rastgele değerler kullanılır.
- Davet kodları veritabanında benzersiz tutulur.
- Daha önce kullanılmış kodlar tekrar başka kullanıcıya atanamaz.
- Askıya alınmış kodlar yeni kayıtlarda kullanılamaz.
- Aynı davetçiye aynı ağ üzerinden tekrarlı kayıtlar incelemeye alınabilir.
- Davetçiyle aynı ağ üzerinden oluşturulan hesaplar incelemeye alınabilir.
- Ham IP adresleri eklenti tablolarında saklanmaz.
- Ağ karşılaştırması için tek yönlü HMAC-SHA256 değerleri kullanılır.
- Ağ kontrol verileri belirlenen saklama süresi sonunda otomatik temizlenir.
- Kritik kod, inceleme ve ödül işlemlerinde veritabanı kilitleri ve işlemsel kayıt kullanılır.
- Admin CP manuel davet işlemleri satır kilidi ve veritabanı transaction'ı ile uygulanır.

## Yetkili işlemleri

XenForo kullanıcı grubu izinleri üzerinden iki ayrı yönetim yetkisi bulunur:

- Davet kodlarını yönetme
- Şüpheli davetleri inceleme

Davet kodu yönetme yetkisine sahip kullanıcılar:

- Kullanıcı arayabilir.
- Davet kodunu görüntüleyebilir.
- Davet kodunu değiştirebilir.
- Kodu askıya alabilir.
- Kodu tekrar etkinleştirebilir.
- Askıya alma nedeni girebilir.

Kod üzerinde yapılan yetkili değişiklikleri işlem geçmişine kaydedilir.

Şüpheli davet inceleme yetkisine sahip kullanıcılar inceleme kuyruğundaki kayıtları onaylayabilir veya reddedebilir. Yetkili kendi oluşturduğu davet kaydını onaylayamaz.

## Yönetici paneli

Admin CP içinde Davet Referans Sistemi bölümü bulunur.

Buradan:

- Genel sistem istatistikleri
- Toplam ve aktif davet kodları
- Korunan eski davet kodları
- Toplam, geçerli, bekleyen, incelemedeki ve reddedilmiş davetler
- Ödül kayıtları ve teslim durumları
- Davet hedefleri
- Son davet kodu değişiklikleri
- Ödül teslim hataları
- Başarısız ödülleri toplu yeniden kontrol etme
- Tek bir başarısız ödülü yeniden deneme
- Kullanıcı bazlı davet detay ekranı
- Kullanıcının kod, ödül, son davet ve kod işlem geçmişini tek ekranda görüntüleme
- Davet eden kullanıcıya göre filtreleme
- Davet edilen kullanıcıya göre filtreleme
- Davet durumuna göre filtreleme
- Tarih aralığına göre filtreleme
- Bekleyen, incelemedeki veya reddedilmiş daveti manuel onaylama
- Daveti tekrar bekleyen duruma alma
- Daveti manuel reddetme
- Davet kaydını onay ekranı ile kalıcı silme

yönetilebilir ve görüntülenebilir.

Geçerli bir davet manuel olarak beklemeye alındığında, reddedildiğinde veya silindiğinde davetçinin ödül uygunluğu yeniden hesaplanır. Manuel onay ve ret işlemlerinde yönetici ve işlem zamanı mevcut inceleme alanlarına kaydedilir.

Sistem ayrıca Admin CP üzerinden tamamen aktif veya pasif hale getirilebilir. Sistem kapatıldığında mevcut veriler silinmez ve yönetim/bakım işlemleri çalışmaya devam eder.

## Otomatik bakım işlemleri

Eklenti büyük forumlarda tek istekte bütün kullanıcıları işlememek için XenForo'nun arka plan görev sistemini kullanır.

Otomatik olarak:

- Bekleyen davetler kontrol edilir.
- Daha önce geçerli olmuş davetler tekrar doğrulanır.
- Kodu olmayan eski kullanıcılara kod oluşturulur.
- Ödül uygunlukları kontrol edilir.
- Süresi dolmuş ağ kontrol verileri temizlenir.

Manuel onaylanan davetlerde hesap yaşı ve mesaj sayısı tekrar zorunlu tutulmaz; buna karşılık kullanıcı ve davetçi hesap bütünlüğü günlük yeniden doğrulamada kontrol edilmeye devam eder.

İşlemler parçalara bölünerek çalıştırıldığı için büyük kullanıcı tablolarında gereksiz yük oluşturulması azaltılır.

## Veritabanı

Kurulum sırasında gerekli tablolar otomatik oluşturulur. Manuel SQL yüklemek gerekmez.

Kullanılan tablolar:

- `xf_wrxt_referral_code`
- `xf_wrxt_referral_code_reservation`
- `xf_wrxt_referral`
- `xf_wrxt_referral_milestone`
- `xf_wrxt_referral_reward`
- `xf_wrxt_referral_code_log`

Eklenti güncellemelerinde gerekli tablo ve alan değişiklikleri XenForo eklenti yükseltme sistemi tarafından otomatik uygulanır.

V1 yükseltmesi tamamlandığında eksik kullanıcı kodları ve ödül uygunlukları arka planda yeniden kontrol edilir.

## Gizlilik

- Ham IP adresi saklanmaz.
- Ağ kontrolleri için tek yönlü hash kullanılır.
- Davet kayıtlarındaki ağ kontrol değerleri varsayılan olarak 90 gün saklanır.
- Davet kodu sahibine ait ağ kontrol değeri de aynı saklama politikasına tabidir.
- Saklama süresi Admin CP üzerinden değiştirilebilir.
- QR oluşturma işlemi istemci tarafında XenForo'nun paketlenmiş kütüphanesiyle yapılır; davet bağlantısı QR üretmek için harici bir servise gönderilmez.

## XenForo uyumluluğu

- XenForo 2.3.0+
- PHP 8.1+
- PHP 8.1, 8.2, 8.3 ve 8.4 sözdizimi kontrolü

Eklenti XenForo çekirdek dosyalarını değiştirmez.

Özel domain, özel tema, kredi eklentisi veya başka bir üçüncü parti eklenti gerektirmez.

## Kurulum

1. `upload` klasörünün içeriğini XenForo kurulum dizinine yükleyin.
2. Admin CP > Add-ons bölümünü açın.
3. `Warext Studios - Davet Referans Sistemi` eklentisini kurun.
4. Admin CP > Options bölümünden davet sistemi ayarlarını yapılandırın.
5. Gerekli kullanıcı gruplarına davet yönetim izinlerini verin.
6. Davet Referans Sistemi bölümünden ödül hedeflerini oluşturun.

Mevcut XenForo kullanıcılarının eksik davet kodları kurulum sonrasında otomatik olarak oluşturulur.

## Güncelleme

0.1.0, 0.2.0, 0.3.0, 0.4.0, 0.4.1, 1.0.0 veya 1.0.1 sürümünden 1.1.0'a yükseltme desteklenir. Dosyaları güncelledikten sonra XenForo Admin CP içinden eklenti yükseltmesini çalıştırmak yeterlidir. Manuel SQL işlemi gerekmez.

## Kaynak kod kuralları

- XenForo çekirdeğinde değişiklik yapılmaz.
- Kaynak PHP dosyalarında açıklama, satır içi yorum veya PHPDoc kullanılmaz.
- PHP tokenizer tabanlı otomatik doğrulama kaynak yorumlarını engeller.
- Kullanıcı girdileri sunucu tarafında doğrulanır.
- Yetkili işlemleri yalnızca arayüz seviyesinde değil servis seviyesinde de kontrol edilir.
- PHP, JSON ve XML dosyaları GitHub Actions üzerinden doğrulanır.

## Proje belgeleri

- `CHANGELOG.md` — sürüm değişiklikleri
- `SECURITY.md` — güvenlik politikası
- `CONTRIBUTING.md` — katkı kuralları
- `docs/ARCHITECTURE.md` — teknik yapı

## Add-on ID

`WarextStudios/ReferralSystem`

## Lisans

MIT License

## Destek

Sorularınız, hata bildirimleriniz, kurulum desteği ve Warext Studios XenForo eklentileriyle ilgili yardım için destek Discord sunucumuza katılabilirsiniz:

**Discord:** https://discord.gg/tgsV5XMcFS
