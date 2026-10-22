# LogQL kiểm thử

Trong Grafana > Explore chọn datasource Loki, chạy lần lượt:

```logql
{container=~".*nginx.*"}
```
```logql
{container=~".*web.*"} |~ "(ERROR|Exception|Failed|PHP Warning)"
```
```logql
sum by (container) (count_over_time({container=~".+"}[5m]))
```

Để sinh log: mở trang chủ, tìm kiếm nhiều từ khoá, thử đăng nhập sai (không ghi password trong log). Nếu query lỗi không có dòng nào, đó có thể do chưa có log lỗi, không phải hỏng hệ thống.
