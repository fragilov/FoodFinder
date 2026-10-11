# Kế Hoạch & Tổng Quan Dự Án Food Finder 🍜

> **Dự án**: Food Finder — Ứng dụng web tìm kiếm quán ăn thông minh quanh vị trí người dùng.  
> **Khu vực dữ liệu mẫu**: Quanh Trường Đại học Tôn Đức Thắng (TDTU), Quận 7, TP. Hồ Chí Minh.  
> **Cập nhật ngày**: 11/10/2026.

---

## 1. TỔNG QUAN DỰ ÁN
Food Finder là nền tảng bản đồ ẩm thực tương tác giúp người dùng dễ dàng tìm kiếm, lọc và định vị các địa điểm ăn uống xung quanh vị trí thực tế của mình. Hệ thống cung cấp giải pháp trọn vẹn từ trải nghiệm tìm kiếm của thực khách (Client-side) cho tới hệ thống quản trị dữ liệu tập trung (Admin CMS).

---

## 2. CÔNG NGHỆ HIỆN ĐANG SỬ DỤNG (TECH STACK)

### 2.1. Frontend (Giao diện & Trải nghiệm người dùng)
* **Ngôn ngữ cốt lõi**:
  * **HTML5 Semantic**: Cấu trúc chuẩn SEO, thân thiện trợ năng (Accessibility/ARIA).
  * **CSS3 Hiện đại**: 
    * Thiết kế hệ thống biến màu sắc, typography theo token (`tokens.css`).
    * Phong cách đồ họa hiện đại Neo-Brutalism kết hợp Glassmorphism thanh lịch (`brutal.css`, `layout.css`).
    * Hỗ trợ giao diện Sáng / Tối (Light / Dark Mode) linh hoạt.
  * **Vanilla JavaScript (ES6+)**:
    * Kiến trúc ES Modules (`type="module"`), phân chia module chức năng độc lập (`main.js`, `explore.js`, `restaurant.js`, `sheet.js`, `map.js`...).
* **Bản đồ & Định vị không gian (Mapping & Geolocation)**:
  * **Leaflet.js (v1.9.4)**: Thư viện bản đồ tương tác mã nguồn mở nhẹ, hiệu năng cao.
  * **Leaflet.markercluster (v1.5.3)**: Tự động gom cụm các địa điểm khi thu nhỏ bản đồ, tránh giật lag khi có nhiều dữ liệu.
  * **OpenStreetMap Tiles**: Dịch vụ bản đồ nền tiêu chuẩn miễn phí, không watermark.
  * **HTML5 Geolocation API**: Tự động xác định toạ độ GPS của người dùng với fallback mượt mà về toạ độ mẫu TDTU Quận 7.
* **Typography & Icons**:
  * Font chữ **Be Vietnam Pro** (hỗ trợ 100% tiếng Việt có dấu, chuẩn mực nét chữ) kết hợp **Plus Jakarta Sans**.
  * Bộ icon vector SVG nội bộ dạng inline (`icons.js`), không bị phụ thuộc tài nguyên bên ngoài.
* **Lưu trữ & Fallback dữ liệu**:
  * **LocalStorage**: Lưu danh sách quán yêu thích (`favorites`) và chế độ nền (`food-finder-theme`).
  * **Mock Data Fallback (`mock-data.js`)**: Tự động nhận diện khi người dùng mở trực tiếp file HTML hoặc khi backend PHP chưa khởi chạy để vẫn có thể trải nghiệm xem trước đầy đủ.

### 2.2. Backend (Xử lý máy chủ & RESTful API)
* **Môi trường & Ngôn ngữ**:
  * **PHP 8.1+** (Strict Types `declare(strict_types=1)`).
  * Kiến trúc RESTful API nhẹ, không sử dụng framework nặng nề cồng kềnh giúp tối ưu tốc độ phản hồi.
* **Thuật toán Không gian Địa lý (Geospatial Computation)**:
  * Thuật toán **Bounding Box** (`geo.php`): Giảm tải phạm vi tìm kiếm kinh độ/vĩ độ trước khi tính toán chuyên sâu.
  * Công thức **Haversine Distance**: Tính toán khoảng cách chính xác theo đường cong mặt cầu Trái Đất ($R = 6371.0088\text{ km}$).
  * Bộ lọc giờ mở cửa thực tế (`is_open_now`): Hỗ trợ quán mở 24/7 và quán mở xuyên đêm qua ngày hôm sau.
* **Thuật toán Gợi ý ngẫu nhiên ("Hôm nay ăn gì?")**:
  * **Weighted Random Sampling**: Quán ăn có điểm đánh giá cao và khoảng cách gần hơn sẽ có tỷ lệ được chọn cao hơn dựa trên hàm trọng số phân rã khoảng cách:
    $$W = \left(0.3 + \frac{\text{rating}}{5}\right) \times e^{-\frac{\text{distance}}{1.5}}$$
  * Cơ chế loại trừ quán vừa quay (`exclude`) và giới hạn tần suất thao tác (Rate Limit chống spam 30 requests/phút).
* **Bảo mật & Phiên làm việc (Security & Session)**:
  * **Secure Sessions**: Cờ HttpOnly, SameSite=Lax, tự động kích hoạt Secure flag khi chạy qua HTTPS.
  * **Bảo vệ Brute-Force**: Ghi nhận nhật ký đăng nhập sai theo địa chỉ IP dạng nhị phân (`inet_pton`), khóa tạm thời nếu nhập sai quá 5 lần trong 15 phút.
  * **Chống CSRF**: Kiểm tra token ngẫu nhiên `bin2hex(random_bytes(32))` ở tất cả các biểu mẫu gửi dữ liệu.
  * **Xác thực mật khẩu**: Sử dụng chuẩn `password_hash()` và `password_verify()`.
  * **Upload an toàn**: Kiểm tra MIME type thực tế thông qua Fileinfo (`finfo`), sinh tên tệp ngẫu nhiên chống thực thi script trái phép.

### 2.3. Cơ sở dữ liệu (Database)
* **Hệ quản trị CSDL**: **MySQL / MariaDB** (Default Charset: `utf8mb4_unicode_ci`).
* **Các bảng dữ liệu chuẩn hoá**:
  1. `categories`: Danh mục ẩm thực (Cơm, Bún/Phở, Bánh mì, Lẩu nướng, Ăn vặt, Chay, Cà phê...) kèm icon, slug, thứ tự sắp xếp.
  2. `restaurants`: Quán ăn (tên, địa chỉ, số điện thoại, lat/lng, khoảng giá, rating, ảnh, giờ hoạt động, trạng thái). Đã đánh chỉ mục địa lý `idx_geo (latitude, longitude)`, `idx_active_cat`, `idx_rating` và tìm kiếm văn bản `FULLTEXT KEY ft_name_address`.
  3. `restaurant_images`: Album nhiều hình ảnh chi tiết cho từng quán ăn (quan hệ 1-N, cascade delete).
  4. `admins`: Tài khoản quản trị viên.
  5. `login_attempts`: Nhật ký các lần đăng nhập sai theo IP để phòng chống brute-force.
  6. `reviews`: Đánh giá của thực khách (số sao, nhận xét, kiểm duyệt duyệt/ẩn).

---

## 3. CÁC TÍNH NĂNG ĐÃ TRIỂN KHAI

### 3.1. Phía Khách Hàng / Người Dùng (Client App)
1. **Bản đồ ẩm thực trực quan (`index.html`)**:
   * Định vị vị trí hiện tại của người dùng.
   * Hiển thị quán ăn theo bán kính (500m, 1km, 2km, 3km, 5km, 10km).
   * Lọc đa chiều: Theo danh mục món ăn, mức giá, số sao tối thiểu, quán đang mở cửa ngay bây giờ.
   * Giao diện Responsive: Desktop có Sidebar thông tin; Mobile có Bottom Sheet vuốt chạm mượt mà.
   * Đổi giao diện Sáng / Tối (Dark / Light).
2. **Tính năng "Hôm nay ăn gì?" (Randomizer)**:
   * Nút nổi FAB truy cập nhanh.
   * Vòng quay gợi ý quán ăn ngẫu nhiên có trọng số thông minh.
   * Nút "Chỉ đường", "Chọn lại" và "Đóng".
3. **Khám phá quán ăn dạng lưới (`explore.html`)**:
   * Xem danh sách quán dưới dạng lưới thẻ (Grid card).
   * Tìm kiếm tức thì theo từ khóa tên hoặc địa chỉ.
   * Bộ lọc nhanh và sắp xếp theo Đánh giá cao / Giá rẻ nhất / Tên quán.
4. **Trang chi tiết quán ăn (`restaurant.html`)**:
   * Hiển thị đầy đủ thông tin: Địa chỉ, khoảng giá, số điện thoại, giờ hoạt động thực tế (bật đèn báo Đang mở / Đã đóng).
   * Nút gọi điện nhanh (`tel:`), nút chia sẻ (Web Share API).
   * Nút dẫn đường thông minh: Tự động điều hướng sang Google Maps hoặc Apple Maps theo thiết bị.
   * Bộ sưu tập hình ảnh món ăn (Gallery).
   * Xem đánh giá, xếp hạng sao và nhận xét của thực khách.
   * Nút "Lưu quán yêu thích" đánh dấu trái tim.
5. **Trang quán ăn đã lưu (`favorites.html`)**:
   * Quản lý các quán yêu thích được lưu cục bộ trên thiết bị của người dùng (LocalStorage).
6. **Trang đề xuất quán ăn (`suggest.html`)**:
   * Biểu mẫu cộng đồng chia sẻ quán ăn mới.
   * Tích hợp bản đồ mini cho phép người dùng chọn điểm ghim toạ độ trực tiếp.
7. **Trang giới thiệu (`about.html`)**:
   * Giới thiệu thông tin nền tảng và định hướng dự án.

### 3.2. Phía Quản Trị Viên (Admin CMS - `public/admin/`)
1. **Bảo mật & Đăng nhập**: Form đăng nhập chống brute-force IP và bảo vệ session.
2. **Bảng điều khiển KPI (`dashboard.php`)**:
   * Thống kê tổng số quán, số quán active, số danh mục, rating trung bình, tổng số đánh giá.
   * Biểu đồ tỷ trọng phân bổ quán ăn theo danh mục.
   * Top 5 quán ăn được đánh giá cao nhất.
   * 5 quán ăn được cập nhật gần nhất.
3. **Quản lý quán ăn (CRUD Quán ăn)**:
   * Xem danh sách quán ăn, phân trang, tìm kiếm.
   * Thêm mới / Cập nhật quán ăn kèm toạ độ GPS, giờ mở cửa, upload ảnh đại diện.
   * Bật/tắt trạng thái hoạt động hoặc xóa quán.
4. **Quản lý danh mục (`categories.php`)**:
   * Thêm, sửa, xóa danh mục món ăn, phân bổ icon đại diện và thứ tự ưu tiên.
5. **Quản lý đánh giá (`reviews.php`)**:
   * Kiểm duyệt các nhận xét, đánh giá của thực khách (Duyệt hiển thị, Ẩn vi phạm, Xoá đánh giá).

---

## 4. KẾ HOẠCH HÀNH ĐỘNG & LỘ TRÌNH PHÁT TRIỂN (PROJECT PLAN)

Dưới đây là kế hoạch hành động phân theo 4 giai đoạn rõ ràng:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        LỘ TRÌNH PHÁT TRIỂN FOOD FINDER                 │
└────────────────────────────────────────────────────────────────────────┘
  Giai đoạn 1: Ổn định & Kiểm thử toàn diện (Testing & Stability)
       │
  Giai đoạn 2: Kết nối dữ liệu & Tính năng tương tác (Feature Completion)
       │
  Giai đoạn 3: Nâng cao trải nghiệm & Tối ưu di động (UX & PWA)
       │
  Giai đoạn 4: Hoàn thiện báo cáo, Triển khai & Đóng gói (Deployment)
```

### 🎯 Giai đoạn 1: Ổn định & Kiểm thử toàn diện (Testing & Stability)
* [ ] **1.1. Kiểm thử môi trường cơ sở dữ liệu**:
  * Kiểm tra tính toàn vẹn của dữ liệu mẫu (`seed_restaurants.sql`, `seed_categories.sql`).
  * Xác thực toạ độ các quán ăn trên Google Maps đảm bảo tính chuẩn xác khi điều hướng.
* [ ] **1.2. Kiểm thử bảo mật & API**:
  * Kiểm tra các kịch bản SQL Injection, XSS, upload file độc hại vào thư mục uploads.
  * Kiểm tra cơ chế Rate-limit trên API random và Brute-force block trên trang đăng nhập Admin.
* [ ] **1.3. Kiểm thử tương thích đa nền tảng**:
  * Kiểm tra hiển thị responsive trên các kích thước màn hình: Mobile (iPhone, Android), Tablet, Desktop.
  * Kiểm tra hành vi Geolocation khi người dùng cho phép / từ chối cấp quyền vị trí.

### 🚀 Giai đoạn 2: Kết nối dữ liệu & Tính năng tương tác (Feature Completion)
* [ ] **2.1. Hoàn thiện luồng Gửi Đánh giá (Review Submission)**:
  * Tạo endpoint API `POST /api/review.php` cho phép thực khách gửi bình luận và chấm điểm sao từ trang chi tiết `restaurant.html`.
  * Tự động tính toán lại `rating` và `rating_count` trung bình của quán sau khi đánh giá được duyệt trong Admin.
* [ ] **2.2. Hoàn thiện luồng Đề xuất Quán mới (`suggest.html`)**:
  * Tạo API lưu thông tin quán đề xuất từ form cộng đồng vào hàng đợi duyệt (Pending Approval) trong Admin CMS.
  * Bổ sung mục "Quán ăn chờ duyệt" trong Admin Dashboard.
* [ ] **2.3. Bổ sung bộ lọc khoảng cách thời gian thực**:
  * Tự động tính khoảng cách từ vị trí người dùng đến từng quán ăn hiển thị trực tiếp trên danh sách thẻ và popup bản đồ.

### 💎 Giai đoạn 3: Nâng cao trải nghiệm người dùng & PWA (Advanced UX & PWA)
* [ ] **3.1. Hỗ trợ Progressive Web App (PWA)**:
  * Thêm `manifest.json` và Service Worker cơ bản để ứng dụng có thể "Cài đặt vào màn hình chính" (Add to Home Screen) trên điện thoại như một app native.
  * Cache các tài nguyên tĩnh (CSS, JS, Fonts, SVG Icons) để khởi động tức thì.
* [ ] **3.2. Tính năng dẫn đường trực tiếp trên bản đồ (In-app Route Navigation)**:
  * Tích hợp chỉ đường tuyến đường ngắn nhất từ vị trí người dùng tới quán ăn ngay trên nền Leaflet (sử dụng OSRM API miễn phí) bên cạnh nút mở Google Maps ngoài.
* [ ] **3.3. Chia sẻ vị trí & Quán ăn**:
  * Tạo mã QR Code cho từng quán ăn để quét nhanh trên thiết bị di động.

### 📦 Giai đoạn 4: Hoàn thiện báo cáo, Đóng gói & Triển khai (Finalization & Deployment)
* [ ] **4.1. Tối ưu SEO & Hiệu năng**:
  * Tối ưu hóa ảnh WebP, nén asset CSS/JS.
  * Bổ sung thẻ Open Graph (OG Tag) và Twitter Card để chia sẻ trên Facebook/Zalo đẹp mắt.
* [ ] **4.2. Cấu hình triển khai Web Server**:
  * Cung cấp file cấu hình Apache VirtualHost hoặc Nginx template hoàn chỉnh.
  * Cấu hình phân quyền thư mục `uploads/` ngăn chặn thực thi PHP.
* [ ] **4.3. Báo cáo & Tài liệu đồ án**:
  * Hoàn thiện tài liệu kiến trúc hệ thống, sơ đồ quan hệ thực thể ERD, sơ đồ use-case và hướng dẫn cài đặt.
