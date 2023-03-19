
# Proje detayları


    1. Sipariş oluşturmak için servis.
    2. Aktif Kampanyalar için servis.
    3. Sipariş detayı görüntülemek için servis.

## Kurulum

Projeyi klonlayın

```bash
  git clone git@github.com:umutpamuk/DR-task.git
```

Proje dizinine gidin

```bash
  cd DR-task
```

Gerekli paketleri yükleyin

```bash
  composer install
```

Sunucuyu çalıştırın

```bash
  php artisan serve
```

Proje kök dizininde bulunan .env dosyasını açarak veritabanı bilgilerini güncelleyiniz.
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=DR
DB_USERNAME=root
DB_PASSWORD=
```

Tabloları oluşturun

```bash
  php artisan migrate
```
Veritabanına demo verilerin oluşturun

```bash
  php artisan db:seed
```

## API Dökümantasyonu



```http
  https://documenter.getpostman.com/view/21222352/2s93JzMLfU
```

  
