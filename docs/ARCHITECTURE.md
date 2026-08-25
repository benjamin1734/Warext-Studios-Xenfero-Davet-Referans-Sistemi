# Teknik Mimari

## Sürüm

V1 — `1.0.0`

## Eklenti kimliği

`WarextStudios/ReferralSystem`

Kaynak dizini:

`upload/src/addons/WarextStudios/ReferralSystem`

## Temel yapı

Eklenti XenForo çekirdek dosyalarını değiştirmez. XenForo'nun standart eklenti yapısı üzerinden sayfalar, kullanıcı işlemleri, veritabanı kayıtları, arka plan görevleri, izinler, seçenekler, dil cümleleri ve şablonlar ekler.

Kritik işlemler yalnızca arayüzde kontrol edilmez. Yetki, veri doğrulama ve bütünlük kontrolleri sunucu tarafında tekrar uygulanır.

## Veritabanı tabloları

### `xf_wrxt_referral_code`

Her kullanıcı için tek ve kalıcı davet kodunu tutar.

Önemli alanlar:

- Kullanıcı numarası
- Davet kodu
- Kod sahibinin ağ kontrol değeri
- Ağ kontrol değerinin oluşturulma tarihi
- Kodun aktif veya askıda durumu
- Son değişiklik bilgileri
- Askıya alma nedeni

`user_id` ana anahtar, `code` ise benzersiz anahtar olarak kullanılır.

### `xf_wrxt_referral_code_reservation`

Bir davet kodunun geçmişte hangi kullanıcıya ait olduğunu kalıcı olarak tutar.

Amaç, yetkili tarafından değiştirilmiş veya artık aktif olmayan eski bir davet kodunun başka bir kullanıcıya verilmesini engellemektir.

Kod bir kez bir kullanıcıya ait olduysa daha sonra başka kullanıcıya devredilemez. Kullanıcı silinse bile bu koruma kaydı korunur.

### `xf_wrxt_referral`

Davetçi ile kayıt olan kullanıcı arasındaki ilişkiyi tutar.

`invited_user_id` benzersiz olduğu için bir hesap yalnızca tek davetçiye bağlanabilir.

Davet durumları:

- `pending` — şartları henüz tamamlamamış
- `review` — yetkili incelemesi gerekiyor
- `valid` — geçerli davet
- `rejected` — reddedilmiş davet

Büyük tablolarda durum kontrolünün hızlı yapılabilmesi için uygun indeksler kullanılır.

### `xf_wrxt_referral_milestone`

Kullanıcının ilerleme çubuğunda gösterilecek davet ve ödül hedeflerini tutar.

Her hedef için:

- Gerekli geçerli davet sayısı
- Ödül adı
- Açıklama
- İkon veya görsel
- Ödül türü
- Kullanıcı grubu ödülü
- Şart kaybedildiğinde ödülün geri alınıp alınmayacağı
- Görüntüleme sırası
- Aktiflik durumu

saklanır.

Tablo adı geliştirme döneminden kalan teknik adlandırmayı korur. Kullanıcı ve yönetici arayüzünde bu yapı “davet hedefi” veya “ödül hedefi” olarak gösterilir.

### `xf_wrxt_referral_reward`

Kullanıcılara verilen ödüllerin geçmişini tutar.

Aynı kullanıcı ve aynı davet hedefi için yalnızca tek ödül kaydı oluşturulabilir.

Ödül durumları:

- `pending` — teslim bekliyor
- `granted` — teslim edildi
- `revoked` — geri alındı
- `failed` — işlem sırasında hata oluştu

Ödülün oluşturulma, teslim, geri alma ve son deneme tarihleri saklanır. Hata durumlarında güvenli hata kodu tutulur.

### `xf_wrxt_referral_code_log`

Yetkili kullanıcıların davet kodu üzerinde yaptığı değişikliklerin işlem geçmişini tutar.

Kod değiştirme, aktiflik değiştirme ve askıya alma gibi işlemler burada kayıt altına alınır. V1 ile son kayıtlar Admin CP içinde doğrudan görüntülenebilir.

## Kullanıcı akışı

1. Kullanıcı hesabı oluşturulduğunda benzersiz davet kodu hazırlanır.
2. Mevcut kullanıcıların eksik kodları arka plan görevleriyle tamamlanır.
3. Kullanıcı Davetlerim sayfasından kodunu ve bağlantısını görür.
4. Davet bağlantısı aktif kodu doğrular ve kayıt bilgisine aktarır.
5. İstenirse kayıt formunda davet kodu manuel olarak girilebilir.
6. Kayıt tamamlandığında yeni hesap yalnızca tek davetçiye bağlanır.
7. Şüpheli durum yoksa kayıt bekleyen olarak başlar.
8. Minimum hesap yaşı ve mesaj şartı tamamlandığında davet geçerli hale gelir.
9. Şüpheli kayıtlar yetkili incelemesi yapılmadan geçerli sayılmaz.
10. Geçerli davet sayısı değiştiğinde kullanıcının ödül uygunluğu yeniden hesaplanır.
11. Gerekli davet sayısına ulaşılmışsa ödül yalnızca bir kez verilir.
12. Ayar gerektiriyorsa geçerli davet sayısı hedefin altına düştüğünde ödül geri alınır.

## Davet kodu üretimi

Kodlar kullanıcı numarasından veya tahmin edilebilir bir değerden türetilmez.

Kod üretiminde `random_int` kullanılır ve karışıklığı azaltmak için benzer karakterlerden kaçınan harf-rakam kümesi tercih edilir.

Kod oluşturulurken:

- Aktif kod tablosu kontrol edilir.
- Geçmiş kod koruma kayıtları kontrol edilir.
- Veritabanı benzersizlik kısıtları son güvenlik katmanı olarak kullanılır.

## Kayıt ilişkilendirmesi

Davet bağlantısına tıklayan ziyaretçinin davet bilgisi kayıt ekranına taşınır.

İlk geçerli davet bilgisi korunur. Kullanıcının daha sonra başka bir davet bağlantısına tıklaması mevcut geçerli davet sahibini sessizce değiştirmez.

Kayıt sırasında manuel bir kod girilmişse kod sunucu tarafında tekrar doğrulanır.

Admin CP veya API üzerinden açılan kullanıcı hesapları normal ziyaretçi tarayıcısındaki davet bilgisinden etkilenmez.

## Kötüye kullanım kontrolü

Aynı ağdan çok sayıda hesap açılması gibi durumlar için ek kontrol bulunur.

Ham IP adresi saklanmaz.

Ağ adresi XenForo'nun global salt değeri ile HMAC-SHA256 kullanılarak tek yönlü değere dönüştürülür.

Bu değerler yalnızca kötüye kullanım karşılaştırması amacıyla kullanılır ve Admin CP'de belirlenen saklama süresi sonunda temizlenir.

Kod sahibine ait ağ kontrol değeri de aynı saklama politikasına tabidir.

## Ödül teslimi

### Görsel ödül

Kullanıcının ödül geçmişine kazanılmış ödül olarak kaydedilir.

### Ek kullanıcı grubu ödülü

Kullanıcının mevcut ek kullanıcı grupları doğrudan ezilmez.

XenForo'nun `XF:User\UserGroupChange` servisi kullanılır. Her davet hedefi için kendine özel değişiklik anahtarı oluşturulur.

Bu sayede başka sistemlerin kullanıcı grubu işlemleriyle gereksiz çakışma azaltılır.

Ödül olarak kullanılan XenForo kullanıcı grubunun yanlışlıkla silinmesi sunucu tarafında engellenir.

## Eşzamanlı işlem güvenliği

Kod yönetimi, şüpheli davet incelemesi ve ödül işlemlerinde gerektiğinde veritabanı işlemleri ve satır kilitleri kullanılır.

Amaç:

- Aynı davetin iki kez işlenmesini engellemek
- Aynı ödülün iki kez verilmesini engellemek
- Kod değişikliği ile işlem geçmişinin birbirinden kopmasını engellemek
- Aynı kullanıcının aynı anda çalışan ödül görevlerinin çakışmasını engellemek

## Arka plan görevleri

Büyük forumlarda tek bir web isteğinin binlerce kullanıcıyı işlemesini engellemek için işlemler XenForo arka plan görevleriyle parçalara bölünür.

Arka planda:

- Bekleyen davetler kontrol edilir.
- Daha önce geçerli olmuş davetler tekrar doğrulanır.
- Kodu eksik kullanıcılar tamamlanır.
- Ödül uygunlukları yeniden hesaplanır.
- Süresi dolmuş ağ kontrol değerleri temizlenir.

Saatlik görevler daha hafif kayıtları hedefler. Daha kapsamlı bütünlük kontrolleri günlük çalışır.

V1'e yükseltme sonrasında eksik davet kodu ve ödül uygunluğu kontrolü bir kez daha kuyruğa eklenir.

## Yönetim ve gözlem

Admin CP ekranı şu bilgileri tek noktada toplar:

- Sistem ve davet istatistikleri
- Aktif kod ve korunan eski kod sayıları
- Ödül teslim durumları
- Davet ve ödül hedefleri
- Son kod değişiklikleri
- Son ödül teslim hataları

Başarısız ödül işlemleri yönetici tarafından tekrar kontrol kuyruğuna gönderilebilir. Gerçek teknik hata XenForo hata kayıtlarında tutulurken yönetim ekranında yalnız güvenli hata nedeni gösterilir.

## Yetki modeli

Kullanıcı grubu izinleri:

- Davet kodlarını yönetme
- Şüpheli davetleri inceleme

Admin CP tarafındaki yönetim ekranı ayrıca XenForo kullanıcı yönetimi admin iznini kontrol eder.

Kritik işlemler yalnızca arayüzde buton gizleyerek korunmaz. Sayfa ve servis katmanında yeniden yetki kontrolü yapılır.

## Tema ve dil uyumluluğu

Arayüz XenForo stil değişkenlerini kullanır. Sabit tema renklerine mümkün olduğunca bağlı değildir.

Davet bağlantısı ve kod alanı esnek genişlik kullanır; uzun bağlantı metni kopyalama butonunu taşırmaz ve mobil görünümde alanın dışına çıkmaz.

Kayıt formu, kullanıcı menüsü ve temel hata mesajları XenForo dil cümlesi sistemiyle çalışır. Böylece farklı dil paketleri çekirdek dosyalara dokunmadan çeviri yapabilir.
