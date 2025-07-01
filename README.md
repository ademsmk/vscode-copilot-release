# Otel Yönetim Sistemi

Modern, dinamik ve profesyonel otel yönetim sistemi. PostgreSQL veritabanı ile tam entegre çalışır.

## Özellikler

- **Room Rack**: Dinamik oda durumu ve yönetimi
- **Check-In/Check-Out**: Otomatik rezervasyon işlemleri
- **Rezervasyon Yönetimi**: Dinamik rezervasyon oluşturma, düzenleme ve takibi
- **Personel Yönetimi**: Tam CRUD işlemleri ile personel yönetimi
- **Dashboard**: Gerçek zamanlı istatistikler ve raporlar
- **Housekeeping**: Temizlik görevleri yönetimi
- **Maintenance**: Bakım talepleri yönetimi

## Kurulum

### 1. Gereksinimler
- PHP 7.4 veya üzeri
- PostgreSQL 12 veya üzeri
- Web server (Apache/Nginx) veya PHP built-in server

### 2. Veritabanı Kurulumu
```bash
# PostgreSQL'e bağlan
psql -U postgres

# Veritabanı oluştur
CREATE DATABASE hotelyonetim;

# Veritabanına geç
\c hotelyonetim

# Şemayı yükle
\i database/postgresql_tables.sql
\i database/reservations_table.sql
\i database/staff_table.sql
\i database/housekeeping_tables.sql
\i database/maintenance_requests_table.sql

# Örnek verileri yükle
\i database/rooms_data.sql
\i database/housekeeping_tasks.sql
```

### 3. Konfigürasyon
`backend/config/database.php` dosyasındaki veritabanı ayarlarını güncelleyin:

```php
private $host = "localhost";
private $db_name = "hotelyonetim";
private $username = "postgres";
private $password = "your_password";
```

### 4. Sunucu Başlatma
```bash
# PHP built-in server
php -S localhost:8080

# Veya Apache/Nginx kullanın
```

### 5. Erişim
- Ana sayfa: `http://localhost:8080/frontend/pages/index.html`
- Room Rack: `http://localhost:8080/frontend/pages/room-rack.html`
- Rezervasyonlar: `http://localhost:8080/frontend/pages/reservations.html`
- Personel: `http://localhost:8080/frontend/pages/staff-management.html`

## Klasör Yapısı

```
hotel-yonetim-sistemi/
├── backend/
│   ├── api/              # REST API endpoints
│   ├── config/           # Veritabanı konfigürasyonu
│   ├── controllers/      # İş mantığı kontrolcüleri
│   └── models/           # Veritabanı modelleri
├── frontend/
│   ├── pages/            # HTML sayfaları
│   └── assets/
│       ├── css/          # Stil dosyaları
│       └── js/           # JavaScript dosyaları
└── database/             # SQL şema ve örnek veriler
```

## Teknolojiler

- **Backend**: PHP 7.4+, PostgreSQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **API**: RESTful JSON API
- **Database**: PostgreSQL

## Geliştirici Notları

- Tüm veriler PostgreSQL veritabanından dinamik olarak yüklenir
- API endpoint'leri `/backend/api/` klasöründe bulunur
- Frontend JavaScript dosyaları modüler yapıdadır
- Bootstrap 5 ve Font Awesome kullanılır

## Lisans

Bu proje özel kullanım içindir.

## İletişim

Geliştirici: ADEM SAMUK
Email: ademsmk@users.noreply.github.com
