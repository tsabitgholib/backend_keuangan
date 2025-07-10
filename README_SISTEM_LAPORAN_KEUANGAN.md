# SISTEM LAPORAN KEUANGAN - BACKEND API

## 📋 DESKRIPSI

Sistem laporan keuangan berbasis Laravel yang menyediakan API lengkap untuk manajemen akuntansi, mulai dari setup COA (Chart of Accounts) hingga generasi laporan keuangan standar.

## 🚀 FITUR UTAMA

### ✅ COA Management

-   Struktur akun 3 level (Induk, Anak, Detail)
-   5 tipe akun: Asset, Kewajiban, Ekuitas, Pendapatan, Beban
-   Validasi hierarki dan relasi parent-child
-   Endpoint khusus untuk level 2 dan 3

### ✅ Periode Akuntansi

-   Manajemen periode akuntansi
-   Sistem tutup buku otomatis
-   Transfer saldo antar periode

### ✅ Saldo Awal

-   Input saldo awal untuk semua akun
-   Validasi keseimbangan debit-kredit
-   Input batch untuk efisiensi

### ✅ Jurnal Transaksi

-   Pencatatan transaksi keuangan
-   Validasi keseimbangan debit-kredit
-   Status Draft dan Diposting
-   Detail transaksi per akun

### ✅ Laporan Keuangan

-   **Neraca Saldo**: Trial balance dengan hierarki
-   **Posisi Keuangan**: Balance sheet (Neraca)
-   **Laporan Aktivitas**: Income statement (Laba Rugi)
-   **Buku Besar**: General ledger per akun
-   **Perbandingan Periode**: Analisis komparatif

## 🛠️ TEKNOLOGI

-   **Framework**: Laravel 10
-   **Database**: MySQL
-   **Authentication**: Laravel Sanctum
-   **API**: RESTful API
-   **Validation**: Laravel Validation
-   **Authorization**: Role-based access control

## 📁 STRUKTUR PROJEK

```
backend_keuangan/
├── app/
│   ├── Http/Controllers/
│   │   ├── COAController.php
│   │   ├── PeriodeController.php
│   │   ├── SaldoAwalController.php
│   │   ├── JurnalController.php
│   │   └── LaporanController.php
│   └── Models/
│       ├── Akun.php
│       ├── Periode.php
│       ├── SaldoAwal.php
│       ├── Jurnal.php
│       └── JurnalDetail.php
├── routes/
│   └── api.php
├── database/
│   └── migrations/
└── resources/
    └── views/
```

## 🔧 INSTALASI

### Prerequisites

-   PHP 8.1+
-   Composer
-   MySQL 8.0+
-   Laravel 10

### Setup

```bash
# Clone repository
git clone <repository-url>
cd backend_keuangan

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keuangan_db
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start development server
php artisan serve
```

## 🔐 AUTHENTICATION

Sistem menggunakan Laravel Sanctum untuk API authentication.

```bash
# Login
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Use token in headers
Authorization: Bearer <token>
```

## 📚 DOKUMENTASI LENGKAP

### 📖 Dokumentasi Alur

-   `DOKUMENTASI_ALUR_LAPORAN_KEUANGAN.md` - Alur lengkap input laporan keuangan
-   `CONTOH_IMPLEMENTASI_PRAKTIS.md` - Contoh implementasi praktis
-   `API_DOCUMENTATION.md` - Dokumentasi API teknis

### 🎯 Endpoint Utama

#### COA Management

```bash
GET    /api/coa                    # Get semua COA
GET    /api/coa/level-2-3         # Get COA level 2 dan 3
GET    /api/coa/tree              # Get struktur tree COA
POST   /api/coa/create            # Create COA baru
PUT    /api/coa/{id}              # Update COA
DELETE /api/coa/{id}              # Delete COA
```

#### Periode Management

```bash
GET    /api/periode               # Get semua periode
POST   /api/periode               # Create periode
PUT    /api/periode/{id}          # Update periode
POST   /api/periode/{id}/tutup    # Tutup periode
POST   /api/periode/{id}/activate # Aktivasi periode
```

#### Saldo Awal

```bash
GET    /api/saldo-awal            # Get saldo awal
POST   /api/saldo-awal            # Input saldo awal
POST   /api/saldo-awal/batch      # Input saldo awal batch
PUT    /api/saldo-awal/{id}       # Update saldo awal
DELETE /api/saldo-awal/{id}       # Delete saldo awal
```

#### Jurnal

```bash
GET    /api/jurnal                # Get semua jurnal
POST   /api/jurnal                # Create jurnal
GET    /api/jurnal/{id}           # Get detail jurnal
PUT    /api/jurnal/{id}           # Update jurnal
DELETE /api/jurnal/{id}           # Delete jurnal
```

#### Laporan

```bash
GET /api/laporan/neraca-saldo         # Neraca saldo
GET /api/laporan/posisi-keuangan      # Posisi keuangan (Neraca)
GET /api/laporan/aktivitas            # Laporan aktivitas (Laba Rugi)
GET /api/laporan/buku-besar           # Buku besar
GET /api/laporan/perbandingan-periode # Perbandingan periode
```

## 📊 CONTOH PENGGUNAAN

### 1. Setup COA

```bash
# Buat akun induk
POST /api/coa/create
{
  "account_code": "1000",
  "account_name": "Aktiva",
  "level": 1,
  "account_type": "Asset",
  "is_active": true
}
```

### 2. Input Saldo Awal

```bash
POST /api/saldo-awal/batch
{
  "periode_id": 1,
  "saldo_awal": [
    {
      "akun_id": 1,
      "jumlah": 1000000,
      "tipe_saldo": "Debit"
    }
  ]
}
```

### 3. Input Jurnal

```bash
POST /api/jurnal
{
  "tanggal": "2024-01-15",
  "keterangan": "Penjualan tunai",
  "periode_id": 1,
  "status": "Diposting",
  "details": [
    {
      "akun_id": 1,
      "debit": 5000000,
      "kredit": 0,
      "keterangan": "Penerimaan kas"
    },
    {
      "akun_id": 10,
      "debit": 0,
      "kredit": 5000000,
      "keterangan": "Pendapatan penjualan"
    }
  ]
}
```

### 4. Generate Laporan

```bash
# Neraca Saldo
GET /api/laporan/neraca-saldo?periode_id=1&level=3

# Posisi Keuangan
GET /api/laporan/posisi-keuangan?periode_id=1&level=3

# Laporan Aktivitas
GET /api/laporan/aktivitas?periode_id=1&level=3
```

## ✅ VALIDASI SISTEM

### Prinsip Akuntansi

-   ✅ Persamaan Dasar: Aset = Kewajiban + Ekuitas
-   ✅ Neraca Saldo: Total Debit = Total Kredit
-   ✅ Laba Rugi: Laba = Pendapatan - Beban

### Validasi Bisnis

-   ✅ COA: Kode unik, hierarki valid, tipe sesuai
-   ✅ Saldo Awal: Debit = Kredit, input sekali saja
-   ✅ Jurnal: Debit = Kredit, akun aktif, periode aktif
-   ✅ Periode: Tanggal valid, tidak tumpang tindih

## 🔒 KEAMANAN

-   **Authentication**: Laravel Sanctum
-   **Authorization**: Role-based access control
-   **Validation**: Input validation yang ketat
-   **Rate Limiting**: 60 requests/minute, 1000/hour
-   **CORS**: Proper CORS configuration
-   **Logging**: Audit trail untuk semua transaksi

## 📈 PERFORMANCE

-   **Database**: Optimized queries dengan indexing
-   **Caching**: Redis untuk data yang sering diakses
-   **Pagination**: Pagination untuk data besar
-   **Lazy Loading**: Eager loading untuk relasi

## 🧪 TESTING

### Unit Tests

```bash
php artisan test
```

### API Tests

```bash
php artisan test --filter=ApiTest
```

### Postman Collection

Import file `keuangan-api.postman_collection.json` untuk testing manual.

## 🚀 DEPLOYMENT

### Production Checklist

-   [ ] Set `APP_ENV=production`
-   [ ] Set `APP_DEBUG=false`
-   [ ] Configure database credentials
-   [ ] Set up SSL certificate
-   [ ] Configure web server (Nginx/Apache)
-   [ ] Set up monitoring (Laravel Telescope)
-   [ ] Configure backup strategy

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🤝 CONTRIBUTING

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📝 CHANGELOG

### v1.0.0 (2024-01-01)

-   ✅ Setup COA 3 level
-   ✅ Manajemen periode akuntansi
-   ✅ Input saldo awal
-   ✅ Pencatatan jurnal transaksi
-   ✅ Generasi laporan keuangan standar
-   ✅ Validasi prinsip akuntansi
-   ✅ API documentation lengkap

## 📄 LICENSE

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 SUPPORT

-   **Email**: support@example.com
-   **Documentation**: [Link ke dokumentasi]
-   **Issues**: [GitHub Issues](https://github.com/username/repo/issues)

## 🙏 ACKNOWLEDGMENTS

-   Laravel Framework
-   MySQL Database
-   Laravel Sanctum
-   Postman for API testing
-   Community contributors

---

**Sistem ini dirancang untuk memenuhi standar akuntansi yang berlaku dan dapat digunakan untuk keperluan bisnis yang memerlukan pelaporan keuangan yang akurat dan terpercaya.**
