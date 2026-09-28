# TimNhaDat.site — CITC BDS

Website quản lý và đăng tin bất động sản, gồm trang công khai, khu vực thành viên và hệ thống quản trị. Dự án có mã Laravel đang được chuyển đổi, nhưng **ứng dụng chạy thực tế hiện tại là PHP MVC trong thư mục `legacy/`**. File `index.php` ở thư mục gốc chuyển toàn bộ request sang `legacy/public/index.php` để giữ đầy đủ chức năng hiện có.

## Mục lục

- [Chức năng chính](#chức-năng-chính)
- [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
- [Cài đặt nhanh bằng Laragon](#cài-đặt-nhanh-bằng-laragon)
- [Tài khoản dùng thử](#tài-khoản-dùng-thử)
- [Cấu hình môi trường](#cấu-hình-môi-trường)
- [Cơ sở dữ liệu](#cơ-sở-dữ-liệu)
- [Cách sử dụng website](#cách-sử-dụng-website)
  - [Đăng tin dành cho thành viên](#đăng-tin-dành-cho-thành-viên)
  - [Bảng giá VIP](#bảng-giá-vip)
  - [Luồng UP tin](#luồng-up-tin)
  - [Xác thực email và OTP](#xác-thực-email-và-otp)
- [Cấu hình các dịch vụ](#cấu-hình-các-dịch-vụ)
- [Cấu trúc dự án](#cấu-trúc-dự-án)
- [Xử lý lỗi thường gặp](#xử-lý-lỗi-thường-gặp)
- [Bảo mật và triển khai](#bảo-mật-và-triển-khai)

## Chức năng chính

### Người truy cập

- Xem và tìm kiếm tin bất động sản theo loại, khu vực và nhu cầu.
- Xem tin đăng, dự án, tin tức và bảng giá dịch vụ.
- Đăng ký, đăng nhập và liên hệ tư vấn.
- Sử dụng trợ lý AI tư vấn bất động sản.

### Thành viên

- Quản lý hồ sơ cá nhân.
- Xác thực email bằng OTP trong popup dùng chung với luồng quên mật khẩu.
- Đăng và chỉnh sửa tin bất động sản; dữ liệu biểu mẫu được giữ lại khi validation không hợp lệ.
- Theo dõi trạng thái duyệt tin.
- Chọn tin thường hoặc VIP 1–VIP 5 theo bảng giá cấu hình.
- Mua lượt UP và làm mới tin đang hiển thị.
- Thao tác với tin qua popup: chỉnh sửa, UP, ẩn/hiện lại, gia hạn VIP và xóa tin.
- Quản lý ví, giao dịch, gói dịch vụ và lịch sử hoạt động.

### Quản trị viên

- Dashboard tổng quan.
- Quản lý dự án, danh mục, tin tức và người dùng.
- Duyệt tin đăng và xử lý yêu cầu tư vấn CRM.
- Quản lý ví, giao dịch, bảng giá và phân quyền.
- Theo dõi báo cáo, xuất Excel/PDF theo mẫu trình ký.
- Cấu hình SEO, PayOS, Google Maps, email và tải lên.
- Sao lưu dữ liệu, quản lý cache và nhật ký hệ thống.

## Yêu cầu hệ thống

- Windows với Laragon được khuyến nghị.
- Apache có bật `mod_rewrite`.
- PHP 8.3 trở lên.
- MySQL hoặc MariaDB.
- Các extension PHP: `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `session` và `zip`.
- Composer chỉ cần khi phát triển phần Laravel hoặc cập nhật thư viện.
- Node.js/npm chỉ cần khi build lại asset Vite của phần Laravel.

## Cài đặt nhanh bằng Laragon

### 1. Chép mã nguồn

Đặt dự án tại:

```text
D:\laragon\www\CITC_BDS
```

Khởi động **Apache** và **MySQL** trong Laragon. Với cấu hình mặc định, website truy cập tại:

```text
http://localhost/CITC_BDS
```

> Không cần chạy `php artisan serve` để sử dụng phiên bản website hiện tại.

### 2. Tạo file cấu hình

Nếu chưa có `legacy/.env`, chạy PowerShell tại thư mục dự án:

```powershell
Copy-Item legacy\.env.example legacy\.env
```

Sau đó cập nhật thông tin database và địa chỉ website trong `legacy/.env`.

### 3. Tạo database

Mở HeidiSQL/phpMyAdmin, tạo database:

```sql
CREATE DATABASE citcbds
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Nhập file **`citcbds.sql`** ở thư mục gốc vào database này. File chứa đầy đủ cấu trúc, dữ liệu mẫu và các tài khoản dùng thử ghi trong README. Các bản sao lưu do hệ thống tạo thêm nằm tại `legacy/storage/backups/`.

### 4. Kiểm tra quyền ghi

PHP/Apache cần quyền ghi vào:

```text
public/uploads/
legacy/logs/
legacy/storage/
```

### 5. Mở website

- Trang chủ: `http://localhost/CITC_BDS/`
- Đăng nhập: `http://localhost/CITC_BDS/?login=1`
- Quản trị: `http://localhost/CITC_BDS/admin`

Nếu dự án dùng virtual host của Laragon, sửa `URL_ROOT` trong `legacy/.env` đúng với domain local, ví dụ `http://citc-bds.test`.

## Tài khoản dùng thử

Các tài khoản dưới đây dành cho giáo viên kiểm tra chức năng trên môi trường local. Tất cả tài khoản đã được kích hoạt và xác thực sẵn.

| Vai trò | Tài khoản | Mật khẩu | Khu vực kiểm tra |
|---|---|---|---|
| Admin (Quản trị tối cao) | `Admin` | `123` | `http://localhost/CITC_BDS/admin` |
| Thành viên 1 | `chitran15111996@gmail.com` | `123456` | `http://localhost/CITC_BDS/nguoi-dung/profile` |
Trang đăng nhập chung: `http://localhost/CITC_BDS/?login=1`.

> Các tài khoản này chỉ phục vụ chấm bài/demo trong database đi kèm dự án. Không sử dụng các mật khẩu trên khi triển khai Internet; hãy xóa hoặc đổi mật khẩu tài khoản demo trước khi đưa hệ thống lên production.

## Cấu hình môi trường

File cấu hình chính của ứng dụng đang chạy là **`legacy/.env`**, không phải `.env` ở thư mục gốc.

```env
APP_ENV=local
APP_DEBUG=false
URL_ROOT=http://localhost/CITC_BDS
APP_TIMEZONE=Asia/Ho_Chi_Minh
SITE_NAME=TimNhaDat.site

DB_HOST=localhost
DB_NAME=citcbds
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

PAYOS_CLIENT_ID=
PAYOS_API_KEY=
PAYOS_CHECKSUM_KEY=
PAYOS_ENDPOINT=https://api-merchant.payos.vn

GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.5-flash
GEMINI_SYSTEM_INSTRUCTION="Bạn là nhân viên tư vấn khách hàng chuyên nghiệp của TimNhaDat.site."
```

Lưu ý:

- Không thêm khoảng trắng quanh dấu `=`.
- `URL_ROOT` không có dấu `/` ở cuối.
- Sau khi đổi `.env`, tải lại trang; nếu dữ liệu cũ vẫn còn, xóa cache trong trang quản trị.
- Không đưa `.env` hoặc API key lên Git/GitHub.

## Cơ sở dữ liệu

Ứng dụng hiện tại dùng MySQL qua lớp PDO của PHP MVC. Tên database mặc định là `citcbds`.

### Nhập dữ liệu bằng HeidiSQL

1. Mở Laragon → **Database**.
2. Tạo database `citcbds` với charset `utf8mb4`.
3. Chọn database vừa tạo.
4. Chọn **File → Run SQL file**.
5. Chọn `citcbds.sql` ở thư mục gốc và chờ nhập hoàn tất.
6. Kiểm tra lại `DB_NAME`, `DB_USER`, `DB_PASS` trong `legacy/.env`.

### Sao lưu trước khi thay đổi

Trong trang quản trị, vào **Cài đặt → Sao lưu hệ thống** và chọn loại sao lưu phù hợp. Luôn sao lưu database trước khi chạy file migration thủ công hoặc thay đổi cấu trúc bảng.

File migration bổ sung được đặt tại:

```text
database/migrations/
```

Không chạy lại một migration đã áp dụng nếu chưa kiểm tra cấu trúc database.

## Cách sử dụng website

### Quản lý tin đăng

1. Đăng nhập tài khoản quản trị.
2. Vào **Dự án/Tin đăng**.
3. Tạo mới hoặc chỉnh sửa nội dung, giá, khu vực, hình ảnh và thông tin liên hệ.
4. Chọn trạng thái phù hợp rồi lưu.
5. Kiểm tra tin ngoài trang chủ sau khi duyệt.

### Đăng tin dành cho thành viên

1. Đăng nhập bằng tài khoản đã xác thực email.
2. Vào **Thành viên → Đăng tin BĐS**.
3. Nhập thông tin cơ bản, địa phương, giá, diện tích, nội dung, vị trí bản đồ và thông tin liên hệ.
4. Tải lên ít nhất một ảnh hợp lệ.
5. Chọn tin thường hoặc cấp VIP và số ngày nếu muốn mua VIP.
6. Nhập captcha, xác nhận chi phí rồi gửi tin chờ quản trị viên duyệt.

Khi backend trả về lỗi validation, các trường chữ, số, lựa chọn địa phương và tọa độ bản đồ được khôi phục. Captcha luôn được tạo lại. Trình duyệt không cho tự động gán lại file sau một lần tải lại trang; riêng lỗi định dạng số điện thoại được chặn trước khi gửi để giữ nguyên ảnh đang chọn.

Trang quản lý tin dùng popup **Thao tác tin đăng**. Các lựa chọn xuất hiện tùy trạng thái:

- **Chỉnh sửa:** cập nhật nội dung tin.
- **UP tin:** chỉ xuất hiện với tin đang hiển thị.
- **Ẩn tin/Hiện lại:** thay đổi trạng thái hiển thị; tin hiện lại sẽ được gửi duyệt.
- **Gia hạn VIP 7 ngày:** chỉ xuất hiện với tin có cấp VIP.
- **Xóa tin:** xóa mềm tin khỏi danh sách hoạt động.

Hệ thống không cung cấp chức năng sao chép tin.

### Bảng giá VIP

Giá mặc định theo ngày được đọc từ `legacy/config/settings.json` và có thể thay đổi trong trang quản trị bảng giá:

| Cấp tin | Phí mỗi ngày |
|---|---:|
| VIP 5 | 30.000đ |
| VIP 4 | 25.000đ |
| VIP 3 | 20.000đ |
| VIP 2 | 15.000đ |
| VIP 1 | 10.000đ |
| Tin thường | Miễn phí |

Cấp VIP và lượt UP là hai dịch vụ độc lập. UP tin không biến tin thường thành tin VIP và không kéo dài thời hạn VIP.

### Luồng UP tin

- Người dùng mua gói lượt UP trong Ví điện tử. Các gói mặc định gồm 60, 150, 500, 750 và 1.500 lượt.
- Mỗi lần UP thành công trừ đúng 1 lượt trong `nguoi_dung.luot_up_tin`.
- Chỉ tin thuộc người dùng và có trạng thái `xuat_ban` mới được UP.
- Hệ thống cập nhật `du_an.ngay_lam_moi`, ghi một dòng vào `lich_su_up_tin`, tạo thông báo và làm mới cache dashboard.
- Các bước trừ lượt, cập nhật tin và ghi lịch sử chạy trong transaction; nếu có lỗi thì giao dịch được rollback.
- Tổng số lần UP không được cộng dồn thành điểm xếp hạng. Lần UP gần nhất mới là thời điểm làm mới hiện hành.

Thứ tự nghiệp vụ mong muốn trên trang công khai là cấp VIP giảm dần, sau đó thời gian UP gần nhất, rồi ngày đăng. **Giới hạn hiện tại:** danh sách quản lý thành viên đã dùng `ngay_lam_moi`, nhưng một số truy vấn danh sách công khai vẫn sắp xếp theo cấp VIP và `ngay_tao`; cần đồng bộ các truy vấn này trước khi xem quyền lợi “đẩy lên đầu trang công khai” là hoàn chỉnh.

### Xác thực email và OTP

- Tài khoản chưa xác thực sẽ được yêu cầu nhận OTP qua email.
- Người dùng nhập OTP trong popup xác thực dùng chung; OTP hợp lệ hoàn tất xác thực và cho phép tiếp tục đăng nhập.
- Luồng quên mật khẩu sử dụng cùng kiểu giao diện OTP nhưng vẫn tách mục đích xác thực phía backend.
- OTP có thời hạn và giới hạn gửi lại; không ghi mã OTP vào log hoặc hiển thị công khai.

### Quản lý danh mục

Danh mục được dùng để phân loại tin bất động sản. Không xóa danh mục đang có tin liên kết; nên đổi trạng thái hoặc chuyển tin sang danh mục khác trước.

### Báo cáo

Vào **Báo cáo**, chọn khoảng thời gian và bộ lọc rồi nhấn **Áp dụng**. Hệ thống hỗ trợ:

- Excel: báo cáo có tiêu đề, bảng số liệu và phần trình ký.
- PDF: báo cáo độc lập theo mẫu trình ký, không phải ảnh chụp màn hình.

Kiểm tra dữ liệu và người lập biểu trước khi gửi báo cáo.

### Cài đặt hệ thống

- Thông tin website và liên hệ.
- Cấu hình SMTP gửi email.
- SEO và mã đo lường.
- PayOS và Google Maps.
- Giới hạn tải ảnh.
- Xóa cache và tạo bản sao lưu.

Các trường khóa bí mật sẽ được che khi đã lưu. Để trống nếu không muốn thay khóa hiện tại.

## Cấu hình các dịch vụ

### Gemini AI Chatbot

Đặt API key ở `legacy/.env`:

```env
GEMINI_API_KEY=your_server_side_key
GEMINI_MODEL=gemini-2.5-flash
```

Chatbot gọi Gemini thông qua backend proxy; trình duyệt không được gọi trực tiếp Gemini API. Nếu chatbot báo gián đoạn:

- Kiểm tra API key còn hiệu lực và còn hạn mức.
- Kiểm tra PHP extension `curl`.
- Kiểm tra máy chủ có kết nối Internet.
- Xem lỗi trong `legacy/logs/php_error.log`.

### PayOS

Có thể cấu hình trong `legacy/.env` hoặc trang quản trị:

```env
PAYOS_CLIENT_ID=
PAYOS_API_KEY=
PAYOS_CHECKSUM_KEY=
PAYOS_ENDPOINT=https://api-merchant.payos.vn
```

Sau khi lưu trong trang quản trị, dùng nút **Kiểm tra kết nối PayOS**. URL trả về và webhook phải đúng domain đang chạy; môi trường production cần HTTPS.

### Google Maps và đăng nhập mạng xã hội

Các biến OAuth được hỗ trợ trong `legacy/.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/CITC_BDS/nguoi-dung/google-callback

FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
FACEBOOK_REDIRECT_URI=http://localhost/CITC_BDS/nguoi-dung/facebook-callback
```

Google Maps API key có thể nhập trong **Quản trị → Cài đặt → Thanh toán & bản đồ**. Callback trên Google/Facebook Console phải trùng tuyệt đối với URL cấu hình.

### Email SMTP

Cấu hình SMTP tại trang **Cài đặt** và lưu trước khi nhấn gửi thử. Nếu không gửi được, kiểm tra host, cổng, chế độ mã hóa, tài khoản, mật khẩu ứng dụng và firewall.

## Cấu trúc dự án

```text
CITC_BDS/
├── index.php                  Entry point tương thích, chuyển sang ứng dụng legacy
├── .htaccess                 Rewrite URL và chặn truy cập file nhạy cảm
├── legacy/                   Ứng dụng PHP MVC đang chạy chính
│   ├── app/
│   │   ├── controllers/      Nhận request và điều phối xử lý
│   │   ├── services/         Nghiệp vụ
│   │   ├── repositories/     Truy vấn dữ liệu
│   │   ├── validation/       Kiểm tra dữ liệu đầu vào
│   │   └── views/            Giao diện PHP
│   ├── config/               Cấu hình ứng dụng và database
│   ├── core/                 Router, database, session và lớp nền
│   ├── public/               Entry point/asset của ứng dụng legacy
│   ├── storage/              Backup và dữ liệu runtime
│   └── .env                  Cấu hình thực tế của website
├── public/                   Asset và thư mục upload dùng chung
├── app/                      Mã Laravel đang được chuyển đổi/đồng bộ
│   ├── Services/            Các lớp xử lý nghiệp vụ
│   ├── Http/
│   │   ├── Controllers/     Controller web, quản trị và thành viên
│   │   └── Middleware/      Middleware xác thực và phân quyền
│   ├── Repositories/        Repository truy cập dữ liệu
│   ├── Models/              Model dữ liệu
│   ├── Providers/           Service provider của ứng dụng
│   └── Helpers/             Hàm hỗ trợ dùng chung
├── routes/, resources/      Route và giao diện Laravel
├── database/                 Migration và model dữ liệu Laravel
└── tests/                    Kiểm thử tự động
```

Khi sửa chức năng đang hiển thị trên website, ưu tiên kiểm tra file tương ứng trong `legacy/`. Một số view được duy trì đồng thời trong `resources/views/` để phục vụ quá trình chuyển đổi Laravel.

## Dành cho lập trình viên

Phần Laravel vẫn có thể được cài dependency để phát triển và kiểm thử:

```powershell
composer install
npm install
npm run build
```

Các lệnh kiểm tra:

```powershell
composer test
vendor\bin\pint --test
php test_syntax.php
```

Không dùng `php artisan migrate` lên database vận hành của PHP MVC nếu chưa đối chiếu schema và tạo backup.

## Xử lý lỗi thường gặp

### Trang trắng hoặc “Hệ thống đang gặp sự cố”

1. Mở `legacy/logs/php_error.log`.
2. Kiểm tra Apache và MySQL đang chạy.
3. Kiểm tra thông tin DB trong `legacy/.env`.
4. Kiểm tra PHP version và extension `pdo_mysql`.
5. Không bật hiển thị lỗi chi tiết trên website production.

### URL con báo 404

- Bật `mod_rewrite` trong Apache.
- Cho phép `.htaccess` bằng `AllowOverride All`.
- Kiểm tra `URL_ROOT` đúng thư mục/domain.
- Khởi động lại Apache sau khi đổi cấu hình.

### Giao diện mất CSS, ảnh hoặc icon

- Kiểm tra `URL_ROOT`.
- Xác nhận các thư mục `public/css`, `public/js`, `public/images` tồn tại.
- Nhấn `Ctrl + F5` để bỏ cache trình duyệt.
- Kiểm tra quyền đọc file của Apache.

### Không tải được ảnh

- Kiểm tra quyền ghi `public/uploads/`.
- Kiểm tra `upload_max_filesize` và `post_max_size` trong `php.ini`.
- Khởi động lại Apache sau khi sửa `php.ini`.
- Kiểm tra định dạng và dung lượng cho phép trong trang Cài đặt.

### Không kết nối database

- Xác nhận MySQL đang chạy và database `citcbds` tồn tại.
- Thử đăng nhập database bằng đúng tài khoản trong `.env`.
- Với Laragon mặc định, thường dùng `DB_USER=root` và mật khẩu trống.
- Kiểm tra tên bảng/dữ liệu đã được import đầy đủ.

### Thay cấu hình nhưng website chưa cập nhật

- Tải lại mạnh bằng `Ctrl + F5`.
- Vào **Quản trị → Cài đặt → Quản lý Cache** và xóa cache cấu hình/view.
- Đăng xuất rồi đăng nhập lại nếu thay đổi liên quan quyền hoặc phiên đăng nhập.

## Bảo mật và triển khai

- Không commit `.env`, API key, mật khẩu, file SQL, log hoặc dữ liệu khách hàng.
- Thu hồi ngay mọi API key từng xuất hiện trong ảnh chụp, chat hoặc kho mã công khai.
- Production phải dùng `APP_DEBUG=false` và HTTPS.
- Đổi toàn bộ tài khoản/mật khẩu mặc định trước khi đưa lên Internet.
- Document root nên giới hạn ở vùng public hoặc giữ đầy đủ quy tắc chặn trong `.htaccess` gốc.
- Không cho phép truy cập HTTP vào `legacy/`, `storage/`, `vendor/`, file `.env`, `.sql` và `.log`.
- Sao lưu database và file upload định kỳ; thử phục hồi backup trước khi coi đó là bản sao lưu hợp lệ.
- Giới hạn quyền ghi của web server chỉ vào các thư mục runtime cần thiết.
- Theo dõi `legacy/logs/php_error.log` và nhật ký quản trị để phát hiện lỗi/bất thường.

## Quy trình cập nhật an toàn

1. Sao lưu database và file upload.
2. Thử thay đổi trên bản local/staging.
3. Kiểm tra đăng nhập, đăng tin, upload ảnh, thanh toán và báo cáo.
4. Chép mã nguồn mới, không ghi đè `.env` production.
5. Áp dụng migration cần thiết đúng một lần.
6. Xóa cache và kiểm tra log sau khi triển khai.

## Giấy phép

Dự án chưa có giấy phép phân phối riêng. Cần xin phép chủ sở hữu trước khi sao chép, phân phối hoặc sử dụng cho mục đích thương mại.
