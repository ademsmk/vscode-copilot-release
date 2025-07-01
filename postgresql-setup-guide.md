# PostgreSQL Dışarıdan Erişim Ayarları

## Sizin IP Bilgileriniz:
- **Local IP:** 192.168.1.104
- **Gateway:** 192.168.1.1

## 1. postgresql.conf dosyasını düzenleyin:
# Dosya konumu: C:\Program Files\PostgreSQL\15\data\postgresql.conf (Windows)
# veya: /etc/postgresql/15/main/postgresql.conf (Linux)

# Bu satırı bulun ve değiştirin:
listen_addresses = '*'          # Önceden 'localhost' idi

# Port ayarını kontrol edin:
port = 5432                     # Varsayılan port

## 2. pg_hba.conf dosyasını düzenleyin:
# Dosya konumu: C:\Program Files\PostgreSQL\15\data\pg_hba.conf

# Bu satırı ekleyin (dikkatli olun, güvenlik riski!):
host    all             all             0.0.0.0/0                md5

# Veya daha güvenli olması için sadece local ağ:
host    all             all             192.168.1.0/24           md5

## 3. PostgreSQL servisini yeniden başlatın:
# Windows'ta: Services.msc'den PostgreSQL servisi
# veya: net stop postgresql-x64-15 && net start postgresql-x64-15

## 4. Windows Firewall'da 5432 portunu açın:
# Denetim Masası > Sistem ve Güvenlik > Windows Defender Güvenlik Duvarı
# Gelişmiş Ayarlar > Gelen Kurallar > Yeni Kural > Port > TCP 5432

## 5. Public IP adresinizi öğrenin:
# https://whatismyipaddress.com/ adresinden kontrol edin

## Test Komutları:
# sudo apt-get update && sudo apt-get install -y postgresql postgresql-contrib    postgreesql kur
# sudo service postgresql start
# sudo su - postgres -c "psql -c \"ALTER USER postgres PASSWORD '521300';\""   oldu

## tablo oluşturuldu
# sudo su - postgres -c "psql -d hotelyonetim -f /workspaces/vscode-copilot-release/database/staff_table.sql"  

## php sunucusunu durdurma
# pkill -f "php -S"    

## php sunucusunu başlatma
# cd /workspaces/vscode-copilot-release && /usr/bin/php -S 0.0.0.0:8000

## api çağrısı
{
  "status": "success",
  "message": "PostgreSQL bağlantısı başarılı",
  "database_info": {
    "name": "hotelyonetim",
    "port": "5432",
    "version": "PostgreSQL 16.9 (Ubuntu 16.9-0ubuntu0.24.04.1) on x86_64-pc-linux-gnu, compiled by gcc (Ubuntu 13.3.0-6ubuntu2~24.04) 13.3.0, 64-bit",
    "total_tables": 1,
    "tables": [
      "staff"
    ],
    "missing_tables": [
      "housekeeping_tasks",
      "maintenance_requests",
      "rooms",
      "room_types"
    ]
  }
}