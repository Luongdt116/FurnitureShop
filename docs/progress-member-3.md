# Tiến độ thành viên 3

Cập nhật: 26/11/2026

## Trạng thái

| Mục | Trạng thái | Ghi chú |
| --- | --- | --- |
| 1. Nhận layout và thống nhất route contract | Hoàn thành | Route contract và quy ước giao diện được ghi tại `route-contract-member-1.md`. |
| 2. Đơn hàng khách | Đã triển khai | Danh sách/chi tiết đơn, snapshot sản phẩm và giá, địa chỉ, payment, lịch sử, tracking, hủy có lý do và thời hạn thanh toán. Timeout xử lý bằng scheduler và `CancellationService`. |
| 3. Đổi trả, hoàn tiền, đánh giá | Đã triển khai | Có thể tạo nhiều yêu cầu trên một đơn; kiểm tra quyền sở hữu và số lượng; hỗ trợ lý do, ảnh bằng chứng, trạng thái/phản hồi admin; đánh giá gắn với đơn đã giao. `refund_amount` được yêu cầu khi hoàn tất loại refund. |
| 4. Admin đơn hàng và người dùng | Đã triển khai | Có lọc/tìm kiếm/phân trang, chi tiết đơn, payment, trạng thái, tracking, lịch sử; quản lý role/trạng thái người dùng với middleware admin. |
| 5. Chatbot | Chưa triển khai | Chưa bắt đầu trong phạm vi công việc hiện tại. |

## Tồn kho đổi trả

- Không thay đổi tồn khi gửi, duyệt, nhận hoặc kiểm tra yêu cầu.
- Khi admin chuyển yêu cầu từ `inspected` sang `completed`, `ReturnInventoryService` cộng số lượng được chấp nhận vào tồn kho và ghi biến động `return_restocked` theo từng dòng yêu cầu.
- Migration `2026_11_26_000002_add_return_restock_movement_support` đã được chạy thành công trong Docker. Khóa duy nhất theo `return_request_item_id` giúp ngăn ghi biến động lặp cho cùng dòng đổi trả.
- `CancellationService` hiện không tự thay đổi tồn kho; phần nhập lại kho do hủy đơn cần tích hợp riêng.

## Kiểm tra và giới hạn hiện tại

- Đã kiểm tra cú pháp PHP và `git diff --check`.
- Đã xác nhận migration ở trạng thái `Ran` và route `admin.returns.update` được bảo vệ bởi `auth` và `admin`.
- Chưa chạy kiểm thử tự động hoặc kiểm tra toàn bộ luồng giao diện end-to-end.
- Hoàn tiền hiện được ghi nhận bằng trạng thái và `refund_amount`; chưa tích hợp lệnh hoàn tiền qua cổng thanh toán.
