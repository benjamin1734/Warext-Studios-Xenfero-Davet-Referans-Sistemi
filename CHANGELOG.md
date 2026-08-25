# Değişiklik Günlüğü

## 1.0.0 - 2026-08-23

### V1 kararlı sürüm

- Geliştirme sürümleri birleştirilerek V1 yayımlandı.
- Davet bağlantısı ve davet kodu alanlarının masaüstü ve mobil hizalaması düzeltildi.
- Kullanıcı ve yönetici arayüzündeki geliştirici terimleri temizlendi.
- Kullanıcıya gösterilen temel hata mesajları XenForo dil cümlesi sistemine taşındı.
- Kullanıcıya tüm davet geçmişini sayfalı olarak görüntüleme ekranı eklendi.
- Davet geçmişine durum filtresi eklendi.
- WhatsApp ve Telegram paylaşım bağlantıları eklendi.
- XenForo 2.3 ile gelen yerleşik QR kütüphanesi kullanılarak davet QR kodu eklendi.
- Davet geçerli hale geldiğinde XenForo bildirim sistemi üzerinden kullanıcı bildirimi eklendi.
- Ödül kazanıldığında XenForo bildirimi eklendi.
- Ödül geri alındığında XenForo bildirimi eklendi.
- Admin CP sistem özetine korunan eski davet kodu sayısı eklendi.
- Admin CP içine son davet kodu değişiklikleri görünümü eklendi.
- Admin CP içine ödül teslim hataları görünümü eklendi.
- Başarısız ödül işlemlerini toplu olarak yeniden kontrol etme işlemi eklendi.
- Başarısız tek bir ödülü yeniden deneme işlemi eklendi.
- Admin CP içine kullanıcı bazlı davet detay ekranı eklendi.
- Kullanıcı detay ekranında kod, kod durumu, korunan kod sayısı, davet istatistikleri, ödüller, son davetler ve kod işlem geçmişi bir araya getirildi.
- Admin CP içine davet eden kullanıcı, davet edilen kullanıcı, durum ve tarih aralığına göre filtrelenebilen tüm davet kayıtları ekranı eklendi.
- 0.4.1 ve daha eski geliştirme sürümlerinden V1'e yükseltmede eksik kod ve ödül uygunluğu kontrolü eklendi.
- Proje belgeleri V1 davranışına göre güncellendi.
- GitHub doğrulama akışı V1 sürüm kimliğini denetleyecek şekilde güncellendi.
- PHP kaynaklarında yorum ve PHPDoc kullanılmaması kuralı PHP tokenizer ile otomatik olarak denetlenir.

### Güvenlik ve bütünlük

- Kod değiştirme, şüpheli davet inceleme ve ödül teslimi için mevcut veritabanı kilitleri korunur.
- Eski davet kodlarının başka kullanıcıya atanmasını engelleyen kalıcı kod koruması V1'in parçasıdır.
- Ham IP adresi saklanmaz; ağ kontrol değerleri yapılandırılabilir süre sonunda temizlenir.
- Kullanıcı grubu ödülleri XenForo'nun kendi kullanıcı grubu değişiklik servisi üzerinden uygulanır.
- Ödül olarak kullanılan kullanıcı grubunun yanlışlıkla silinmesi engellenir.
- Admin CP filtre sorguları parametreli sorgularla çalışır.
- QR oluşturma için harici API veya üçüncü parti ağ servisi kullanılmaz.

## 0.4.1 - 2026-08-22

### Eklendi

- Davet kodu sahibine ait ağ kontrol bilgisinin saklama tarihi
- Davet kodu sahibine ait eski ağ kontrol verilerini otomatik temizleyen arka plan görevi
- 0.4.0 sürümünden otomatik veritabanı yükseltme adımı

### Düzeltildi

- Davet kayıtlarının ağ kontrol verileri temizlenirken kod sahibine ait verinin süresiz kalması engellendi
- Ağ kontrol verileri temizlendikten sonra gerektiğinde yeniden güvenli şekilde oluşturulabilmesi sağlandı

## 0.4.0 - 2026-08-22

### Eklendi

- Daha önce kullanılmış davet kodlarını kalıcı olarak koruyan kod rezervasyon sistemi
- `xf_wrxt_referral_code_reservation` tablosu
- Eski kodların başka kullanıcıya yeniden atanmasını engelleyen kontrol
- Sistem için Admin CP üzerinden genel aktif/pasif anahtarı
- Davet sistemi kapatıldığında kullanıcı tarafındaki bağlantı ve kayıt alanlarının otomatik gizlenmesi
- Ödül olarak kullanılan XenForo kullanıcı grubunun yanlışlıkla silinmesini engelleyen koruma
- Kayıt formu ve kullanıcı menüsünde XenForo dil cümlesi desteği

### Düzeltildi

- Yetkili tarafından değiştirilen eski davet kodunun gelecekte başka kullanıcıya atanabilmesi engellendi
- Ödül geri alma işlemi başarısız olursa sonraki kontrolde tekrar denenmesi sağlandı
- Aynı kullanıcının ödül işlemlerinin aynı anda çakışması engellendi
- Sistem kapalıyken doğrudan bağlantı veya elle gönderilen kayıt verisiyle davet sisteminin kullanılabilmesi engellendi
- Kayıt formuna davet alanı ekleyen şablon değişikliği XenForo güncellemelerine karşı daha dayanıklı hale getirildi

## 0.3.0 - 2026-08-22

### Eklendi

- Davet hedeflerine bağlı gerçek ödül teslim sistemi
- `xf_wrxt_referral_reward` ödül geçmişi tablosu
- Görsel ödül desteği
- XenForo ek kullanıcı grubu ödülü desteği
- Kullanıcı panelinde Ödüllerim bölümü
- Ödül durumları: bekliyor, teslim edildi, geri alındı ve teslim hatası
- Geçerli davet sayısı düşerse ödülü geri alma seçeneği
- Mevcut kullanıcıların geçmiş davet sayılarına göre ödüllerini otomatik tamamlama görevi
- Günlük ödül uygunluğu kontrolü
- Admin CP üzerinden ödül türü ve kullanıcı grubu seçimi
- Ödül teslim istatistikleri

### Güvenlik ve bütünlük

- Aynı ödülün aynı kullanıcıya birden fazla kez verilmesi veritabanı seviyesinde engellendi
- Teslim edilmiş ödülü bulunan bir davet hedefinin kritik ödül ayarlarının sonradan değiştirilmesi engellendi
- Ödül geçmişi bulunan davet hedefinin silinmesi engellendi
- Kullanıcı silindiğinde ilgili ödül kayıtları temizleniyor
- Davet edilen kullanıcı silinirse davetçinin ödül durumu yeniden hesaplanıyor
- Yetkili inceleme sonucu ödül durumu anında güncelleniyor

## 0.2.0 - 2026-08-22

### Eklendi

- Davet durumlarını güvenli biçimde yeniden doğrulayan veritabanı kilitleri
- Geçerli davetlerin günlük bütünlük kontrolü
- Kodu eksik kullanıcıları günlük tamamlayan arka plan görevi
- Ağ kontrol verileri için otomatik saklama süresi ve temizleme görevi
- Admin CP üzerinden ağ kontrol verisi saklama süresi ayarı
- Büyük forumlar için parçalı arka plan işlemleri
- Davet durum sorgularını hızlandıran veritabanı indeksi
- 0.1.0 sürümünden otomatik yükseltme adımları
- PHP, JSON ve XML dosyaları için GitHub Actions doğrulaması

### Güvenlik ve düzeltmeler

- Kod değiştirme ve askıya alma işlemleri veritabanı işlemi içine alındı
- Kod değişikliği ile işlem geçmişi aynı anda güvenli şekilde kaydediliyor
- Askıya alınan kodun bekleyen davetleri inceleme kuyruğuna taşınıyor
- Askıdaki kod tekrar aktif edilmeden ilgili riskli kayıt onaylanamıyor
- Admin CP erişimi XenForo kullanıcı yönetimi izniyle sınırlandı
- Geçersiz manuel davet kodunun sessizce yok sayılması engellendi
- Davet bağlantısındaki kayıt bilgisinin tarayıcı çerezi kapalı olsa bile korunması sağlandı
- İlk geçerli davet bilgisinin başka bağlantılar tarafından değiştirilmesi engellendi
- Kullanıcı silme sırasında davet ve işlem geçmişi temizliği güçlendirildi
- Ham IP adresine ek olarak tek yönlü ağ kontrol değerleri de süre sonunda temizleniyor

## 0.1.0 - 2026-08-22

İlk geliştirme sürümü.

### Eklendi

- XenForo 2.3.x eklenti yapısı
- Her kullanıcı için benzersiz ve kalıcı davet kodu
- Davet koduna bağlı kişisel davet bağlantısı
- Manuel davet kodu ile kayıt desteği
- Davet bağlantısından kayıt bilgisini taşıma sistemi
- Mevcut kullanıcılar için otomatik kod oluşturma görevi
- Bir hesabın yalnızca tek davetçiye bağlanabilmesi
- Bekleyen, incelemede, geçerli ve reddedilmiş davet durumları
- Hesap yaşı ve mesaj sayısına göre otomatik geçerlilik
- Aynı ağdan tekrarlı kayıt kontrolü
- Şüpheli davetler için yetkili inceleme sistemi
- Davet kodu değiştirme, askıya alma ve etkinleştirme izinleri
- Yetkili kod işlemleri için işlem geçmişi
- Kullanıcı Davetlerim sayfası
- Toplam, geçerli, bekleyen ve incelemedeki davet istatistikleri
- İlerleme çubuğu ve ödül hedefleri
- Font Awesome ikon veya özel ödül görseli desteği
- Admin CP üzerinden sınırsız ödül hedefi yönetimi
- XenForo seçenek ve izin sistemi entegrasyonu
- Saatlik bekleyen davet doğrulama görevi
- XenForo tema yapısına uyumlu mobil arayüz
- Otomatik veritabanı kurulumu
