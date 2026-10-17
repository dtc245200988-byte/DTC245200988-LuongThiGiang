# Website Tin tức / Cổng thông tin

Đề tài 19 - DTC245200988

## 1. Giới thiệu

Đây là hệ thống Website Tin tức / Cổng thông tin được xây dựng bằng PHP và MySQL, triển khai bằng Docker Compose trên Ubuntu.

Hệ thống hỗ trợ:

- Đăng ký, đăng nhập
- Phân quyền người dùng
- CRUD bài viết
- Quản lý danh mục
- Tìm kiếm bài viết
- Upload ảnh bài viết
- Bình luận bài viết
- Trang quản trị
- Nginx Reverse Proxy
- Prometheus Monitoring
- Grafana Dashboard
- Loki + Promtail Logging
- Hardening cơ bản

## 2. Công nghệ sử dụng

- PHP 8.2
- MySQL 8.0
- phpMyAdmin
- Docker
- Docker Compose
- Nginx
- Prometheus
- Node Exporter
- cAdvisor
- MySQL Exporter
- Grafana
- Loki
- Promtail
- Ubuntu Linux

## 3. Kiến trúc hệ thống

```text
Client
  |
  v
Nginx :80
  |
  v
PHP Web Container
  |
  v
MySQL Container

Monitoring:
Prometheus
 ├── Node Exporter
 ├── cAdvisor
 └── MySQL Exporter
       |
       v
    Grafana

Logging:
Docker Logs
   |
Promtail
   |
Loki
   |
Grafana
