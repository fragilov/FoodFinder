---
trigger: always_on
---

# Quy Tắc Tự Động Push Git Sau Mỗi Lần Sửa Đổi (Auto Git Push)

Mỗi khi Agent hoàn thành công việc chỉnh sửa code, sửa lỗi giao diện, tối ưu font chữ, thêm tính năng hoặc refactor:

1. Chạy `git status` kiểm tra các file đã thay đổi.
2. Chạy `git add .` để stage tất cả thay đổi hợp lệ.
3. Chạy `git commit -m "<mô tả nội dung thay đổi>"` theo quy chuẩn Conventional Commits.
4. Chạy `git push` để đẩy code lên GitHub.
5. Luôn phản hồi thông báo commit hash và kết quả push cho người dùng.
