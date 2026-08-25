# Warext Studios | XenForo Davet Referans Sistemi

XenForo 2.3.x için açık kaynak davet, referans ve ödül eklentisidir.

Eklenti her kullanıcıya kendine özel, kalıcı ve benzersiz bir davet kodu ile davet bağlantısı oluşturur. Kullanıcılar arkadaşlarını bağlantı veya kod ile davet edebilir, davet durumlarını takip edebilir ve belirlenen davet sayılarına ulaştıkça ödül kazanabilir.

## Güncel sürüm

**V1 — 1.0.0**

V1, temel ve yönetim özellikleri tamamlanmış kararlı sürümdür.

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

## Davet durumları

- **Bekleyen:** Kullanıcı kayıt olmuş ancak gerekli şartları henüz tamamlamamış.
- **İncelemede:** Kayıtta kötüye kullanım ihtimali tespit edilmiş ve yetkili kontrolü gerekiyor.
- **Geçerli:** Davet gerekli şartları karşılıyor.
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

yönetilebilir ve görüntülenebilir.

Sistem ayrıca Admin CP üzerinden tamamen aktif veya pasif hale getirilebilir. Sistem kapatıldığında mevcut veriler silinmez ve yönetim/bakım işlemleri çalışmaya devam eder.

## Otomatik bakım işlemleri

Eklenti büyük forumlarda tek istekte bütün kullanıcıları işlememek için XenForo'nun arka plan görev sistemini kullanır.

Otomatik olarak:

- Bekleyen davetler kontrol edilir.
- Daha önce geçerli olmuş davetler tekrar doğrulanır.
- Kodu olmayan eski kullanıcılara kod oluşturulur.
- Ödül uygunlukları kontrol edilir.
- Süresi dolmuş ağ kontrol verileri temizlenir.

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

0.1.0, 0.2.0, 0.3.0, 0.4.0 veya 0.4.1 sürümünden V1'e yükseltme desteklenir. Dosyaları güncelledikten sonra XenForo Admin CP içinden eklenti yükseltmesini çalıştırmak yeterlidir. Manuel SQL işlemi gerekmez.

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
