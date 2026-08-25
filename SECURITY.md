# Güvenlik Politikası

Warext Studios Davet Referans Sistemi kullanıcı kayıtları, davet ilişkileri, ödül teslimleri ve yetkili işlemleriyle doğrudan çalıştığı için güvenlik hataları öncelikli kabul edilir.

## Desteklenen sürüm

Güvenlik düzeltmeleri için desteklenen ana sürüm **V1 / 1.0.x** serisidir.

## Güvenlik ilkeleri

- Davet kodları güvenli rastgele değerlerden üretilir.
- Her kullanıcı için yalnızca bir aktif kod kaydı bulunur.
- Daha önce bir kullanıcıya ait olmuş davet kodu başka bir kullanıcıya tekrar atanamaz.
- Her davet edilen hesap yalnızca bir davetçiye bağlanabilir.
- Normal kullanıcılar kendi davet kodlarını değiştiremez veya askıya alamaz.
- Kod yönetimi ve şüpheli davet incelemesi ayrı XenForo izinleriyle korunur.
- Admin CP yönetim alanı XenForo kullanıcı yönetimi izniyle korunur.
- Şüpheli davetleri inceleyen kullanıcı kendi davetini onaylayamaz.
- İnceleme onayı minimum hesap yaşı ve mesaj şartlarını atlayamaz.
- Ham IP adresleri eklenti tablolarında saklanmaz.
- Ağ kontrollerinde XenForo global salt değeri ile HMAC-SHA256 kullanılır.
- Ağ kontrol değerleri yapılandırılabilir süre sonunda otomatik temizlenir.
- Kod sahibine ait ağ kontrol değeri de aynı saklama politikasına tabidir.
- Aynı ağdan tekrarlı kayıtlar otomatik ödüllendirilmez ve inceleme durumuna alınır.
- Askıya alınan kodlar yeni davetlerde kullanılamaz.
- Yetkili kod değişiklikleri işlem geçmişine kaydedilir.
- Kritik kod yönetimi, davet inceleme ve ödül işlemlerinde veritabanı işlemleri ve satır kilitleri kullanılır.
- Veritabanı benzersizlik kısıtları uygulama katmanına ek güvenlik sağlar.
- Admin CP veya API ile oluşturulan kullanıcılar tarayıcıdaki davet bilgisinden etkilenmez.
- İlk geçerli davet bilgisi sonraki davet bağlantıları tarafından sessizce değiştirilemez.
- Bir kullanıcı ve bir davet hedefi için yalnızca tek ödül kaydı oluşturulabilir.
- Kullanıcı grubu ödülleri doğrudan kullanıcı grup alanını değiştirmek yerine XenForo'nun kendi kullanıcı grubu değişiklik servisiyle uygulanır.
- Teslim edilmiş ödülü bulunan bir davet hedefinin kritik ödül ayarları sessizce değiştirilemez.
- Ödül olarak kullanılan kullanıcı grubunun yanlışlıkla silinmesi engellenir.
- Ödül teslim hatalarında kullanıcıya teknik hata dökümü gösterilmez; güvenli hata kodu kaydedilir ve gerçek hata XenForo hata kayıtlarına gönderilir.
- Admin CP içinde son kod değişiklikleri ve ödül teslim hataları görüntülenebilir.

## Gizlilik

Eklenti kötüye kullanım kontrolü için ham IP adresi saklamaz. Ağ karşılaştırması tek yönlü değerler üzerinden yapılır.

Varsayılan saklama süresi 90 gündür ve Admin CP üzerinden değiştirilebilir. Süresi dolan hem davet kayıtlarına hem de davet kodu sahibine ait ağ kontrol değerleri arka plan göreviyle temizlenir.

Davet kodu geçmişinin başka kullanıcıya devredilmesini engelleyen kod koruma kayıtları güvenlik ve veri bütünlüğü amacıyla kalıcıdır. Kullanıcı silinse bile daha önce ona ait olmuş davet kodunun başka kullanıcıya verilmemesi için bu kayıt korunur.

## Güvenlik açığı bildirimi

Güvenlik açığı bulunduğunda herkese açık issue içinde saldırının uygulanabilir ayrıntılarını paylaşmayın. Repo sahibiyle özel bir kanal üzerinden iletişim kurun ve mümkünse şu bilgileri ekleyin:

- Etkilenen sürüm
- XenForo ve PHP sürümü
- Açığın oluşması için gerekli koşullar
- Beklenen ve gerçekleşen davranış
- Güvenli şekilde hazırlanmış yeniden üretim adımları
- Varsa önerilen düzeltme

## Destek kapsamı

V1 ana sürümü güvenlik düzeltmeleri için önceliklidir. Eski 0.x geliştirme sürümleri yerine güncel V1 sürümüne yükseltilmesi önerilir.
