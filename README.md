# DTC245200988 — Đề 19: Website Tin tức / Cổng thông tin (ICTU NEWS)

**Môn học:** Triển khai và Quản trị Hệ thống Phần mềm
**Sinh viên:** Lường Thị Giang — **MSSV:** DTC245200988 — **Lớp:** CNTT K23E
**Giải pháp:** PHP 8.3 + MySQL 8 + phpMyAdmin + Nginx reverse proxy + Prometheus + Grafana + Loki + Promtail + exporters, triển khai bằng Docker Compose.

## 1. Chức năng website

- Trang chủ tin tức responsive; các mục Công nghệ, Giáo dục, Khoa học, Đời sống, Sự kiện.
- Tìm kiếm bài viết; xem bài chi tiết; bài nháp không công khai.
- Đăng nhập admin bằng mật khẩu băm; thêm/sửa/xóa/ẩn bài viết; thêm danh mục và xóa khi không còn bài viết liên quan.
- Tự tạo database schema và 5 bài viết mẫu lúc khởi động lần đầu.
- PHP PDO prepared statements, output HTML escaping, CSRF token, session httpOnly, network nội bộ, non-root PHP & Nginx.

## 2. Môi trường

Ubuntu 22.04/24.04, Docker Engine + Docker Compose v2, RAM khuyến nghị >= 4 GB, tối thiểu ~ 6 GB dung lượng trống. Docker trên Linux sử dụng `json-file` logging driver mặc định để Promtail đọc file log. Một số thiết lập cAdvisor và Promtail truy cập Docker host cần được quản trị viên chấp thuận, không phải container nào cũng có thể non-root. Kiểm tra cổng 8088, 8081, 3000, 9090 và 3100.

## 3. Cài đặt

```bash
unzip DTC245200988_De19_Website_Tin_Tuc.zip
cd news_portal_project
cp .env.example .env
nano .env
```

**QUAN TRỌNG:** Thay toàn bộ giá trị `CHANGE_ME...` trong `.env` bằng mật khẩu mạnh và không tải `.env` lên GitHub. `ADMIN_EMAIL` / `ADMIN_PASSWORD` chỉ được dùng để tạo tài khoản lần đầu khi bảng admin chưa có dữ liệu.

```bash
docker compose config
docker compose up -d --build
docker compose ps
```

Chờ MySQL healthy. Để bật metrics của MySQL exporter, tạo account metrics ít quyền (một lần và sau mỗi lần xóa DB volume):

```bash
chmod +x scripts/*.sh
./scripts/setup-exporter.sh
docker compose restart mysql-exporter
```

Khi dùng PowerShell Windows, nên chạy các lệnh shell trên bên trong Ubuntu VM. Các service đều chạy trong Docker Compose.

## 4. Địa chỉ truy cập

| Dịch vụ | URL trên máy Ubuntu | Vai trò |
|---|---|---|
| Website qua Nginx | http://localhost:8088 | Xem tin / quản trị |
| Trang quản trị | http://localhost:8088/?p=login | Tài khoản trong `.env` |
| phpMyAdmin | http://127.0.0.1:8082 | DB: `news_user`, mật khẩu MYSQL_PASSWORD |
| Grafana | http://127.0.0.1:3001 | GRAFANA_USER / GRAFANA_PASSWORD |
| Prometheus | http://127.0.0.1:9091 | Targets và PromQL |
| Loki status | http://127.0.0.1:3100/ready | Log store |

Các dịch vụ quản trị/monitoring được bind vào `127.0.0.1` để không công khai ra mạng LAN. Truy cập từ Windows vào VM qua SSH tunnel, ví dụ:

```bash
ssh -L 8081:127.0.0.1:8081 -L 3000:127.0.0.1:3000 -L 9090:127.0.0.1:9090 -L 3100:127.0.0.1:3100 ubuntu@IP_CUA_MAY_AO
```

Sau đó mở `http://localhost:3001` trên Windows. Với website dùng `http://IP_CUA_MAY_AO:8088` nếu mạng và UFW cho phép.

## 5. Sơ đồ kiến trúc

```text
Browser --> Nginx (public :8088) --> PHP CMS (non-root) --> MySQL (internal network)
                                     phpMyAdmin ------------> MySQL
Prometheus <-- cAdvisor (container metrics)
Prometheus <-- Nginx exporter <-- Nginx /nginx_status
Prometheus <-- MySQL exporter <-- MySQL (user chỉ giám sát)
Grafana --> Prometheus
Grafana --> Loki <-- Promtail <-- Docker container logs
```

Grafana tự provision `Prometheus` và `Loki` datasource, và dashboard **News Portal Overview**. Mở Grafana > Dashboards > News Portal và kiểm tra các panel.

Prometheus > Status > Targets: các job `prometheus`, `cadvisor`, `nginx`, `mysql` nên UP (exporter MySQL chỉ UP khi đã tạo account bằng script trên). Query gợi ý: `up`, `nginx_connections_active`, `mysql_up`, `rate(container_cpu_usage_seconds_total[5m])`.

## 6. LogQL (Commit 3)

Grafana > Explore > Loki. Chi tiết ví dụ trong [`docs/LOGQL.md`](docs/LOGQL.md):

```logql
{container=~".*nginx.*"}
{container=~".*web.*"} |~ "(ERROR|Exception|Failed|PHP Warning)"
sum by (container) (count_over_time({container=~".+"}[5m]))
```

Nếu không thấy log, xem `docker compose logs promtail`, kiểm tra Docker logging driver đang là `json-file` và quyền đọc các file `/var/lib/docker/containers`. Các nhãn `container` lấy từ Docker service discovery.

## 7. Hardening & hạn chế

- PHP chạy `www-data` non-root; Nginx dùng image unprivileged lắng nghe 8080. Hạn chế Linux capabilities và no-new-privileges.
- MySQL không public cổng 3306. Frontend/backend/monitoring là các mạng riêng; `backend` có `internal: true`.
- Mật khẩu nằm trong file `.env` được `.gitignore`, không chứa secret trong repo.
- Nginx thêm X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.
- Prepared statements, CSRF protection, escaping và password hashing.
- Grafana, phpMyAdmin, Prometheus, Loki bind localhost; muốn truy cập từ bên ngoài sử dụng SSH tunnel.
- **Không triển khai HTTPS trong bản demo HTTP này** vì yêu cầu cho phép sử dụng security headers thay thế. Nếu đưa lên Internet cần TLS chuẩn, cookie secure, xác thực bổ sung và reverse proxy cho giao diện quản trị.
- MySQL, phpMyAdmin, cAdvisor, Promtail là image bên thứ ba có quyền runtime cần thiết; không khẳng định tất cả container là non-root. Promtail được mount Docker socket read-only (Docker socket vẫn là tài nguyên nhạy cảm). Hạn chế triển khai trên host đáng tin cậy.
- Đây là hệ thống thực hành; không có chống brute-force phân tán, không có luồng thay đổi mật khẩu admin trong UI, không thay thế CMS production.

## 8. Kiểm tra và minh chứng báo cáo

```bash
docker compose ps
curl -I http://localhost:8088
curl http://localhost:8088/health
curl -I http://localhost:8088/?p=login
docker compose logs --tail=50 nginx web mysql promtail
docker compose exec web id
```

Chụp màn hình: website, trang danh mục, đăng nhập + CRUD bài viết, phpMyAdmin (`posts`, `categories`, `admins`), HTTP response headers Nginx, Prometheus Targets, Grafana dashboard, Grafana Explore + 3 LogQL queries, `docker compose ps`, network isolation, non-root process, GitHub repo + lịch sử commit. Dùng bằng chứng *thực tế của VM*, không sử dụng ảnh giả lập làm kết quả đã chạy.

## 9. Lộ trình GitHub / commit bắt buộc

Tạo repository theo MSSV, ví dụ `DTC245200988-news-portal`.

```bash
git init
git add .
git commit -m "Commit 1: Website tin tuc MySQL phpMyAdmin Nginx security headers"
git remote add origin https://github.com/USERNAME/DTC245200988-news-portal.git
git branch -M main
git push -u origin main
```

Để có **ba commit đúng tiến trình thực hành**, nên thực hiện lần lượt trên repo từ lúc triển khai: commit 1 (website + DB + Nginx), commit 2 (bổ sung Prometheus + Grafana), commit 3 (bổ sung Loki + Promtail), và commit bổ sung hardening/docs nếu cần. ZIP này chứa **toàn bộ mã nguồn bản cuối**, không chứa lịch sử Git thật; một lần commit toàn bộ ZIP sẽ **không** đáp ứng tiêu chí ba commit có ý nghĩa. Không giả lập lịch sử Git hoặc ảnh minh chứng.

## 10. Dừng, sao lưu

```bash
docker compose down                 # Giữ dữ liệu
# docker compose down -v           # XÓA VĨNH VIỄN các volumes - cẩn thận
docker compose logs -f web
```

**Lưu ý vận hành:** Cần Docker Engine thực tế để kiểm chứng container, các dashboard và ảnh báo cáo. Không kiểm chứng bằng công cụ chạy website đơn lẻ.
